<?php

namespace Tests\Feature\Jobs;

use App\Enums\TicketStatus;
use App\Jobs\Tickets\GenerateTicketHistoryPdfJob;
use App\Jobs\Tickets\SendTicketAssignedMailJob;
use App\Jobs\Tickets\SendTicketResolvedMailJob;
use App\Mail\Tickets\TicketAssignedMail;
use App\Mail\Tickets\TicketResolvedMail;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\Tickets\TicketHistoryPdfReadyNotification;
use App\Services\Tickets\AssignTicketService;
use App\Services\Tickets\TransitionTicketService;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketSideEffectsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_assign_dispatches_assigned_mail_job(): void
    {
        Queue::fake();

        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        app(AssignTicketService::class)->handle($ticket, $agent);

        Queue::assertPushed(SendTicketAssignedMailJob::class, function (SendTicketAssignedMailJob $job) use ($ticket) {
            return $job->ticketId === $ticket->id;
        });
    }

    public function test_assigned_mail_job_is_idempotent(): void
    {
        Mail::fake();

        $customer = User::factory()->create();
        $agent = User::factory()->create();

        $ticket = Ticket::factory()
            ->forCreator($customer)
            ->assignedTo($agent)
            ->create();

        $job = new SendTicketAssignedMailJob($ticket->id);

        $job->handle();
        $job->handle();

        Mail::assertSent(TicketAssignedMail::class, 1);
    }

    public function test_resolve_dispatches_resolved_mail_and_pdf_jobs(): void
    {
        Queue::fake();

        $customer = User::factory()->create();
        $agent = User::factory()->create();

        $ticket = Ticket::factory()
            ->forCreator($customer)
            ->assignedTo($agent)
            ->inProgress()
            ->create();

        app(TransitionTicketService::class)->handle($ticket, TicketStatus::Resolved);

        Queue::assertPushed(SendTicketResolvedMailJob::class, fn ($job) => $job->ticketId === $ticket->id);
        Queue::assertPushed(GenerateTicketHistoryPdfJob::class, fn ($job) => $job->ticketId === $ticket->id);
    }

    public function test_resolved_mail_job_is_idempotent(): void
    {
        Mail::fake();

        $customer = User::factory()->create();
        $ticket = Ticket::factory()
            ->forCreator($customer)
            ->resolved()
            ->create();

        $job = new SendTicketResolvedMailJob($ticket->id);

        $job->handle();
        $job->handle();

        Mail::assertSent(TicketResolvedMail::class, 1);
    }

    public function test_pdf_job_is_idempotent(): void
    {
        Storage::fake('local');
        Notification::fake();

        Pdf::shouldReceive('loadView')
            ->once()
            ->andReturnSelf();
        Pdf::shouldReceive('output')
            ->once()
            ->andReturn('%PDF-fake');

        $customer = User::factory()->create();
        $ticket = Ticket::factory()
            ->forCreator($customer)
            ->resolved()
            ->create();

        $job = new GenerateTicketHistoryPdfJob($ticket->id);

        $job->handle();
        $job->handle();

        Storage::disk('local')->assertExists("ticket-histories/{$ticket->id}.pdf");
        Notification::assertSentTo($customer, TicketHistoryPdfReadyNotification::class, 1);
    }
}