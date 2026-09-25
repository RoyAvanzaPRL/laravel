<?php

namespace App\Repositories\Tickets;

use App\Models\Ticket;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TicketRepository
{
    /**
     * Tickets que un agente puede tomar: abiertos y sin asignar.
     *
     * @return Collection<int, Ticket>
     */
    public function findAssignableByAgent(User $agent): Collection;

    /**
     * Tickets en resolved cuya última actividad es anterior a $since
     * (candidatos a cierre automático).
     *
     * @return Collection<int, Ticket>
     */
    public function findResolvedInactiveSince(DateTimeInterface $since): Collection;

    public function findById(int $id): ?Ticket;

    public function findByIdOrFail(int $id): Ticket;

    /**
     * @param  list<string>  $relations
     */
    public function findByIdWith(int $id, array $relations): ?Ticket;

    /**
     * @return LengthAwarePaginator<int, Ticket>
     */
    public function paginateVisibleTo(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Ticket;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Ticket $ticket, array $attributes): Ticket;
}
