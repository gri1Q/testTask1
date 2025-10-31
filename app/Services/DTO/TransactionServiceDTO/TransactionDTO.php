<?php

declare(strict_types=1);

namespace App\Services\DTO\TransactionServiceDTO;

/**
 * Универсальный DTO для транзакций любого типа.
 *
 * @property int $id
 * @property int $userID
 * @property string $type
 * @property float $amount
 * @property string $createdAt
 * @property int|null $transferId
 */
readonly class TransactionDTO
{
    public function __construct(
        public int $id,
        public int $userID,
        public string $type,
        public float $amount,
        public string $createdAt,
        public ?int $transferId = null
    ) {
    }

    /**
     * Преобразует DTO в массив для ответа контроллера.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userID' => $this->userID,
            'type' => $this->type,
            'amount' => $this->amount,
            'createdAt' => $this->createdAt,
            'transferId' => $this->transferId,
        ];
    }
}
