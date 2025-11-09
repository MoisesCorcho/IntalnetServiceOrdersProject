<?php

namespace App\Filament\Widgets;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class ServiceOrdersSummaryStats extends BaseWidget
{
    protected function getCards(): array
    {
        $openStates = array_diff(
            EnumServiceOrderStatus::values(),
            [EnumServiceOrderStatus::COMPLETED->value, EnumServiceOrderStatus::CLOSED->value]
        );

        $openCount = ServiceOrder::query()
            ->whereIn('state', $openStates)
            ->count();

        $closedCount = ServiceOrder::query()
            ->whereIn('state', [
                EnumServiceOrderStatus::COMPLETED->value,
                EnumServiceOrderStatus::CLOSED->value,
            ])
            ->count();

        $avgCycleHours = ServiceOrder::query()
            ->whereNotNull('completed_at')
            ->get(['check_in_date', 'completed_at'])
            ->avg(function (ServiceOrder $order): float {
                $receivedAt = Carbon::parse($order->check_in_date)->startOfDay();
                $completedAt = Carbon::parse($order->completed_at);

                return $receivedAt->diffInMinutes($completedAt) / 60;
            });

        $avgCycleDisplay = $avgCycleHours
            ? $this->formatHours((float) $avgCycleHours)
            : 'Sin datos';

        $nextAppointment = ServiceOrder::query()
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->first();

        $nextAppointmentDisplay = $nextAppointment
            ? Carbon::parse($nextAppointment->scheduled_at)->translatedFormat('d/m/Y H:i')
            : 'Sin programación';

        $nextAppointmentTechnician = $nextAppointment?->assignedUser?->full_name ?: 'Sin técnico asignado';

        return [
            Card::make('Órdenes abiertas', number_format($openCount))
                ->description('Cerradas: ' . number_format($closedCount))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('warning'),

            Card::make('Tiempo promedio de ciclo', $avgCycleDisplay)
                ->description('Desde recepción hasta cierre')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),

            Card::make('Próxima cita programada', $nextAppointmentDisplay)
                ->description($nextAppointmentTechnician)
                ->descriptionIcon('heroicon-o-calendar')
                ->extraAttributes([
                    'title' => $nextAppointment?->title,
                ])
                ->color('primary'),
        ];
    }

    private function formatHours(float $hours): string
    {
        if ($hours < 24) {
            return number_format($hours, 1) . ' h';
        }

        $days = floor($hours / 24);
        $remainingHours = $hours - ($days * 24);

        return sprintf('%d d %d h', $days, (int) round($remainingHours));
    }
}

