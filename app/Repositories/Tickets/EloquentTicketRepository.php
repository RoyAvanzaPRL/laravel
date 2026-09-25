<?php

namespace App\Repositories\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentTicketRepository implements TicketRepository
{
    public function findAssignableByAgent(User $agent): Collection
    {
        if (! $agent->is_active) {
            return collect();
        }

        return Ticket::query()
            ->where('status', TicketStatus::Open)
            ->whereNull('assignee_id')
            ->latest('last_activity_at')
            ->get();
    }

    public function findResolvedInactiveSince(DateTimeInterface $since): Collection
    {
        return Ticket::query()
            ->where('status', TicketStatus::Resolved)
            ->where('last_activity_at', '<', $since)
            ->get();
    }

    public function findById(int $id): ?Ticket
    {
        return Ticket::query()->find($id);
    }

    public function findByIdOrFail(int $id): Ticket
    {
        return Ticket::query()->findOrFail($id);
    }

    public function findByIdWith(int $id, array $relations): ?Ticket
    {
        return Ticket::query()->with($relations)->find($id);
    }

    public function paginateVisibleTo(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Ticket::query()
            ->visibleTo($user)
            ->with(['creator', 'assignee'])
            ->latest('last_activity_at')
            ->paginate($perPage);
    }

    public function create(array $attributes): Ticket
    {
        return Ticket::query()->create($attributes);
    }

    public function update(Ticket $ticket, array $attributes): Ticket
    {
        $ticket->fill($attributes);
        $ticket->save();

        return $ticket;
    }
}
