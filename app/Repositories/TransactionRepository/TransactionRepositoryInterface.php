<?php

declare(strict_types=1);

namespace App\Repositories\TransactionRepository;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface
{
    /**
     * Создать новую транзакцию.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public function create(Transaction $transaction): Transaction;

    /**
     * Получить транзакции по ID пользователя.
     *
     * @param int $userID
     * @return Collection
     */
    public function getByUserId(int $userID): Collection;

    /**
     * Получить транзакции по типу и ID пользователя.
     *
     * @param string $type
     * @param int $userID
     * @return Collection
     */
    public function getByTypeAndUserId(string $type, int $userID): Collection;

    /**
     * Получить транзакции по ID перевода
     *
     * @param int $transferID
     * @return Collection
     */
    public function getByTransferId(int $transferID): Collection;

    /**
     * Получить исходящую транзакцию перевода по ID перевода и ID пользователя.
     *
     * @param int $transferId
     * @param int $fromUserId
     * @return Transaction
     */
    public function getOutgoingTransferTransaction(int $transferId, int $fromUserId): Transaction;

    /**
     * Получить входящую транзакцию перевода по ID перевода и ID пользователя.
     *
     * @param int $transferId
     * @param int $toUserId
     * @return Transaction
     */
    public function getIncomingTransferTransaction(int $transferId, int $toUserId): Transaction;
}
