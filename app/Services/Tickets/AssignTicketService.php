<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Jobs\Tickets\SendTicketAssignedMailJob;

class AssignTicketService
{
    public function handle(Ticket $ticket, User $assignee): Ticket
    {
        $ticket->assignTo($assignee);
    
        $ticket = $ticket->fresh(['creator', 'assignee']);
    
        SendTicketAssignedMailJob::dispatch($ticket->id);
    
        return $ticket;
    }
}