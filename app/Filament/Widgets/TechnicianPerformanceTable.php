<?php

namespace App\Filament\Widgets;

use App\Enums\EnumServiceOrderStatus;
use App\Models\User;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TechnicianPerformanceTable extends BaseWidget
{
    protected static ?string $heading = 'Desempeño de técnicos';

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $completedStates = [
            EnumServiceOrderStatus::COMPLETED->value,
            EnumServiceOrderStatus::CLOSED->value,
        ];
        $openStates = array_diff(EnumServiceOrderStatus::values(), $completedStates);

        $now = Carbon::now();
        $sevenDaysLater = $now->copy()->addDays(7);

        $query = User::query()
            ->whereHas('serviceOrders')
            ->with('serviceOrders')
            ->withCount([
                'serviceOrders as total_asignadas',
                'serviceOrders as total_cerradas' => function (Builder $query) use ($completedStates): void {
                    $query->whereIn('state', $completedStates);
                },
                'serviceOrders as abiertas' => function (Builder $query) use ($openStates): void {
                    $query->whereIn('state', $openStates);
                },
                'serviceOrders as atrasadas' => function (Builder $query) use ($now): void {
                    $query
                        ->whereNull('completed_at')
                        ->whereNotNull('scheduled_at')
                        ->where('scheduled_at', '<', $now);
                },
                'serviceOrders as proximas' => function (Builder $query) use ($now, $sevenDaysLater): void {
                    $query
                        ->whereNull('completed_at')
                        ->whereNotNull('scheduled_at')
                        ->whereBetween('scheduled_at', [$now, $sevenDaysLater]);
                },
            ])
            ->orderByDesc('total_asignadas');

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('full_name')
                ->label('Técnico')
                ->getStateUsing(fn(User $record): string => $record->full_name ?? $record->name ?? 'Sin nombre')
                ->searchable(['name', 'last_name']),
            Tables\Columns\TextColumn::make('total_asignadas')
                ->label('Asignadas')
                ->numeric(),
            Tables\Columns\TextColumn::make('total_cerradas')
                ->label('Cerradas')
                ->numeric(),
            Tables\Columns\TextColumn::make('completion_rate')
                ->label('% completadas')
                ->getStateUsing(function (User $record): string {
                    $assigned = (int) ($record->total_asignadas ?? 0);
                    $closed = (int) ($record->total_cerradas ?? 0);

                    return $assigned > 0
                        ? number_format(($closed / $assigned) * 100, 1) . '%'
                        : '0%';
                }),
            Tables\Columns\TextColumn::make('promedio_ciclo_horas')
                ->label('Prom. ciclo')
                ->getStateUsing(function (User $record): string {
                    $hours = $record->serviceOrders
                        ->filter(fn($order) => $order->completed_at !== null && $order->state && in_array($order->state, [
                            EnumServiceOrderStatus::CLOSED->value,
                        ], true))
                        ->map(function ($order): float {
                            $startedAt = $order->created_at
                                ? Carbon::parse($order->created_at)
                                : Carbon::parse($order->check_in_date)->startOfDay();

                            return $startedAt->diffInMinutes(Carbon::parse($order->completed_at)) / 60;
                        })
                        ->avg();

                    if ($hours === null) {
                        return 'Sin datos';
                    }

                    if ($hours < 24) {
                        return number_format($hours, 1) . ' h';
                    }

                    $days = floor($hours / 24);
                    $remainingHours = $hours - ($days * 24);

                    return sprintf('%d d %d h', $days, (int) round($remainingHours));
                }),
            Tables\Columns\TextColumn::make('abiertas')
                ->label('Abiertas')
                ->numeric(),
            Tables\Columns\TextColumn::make('atrasadas')
                ->label('Atrasadas')
                ->numeric()
                ->badge()
                ->color(fn(User $record): string => ($record->atrasadas ?? 0) > 0 ? 'danger' : 'success'),
            Tables\Columns\TextColumn::make('proximas')
                ->label('Próximas 7d')
                ->numeric(),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
