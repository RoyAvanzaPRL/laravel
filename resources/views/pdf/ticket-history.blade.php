<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket #{{ $ticket->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
        .comment { margin-bottom: 12px; border-bottom: 1px solid #ccc; padding-bottom: 8px; }
    </style>
</head>
<body>
    <h1>Ticket #{{ $ticket->id }} — {{ $ticket->title }}</h1>
    <p><strong>Status:</strong> {{ $ticket->status->value }}</p>
    <p>{{ $ticket->body }}</p>

    <h2>History</h2>
    @forelse ($ticket->comments as $comment)
        <div class="comment">
            <strong>{{ $comment->user?->name ?? 'User' }}</strong>
            <em>{{ $comment->created_at }}</em>
            <p>{{ $comment->body }}</p>
        </div>
    @empty
        <p>No comments.</p>
    @endforelse
</body>
</html>