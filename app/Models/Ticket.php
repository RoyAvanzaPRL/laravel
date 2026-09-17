<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

#[Fillable([
    'creator_id',
    'assignee_id',
    'title',
    'body',
    'status',
    'resolved_at',
    'closed_at',
    'last_activity_at',
])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function transitionTo(TicketStatus $next): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw new InvalidArgumentException(
                "Cannot transition from {$this->status->value} to {$next->value}."
            );
        }

        $this->status = $next;
        $this->resolved_at = $next === TicketStatus::Resolved ? now() : null;
        $this->closed_at = $next === TicketStatus::Closed ? now() : null;
        $this->last_activity_at = now();
        $this->save();
    }

    public function assignTo(User $user): void
    {
        if (! $user->is_active) {
            throw new InvalidArgumentException('Cannot assign ticket to an inactive user.');
        }

        $this->assignee_id = $user->id;
        $this->last_activity_at = now();
        $this->save();
    }
}