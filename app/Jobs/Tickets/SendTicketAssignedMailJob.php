<?php

namespace App\Jobs\Tickets;

use App\Mail\Tickets\TicketAssignedMail;
use App\Repositories\Tickets\TicketRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendTicketAssignedMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $ticketId,
    ) {}

    public function handle(TicketRepository $tickets): void
    {
        $ticket = $tickets->findByIdWith($this->ticketId, ['assignee']);

        if ($ticket === null || $ticket->assignee === null) {
            return;
        }

        $cacheKey = "tickets:{$ticket->id}:assigned-mail:{$ticket->assignee_id}";

        // Atomic: only the first execution proceeds.
        if (! Cache::add($cacheKey, true, now()->addDays(7))) {
            return;
        }

        Mail::to($ticket->assignee)->send(new TicketAssignedMail($ticket));
    }
}
