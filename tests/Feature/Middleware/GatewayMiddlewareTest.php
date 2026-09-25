<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_inactive_user_is_blocked(): void
    {
        $user = User::factory()->inactive()->create();
        $user->assignRole('customer');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me')
            ->assertForbidden()
            ->assertJsonPath('message', 'This account is inactive.');
    }

    public function test_active_user_can_reach_me(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }
}