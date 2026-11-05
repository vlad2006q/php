<?php
declare(strict_types=1);

// === 1. ПРОСТРАНСТВО ИМЕН: ОБЪЕКТЫ ДАННЫХ ===
namespace App\ValueObject {
    final readonly class DeliveryRequest {
        public function __construct(
            public string $address,
            public float $weight,
            public float $declaredValue,
            public string $type
        ) {
            if (trim($address) === '') {
                throw new \InvalidArgumentException('Адрес не может быть пустым.');
            }
            if ($weight <= 0) {
                throw new \InvalidArgumentException('Вес должен быть больше нуля.');
            }
        }
    }

    final readonly class DeliveryCost {
        public function __construct(
            public float $price,
            public int $estimatedDays,
            public string $type
        ) {}
    }
}

// === 2. ПРОСТРАНСТВО ИМЕН: ИСКЛЮЧЕНИЯ ===
namespace App\Exceptions {
    final class AddressException extends \InvalidArgumentException {}
    
    final class CalculationException extends \RuntimeException {
        public function __construct(string $message, public readonly string $type, ?\Throwable $previous = null) {
            parent::__construct($message, 0, $previous);
        }
    }
}

// === 3. ПРОСТРАНСТВО ИМЕН: КОНТРАКТЫ (ИНТЕРФЕЙСЫ) ===
namespace App\Contracts {
    use App\ValueObject\DeliveryRequest;
    use App\ValueObject\DeliveryCost;

    interface DeliveryCalculatorInterface {
        public function supports(DeliveryRequest $request): bool;
        public function calculate(DeliveryRequest $request): DeliveryCost;
    }
}

// === 4. ПРОСТРАНСТВО ИМЕН: ВСПОМОГАТЕЛЬНЫЕ ИНСТРУМЕНТЫ (ТРЕЙТЫ) ===
namespace App\Support {
    trait LogsActivity {
        private array $log = [];
        final protected function log(string $message): void {
            $this->log[] = (new \DateTimeImmutable())->format(\DATE_ATOM) . ' ' . $message;
        }
        final public function logEntries(): array {
            return $this->log;
        }
    }
}

// === 5. ПРОСТРАНСТВО ИМЕН: ЛОГИКА ДОСТАВКИ ===
namespace App\Delivery {
    use App\Contracts\DeliveryCalculatorInterface;
    use App\Exceptions\CalculationException;
    use App\Exceptions\AddressException;
    use App\Support\LogsActivity;
    use App\ValueObject\DeliveryCost;
    use App\ValueObject\DeliveryRequest;

    abstract class AbstractCalculator implements DeliveryCalculatorInterface {
        use LogsActivity;

        final public function calculate(DeliveryRequest $request): DeliveryCost {
            if (!$this->supports($request)) {
                throw new CalculationException('Тип доставки не поддерживается.', $request->type);
            }
            $this->validateAddress($request->address);
            try {
                $cost = $this->doCalculation($request);
                $this->log("Успешно для: " . $request->type);
                return $cost;
            } catch (AddressException $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw new CalculationException('Ошибка расчёта.', $request->type, $e);
            }
        }
        abstract protected function validateAddress(string $address): void;
        abstract protected function doCalculation(DeliveryRequest $request): DeliveryCost;
    }

    final class CourierCalculator extends AbstractCalculator {
        public function supports(DeliveryRequest $request): bool { return $request->type === 'courier'; }
        protected function validateAddress(string $address): void {
            if (str_ireplace('деревня', '', $address) !== $address) {
                throw new \App\Exceptions\AddressException('Курьер не возит в деревни.');
            }
        }
        protected function doCalculation(DeliveryRequest $request): DeliveryCost {
            if ($request->weight > 30) throw new \RuntimeException('Перевес > 30 кг.');
            return new DeliveryCost(1500 + ($request->weight * 200), 3, 'courier');
        }
    }

    final class PickupCalculator extends AbstractCalculator {
        public function supports(DeliveryRequest $request): bool { return $request->type === 'pickup'; }
        protected function validateAddress(string $address): void {
            if (is_numeric($address)) throw new \App\Exceptions\AddressException('Адрес ПВЗ не из одних цифр.');
        }
        protected function doCalculation(DeliveryRequest $request): DeliveryCost {
            return new DeliveryCost(500 + ($request->declaredValue * 0.01), 5, 'pickup');
        }
    }
}

// === 6. ПРОСТРАНСТВО ИМЕН: СЕРВИСЫ ===
namespace App\Services {
    use App\Contracts\DeliveryCalculatorInterface;
    use App\Exceptions\CalculationException;
    use App\ValueObject\DeliveryCost;
    use App\ValueObject\DeliveryRequest;

    final class DeliveryService {
        /** @param list<DeliveryCalculatorInterface> $calculators */
        public function __construct(private array $calculators) {}

        public function getCost(DeliveryRequest $request): DeliveryCost {
            foreach ($this->calculators as $calculator) {
                if ($calculator->supports($request)) {
                    return $calculator->calculate($request);
                }
            }
            throw new CalculationException('Способ доставки не найден.', $request->type);
        }
    }
}
