<?php

declare(strict_types=1);

namespace App\Services\DTO\BalanceServiceDTO;

/**
 * DTO для ответа при получении баланса пользователя.
 *
 * @property-read int $userId
 * @property-read float $balance
 * @property-read string $currency
 */
readonly class BalanceResponseDTO
{
    public function __construct(
        public int $userId,
        public float $balance,
    ) {
    }

    /**
     * Преобразует DTO в массив для ответа контроллера.
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'balance' => $this->balance,
        ];
    }
}
