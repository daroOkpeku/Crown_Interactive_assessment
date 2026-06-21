<?php

namespace App\Providers;

use App\Http\Repository\AuthRepository;
use App\Http\Repository\Contracts\AuthRepositoryInterface;
use App\Http\Repository\Contracts\ProfileRepositoryInterface;
use App\Http\Repository\Contracts\WalletRepositoryInterface;
use App\Http\Repository\WalletRepository;
use Illuminate\Support\ServiceProvider;
use App\Http\Repository\ProfileRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ApprovalLevel::observe(ApprovalLevelObserver::class);
        ApprovalRequest::observe(ApprovalRequestObserver::class);
        Department::observe(DepartmentObserver::class);
        Approver::observe(ApproverObserver::class);
    }
}
