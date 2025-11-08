<?php

namespace App\Enums;

enum EnumServiceOrderStatus: string
{
    case RECEIVED = 'received';
    case ON_THE_WAY = 'on_the_way';
    case AT_DESTINATION = 'at_destination';
    case PROCESS_STARTED = 'process_started';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';

    /**
     * Return all enum values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::cases()
        );
    }
}

