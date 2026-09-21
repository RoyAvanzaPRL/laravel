<?php

namespace App\Console\Commands\Tickets;

use App\Models\Ticket;
use App\Services\Tickets\CloseTicketService;
use Illuminate\Console\Command;
use InvalidArgumentException;

class CloseTicketCommand extends Command
{
    protected $signature = 'tickets:close {ticket : The ticket ID}';

    protected $description = 'Close a ticket using CloseTicketService (no HTTP)';

    public function handle(CloseTicketService $closeTicket): int
    {
        $ticket = Ticket::query()->findOrFail($this->argument('ticket'));

        try {
            $ticket = $closeTicket->handle($ticket);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Ticket #{$ticket->id} closed (status: {$ticket->status->value}).");

        return self::SUCCESS;
    }
}