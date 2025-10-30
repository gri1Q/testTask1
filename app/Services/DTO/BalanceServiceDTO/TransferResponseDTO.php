<?php

declare(strict_types=1);

namespace App\Services\DTO\BalanceServiceDTO;

use App\Services\DTO\TransactionServiceDTO\CreateTransferTransactionResponseDTO;

/**
 * DTO для ответа при переводе средств между пользователями
 *
 * @property-read int $fromUserID
 * @property-read int $toUserID
 * @property-read float $fromUserNewBalance
 * @property-read float $toUserNewBalance
 * @property-read float $amount
 * @property-read string $message
 * @property-read CreateTransferTransactionResponseDTO $transferData
 */
readonly class TransferResponseDTO
{
    public function __construct(
        public int $fromUserID,
        public int $toUserID,
        public float $fromUserNewBalance,
        public float $toUserNewBalance,
        public float $amount,
        public string $message,
        public CreateTransferTransactionResponseDTO $transferData
    ) {
    }

    public function toArray(): array
    {
        return [
            'fromUserID' => $this->fromUserID,
            'toUserID' => $this->toUserID,
            'fromUserNewBalance' => $this->fromUserNewBalance,
            'toUserNewBalance' => $this->toUserNewBalance,
            'amount' => $this->amount,
            'message' => $this->message,
            'transferData' => $this->transferData->toArray(),
        ];
    }
}
