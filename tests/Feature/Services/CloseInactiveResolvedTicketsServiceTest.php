<?php

namespace Tests\Feature\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\Tickets\CloseInactiveResolvedTicketsService;
use App\Services\Tickets\CloseTicketService;
use Mockery;
use Tests\Fakes\InMemoryTicketRepository;
use Tests\TestCase;

class CloseInactiveResolvedTicketsServiceTest extends TestCase
{
    public function test_service_uses_repository_interface_with_in_memory_fake(): void
    {
        $inactive = Ticket::factory()->make([
            'id' => 1,
            'status' => TicketStatus::Resolved,
            'last_activity_at' => now()->subDays(10),
        ]);

        $recent = Ticket::factory()->make([
            'id' => 2,
            'status' => TicketStatus::Resolved,
            'last_activity_at' => now()->subDay(),
        ]);

        $fake = new InMemoryTicketRepository(collect([$inactive, $recent]));

        $closeTicket = Mockery::mock(CloseTicketService::class);
        $closeTicket
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(fn (Ticket $ticket) => $ticket->id === 1))
            ->andReturn($inactive);

        $service = new CloseInactiveResolvedTicketsService($fake, $closeTicket);

        $closed = $service->handle(now()->subDays(7));

        $this->assertSame(1, $closed);
    }
}
