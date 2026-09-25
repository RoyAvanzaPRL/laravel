<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Tickets\TicketRepository;

class CreateTicketService
{
    public function __construct(
        private TicketRepository $tickets,
    ) {}

    public function handle(User $creator, string $title, string $body): Ticket
    {
        $ticket = $this->tickets->create([
            'creator_id' => $creator->id,
            'title' => $title,
            'body' => $body,
            'status' => TicketStatus::Open,
            'last_activity_at' => now(),
        ]);

        return $ticket->load(['creator', 'assignee']);
    }
}
