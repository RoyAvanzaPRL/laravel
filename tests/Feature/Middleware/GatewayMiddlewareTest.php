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

    public function test_admin_can_access_admin_ping(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/ping')
            ->assertOk()
            ->assertJsonPath('message', 'Admin area OK.');
    }

    public function test_customer_cannot_access_admin_ping(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/admin/ping')
            ->assertForbidden();
    }
}