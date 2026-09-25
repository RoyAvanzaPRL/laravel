<?php

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::factory()->create([
            'name' => 'Demo Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $customer->assignRole('customer');

        $agent = User::factory()->create([
            'name' => 'Demo Agent',
            'email' => 'agent@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $agent->assignRole('agent');

        $admin = User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $supervisor = User::factory()->create([
            'name' => 'Demo Supervisor',
            'email' => 'supervisor@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $supervisor->assignRole('supervisor');

        $openTicket = Ticket::factory()
            ->forCreator($customer)
            ->create([
                'title' => 'Cannot login to the portal',
                'body' => 'I get an error when trying to sign in.',
                'status' => TicketStatus::Open,
            ]);

        Comment::factory()->create([
            'ticket_id' => $openTicket->id,
            'user_id' => $customer->id,
            'body' => 'Still happening after clearing cache.',
        ]);

        Ticket::factory()
            ->forCreator($customer)
            ->assignedTo($agent)
            ->inProgress()
            ->create([
                'title' => 'Invoice PDF is blank',
                'body' => 'The downloaded invoice has no content.',
                'assignee_id' => $agent->id,
                'status' => TicketStatus::InProgress,
            ]);

        Ticket::factory()
            ->forCreator($customer)
            ->assignedTo($agent)
            ->resolved()
            ->create([
                'title' => 'Wrong language in emails',
                'body' => 'Notification emails arrive in English instead of Spanish.',
                'assignee_id' => $agent->id,
                'status' => TicketStatus::Resolved,
                'resolved_at' => now(),
            ]);
    }
}