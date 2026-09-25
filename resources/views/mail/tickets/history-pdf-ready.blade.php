<x-mail::message>
# History PDF ready

Hello {{ $ticket->creator?->name ?? 'customer' }},

The history PDF for ticket **#{{ $ticket->id }}** is ready.

Stored at: `{{ $diskPath }}`

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
