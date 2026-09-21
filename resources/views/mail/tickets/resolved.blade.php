<x-mail::message>
# Ticket resolved

Hello {{ $ticket->creator?->name ?? 'customer' }},

Your ticket **#{{ $ticket->id }}** — {{ $ticket->title }} — has been marked as resolved.

If you still need help, reply on the ticket or open a new one.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>