<?php

namespace Tests\Fakes;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Tickets\TicketRepository;
use DateTimeInterface;
use Illuminate\Support\Collection;

class InMemoryTicketRepository implements TicketRepository
{
    /** @param  Collection<int, Ticket>  $tickets */
    public function __construct(
        private Collection $tickets = new Collection,
    ) {}

    public function add(Ticket $ticket): void
    {
        $this->tickets->push($ticket);
    }

    public function findAssignableByAgent(User $agent): Collection
    {
        if (! $agent->is_active) {
            return collect();
        }

        return $this->tickets
            ->filter(fn (Ticket $ticket) => $ticket->status === TicketStatus::Open
                && $ticket->assignee_id === null)
            ->values();
    }

    public function findResolvedInactiveSince(DateTimeInterface $since): Collection
    {
        return $this->tickets
            ->filter(fn (Ticket $ticket) => $ticket->status === TicketStatus::Resolved
                && $ticket->last_activity_at !== null
                && $ticket->last_activity_at < $since)
            ->values();
    }

    public function findById(int $id): ?Ticket
    {
        return $this->tickets->firstWhere('id', $id);
    }
}
