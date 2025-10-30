<?php

namespace App\Services;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\UserNotFoundException;
use App\Models\Balance;
use App\Repositories\BalanceRepository\BalanceRepositoryInterface;
use App\Services\DTO\BalanceServiceDTO\BalanceResponseDTO;
use App\Services\DTO\BalanceServiceDTO\DepositResponseDTO;
use App\Services\DTO\BalanceServiceDTO\TransferResponseDTO;
use App\Services\DTO\BalanceServiceDTO\WithdrawResponseDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class BalanceService
{
    public function __construct(
        private AuthService $authService,
        private TransactionService $transactionService,
        private BalanceRepositoryInterface $balanceRepository,
    ) {
    }

    /**
     * Пополнение баланса пользователя.
     *
     * @param int $userId
     * @param float $amount
     * @param string $comment
     * @return DepositResponseDTO
     * @throws Throwable
     */
    public function deposit(int $userId, float $amount, string $comment): DepositResponseDTO
    {
        DB::beginTransaction();
        try {
            $this->ensureUserExists($userId);

            $balance = $this->balanceRepository->getByUserIDWithLock($userId);
            $balance->amount += $amount;
            $this->balanceRepository->updateBalance($balance);

            $createDepositTransactionResponseDTO = $this->transactionService
                ->createDepositTransaction($userId, $amount, $comment);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return new DepositResponseDTO(
            $userId,
            $balance->amount,
            $amount,
            'Баланс успешно пополнен',
            $createDepositTransactionResponseDTO
        );
    }

    /**
     * Списание средств с баланса пользователя.
     *
     * @param int $userId
     * @param float $amount
     * @param string $comment
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function withdraw(int $userId, float $amount, string $comment): WithdrawResponseDTO
    {
        DB::beginTransaction();
        try {
            $this->ensureUserExists($userId);

            $balance = $this->balanceRepository->getByUserIDWithLock($userId);

            if ($balance->amount < $amount) {
                throw new InsufficientFundsException(
                    "Недостаточно средств у вас на балансе {$balance->amount}, а вы хотите списать {$amount}."
                );
            }

            $balance->amount -= $amount;
            $this->balanceRepository->updateBalance($balance);

            $createWithdrawTransactionResponseDTO = $this->transactionService
                ->createWithdrawTransaction($userId, $amount, $comment);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return new WithdrawResponseDTO(
            $userId,
            $balance->amount,
            $amount,
            'Средства успешно списаны',
            $createWithdrawTransactionResponseDTO
        );
    }

    /**
     * Перевод средств между пользователями.
     *
     * @param int $fromUserId
     * @param int $toUserId
     * @param float $amount
     * @param string $comment
     * @return TransferResponseDTO
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function transfer(int $fromUserId, int $toUserId, float $amount, string $comment): TransferResponseDTO
    {
        DB::beginTransaction();
        try {
            $this->ensureUserExists($fromUserId);
            $this->ensureUserExists($toUserId);

            // Получаем балансы с блокировкой
            $fromBalance = $this->balanceRepository->getByUserIDWithLock($fromUserId);
            $toBalance = $this->balanceRepository->getByUserIDWithLock($toUserId);

            if ($fromBalance->amount < $amount) {
                throw new InsufficientFundsException();
            }

            // Создаем перевод и транзакции
            $createTransferResponseDTO = $this->transactionService
                ->createTransfer($fromUserId, $toUserId, $amount, $comment);

            // Обновляем балансы
            $fromBalance->amount -= $amount;
            $toBalance->amount += $amount;

            $this->balanceRepository->updateBalance($fromBalance);
            $this->balanceRepository->updateBalance($toBalance);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return new TransferResponseDTO(
            $fromUserId,
            $toUserId,
            $fromBalance->amount,
            $toBalance->amount,
            $amount,
            'Перевод успешно выполнен',
            $createTransferResponseDTO
        );
    }

    /**
     * Получение текущего баланса пользователя.
     *
     * @param int $userId
     * @return BalanceResponseDTO
     */
    public function getBalance(int $userId): BalanceResponseDTO
    {
        $this->ensureUserExists($userId);


        $balance = $this->balanceRepository->getByUserID($userId);

        return new BalanceResponseDTO(
            userId: $balance->user_id,
            balance: $balance->amount
        );
    }

    /**
     * Получение истории транзакций пользователя.
     *
     * @param int $userId
     * @return Collection
     */
    public function getTransactionHistory(int $userId): Collection
    {
        $this->ensureUserExists($userId);
        return $this->transactionService->getUserTransactionHistory($userId);
    }

    /**
     * Создание баланса для нового пользователя.
     *
     * @param int $userId
     * @throws Throwable
     */
    public function createBalanceForUser(int $userId): void
    {
        $balance = new Balance;
        $balance->user_id = $userId;
        $balance->amount = 0.0;
        $this->balanceRepository->createForUser($balance);
    }

    /**
     * Проверка существования пользователя
     */
    private function ensureUserExists(int $userId): void
    {
        if (!$this->authService->exists($userId)) {
            throw new UserNotFoundException();
        }
    }
}
