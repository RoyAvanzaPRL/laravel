<?php

namespace App\Notifications\Tickets;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketHistoryPdfReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $diskPath,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket #{$this->ticket->id} history PDF is ready")
            ->markdown('mail.tickets.history-pdf-ready', [
                'ticket' => $this->ticket,
                'diskPath' => $this->diskPath,
            ]);
    }
}
