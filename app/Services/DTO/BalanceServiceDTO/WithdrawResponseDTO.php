<?php

declare(strict_types=1);

namespace App\Services\DTO\BalanceServiceDTO;

use App\Services\DTO\TransactionServiceDTO\CreateWithdrawTransactionResponseDTO;

/**
 * DTO для ответа при списании средств с баланса
 *
 * @property string $message
 * @property float $newBalance
 * @property int $userID
 * @property float $amount
 * @property CreateWithdrawTransactionResponseDTO $transaction
 */
readonly class WithdrawResponseDTO
{

    public function __construct(
        public int $userID,
        public float $newBalance,
        public float $amount,
        public string $message,
        public CreateWithdrawTransactionResponseDTO $transaction
    ) {
    }

    /**
     * Преобразует DTO в массив для ответа контроллера
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
