<?php

namespace App\Jobs\Tickets;

use App\Mail\Tickets\TicketResolvedMail;
use App\Repositories\Tickets\TicketRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendTicketResolvedMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $ticketId,
    ) {}

    public function handle(TicketRepository $tickets): void
    {
        $ticket = $tickets->findByIdWith($this->ticketId, ['creator']);

        if ($ticket === null || $ticket->creator === null) {
            return;
        }

        $cacheKey = "tickets:{$ticket->id}:resolved-mail";

        if (! Cache::add($cacheKey, true, now()->addDays(7))) {
            return;
        }

        Mail::to($ticket->creator)->send(new TicketResolvedMail($ticket));
    }
}
