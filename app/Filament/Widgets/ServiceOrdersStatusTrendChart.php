<?php

namespace App\Filament\Widgets;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Filament\Support\Colors\Color;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class ServiceOrdersStatusTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Flujo de órdenes por estado';

    protected static ?string $maxHeight = '320px';

    protected static string $color = 'primary';

    public ?string $filter = 'month';

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
            'x' => [
                'stacked' => true,
            ],
            'y' => [
                'stacked' => true,
                'ticks' => [
                    'precision' => 0,
                ],
            ],
        ],
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Últimas 12 semanas',
            'month' => 'Últimos 12 meses',
        ];
    }

    protected function getData(): array
    {
        $periodData = $this->resolvePeriods();

        $records = ServiceOrder::query()
            ->whereDate('check_in_date', '>=', $periodData->get('start')->toDateString())
            ->get(['check_in_date', 'state']);

        $buckets = $periodData->get('buckets');
        $bucketFormat = $periodData->get('bucketFormat');
        $bucketMap = $buckets->mapWithKeys(fn (CarbonImmutable $bucket): array => [
            $bucket->format($bucketFormat) => $bucket,
        ]);
        $states = EnumServiceOrderStatus::cases();
        $labels = $buckets->map(fn (CarbonImmutable $date): string => $date->format($periodData->get('labelFormat')));

        $counts = [];

        foreach ($records as $order) {
            $checkIn = $order->check_in_date
                ? CarbonImmutable::parse($order->check_in_date)
                : null;

            if ($checkIn === null) {
                continue;
            }

            $bucket = $this->filter === 'week'
                ? $checkIn->startOfWeek()
                : $checkIn->startOfMonth();

            $key = $bucket->format($bucketFormat);

            if (! $bucketMap->has($key)) {
                continue;
            }

            $counts[$order->state][$key] = ($counts[$order->state][$key] ?? 0) + 1;
        }

        $dataset = [];

        foreach ($states as $state) {
            $dataset[] = [
                'label' => $state->label(),
                'data' => $buckets->map(function (CarbonImmutable $bucket) use ($counts, $state, $bucketFormat): int {
                    $key = $bucket->format($bucketFormat);

                    return (int) ($counts[$state->value][$key] ?? 0);
                }),
                'backgroundColor' => $this->resolveColorForState($state),
                'borderColor' => $this->resolveColorForState($state),
            ];
        }

        return [
            'datasets' => $dataset,
            'labels' => $labels->toArray(),
        ];
    }

    /**
     * @return Collection<string, mixed>
     */
    private function resolvePeriods(): Collection
    {
        if ($this->filter === 'week') {
            $end = CarbonImmutable::now()->endOfWeek();
            $start = $end->subWeeks(11)->startOfWeek();
            $period = CarbonPeriod::create($start, '1 week', $end);

            return collect([
                'start' => $start,
                'buckets' => collect($period)->map(fn (Carbon $date): CarbonImmutable => CarbonImmutable::parse($date)->startOfWeek()),
                'select' => "DATE_FORMAT(check_in_date, '%x-%v')",
                'bucketFormat' => 'o-W',
                'labelFormat' => '\S\e\m W (d/m)',
            ]);
        }

        $end = CarbonImmutable::now()->endOfMonth();
        $start = $end->subMonths(11)->startOfMonth();
        $period = CarbonPeriod::create($start, '1 month', $end);

        return collect([
            'start' => $start,
            'buckets' => collect($period)->map(fn (Carbon $date): CarbonImmutable => CarbonImmutable::parse($date)->startOfMonth()),
            'select' => "DATE_FORMAT(check_in_date, '%Y-%m-01')",
            'bucketFormat' => 'Y-m-01',
            'labelFormat' => 'M Y',
        ]);
    }

    private function resolveColorForState(EnumServiceOrderStatus $state): string
    {
        return match ($state) {
            EnumServiceOrderStatus::RECEIVED => '#6B7280', // Gray 600
            EnumServiceOrderStatus::ON_THE_WAY => '#F59E0B', // Amber 500
            EnumServiceOrderStatus::AT_DESTINATION => '#3B82F6', // Blue 500
            EnumServiceOrderStatus::PROCESS_STARTED => '#F97316', // Orange 500
            EnumServiceOrderStatus::COMPLETED => '#22C55E', // Green 500
            EnumServiceOrderStatus::CLOSED => '#059669', // Emerald 600
        };
    }
}

