<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\UserNotFoundException;
use App\Services\BalanceService;
use Generated\DTO\BalanceResponse;
use Generated\DTO\DepositRequest;
use Generated\DTO\DepositResponse;
use Generated\DTO\Error;
use Generated\DTO\NoContent401;
use Generated\DTO\NoContent403;
use Generated\DTO\NoContent404;
use Generated\DTO\NoContent409;
use Generated\DTO\NoContent419;
use Generated\DTO\TransferRequest;
use Generated\DTO\TransferResponse;
use Generated\DTO\ValidationError;
use Generated\DTO\ValidationErrorItem;
use Generated\DTO\WithdrawRequest;
use Generated\DTO\WithdrawResponse;
use Generated\Http\Controllers\BalanceApiInterface;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class BalanceController extends Controller implements BalanceApiInterface
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    /**
     * Получить баланс.
     *
     * @param int $userID
     * @return BalanceResponse|NoContent403|NoContent404|NoContent419|Error
     */
    public function getBalance(int $userID): BalanceResponse|NoContent403|NoContent404|NoContent419|Error
    {
        try {
            // Получаем баланс через сервис
            $balanceDTO = $this->balanceService->getBalance($userID);
        } catch (Throwable $e) {
            report($e);

            return new Error($e->getMessage());
        }

        return new BalanceResponse(
            $balanceDTO->userID,
            $balanceDTO->balance
        );
    }


    public function deposit(DepositRequest $depositRequest
    ): DepositResponse|ValidationError|NoContent401|NoContent419|Error {
        if (!Auth::check()) {
            return new NoContent401();
        }

        $ve = $this->validateOrNull([
            'amount' => $depositRequest->amount,
        ], [
            'amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'amount.required' => 'Сумма пополнения обязательна',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.min' => 'Минимальная сумма пополнения: 0.01',
        ]);

        if ($ve !== null) {
            return $ve;
        }

        try {
            $userId = Auth::id();

            $depositDTO = $this->balanceService->deposit(
                $userId,
                $depositRequest->amount,
            );
        } catch (Throwable $e) {
            report($e);
            return new Error("Что то пошло не так");
        }

        return new DepositResponse(
            $depositDTO->userID,
            $depositDTO->newBalance,
            $depositDTO->amount,
            $depositDTO->message
        );
    }


    /**
     * Валидирует данные и возвращает ошибку или null.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return ValidationError|null
     */
    private function validateOrNull(array $data, array $rules, array $messages = []): ?ValidationError
    {
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return $this->makeValidationError($validator);
        }

        return null;
    }

    /**
     * Сформировать объект ошибки валидации из валидатора Laravel.
     *
     * @param ValidatorContract $validator
     * @return ValidationError
     */
    private function makeValidationError(ValidatorContract $validator): ValidationError
    {
        $errorItems = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $errorItems[] = new ValidationErrorItem($message, $field);
            }
        }

        return new ValidationError(null, $errorItems);
    }

    public function withdraw(WithdrawRequest $withdrawRequest,
    ): WithdrawResponse|ValidationError|NoContent401|NoContent409|NoContent419|Error {
        if (!Auth::check()) {
            return new NoContent401();
        }

        $ve = $this->validateOrNull([
            'amount' => $withdrawRequest->amount,
        ], [
            'amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'amount.required' => 'Сумма списания обязательна',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.min' => 'Минимальная сумма списания: 0.01',
        ]);

        if ($ve !== null) {
            return $ve;
        }

        try {
            $userId = Auth::id();

            $withdrawDTO = $this->balanceService->withdraw(
                $userId,
                $withdrawRequest->amount,
            );
        } catch (InsufficientFundsException $e) {
            return new NoContent409("Недостаточно средств");
        } catch (Throwable $e) {
            report($e);
            return new Error("Что то пошло не так");
        }

        return new WithdrawResponse(
            $withdrawDTO->userID,
            $withdrawDTO->newBalance,
            $withdrawDTO->amount,
            $withdrawDTO->message
        );
    }

    public function transfer(TransferRequest $transferRequest
    ): TransferResponse|ValidationError|NoContent401|NoContent404|NoContent409|NoContent419|Error {
        if (!Auth::check()) {
            return new NoContent401();
        }

        $ve = $this->validateOrNull([
            'to_user_id' => $transferRequest->toUserId,
            'amount' => $transferRequest->amount,
            'comment' => $transferRequest->comment ?? '',
        ], [
            'to_user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'to_user_id.required' => 'ID получателя обязательно',
            'to_user_id.integer' => 'ID получателя должен быть целым числом',
            'to_user_id.min' => 'ID получателя должен быть положительным',
            'to_user_id.exists' => 'Получатель не найден',
            'amount.required' => 'Сумма перевода обязательна',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.min' => 'Минимальная сумма перевода: 0.01',
            'comment.string' => 'Комментарий должен быть строкой',
            'comment.max' => 'Комментарий не должен превышать 500 символов',
        ]);

        if ($ve !== null) {
            return $ve;
        }

        $fromUserID = Auth::id();

        if ($fromUserID == $transferRequest->toUserId) {
            return new NoContent409("Нельзя переводить средства самому себе");
        }

        try {
            $transferDTO = $this->balanceService->transfer(
                $fromUserID,
                $transferRequest->toUserId,
                $transferRequest->amount,
                $transferRequest->comment ?? ''
            );
        } catch (InsufficientFundsException $e) {
//            return new NoContent409("Недостаточно средств для перевода");
            return new NoContent409($e->getMessage());
        } catch (UserNotFoundException $e) {
            return new NoContent404("Пользователь-получатель не найден");
        } catch (Throwable $e) {
            report($e);
            return new Error($e->getMessage());
            return new Error("Что то пошло не так");
        }

        return new TransferResponse(
            $transferDTO->fromUserID,
            $transferDTO->toUserID,
            $transferDTO->fromUserNewBalance,
            $transferDTO->toUserNewBalance,
            $transferDTO->amount,
            $transferDTO->message
        );
    }
}
