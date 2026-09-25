<?php

namespace Tests\Feature\Console;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AutoCloseTicketsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Queue::fake();
    }

    public function test_dry_run_does_not_persist_closes(): void
    {
        $customer = User::factory()->create();

        $inactive = Ticket::factory()
            ->forCreator($customer)
            ->resolved()
            ->create([
                'last_activity_at' => now()->subDays(10),
            ]);

        $this->artisan('tickets:auto-close', [
            '--days' => 7,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('tickets', [
            'id' => $inactive->id,
            'status' => TicketStatus::Resolved->value,
        ]);
    }

    public function test_real_run_closes_inactive_resolved_only(): void
    {
        $customer = User::factory()->create();

        $inactiveResolved = Ticket::factory()
            ->forCreator($customer)
            ->resolved()
            ->create([
                'last_activity_at' => now()->subDays(10),
            ]);

        $recentResolved = Ticket::factory()
            ->forCreator($customer)
            ->resolved()
            ->create([
                'last_activity_at' => now()->subDays(2),
            ]);

        $openTicket = Ticket::factory()
            ->forCreator($customer)
            ->create([
                'status' => TicketStatus::Open,
                'last_activity_at' => now()->subDays(30),
            ]);

        $this->artisan('tickets:auto-close', [
            '--days' => 7,
        ])->assertSuccessful();

        $this->assertDatabaseHas('tickets', [
            'id' => $inactiveResolved->id,
            'status' => TicketStatus::Closed->value,
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $recentResolved->id,
            'status' => TicketStatus::Resolved->value,
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $openTicket->id,
            'status' => TicketStatus::Open->value,
        ]);
    }
}