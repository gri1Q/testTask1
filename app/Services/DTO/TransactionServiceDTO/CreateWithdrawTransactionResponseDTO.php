<?php

declare(strict_types=1);

namespace App\Services\DTO\TransactionServiceDTO;

/**
 * DTO для ответа при создании транзакции списания.
 *
 * @property int $id
 * @property int $userID
 * @property string $type
 * @property float $amount
 * @property string|null $comment
 * @property string $createdAt
 */
readonly class CreateWithdrawTransactionResponseDTO
{
    public function __construct(
        public int $id,
        public int $userID,
        public string $type,
        public float $amount,
        public ?string $comment,
        public string $createdAt
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
            'comment' => $this->comment,
            'createdAt' => $this->createdAt,
        ];
    }
}
