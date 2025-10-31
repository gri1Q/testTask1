<?php

namespace App\Providers;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\BalanceController;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Repositories\BalanceRepository\BalanceRepository;
use App\Repositories\BalanceRepository\BalanceRepositoryInterface;
use App\Repositories\TransactionRepository\TransactionRepository;
use App\Repositories\TransactionRepository\TransactionRepositoryInterface;
use App\Repositories\TransferRepository\TransferRepository;
use App\Repositories\TransferRepository\TransferRepositoryInterface;
use App\Repositories\UserRepository\UserRepository;
use App\Repositories\UserRepository\UserRepositoryInterface;
use Generated\Http\Controllers\AuthApiInterface;
use Generated\Http\Controllers\BalanceApiInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BalanceRepositoryInterface::class, BalanceRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(TransferRepositoryInterface::class, TransferRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        $this->app->bind(AuthApiInterface::class, AuthController::class);
        $this->app->bind(BalanceApiInterface::class, BalanceController::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
    }
}
