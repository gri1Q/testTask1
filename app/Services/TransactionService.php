<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionStatusEnum;
use App\Exceptions\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Repositories\TransactionRepository\TransactionRepositoryInterface;
use App\Repositories\TransferRepository\TransferRepositoryInterface;
use App\Services\DTO\TransactionServiceDTO\CreateDepositTransactionResponseDTO;
use App\Services\DTO\TransactionServiceDTO\CreateTransferTransactionResponseDTO;
use App\Services\DTO\TransactionServiceDTO\CreateWithdrawTransactionResponseDTO;
use App\Services\DTO\TransactionServiceDTO\TransactionDTO;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class TransactionService
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private TransferRepositoryInterface $transferRepository
    ) {
    }


    /**
     * Создать транзакцию.
     *
     * @param array $data
     * @return Transaction
     */
    public function createTransaction(array $data): Transaction
    {
        return $this->transactionRepository->create(new Transaction($data));
    }

    /**
     * Создать транзакцию пополнения.
     *
     * @param int $userId
     * @param float $amount
     * @return CreateDepositTransactionResponseDTO
     */
    public function createDepositTransaction(
        int $userId,
        float $amount,
    ): CreateDepositTransactionResponseDTO {
        $transaction = $this->createTransaction([
            'user_id' => $userId,
            'type' => TransactionStatusEnum::DEPOSIT->value,
            'amount' => $amount,
        ]);

        return new CreateDepositTransactionResponseDTO(
            $transaction->id,
            $transaction->user_id,
            $transaction->type,
            (float)$transaction->amount,
            $transaction->created_at->toDateString()
        );
    }


    /**
     * Создать транзакцию списания.
     *
     * @param int $userId
     * @param float $amount
     * @return CreateWithdrawTransactionResponseDTO
     */
    public function createWithdrawTransaction(
        int $userId,
        float $amount,
    ): CreateWithdrawTransactionResponseDTO {
        $transaction = $this->createTransaction([
            'user_id' => $userId,
            'type' => TransactionStatusEnum::WITHDRAW->value,
            'amount' => $amount,
        ]);

        return new CreateWithdrawTransactionResponseDTO(
            $transaction->id,
            $transaction->user_id,
            $transaction->type,
            (float)$transaction->amount,
            $transaction->created_at->toDateString()
        );
    }


    /**
     * Создать перевод денег между пользователями.
     *
     * @param int $fromUserId
     * @param int $toUserId
     * @param float $amount
     * @param string|null $comment
     * @return CreateTransferTransactionResponseDTO
     * @throws Throwable
     */
    public function createTransfer(
        int $fromUserId,
        int $toUserId,
        float $amount,
        ?string $comment = null
    ): CreateTransferTransactionResponseDTO {
        try {
            // Создаем перевод
            $transfer = new Transfer();
            $transfer->from_user_id = $fromUserId;
            $transfer->to_user_id = $toUserId;
            $transfer->amount = $amount;
            $transfer->comment = $comment;
            $transfer = $this->transferRepository->create($transfer);

            // Создаем обе транзакции
            $transactionsData = [
                [
                    'user_id' => $fromUserId,
                    'type' => TransactionStatusEnum::TRANSFER_OUT->value,
                    'amount' => $amount,
                    'transfer_id' => $transfer->id,
                    'comment' => $comment,
                ],
                [
                    'user_id' => $toUserId,
                    'type' => TransactionStatusEnum::TRANSFER_IN->value,
                    'amount' => $amount,
                    'transfer_id' => $transfer->id,
                    'comment' => $comment,
                ]
            ];

            $this->transactionRepository->createMultiple($transactionsData);

            // Получаем созданные транзакции через репозиторий
            $outgoingTransaction = $this->transactionRepository->getOutgoingTransferTransaction(
                $transfer->id,
                $fromUserId
            );
            $incomingTransaction = $this->transactionRepository->getIncomingTransferTransaction(
                $transfer->id,
                $toUserId
            );
        } catch (Throwable $e) {
            throw new InsufficientFundsException($e->getMessage());
        }

        $outgoingTransactionDTO = new TransactionDTO(
            id: $outgoingTransaction->id,
            userID: $outgoingTransaction->user_id,
            type: $outgoingTransaction->type,
            amount: $outgoingTransaction->amount,
            comment: $outgoingTransaction->comment,
            createdAt: $outgoingTransaction->created_at->toDateString(),
            transferId: $outgoingTransaction->transfer_id
        );

        $incomingTransactionDTO = new TransactionDTO(
            id: $incomingTransaction->id,
            userID: $incomingTransaction->user_id,
            type: $incomingTransaction->type,
            amount: $incomingTransaction->amount,
            comment: $incomingTransaction->comment,
            createdAt: $incomingTransaction->created_at->toDateString(),
            transferId: $incomingTransaction->transfer_id
        );

        return new CreateTransferTransactionResponseDTO(
            transferId: $transfer->id,
            fromUserId: $transfer->from_user_id,
            toUserId: $transfer->to_user_id,
            amount: $transfer->amount,
            comment: $transfer->comment,
            transferCreatedAt: $transfer->created_at->toDateString(),
            outgoingTransaction: $outgoingTransactionDTO,
            incomingTransaction: $incomingTransactionDTO
        );
    }


    /**
     * Получить историю транзакций пользователя.
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserTransactionHistory(int $userId): Collection
    {
        return $this->transactionRepository->getByUserId($userId);
    }


    /**
     * Получить транзакции пользователя по типу.
     *
     * @param int $userId
     * @param string $type
     * @return Collection
     */
    public function getUserTransactionsByType(int $userId, string $type): Collection
    {
        return $this->transactionRepository->getByTypeAndUserId($type, $userId);
    }


    /**
     * Получить транзакции по ID перевода.
     *
     * @param int $transferId
     * @return Collection
     */
    public function getTransactionsByTransferId(int $transferId): Collection
    {
        return $this->transactionRepository->getByTransferId($transferId);
    }


    /**
     * Получить все пополнения пользователя.
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserDeposits(int $userId): Collection
    {
        return $this->getUserTransactionsByType($userId, TransactionStatusEnum::DEPOSIT->value);
    }


    /**
     * Получить все списания пользователя.
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserWithdrawals(int $userId): Collection
    {
        return $this->getUserTransactionsByType($userId, TransactionStatusEnum::WITHDRAW->value);
    }


    /**
     * Получить исходящие переводы пользователя.
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserOutgoingTransfers(int $userId): Collection
    {
        return $this->getUserTransactionsByType($userId, TransactionStatusEnum::TRANSFER_OUT->value);
    }


    /**
     * Получить входящие переводы пользователя.
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserIncomingTransfers(int $userId): Collection
    {
        return $this->getUserTransactionsByType($userId, TransactionStatusEnum::TRANSFER_IN->value);
    }

    /**
     * Получить перевод по ID с связанными транзакциями.
     *
     * @param int $transferId
     * @return \Illuminate\Support\Collection
     */
    public function getTransferWithTransactions(int $transferId): \Illuminate\Support\Collection
    {
        return $this->transferRepository->getByIdWithTransactions($transferId);
    }
}
