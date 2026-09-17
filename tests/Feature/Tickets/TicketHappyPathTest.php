<?php

namespace Tests\Feature\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketHappyPathTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_authenticated_happy_path_for_ticket_flow(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $create = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/tickets', [
                'title' => 'Printer is broken',
                'body' => 'Cannot print invoices.',
            ]);

        $create
            ->assertCreated()
            ->assertJsonPath('data.title', 'Printer is broken')
            ->assertJsonPath('data.status', 'open');

        $ticketId = $create->json('data.id');

        $comment = $this->actingAs($customer, 'sanctum')
            ->post('/api/tickets/'.$ticketId.'/comments', [
                'body' => 'Here is a photo of the error.',
                'attachment' => UploadedFile::fake()->create('error.pdf', 100, 'application/pdf'),
            ], [
                'Accept' => 'application/json',
            ]);

        $comment
            ->assertCreated()
            ->assertJsonPath('data.body', 'Here is a photo of the error.')
            ->assertJsonPath('data.attachments.0.original_name', 'error.pdf');

        $this->assertDatabaseHas('attachments', [
            'ticket_id' => $ticketId,
            'original_name' => 'error.pdf',
        ]);

        $assign = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/tickets/'.$ticketId.'/assign', [
                'assignee_id' => $agent->id,
            ]);

        $assign
            ->assertOk()
            ->assertJsonPath('data.assignee.id', $agent->id);

        $toInProgress = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/tickets/'.$ticketId.'/transition', [
                'status' => TicketStatus::InProgress->value,
            ]);

        $toInProgress
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $toResolved = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/tickets/'.$ticketId.'/transition', [
                'status' => TicketStatus::Resolved->value,
            ]);

        $toResolved
            ->assertOk()
            ->assertJsonPath('data.status', 'resolved');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'status' => 'resolved',
            'assignee_id' => $agent->id,
        ]);

        $this->assertNotNull(Ticket::query()->find($ticketId)?->resolved_at);
    }

    public function test_guest_cannot_create_ticket(): void
    {
        $this->postJson('/api/tickets', [
            'title' => 'Nope',
            'body' => 'Unauthenticated',
        ])->assertUnauthorized();
    }
}