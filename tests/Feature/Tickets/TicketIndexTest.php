<?php

namespace Tests\Feature\Tickets;

use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_customer_index_does_not_list_foreign_tickets(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $own = Ticket::factory()->forCreator($customer)->create();
        Ticket::factory()->forCreator($other)->create();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);
    }

    public function test_agent_index_lists_all_tickets(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        Ticket::factory()->forCreator($customer)->create();
        Ticket::factory()->forCreator($other)->create();

        $this->actingAs($agent, 'sanctum')
            ->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_supervisor_index_lists_all_tickets(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');

        Ticket::factory()->forCreator($customer)->create();
        Ticket::factory()->forCreator($other)->create();

        $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}