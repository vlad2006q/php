<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use RuntimeException;
use Throwable;

final readonly class MediaRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO media (catalog_no, title, type, available)
             VALUES (:catalog_no, :title, :type, :available)'
        );
        $stmt->execute([
            'catalog_no' => $data['catalog_no'],
            'title' => $data['title'],
            'type' => $data['type'],
            'available' => $data['available'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, catalog_no, title, type, available, created_at, updated_at
             FROM media WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findAll(
        string $search = '',
        string $sort = 'title',
        int $limit = 20,
        int $offset = 0,
    ): array {
        $allowedSort = ['title', 'catalog_no', 'type', 'available', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'title';
        }

        $sql = "SELECT id, catalog_no, title, type, available, created_at
                FROM media
                WHERE title LIKE :search_title OR catalog_no LIKE :search_code
                ORDER BY $sort ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $pattern = '%' . $search . '%';
        $stmt->bindValue('search_title', $pattern, PDO::PARAM_STR);
        $stmt->bindValue('search_code', $pattern, PDO::PARAM_STR);
        $stmt->bindValue('limit', max(1, min($limit, 100)), PDO::PARAM_INT);
        $stmt->bindValue('offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE media
             SET catalog_no = :catalog_no,
                 title = :title,
                 type = :type,
                 available = :available
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'catalog_no' => $data['catalog_no'],
            'title' => $data['title'],
            'type' => $data['type'],
            'available' => $data['available'],
        ]);

        return $this->find($id) !== null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM media WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() === 1;
    }

    public function issueWithAudit(int $id): void
    {
        try {
            $this->pdo->beginTransaction();

            $update = $this->pdo->prepare(
                'UPDATE media SET available = 0 WHERE id = :id AND available = 1'
            );
            $update->execute(['id' => $id]);

            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Объект не найден или уже выдан');
            }

            $log = $this->pdo->prepare(
                'INSERT INTO audit_log (entity_type, entity_id, action)
                 VALUES (:type, :id, :action)'
            );
            $log->execute([
                'type' => 'media',
                'id' => $id,
                'action' => 'issued',
            ]);

            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
}
