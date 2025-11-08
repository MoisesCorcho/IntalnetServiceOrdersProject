<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopCustomersTable extends BaseWidget
{
    protected static ?string $heading = 'Clientes más activos';

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $statsSubquery = DB::table('service_orders')
            ->select([
                'customer_id',
                DB::raw('COUNT(*) as total_ordenes'),
                DB::raw('SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as ordenes_cerradas'),
                DB::raw('MIN(check_in_date) as primera_orden'),
                DB::raw('MAX(check_in_date) as ultima_orden'),
            ])
            ->whereNotNull('customer_id')
            ->groupBy('customer_id');

        return Customer::query()
            ->select([
                'customers.id',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'customers.phone',
                DB::raw('order_stats.total_ordenes as total_ordenes'),
                DB::raw('order_stats.ordenes_cerradas as ordenes_cerradas'),
                DB::raw('order_stats.primera_orden as primera_orden'),
                DB::raw('order_stats.ultima_orden as ultima_orden'),
            ])
            ->joinSub($statsSubquery, 'order_stats', 'order_stats.customer_id', '=', 'customers.id')
            ->orderByDesc('order_stats.total_ordenes');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('full_name')
                ->label('Cliente')
                ->getStateUsing(fn (Customer $record): string => $record->full_name ?? 'Sin nombre')
                ->searchable(['first_name', 'last_name']),
            Tables\Columns\TextColumn::make('total_ordenes')
                ->label('Órdenes')
                ->numeric(),
            Tables\Columns\TextColumn::make('ordenes_cerradas')
                ->label('Cerradas')
                ->numeric(),
            Tables\Columns\TextColumn::make('recurrencia')
                ->label('Fidelidad')
                ->getStateUsing(function (Customer $record): string {
                    $total = (int) ($record->total_ordenes ?? 0);

                    return match (true) {
                        $total >= 10 => 'Alta',
                        $total >= 5 => 'Media',
                        $total > 0 => 'Baja',
                        default => 'Sin datos',
                    };
                })
                ->badge()
                ->color(function (Customer $record): string {
                    $total = (int) ($record->total_ordenes ?? 0);

                    return match (true) {
                        $total >= 10 => 'success',
                        $total >= 5 => 'warning',
                        $total > 0 => 'gray',
                        default => 'secondary',
                    };
                }),
            Tables\Columns\TextColumn::make('ultima_orden')
                ->label('Última orden')
                ->date('d/m/Y'),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25];
    }
}

