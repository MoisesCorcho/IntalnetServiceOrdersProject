<?php

namespace App\Filament\Widgets;

use App\Models\ServiceOrder;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Filament\Support\Colors\Color;
use Filament\Widgets\ChartWidget;

class ServiceOrderForecastChart extends ChartWidget
{
    protected static ?string $heading = 'Tendencia y backlog';

    protected static ?string $maxHeight = '320px';

    protected static string $color = 'info';

    protected function getType(): string
    {
        return 'line';
    }

    protected static ?array $options = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => [
            'legend' => [
                'display' => true,
                'position' => 'bottom',
            ],
        ],
        'scales' => [
            'y' => [
                'ticks' => [
                    'precision' => 0,
                ],
            ],
        ],
    ];

    protected function getData(): array
    {
        $end = CarbonImmutable::now()->endOfMonth();
        $start = $end->subMonths(11)->startOfMonth();
        $period = collect(CarbonPeriod::create($start, '1 month', $end))
            ->map(fn ($date) => CarbonImmutable::parse($date)->startOfMonth());

        $orders = ServiceOrder::query()
            ->select(['check_in_date', 'completed_at'])
            ->where(function ($query) use ($start): void {
                $query->whereDate('check_in_date', '>=', $start->toDateString())
                    ->orWhereDate('completed_at', '>=', $start->toDateString());
            })
            ->get();

        $received = [];
        $closed = [];

        foreach ($orders as $order) {
            if ($order->check_in_date) {
                $bucket = CarbonImmutable::parse($order->check_in_date)->startOfMonth();

                if ($bucket->lessThan($start)) {
                    continue;
                }

                $key = $bucket->format('Y-m-01');
                $received[$key] = ($received[$key] ?? 0) + 1;
            }

            if ($order->completed_at) {
                $bucket = CarbonImmutable::parse($order->completed_at)->startOfMonth();

                if ($bucket->lessThan($start)) {
                    continue;
                }

                $key = $bucket->format('Y-m-01');
                $closed[$key] = ($closed[$key] ?? 0) + 1;
            }
        }

        $labels = [];
        $receivedDataset = [];
        $closedDataset = [];
        $backlogDataset = [];
        $runningBacklog = 0;

        foreach ($period as $month) {
            $key = $month->format('Y-m-01');
            $monthReceived = (int) ($received[$key] ?? 0);
            $monthClosed = (int) ($closed[$key] ?? 0);

            $runningBacklog += ($monthReceived - $monthClosed);

            $labels[] = $month->translatedFormat('M Y');
            $receivedDataset[] = $monthReceived;
            $closedDataset[] = $monthClosed;
            $backlogDataset[] = max($runningBacklog, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes recibidas',
                    'data' => $receivedDataset,
                    'borderColor' => Color::Blue[500],
                    'backgroundColor' => Color::Blue[200],
                    'fill' => false,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Órdenes cerradas',
                    'data' => $closedDataset,
                    'borderColor' => Color::Green[500],
                    'backgroundColor' => Color::Green[200],
                    'fill' => false,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Backlog proyectado',
                    'data' => $backlogDataset,
                    'borderColor' => Color::Amber[500],
                    'backgroundColor' => Color::Amber[200],
                    'fill' => false,
                    'borderDash' => [6, 6],
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}

