<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Repositories\Tickets\TicketRepository;

class UpdateTicketService
{
    public function __construct(
        private TicketRepository $tickets,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Ticket $ticket, array $attributes): Ticket
    {
        $attributes['last_activity_at'] = now();

        $ticket = $this->tickets->update($ticket, $attributes);

        return $ticket->load(['creator', 'assignee']);
    }
}
