<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

use function database_path;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('testing')) {
            $this->loadMigrationsFrom(database_path('migrations/landlord'));
        }

        Gate::before(static fn (User $user, string $ability): ?true => $user->hasRole(Role::SUPER_ADMIN) ? true : null);
    }
}
