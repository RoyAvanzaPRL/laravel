<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

#[Fillable(['ticket_id', 'user_id', 'body'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Comment $comment): void {
            $ticket = Ticket::query()->findOrFail($comment->ticket_id);
            $user = User::query()->findOrFail($comment->user_id);

            if ($ticket->status === TicketStatus::Closed) {
                throw new InvalidArgumentException('Cannot comment on a closed ticket.');
            }

            if (! $user->is_active) {
                throw new InvalidArgumentException('Inactive users cannot comment.');
            }
        });

        static::created(function (Comment $comment): void {
            $comment->ticket()->update([
                'last_activity_at' => now(),
            ]);
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}