<x-mail::message>
# Ticket assigned

Hello {{ $ticket->assignee?->name ?? 'agent' }},

Ticket **#{{ $ticket->id }}** — {{ $ticket->title }} — has been assigned to you.

{{ $ticket->body }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>