<?php

declare(strict_types=1);

namespace App\Repositories\TransferRepository;

use App\Models\Transfer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Throwable;

interface TransferRepositoryInterface
{
    /**
     * Создать новый перевод.
     *
     * @param Transfer $transfer
     * @return Transfer
     * @throws Throwable
     */
    public function create(Transfer $transfer);

    /**
     * Получить перевод по ID.
     *
     * @param int $transferID
     * @return Transfer
     */
    public function getByID(int $transferID): Transfer;

    /**
     * Получить перевод по ID с транзакциями.
     *
     * @param int $transferID
     * @return SupportCollection
     */
    public function getByIDWithTransactions(int $transferID): SupportCollection;

    /**
     * Получить переводы по ID пользователя.
     *
     * @param int $userID
     * @return Collection
     */
    public function getByUserID(int $userID): Collection;

    /**
     * Получить переводы, где пользователь отправитель.
     *
     * @param int $fromUserID
     * @return Collection
     */
    public function getByFromUserID(int $fromUserID): Collection;

    /**
     * Получить переводы, где пользователь получатель.
     *
     * @param int $toUserID
     * @return Collection
     */
    public function getByToUserID(int $toUserID): Collection;
}
