<?php

declare(strict_types=1);

namespace App\Repositories\BalanceRepository;

use App\Models\Balance;
use Throwable;

/**
 * Интерфейс репозитория для работы с балансами пользователей.
 */
interface BalanceRepositoryInterface
{
    /**
     * Получить баланс пользователя (без блокировки).
     *
     * @param int $userID
     * @return Balance
     */
    public function getByUserID(int $userID): Balance;

    /**
     * Получить баланс пользователя с блокировкой строки (FOR UPDATE).
     *
     * Важно: метод ожидает, что вызывающий код открыл транзакцию.
     *
     * @param int $userID
     * @return Balance
     */
    public function getByUserIDWithLock(int $userID): Balance;

    /**
     * Создать баланс.
     *
     * @param Balance $balance
     * @return Balance
     * @throws Throwable
     */
    public function createForUser(Balance $balance): Balance;

    /**
     * Обновить модель Balance.
     *
     * @param Balance $balance
     * @return Balance
     */
    public function updateBalance(Balance $balance): Balance;

    /**
     * Проверить, существует ли запись баланса у пользователя.
     *
     * @param int $userID
     * @return bool
     */
    public function userHasBalance(int $userID): bool;
}
