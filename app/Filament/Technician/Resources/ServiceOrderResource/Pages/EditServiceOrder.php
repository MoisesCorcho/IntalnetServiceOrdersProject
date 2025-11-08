<?php

namespace App\Filament\Technician\Resources\ServiceOrderResource\Pages;

use App\Filament\Technician\Resources\ServiceOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceOrder extends EditRecord
{
    protected static string $resource = ServiceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
