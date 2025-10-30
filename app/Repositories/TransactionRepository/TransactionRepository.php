<?php

declare(strict_types=1);

namespace App\Repositories\TransactionRepository;

use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Создать новую транзакцию.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public function create(Transaction $transaction): Transaction
    {
        $transaction->save();

        return $transaction;
    }

    /**
     * Создает 2 записи: кто перевел и кому перевел.
     *
     * @param array $transactionsData
     * @return bool
     */
    public function createMultiple(array $transactionsData): bool
    {
        return Transaction::query()->insert($transactionsData);
    }

    /**
     * Получить транзакции по ID пользователя.
     *
     * @param int $userID
     * @return Collection
     */
    public function getByUserID(int $userID): Collection
    {
        return Transaction::query()->where('user_id', $userID)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Получить транзакции по типу и ID пользователя.
     *
     * @param string $type
     * @param int $userID
     * @return Collection
     */
    public function getByTypeAndUserID(string $type, int $userID): Collection
    {
        return Transaction::query()
            ->where('user_id', $userID)
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить транзакции по ID перевода
     *
     * @param int $transferID
     * @return Collection
     */
    public function getByTransferID(int $transferID): Collection
    {
        return Transaction::query()->where('transfer_id', $transferID)->get();
    }

    /**
     * Получить исходящую транзакцию перевода по ID перевода и ID пользователя.
     *
     * @param int $transferId
     * @param int $fromUserId
     * @return Transaction
     */
    public function getOutgoingTransferTransaction(int $transferId, int $fromUserId): Transaction
    {
        return Transaction::query()
            ->where('transfer_id', $transferId)
            ->where('user_id', $fromUserId)
            ->where('type', TransactionStatusEnum::TRANSFER_OUT->value)
            ->firstOrFail();
    }

    /**
     * Получить входящую транзакцию перевода по ID перевода и ID пользователя.
     *
     * @param int $transferId
     * @param int $toUserId
     * @return Transaction
     */
    public function getIncomingTransferTransaction(int $transferId, int $toUserId): Transaction
    {
        return Transaction::query()
            ->where('transfer_id', $transferId)
            ->where('user_id', $toUserId)
            ->where('type', TransactionStatusEnum::TRANSFER_IN->value)
            ->firstOrFail();
    }
}
