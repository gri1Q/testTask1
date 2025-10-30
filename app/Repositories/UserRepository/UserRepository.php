<?php

declare(strict_types=1);

namespace App\Repositories\UserRepository;

use App\Models\User;
use Throwable;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Создать пользователя.
     *
     * @param User $user
     * @return User
     * @throws Throwable
     */
    public function create(User $user): User
    {
        $user->saveOrFail();

        return $user;
    }

    /**
     * Найти пользователя по его имени.
     *
     * @param string $name
     * @return User
     */
    public function findByName(string $name): User
    {
        return User::query()->where('name', $name)->firstOrFail();
    }
}
