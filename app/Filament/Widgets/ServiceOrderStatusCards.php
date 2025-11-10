<?php

namespace App\Filament\Widgets;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class ServiceOrderStatusCards extends BaseWidget
{
    protected function getStats(): array
    {
        $definitions = $this->definitions();
        $counts = $this->currentCounts();
        $chartData = $this->chartData();

        return collect(EnumServiceOrderStatus::cases())
            ->map(function (EnumServiceOrderStatus $status) use ($definitions, $counts, $chartData): Stat {
                $definition = $definitions[$status->value] ?? [
                    'title' => $status->label(),
                    'description' => null,
                    'description_icon' => null,
                    'color' => null,
                ];
                $stat = Stat::make($definition['title'], number_format((int) $counts->get($status->value, 0)))
                    ->url(route('filament.admin.pages.dashboard', ['status' => $status->value]));

                if ($definition['description'] !== null) {
                    $stat->description($definition['description']);
                }

                if ($definition['description_icon'] !== null) {
                    $stat->descriptionIcon($definition['description_icon']);
                }

                if ($definition['color'] !== null) {
                    $stat->color($definition['color']);
                }

                if (! empty($chartData[$status->value])) {
                    $stat->chart($chartData[$status->value]);
                }

                return $stat;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, array{title: string, description: ?string, description_icon: ?string, color: ?string}>
     */
    private function definitions(): array
    {
        return [
            EnumServiceOrderStatus::CREATED->value => [
                'title' => 'Órdenes creadas',
                'description' => 'Registradas recientemente',
                'description_icon' => 'heroicon-m-document-plus',
                'color' => 'gray',
            ],
            EnumServiceOrderStatus::ASSIGNED->value => [
                'title' => 'Órdenes asignadas',
                'description' => 'Técnico designado',
                'description_icon' => 'heroicon-m-user-circle',
                'color' => 'warning',
            ],
            EnumServiceOrderStatus::RECEIVED->value => [
                'title' => 'Órdenes recibidas',
                'description' => 'Órdenes nuevas sin asignar',
                'description_icon' => 'heroicon-m-arrow-trending-up',
                'color' => 'success',
            ],
            EnumServiceOrderStatus::ON_THE_WAY->value => [
                'title' => 'Órdenes en camino',
                'description' => 'Técnico desplazándose',
                'description_icon' => 'heroicon-m-truck',
                'color' => 'warning',
            ],
            EnumServiceOrderStatus::AT_DESTINATION->value => [
                'title' => 'Órdenes en destino',
                'description' => 'Técnico en sitio',
                'description_icon' => 'heroicon-m-map-pin',
                'color' => 'info',
            ],
            EnumServiceOrderStatus::PROCESS_STARTED->value => [
                'title' => 'Órdenes en proceso',
                'description' => 'Trabajo en ejecución',
                'description_icon' => 'heroicon-m-cog-8-tooth',
                'color' => 'primary',
            ],
            EnumServiceOrderStatus::COMPLETED->value => [
                'title' => 'Órdenes completadas',
                'description' => 'Servicio finalizado con éxito',
                'description_icon' => 'heroicon-m-check-badge',
                'color' => 'emerald',
            ],
            EnumServiceOrderStatus::CLOSED->value => [
                'title' => 'Órdenes cerradas',
                'description' => 'Ordenes archivadas',
                'description_icon' => 'heroicon-m-archive-box',
                'color' => 'gray',
            ],
        ];
    }

    /**
     * @return Collection<string, int>
     */
    private function currentCounts(): Collection
    {
        return ServiceOrder::query()
            ->selectRaw('state, COUNT(*) as aggregate')
            ->groupBy('state')
            ->pluck('aggregate', 'state');
    }

    /**
     * @return array<string, array<int, int>>
     */
    private function chartData(): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $period = CarbonPeriod::create($start, '1 day', $end);

        $raw = ServiceOrder::query()
            ->selectRaw('state, DATE(created_at) as day, COUNT(*) as aggregate')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('state', 'day')
            ->get()
            ->groupBy('state')
            ->map(fn ($group) => $group->pluck('aggregate', 'day'));

        $days = collect(iterator_to_array($period))
            ->map(fn (Carbon $day) => $day->toDateString());

        $charts = [];

        foreach (EnumServiceOrderStatus::values() as $status) {
            $dayCounts = $raw->get($status, collect());

            $charts[$status] = $days
                ->map(fn (string $day) => (int) $dayCounts->get($day, 0))
                ->all();
        }

        return $charts;
    }
}

