<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientFundsException;
use App\Services\BalanceService;
use Generated\DTO\BalanceResponse;
use Generated\DTO\DepositRequest;
use Generated\DTO\DepositResponse;
use Generated\DTO\Error;
use Generated\DTO\NoContent401;
use Generated\DTO\NoContent403;
use Generated\DTO\NoContent404;
use Generated\DTO\NoContent419;
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
            $balanceDTO->userId,
            $balanceDTO->balance
        );
    }


    public function deposit(DepositRequest $depositRequest
    ): DepositResponse|ValidationError|NoContent401|NoContent419|Error {
        $ve = $this->validateOrNull([
            'amount' => $depositRequest->amount,
            'comment' => $depositRequest->comment ?? '',
        ], [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'comment' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required' => 'Сумма пополнения обязательна',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.min' => 'Минимальная сумма пополнения: 0.01',
            'comment.string' => 'Комментарий должен быть строкой',
            'comment.max' => 'Комментарий не должен превышать 255 символов',
        ]);

        if ($ve !== null) {
            return $ve;
        }

        if (!Auth::check()) {
            return new NoContent401();
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


    public function withdraw(WithdrawRequest $withdrawRequest,
    ): WithdrawResponse|ValidationError|NoContent401|NoContent419|Error {
        $ve = $this->validateOrNull([
            'amount' => $withdrawRequest->amount,
            'comment' => $withdrawRequest->comment ?? '',
        ], [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'comment' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required' => 'Сумма списания обязательна',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.min' => 'Минимальная сумма списания: 0.01',
            'comment.string' => 'Комментарий должен быть строкой',
            'comment.max' => 'Комментарий не должен превышать 255 символов',
        ]);

        if ($ve !== null) {
            return $ve;
        }

        if (!Auth::check()) {
            return new NoContent401();
        }

        try {
            $userId = Auth::id();

            $withdrawDTO = $this->balanceService->withdraw(
                $userId,
                $withdrawRequest->amount,
            );
        } catch (InsufficientFundsException $e) {


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

}
