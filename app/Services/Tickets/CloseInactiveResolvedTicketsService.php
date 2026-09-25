<?php

namespace App\Services\Tickets;

use App\Repositories\Tickets\TicketRepository;
use DateTimeInterface;
use InvalidArgumentException;

class CloseInactiveResolvedTicketsService
{
    public function __construct(
        private TicketRepository $tickets,
        private CloseTicketService $closeTicket,
    ) {}

    /**
     * @return int Number of tickets closed
     */
    public function handle(DateTimeInterface $since): int
    {
        $closed = 0;

        foreach ($this->tickets->findResolvedInactiveSince($since) as $ticket) {
            try {
                $this->closeTicket->handle($ticket);
                $closed++;
            } catch (InvalidArgumentException) {
                // Skip tickets that cannot transition (race / state change).
            }
        }

        return $closed;
    }
}