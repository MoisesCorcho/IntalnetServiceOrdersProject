<?php

namespace App\Filament\Widgets;

use App\Models\ServiceOrder;
use App\Enums\EnumServiceOrderStatus;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingServiceAppointments extends BaseWidget
{
    protected static ?string $heading = 'Próximas citas';

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $openStates = [
            EnumServiceOrderStatus::RECEIVED->value,
            EnumServiceOrderStatus::ON_THE_WAY->value,
            EnumServiceOrderStatus::AT_DESTINATION->value,
            EnumServiceOrderStatus::PROCESS_STARTED->value,
        ];

        return ServiceOrder::query()
            ->with(['assignedUser', 'customer'])
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now()->startOfDay())
            ->whereIn('state', $openStates)
            ->whereNull('completed_at')
            ->orderBy('scheduled_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('order_number')
                ->label('Orden')
                ->searchable(),
            Tables\Columns\TextColumn::make('title')
                ->label('Servicio')
                ->limit(30)
                ->tooltip(fn (ServiceOrder $record): ?string => $record->title),
            Tables\Columns\TextColumn::make('scheduled_at')
                ->label('Programado')
                ->dateTime('d/m/Y H:i'),
            Tables\Columns\TextColumn::make('assignedUser.full_name')
                ->label('Técnico')
                ->placeholder('Sin asignar'),
            Tables\Columns\TextColumn::make('customer_name_snapshot')
                ->label('Cliente'),
            Tables\Columns\TextColumn::make('state')
                ->label('Estado')
                ->badge(),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 20, 50];
    }
}

