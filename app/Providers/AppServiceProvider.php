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
        // Authorization untuk manajemen kurir:
        // - admin: full CRUD (create, update, delete)
        // - staff: read-only (index, show)
        // Read endpoints tidak perlu Gate karena 'manage-courier' hanya membungkus write ops.
        Gate::define('manage-courier', fn (User $user) => $user->isAdmin());
    }
}
