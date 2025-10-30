<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidCredentialsException;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Exception;
use Generated\DTO\Error;
use Generated\DTO\LoginUser;
use Generated\DTO\NoContent200;
use Generated\DTO\NoContent419;
use Generated\DTO\NoContent429;
use Generated\DTO\RegisterResponse;
use Generated\DTO\RegisterUser;
use Generated\DTO\ValidationError;
use Generated\DTO\ValidationErrorItem;
use Generated\Http\Controllers\AuthApiInterface;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller implements AuthApiInterface
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * Регистрация пользователя.
     *
     * @param RegisterUser $registerUser
     * @return RegisterResponse|ValidationError|NoContent419|Error
     */
    public function registerUser(RegisterUser $registerUser,
    ): RegisterResponse|ValidationError|NoContent419|Error {
        $ve = $this->validateOrNull([
            'name' => $registerUser->name,
        ], [
            'name' => ['required', 'string', 'min:4', 'max:255', 'unique:users,name'],

        ], [
            'name.unique' => 'Пользователь с таким именем уже существует.',
            'name.required' => 'Имя обязательно.',
        ]);
        if ($ve !== null) {
            return $ve;
        }

        try {
            $user = $this->authService->register($registerUser);
        } catch (Exception $e) {
            report($e);
            return new Error("Что-то пошло не так");
        }

        return new RegisterResponse($user);
    }

    public function loginUser(LoginUser $loginUser): NoContent200|ValidationError|NoContent419|NoContent429|Error
    {
        $ve = $this->validateOrNull([
            'name' => $loginUser->name,
        ], [
            'name' => ['required', 'string', 'min:4', 'max:255'],
        ], [
            'name.required' => 'Имя обязательно.',
            'name.min' => 'Имя должно быть не меньше 4 символов.',
            'name.max' => 'Имя должно быть не больше 255 символов.',
        ]);

        if ($ve !== null) {
            return $ve;
        }

        try {
            $this->authService->login($loginUser);

            return new NoContent200('success');
        } catch (InvalidCredentialsException $e) {
            return new Error($e->getMessage());
        } catch (\Exception $e) {
            report($e);
            return new Error("Что-то пошло не так при логине");
        }
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
