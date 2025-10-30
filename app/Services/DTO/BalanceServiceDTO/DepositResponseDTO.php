<?php

declare(strict_types=1);

namespace App\Services\DTO\BalanceServiceDTO;

use App\Services\DTO\TransactionServiceDTO\CreateDepositTransactionResponseDTO;

/**
 * DTO для ответа при пополнении баланса.
 *
 * @property bool $success
 * @property string $message
 * @property float $newBalance
 * @property int $userID
 * @property float $amount
 * @property CreateDepositTransactionResponseDTO $transaction
 */
readonly class DepositResponseDTO
{
    public function __construct(
        public int $userID,
        public float $newBalance,
        public float $amount,
        public string $message,
        public CreateDepositTransactionResponseDTO $transaction
    ) {
    }

    /**
     * Преобразует DTO в массив для ответа контроллера.
     */
    public function toArray(): array
    {
        return [
            'userID' => $this->userID,
            'newBalance' => $this->newBalance,
            'amount' => $this->amount,
            'message' => $this->message,
            'transaction' => $this->transaction->toArray(),
        ];
    }
}
