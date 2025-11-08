<?php

namespace App\Filament\Technician\Resources\ServiceOrderResource\Pages;

use App\Filament\Technician\Resources\ServiceOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceOrders extends ListRecords
{
    protected static string $resource = ServiceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
