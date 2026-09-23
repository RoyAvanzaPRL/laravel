<?php

namespace Tests\Fakes;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Tickets\TicketRepository;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
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

    public function findByIdOrFail(int $id): Ticket
    {
        $ticket = $this->findById($id);

        if ($ticket === null) {
            throw (new ModelNotFoundException)->setModel(Ticket::class, [$id]);
        }

        return $ticket;
    }

    public function findByIdWith(int $id, array $relations): ?Ticket
    {
        return $this->findById($id);
    }

    public function paginateVisibleTo(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $items = $this->tickets->values();

        return new Paginator($items->forPage(1, $perPage)->values(), $items->count(), $perPage);
    }

    public function create(array $attributes): Ticket
    {
        $ticket = new Ticket($attributes);
        $ticket->id = ($this->tickets->max('id') ?? 0) + 1;
        $this->tickets->push($ticket);

        return $ticket;
    }

    public function update(Ticket $ticket, array $attributes): Ticket
    {
        $ticket->fill($attributes);

        return $ticket;
    }
}
