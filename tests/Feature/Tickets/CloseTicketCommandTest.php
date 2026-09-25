<?php

namespace Tests\Feature\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseTicketCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_can_close_ticket_from_console_without_http(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($customer)->create([
            'status' => TicketStatus::Open,
        ]);

        $this->artisan('tickets:close', ['ticket' => $ticket->id])
            ->assertSuccessful();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Closed->value,
        ]);

        $this->assertNotNull($ticket->fresh()->closed_at);
    }

    public function test_console_close_fails_when_transition_is_invalid(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($customer)->closed()->create();

        $this->artisan('tickets:close', ['ticket' => $ticket->id])
            ->assertFailed();
    }
}