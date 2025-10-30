<?php

declare(strict_types=1);

namespace App\Services\DTO\TransactionServiceDTO;

/**
 * DTO для ответа при создании перевода
 *
 * @property int $transferID
 * @property int $fromUserID
 * @property int $toUserID
 * @property float $amount
 * @property string|null $comment
 * @property string $transferCreatedAt
 * @property TransactionDTO $outgoingTransaction
 * @property TransactionDTO $incomingTransaction
 */
readonly class CreateTransferTransactionResponseDTO
{
    public function __construct(
        public int $transferID,
        public int $fromUserID,
        public int $toUserID,
        public float $amount,
        public ?string $comment,
        public string $transferCreatedAt,
        public TransactionDTO $outgoingTransaction,
        public TransactionDTO $incomingTransaction
    ) {
    }

    /**
     * Преобразует DTO в массив для ответа контроллера.
     */
    public function toArray(): array
    {
        return [
            'transfer' => [
                'id' => $this->transferID,
                'from_user_id' => $this->fromUserID,
                'to_user_id' => $this->toUserID,
                'amount' => $this->amount,
                'comment' => $this->comment,
                'created_at' => $this->transferCreatedAt,
            ],
            'outgoingTransaction' => $this->outgoingTransaction->toArray(),
            'incomingTransaction' => $this->incomingTransaction->toArray(),
        ];
    }
}
