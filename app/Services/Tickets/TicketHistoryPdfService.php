<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TicketHistoryPdfService
{
    public const DISK = 'local';

    public function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    public function pathFor(Ticket|int $ticket): string
    {
        $id = $ticket instanceof Ticket ? $ticket->id : $ticket;

        return "ticket-histories/{$id}.pdf";
    }

    public function exists(Ticket|int $ticket): bool
    {
        return $this->disk()->exists($this->pathFor($ticket));
    }

    /**
     * Ensure the PDF exists on disk. Does not regenerate unless $force is true.
     *
     * @return string Relative path on the disk
     */
    public function ensureGenerated(Ticket $ticket, bool $force = false): string
    {
        $path = $this->pathFor($ticket);

        if (! $force && $this->disk()->exists($path)) {
            return $path;
        }

        $ticket->loadMissing(['comments.user']);

        $pdf = Pdf::loadView('pdf.ticket-history', ['ticket' => $ticket]);

        $this->disk()->put($path, $pdf->output());

        return $path;
    }

    public function absolutePath(Ticket|int $ticket): string
    {
        $path = $this->pathFor($ticket);

        if (! $this->disk()->exists($path)) {
            throw new RuntimeException("Ticket history PDF not found at [{$path}].");
        }

        return $this->disk()->path($path);
    }
}