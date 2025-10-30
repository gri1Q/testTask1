<?php

namespace App\Services;

use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Repositories\UserRepository\UserRepositoryInterface;
use Generated\DTO\LoginUser;
use Generated\DTO\RegisterUser;
use Generated\DTO\User as GenUserDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Throwable;
/**
 * Сервис для
 */
class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * Регистрируем пользователя.
     *
     * @param RegisterUser $registerUser
     * @return GenUserDTO
     * @throws Throwable
     */
    public function register(RegisterUser $registerUser): GenUserDTO
    {
        $user = new User();
        $user->name = $registerUser->name;

        $user = $this->userRepository->create($user);

        return new GenUserDTO($user->id, $user->name);
    }

    /**
     * Логинимся.
     *
     * @param LoginUser $loginUser
     * @return void
     * @throws InvalidCredentialsException
     */
    public function login(LoginUser $loginUser): void
    {
        try {
            $user = $this->userRepository->findByName($loginUser->name);
        } catch (ModelNotFoundException $e) {
            throw new InvalidCredentialsException('Неверное имя');
        }

        // Логиним через сессии
        Auth::login($user);
        request()->session()->regenerate();
    }

    /**
     * Получить пользователя по имени.
     *
     * @param LoginUser $loginUser
     * @return GenUserDTO
     */
    public function getUserByName(LoginUser $loginUser): GenUserDTO
    {
       $user= $this->userRepository->findByName($loginUser->name);

        return new GenUserDTO($user->id, $user->name);
    }
}
