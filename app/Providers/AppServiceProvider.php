<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if (! $user->is_active) {
                return false;
            }

            if (! $user->hasRole('admin')) {
                return null;
            }

            // Admin: solo atajo de visibilidad. Mutaciones → TicketPolicy.
            if (in_array($ability, ['view', 'viewAny'], true)) {
                return true;
            }

            return null;
        });
    }
}