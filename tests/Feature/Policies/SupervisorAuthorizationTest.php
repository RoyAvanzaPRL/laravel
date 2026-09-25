<?php

namespace Tests\Feature\Policies;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_supervisor_can_view_foreign_ticket_but_cannot_close(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->assertTrue($supervisor->can('view', $ticket));
        $this->assertFalse($supervisor->can('close', $ticket));
        $this->assertFalse($supervisor->can('assign', $ticket));
    }

    public function test_customer_http_cannot_show_foreign_ticket(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($other)->create();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/tickets/'.$ticket->id)
            ->assertForbidden();
    }

    public function test_supervisor_http_can_show_foreign_ticket_but_not_transition(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/tickets/'.$ticket->id)
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id);

        $this->actingAs($supervisor, 'sanctum')
            ->postJson('/api/tickets/'.$ticket->id.'/transition', [
                'status' => TicketStatus::Closed->value,
            ])
            ->assertForbidden();
    }

    public function test_agent_http_can_transition(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->actingAs($agent, 'sanctum')
            ->postJson('/api/tickets/'.$ticket->id.'/transition', [
                'status' => TicketStatus::InProgress->value,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');
    }
}