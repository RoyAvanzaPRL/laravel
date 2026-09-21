<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Jobs\Tickets\GenerateTicketHistoryPdfJob;
use App\Jobs\Tickets\SendTicketResolvedMailJob;
use App\Models\Ticket;

class TransitionTicketService
{
    public function handle(Ticket $ticket, TicketStatus $next): Ticket
    {
        $ticket->transitionTo($next);

        $ticket = $ticket->fresh(['creator', 'assignee']);

        if ($next === TicketStatus::Resolved) {
            SendTicketResolvedMailJob::dispatch($ticket->id);
            GenerateTicketHistoryPdfJob::dispatch($ticket->id);
        }

        return $ticket;
    }
}