<?php

namespace App\Repositories\Tickets;

use App\Models\Ticket;
use App\Models\User;
use DateTimeInterface;
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
}