<?php

namespace App\Console\Commands\Tickets;

use App\Repositories\Tickets\TicketRepository;
use App\Services\Tickets\CloseInactiveResolvedTicketsService;
use Illuminate\Console\Command;

class AutoCloseTicketsCommand extends Command
{
    protected $signature = 'tickets:auto-close
                            {--days=7 : Close resolved tickets inactive for this many days}
                            {--dry-run : List candidates without closing}';

    protected $description = 'Auto-close resolved tickets that have been inactive (uses CloseInactiveResolvedTicketsService)';

    public function handle(
        TicketRepository $tickets,
        CloseInactiveResolvedTicketsService $closer,
    ): int {
        $days = max(1, (int) $this->option('days'));
        $since = now()->subDays($days);

        $candidates = $tickets->findResolvedInactiveSince($since);

        if ($this->option('dry-run')) {
            $this->info("Dry-run: {$candidates->count()} ticket(s) would be closed (inactive since {$since->toDateTimeString()}).");

            foreach ($candidates as $ticket) {
                $this->line("  #{$ticket->id} — {$ticket->title}");
            }

            return self::SUCCESS;
        }

        $closed = $closer->handle($since);

        $this->info("Closed {$closed} ticket(s) (inactive since {$since->toDateTimeString()}).");

        return self::SUCCESS;
    }
}