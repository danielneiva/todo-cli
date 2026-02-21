<?php

namespace App\Enums;

/**
 * Internal classification for task statuses.
 * This determines how the app logic treats each status,
 * regardless of the user-defined status name.
 */
enum StatusType: string
{
    case Inbox = 'inbox';
    case Active = 'active';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Inbox => 'Inbox',
            self::Active => 'Active',
            self::Done => 'Done',
            self::Cancelled => 'Cancelled',
        };
    }
}