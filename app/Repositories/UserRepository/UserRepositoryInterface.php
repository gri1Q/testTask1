<?php

declare(strict_types=1);

namespace App\Repositories\UserRepository;

use App\Models\User;
use Throwable;

interface UserRepositoryInterface
{
    /**
     * @param User $user
     * @return User
     * @throws Throwable
     */
    public function create(User $user): User;


    /**
     * Найти пользователя по его имени.
     *
     * @param string $name
     * @return User
     */
    public function findByName(string $name): User;

    /**
     * Существует ли пользователь.
     *
     * @param int $id
     * @return bool
     */
    public function isExist(int $id): bool;

}
