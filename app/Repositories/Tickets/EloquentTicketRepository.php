<?php

namespace App\Repositories\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use DateTimeInterface;
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
}