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
     * @param int $userID
     * @param float $amount
     * @return DepositResponseDTO
     * @throws Throwable
     */
    public function deposit(int $userID, float $amount): DepositResponseDTO
    {
        DB::beginTransaction();
        try {
            $balance = $this->balanceRepository->getByUserIDWithLock($userID);
            $balance->amount += $amount;
            $this->balanceRepository->updateBalance($balance);

            $createDepositTransactionResponseDTO = $this->transactionService
                ->createDepositTransaction($userID, $amount);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return new DepositResponseDTO(
            $userID,
            $balance->amount,
            $amount,
            'Баланс успешно пополнен',
            $createDepositTransactionResponseDTO
        );
    }

    /**
     * Списание средств с баланса пользователя.
     *
     * @param int $userID
     * @param float $amount
     * @return WithdrawResponseDTO
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function withdraw(int $userID, float $amount): WithdrawResponseDTO
    {
        DB::beginTransaction();
        try {
            $balance = $this->balanceRepository->getByUserIDWithLock($userID);

            if ($balance->amount < $amount) {
                throw new InsufficientFundsException(
                    "Недостаточно средств у вас на балансе {$balance->amount}, а вы хотите списать {$amount}."
                );
            }

            $balance->amount -= $amount;
            $this->balanceRepository->updateBalance($balance);

            $createWithdrawTransactionResponseDTO = $this->transactionService
                ->createWithdrawTransaction($userID, $amount);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return new WithdrawResponseDTO(
            $userID,
            $balance->amount,
            $amount,
            'Средства успешно списаны',
            $createWithdrawTransactionResponseDTO
        );
    }

    /**
     * Перевод средств между пользователями.
     *
     * @param int $fromUserID
     * @param int $toUserID
     * @param float $amount
     * @param string $comment
     * @return TransferResponseDTO
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function transfer(int $fromUserID, int $toUserID, float $amount, string $comment): TransferResponseDTO
    {
        DB::beginTransaction();
        try {
            $this->ensureUserExists($toUserID);

            // Получаем балансы с блокировкой
            $fromBalance = $this->balanceRepository->getByUserIDWithLock($fromUserID);
            $toBalance = $this->balanceRepository->getByUserIDWithLock($toUserID);

            if ($fromBalance->amount < $amount) {
                dd($fromBalance->amount,$amount);
//                throw new InsufficientFundsException();
            }

            // Создаем перевод и транзакции
            $createTransferResponseDTO = $this->transactionService
                ->createTransfer($fromUserID, $toUserID, $amount, $comment);

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
            $fromUserID,
            $toUserID,
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
     * @param int $userID
     * @return BalanceResponseDTO
     */
    public function getBalance(int $userID): BalanceResponseDTO
    {
        $balance = $this->balanceRepository->getByUserID($userID);

        return new BalanceResponseDTO(
            userID: $balance->user_id,
            balance: $balance->amount
        );
    }

    /**
     * Получение истории транзакций пользователя.
     *
     * @param int $userID
     * @return Collection
     * @throws UserNotFoundException
     */
    public function getTransactionHistory(int $userID): Collection
    {
        $this->ensureUserExists($userID);
        return $this->transactionService->getUserTransactionHistory($userID);
    }

    /**
     * Создание баланса для нового пользователя.
     *
     * @param int $userID
     * @throws Throwable
     */
    public function createBalanceForUser(int $userID): void
    {
        $balance = new Balance;
        $balance->user_id = $userID;
        $balance->amount = 0.0;
        $this->balanceRepository->createForUser($balance);
    }

    /**
     * Проверка существования пользователя
     */
    private function ensureUserExists(int $userID): void
    {
        if (!$this->authService->userIsExist($userID)) {
            throw new UserNotFoundException();
        }
    }
}
