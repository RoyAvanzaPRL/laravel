<?php

namespace App\Jobs\Tickets;

use App\Models\Ticket;
use App\Notifications\Tickets\TicketHistoryPdfReadyNotification;
use App\Services\Tickets\TicketHistoryPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateTicketHistoryPdfJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $ticketId,
    ) {}

    public function handle(TicketHistoryPdfService $pdfs): void
    {
        $ticket = Ticket::query()
            ->with(['creator', 'comments.user'])
            ->find($this->ticketId);

        if ($ticket === null || $ticket->creator === null) {
            return;
        }

        // Already persisted → no regenerate, no second notification.
        if ($pdfs->exists($ticket)) {
            return;
        }

        $path = $pdfs->ensureGenerated($ticket);

        $ticket->creator->notify(new TicketHistoryPdfReadyNotification($ticket, $path));
    }
}