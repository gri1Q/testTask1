<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{

    /**
     * Проверка, может ли пользователь просматривать баланс.
     *
     * @param User $user
     * @param int $id
     * @return bool
     */
    public function get(User $user, int $id)
    {
        return $user->id === $id;
    }
}
