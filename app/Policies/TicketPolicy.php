<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('tickets.view');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if (! $user->is_active || ! $user->can('tickets.view')) {
            return false;
        }

        return $this->ownsOrStaff($user, $ticket);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('tickets.create');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === TicketStatus::Closed) {
            return false;
        }

        return $this->view($user, $ticket);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === TicketStatus::Closed) {
            return false;
        }

        return $user->is_active
            && $user->can('tickets.assign')
            && $this->view($user, $ticket);
    }

    /**
     * Cerrar. Un ticket ya Closed no se “cierra” otra vez.
     * Reopen (Closed → Open) va por transition().
     */
    public function close(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === TicketStatus::Closed) {
            return false;
        }
        return $user->is_active
            && $user->can('tickets.close')
            && $this->view($user, $ticket);
    }
    /**
     * Cambiar estado que no es close (in_progress, resolved, reopen…).
     * La validez de la transición concreta la sigue imponiendo el modelo.
     */
    public function transition(User $user, Ticket $ticket): bool
    {
        return $user->is_active
            && $user->can('tickets.close')
            && $this->view($user, $ticket);
    }

    public function comment(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === TicketStatus::Closed) {
            return false;
        }

        return $user->is_active
            && $user->can('comments.create')
            && $this->view($user, $ticket);
    }

    public function attach(User $user, Ticket $ticket): bool
    {
        return $user->is_active
            && $user->can('attachments.create')
            && $this->comment($user, $ticket);
    }

    /**
     * Propiedad o staff (staff = quien puede asignar; no usamos hasRole).
     */
    private function ownsOrStaff(User $user, Ticket $ticket): bool
    {
        if ($ticket->creator_id === $user->id || $ticket->assignee_id === $user->id) {
            return true;
        }
        return $user->can('tickets.view_any');
    }

    public function downloadHistory(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}