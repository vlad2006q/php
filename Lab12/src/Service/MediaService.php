<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\MediaRepository;
use App\Validation\MediaValidator;

final readonly class MediaService
{
    public function __construct(private MediaRepository $repository) {}

    public function list(string $search = '', string $sort = 'title', int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        return $this->repository->findAll($search, $sort, $perPage, $offset);
    }

    public function get(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function create(array $input): array
    {
        $validated = MediaValidator::validate($input);
        if ($validated['errors'] !== []) {
            return ['id' => null, 'errors' => $validated['errors']];
        }

        $id = $this->repository->create($validated['data']);
        return ['id' => $id, 'errors' => []];
    }

    public function update(int $id, array $input): array
    {
        $validated = MediaValidator::validate($input);
        if ($validated['errors'] !== []) {
            return ['updated' => false, 'errors' => $validated['errors']];
        }

        $updated = $this->repository->update($id, $validated['data']);
        return ['updated' => $updated, 'errors' => []];
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function issue(int $id): void
    {
        $this->repository->issueWithAudit($id);
    }
}
