<?php

namespace App\Jobs\Tickets;

use App\Notifications\Tickets\TicketHistoryPdfReadyNotification;
use App\Repositories\Tickets\TicketRepository;
use App\Services\Tickets\TicketHistoryPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateTicketHistoryPdfJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $ticketId,
    ) {}

    public function handle(TicketHistoryPdfService $pdfs, TicketRepository $tickets): void
    {
        $ticket = $tickets->findByIdWith($this->ticketId, ['creator', 'comments.user']);

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
