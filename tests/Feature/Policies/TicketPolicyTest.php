<?php

namespace Tests\Feature\Policies;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_customer_can_view_own_ticket_but_not_others(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $own = Ticket::factory()->forCreator($customer)->create();
        $foreign = Ticket::factory()->forCreator($other)->create();

        $this->assertTrue($customer->can('view', $own));
        $this->assertFalse($customer->can('view', $foreign));
    }

    public function test_customer_cannot_assign_or_close(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->assertFalse($customer->can('assign', $ticket));
        $this->assertFalse($customer->can('close', $ticket));
    }

    public function test_agent_can_view_and_assign_open_ticket(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->assertTrue($agent->can('view', $ticket));
        $this->assertTrue($agent->can('assign', $ticket));
        $this->assertTrue($agent->can('close', $ticket));
    }

    public function test_agent_cannot_assign_closed_ticket(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::factory()->forCreator($customer)->closed()->create();

        $this->assertFalse($agent->can('assign', $ticket));
        $this->assertFalse($agent->can('comment', $ticket));
        $this->assertFalse($agent->can('close', $ticket));
        $this->assertTrue($agent->can('transition', $ticket));
    }

    public function test_customer_can_comment_open_but_not_closed(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $open = Ticket::factory()->forCreator($customer)->create();
        $closed = Ticket::factory()->forCreator($customer)->closed()->create();

        $this->assertTrue($customer->can('comment', $open));
        $this->assertFalse($customer->can('comment', $closed));
    }

    public function test_admin_can_view_any_but_cannot_comment_closed(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ticket = Ticket::factory()->forCreator($customer)->closed()->create();

        $this->assertTrue($admin->can('view', $ticket));
        $this->assertTrue($admin->can('viewAny', Ticket::class));
        $this->assertFalse($admin->can('comment', $ticket));
        $this->assertFalse($admin->can('update', $ticket));
    }

    public function test_agent_can_transition_closed_ticket_to_reopen(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::factory()->forCreator($customer)->closed()->create();

        $this->assertFalse($agent->can('close', $ticket));
        $this->assertTrue($agent->can('transition', $ticket));
    }
}