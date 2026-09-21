<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;

class CloseTicketService
{
    public function __construct(
        private TransitionTicketService $transitionTicket,
    ) {}

    public function handle(Ticket $ticket): Ticket
    {
        return $this->transitionTicket->handle($ticket, TicketStatus::Closed);
    }
}