<?php

namespace App\Filament\Widgets;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class ServiceOrderAlerts extends BaseWidget
{
    protected function getCards(): array
    {
        $withoutTechnician = ServiceOrder::query()
            ->whereNull('assigned_user_id')
            ->count();

        $overdue = ServiceOrder::query()
            ->whereNull('completed_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', Carbon::now())
            ->count();

        $withoutSchedule = ServiceOrder::query()
            ->whereNull('scheduled_at')
            ->whereNotIn('state', [
                EnumServiceOrderStatus::COMPLETED->value,
                EnumServiceOrderStatus::CLOSED->value,
            ])
            ->count();

        return [
            Card::make('Órdenes sin técnico', number_format($withoutTechnician))
                ->description('Asignar responsable')
                ->descriptionIcon('heroicon-o-user-minus')
                ->color('warning'),

            Card::make('Órdenes vencidas', number_format($overdue))
                ->description('Programación ya pasó')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Card::make('Órdenes sin programación', number_format($withoutSchedule))
                ->description('Revisar agenda')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('gray'),
        ];
    }
}

