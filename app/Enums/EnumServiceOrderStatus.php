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

    /**
     * Return the enum values mapped to their Spanish labels.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::RECEIVED->value => 'Recibido',
            self::ON_THE_WAY->value => 'En camino',
            self::AT_DESTINATION->value => 'En destino',
            self::PROCESS_STARTED->value => 'Proceso iniciado',
            self::COMPLETED->value => 'Completado',
            self::CLOSED->value => 'Cerrado',
        ];
    }
}

