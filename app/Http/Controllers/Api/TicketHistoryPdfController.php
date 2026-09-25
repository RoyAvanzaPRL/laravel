<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\Tickets\TicketHistoryPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketHistoryPdfController extends Controller
{
    public function show(
        Request $request,
        Ticket $ticket,
        TicketHistoryPdfService $pdfs,
    ): JsonResponse {
        $this->authorize('downloadHistory', $ticket);

        if (! $pdfs->exists($ticket)) {
            return response()->json([
                'message' => 'History PDF is not ready yet.',
            ], 404);
        }

        $url = URL::temporarySignedRoute(
            'tickets.history-pdf.download',
            now()->addMinutes(15),
            ['ticket' => $ticket->id],
        );

        return response()->json([
            'url' => $url,
            'expires_in_minutes' => 15,
        ]);
    }

    public function download(
        Ticket $ticket,
        TicketHistoryPdfService $pdfs,
    ): BinaryFileResponse {
        // Signed URL only — do not regenerate on download.
        abort_unless($pdfs->exists($ticket), 404);

        return response()->download(
            $pdfs->absolutePath($ticket),
            "ticket-{$ticket->id}-history.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }
}