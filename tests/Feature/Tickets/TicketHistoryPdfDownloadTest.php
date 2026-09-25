<?php

namespace Tests\Feature\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\TicketHistoryPdfService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TicketHistoryPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
    }

    public function test_owner_gets_temporary_signed_url_when_pdf_exists(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $pdfs = app(TicketHistoryPdfService::class);
        Storage::disk('local')->put($pdfs->pathFor($ticket), '%PDF-fake');

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/tickets/'.$ticket->id.'/history-pdf');

        $response
            ->assertOk()
            ->assertJsonStructure(['url', 'expires_in_minutes']);

        $this->assertStringContainsString('signature=', $response->json('url'));
    }

    public function test_foreign_customer_cannot_request_download_url(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('customer');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($owner)->create();

        $pdfs = app(TicketHistoryPdfService::class);
        Storage::disk('local')->put($pdfs->pathFor($ticket), '%PDF-fake');

        $this->actingAs($other, 'sanctum')
            ->getJson('/api/tickets/'.$ticket->id.'/history-pdf')
            ->assertForbidden();
    }

    public function test_signed_route_downloads_existing_pdf_without_regenerating(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $pdfs = app(TicketHistoryPdfService::class);
        $path = $pdfs->pathFor($ticket);
        Storage::disk('local')->put($path, '%PDF-persisted');

        $url = URL::temporarySignedRoute(
            'tickets.history-pdf.download',
            now()->addMinutes(15),
            ['ticket' => $ticket->id],
        );

        $this->get($url)
            ->assertOk()
            ->assertHeader('content-disposition');

        // Still the same bytes — download did not regenerate.
        $this->assertSame('%PDF-persisted', Storage::disk('local')->get($path));
    }

    public function test_unsigned_download_url_is_rejected(): void
    {
        $customer = User::factory()->create();
        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->get('/tickets/'.$ticket->id.'/history-pdf/file')
            ->assertForbidden();
    }

    public function test_returns_404_when_pdf_not_ready(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ticket = Ticket::factory()->forCreator($customer)->create();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/tickets/'.$ticket->id.'/history-pdf')
            ->assertNotFound();
    }
}