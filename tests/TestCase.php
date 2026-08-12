<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Authenticate the current test request as a fresh staff user via Sanctum.
     *
     * Returns the test case so it can be chained: `$this->actingAsUser()->getJson(...)`.
     * Subsequent calls return the same user, so tests don't bloat the DB.
     */
    protected function actingAsUser(?User $user = null): static
    {
        $user ??= User::factory()->create();

        return $this->actingAs($user, 'sanctum');
    }

    /**
     * Authenticate as a fresh admin user (full CRUD on couriers).
     */
    protected function actingAsAdmin(?User $user = null): static
    {
        $user ??= User::factory()->admin()->create();

        return $this->actingAs($user, 'sanctum');
    }
}
