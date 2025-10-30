<?php

declare(strict_types=1);

namespace App\Repositories\BalanceRepository;

use App\Models\Balance;
use Throwable;

class BalanceRepository implements BalanceRepositoryInterface
{

    /**
     * Получить баланс пользователя (без блокировки).
     *
     * @param int $userID
     * @return Balance
     */
    public function getByUserID(int $userID): Balance
    {
        return Balance::query()->where('user_id', $userID)->firstOrFail();
    }

    /**
     * Получить баланс пользователя с блокировкой строки (FOR UPDATE).
     *
     * Важно: метод ожидает, что вызывающий код открыл транзакцию.
     *
     * @param int $userID
     * @return Balance
     */
    public function getByUserIDWithLock(int $userID): Balance
    {
        return Balance::query()->where('user_id', $userID)->lockForUpdate()->firstOrFail();
    }

    /**
     * Создать баланс.
     *
     * @param Balance $balance
     * @return Balance
     * @throws Throwable
     */
    public function createForUser(Balance $balance): Balance
    {
        $balance->saveOrFail();

        return $balance;
    }


    /**
     * @param Balance $balance
     * @return Balance
     * @throws Throwable
     */
    public function updateBalance(Balance $balance): Balance
    {
        $balance->saveOrFail();

        return $balance;
    }

    /**
     * Проверка, существует ли баланс у пользователя.
     *
     * @param int $userID
     * @return bool
     */
    public function userHasBalance(int $userID): bool
    {
        return Balance::where('user_id', $userID)->exists();
    }
}
