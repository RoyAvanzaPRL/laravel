<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Open => in_array($next, [self::InProgress, self::Closed], true),
            self::InProgress => in_array($next, [self::Open, self::Resolved, self::Closed], true),
            self::Resolved => in_array($next, [self::InProgress, self::Closed], true),
            self::Closed => $next === self::Open,
        };
    }
}