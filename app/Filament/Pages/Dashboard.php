<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ServiceOrderStatusCards;
use App\Filament\Widgets\ServiceOrdersTable;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Dashboard';

    // Propiedad para recibir el status desde la URL
    public ?string $status = 'all';

    public function mount(): void
    {
        $this->status = request()->query('status', 'all');
    }

    public function getWidgets(): array
    {
        return [
            ServiceOrderStatusCards::class,
            ServiceOrdersTable::class,
        ];
    }
}