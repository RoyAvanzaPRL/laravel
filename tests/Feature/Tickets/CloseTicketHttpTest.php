<?php

namespace Tests\Feature\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CloseTicketHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Queue::fake();
    }

    public function test_agent_can_close_via_close_endpoint(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->actingAs($agent, 'sanctum')
            ->postJson('/api/tickets/'.$ticket->id.'/close')
            ->assertOk()
            ->assertJsonPath('data.status', 'closed');

        $ticket->refresh();

        $this->assertSame(TicketStatus::Closed, $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }

    public function test_customer_cannot_close(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/tickets/'.$ticket->id.'/close')
            ->assertForbidden();
    }

    public function test_supervisor_cannot_close(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->actingAs($supervisor, 'sanctum')
            ->postJson('/api/tickets/'.$ticket->id.'/close')
            ->assertForbidden();
    }

    public function test_transition_to_closed_sets_status_and_closed_at(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->actingAs($agent, 'sanctum')
            ->postJson('/api/tickets/'.$ticket->id.'/transition', [
                'status' => TicketStatus::Closed->value,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'closed');

        $ticket->refresh();

        $this->assertSame(TicketStatus::Closed, $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }
}