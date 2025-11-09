<?php

namespace App\Filament\Widgets;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\User; // <-- 1. IMPORTAR USER
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter; // <-- 2. IMPORTAR FILTER
use Filament\Forms\Components\CheckboxList; // <-- 3. IMPORTAR CHECKBOXLIST

class ServiceOrdersTable extends BaseWidget
{
    public ?string $status = null;

    protected int|string|array $columnSpan = 'full';

    public function mount(): void
    {
        $this->status = request()->query('status');
    }

    protected function getTableHeading(): string
    {
        $labels = EnumServiceOrderStatus::labels();

        if (blank($this->status) || $this->status === 'all') {
            return 'Todas las órdenes';
        }

        if (! array_key_exists($this->status, $labels)) {
            return 'Todas las órdenes';
        }

        return 'Órdenes en estado: ' . $labels[$this->status];
    }

    protected function getTableQuery(): Builder
    {
        $query = ServiceOrder::query()
            ->with(['assignedUser', 'customer'])
            ->latest('created_at');

        if (! blank($this->status) && $this->status !== 'all' && $this->isValidStatus($this->status)) {
            $query->where('state', $this->status);
        }

        return $query;
    }

    public function table(Table $table): Table
    {
        $statusLabels = EnumServiceOrderStatus::labels();

        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('order_number')
                    ->label('N° Orden')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Servicio')
                    ->toggleable()
                    ->wrap()
                    ->searchable(),

                // *** 1. AQUÍ ESTÁ LA CORRECCIÓN PRINCIPAL ***
                TextColumn::make('assignedUser.full_name')
                    ->label('Técnico Asignado')
                    ->placeholder('Sin asignar')
                    ->searchable(['name', 'last_name'])
                    ->sortable(['name', 'last_name']), // <-- Se añadió el array

                TextColumn::make('customer_name_snapshot')
                    ->label('Cliente')
                    ->placeholder('Sin registro')
                    ->searchable(),

                TextColumn::make('customer_phone_snapshot')
                    ->label('Contacto')
                    ->placeholder('Sin contacto')
                    ->toggleable(),

                TextColumn::make('state')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $statusLabels[$state] ?? 'Desconocido')
                    ->icon(fn (?string $state): ?string => match ($state) {
                        EnumServiceOrderStatus::RECEIVED->value => 'heroicon-m-inbox',
                        EnumServiceOrderStatus::ON_THE_WAY->value => 'heroicon-m-truck',
                        EnumServiceOrderStatus::AT_DESTINATION->value => 'heroicon-m-map-pin',
                        EnumServiceOrderStatus::PROCESS_STARTED->value => 'heroicon-m-cog-8-tooth',
                        EnumServiceOrderStatus::COMPLETED->value => 'heroicon-m-check-badge',
                        EnumServiceOrderStatus::CLOSED->value => 'heroicon-m-archive-box',
                        default => null,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        EnumServiceOrderStatus::RECEIVED->value => 'success',
                        EnumServiceOrderStatus::ON_THE_WAY->value,
                        EnumServiceOrderStatus::AT_DESTINATION->value => 'warning',
                        EnumServiceOrderStatus::PROCESS_STARTED->value => 'info',
                        EnumServiceOrderStatus::COMPLETED->value => 'primary',
                        EnumServiceOrderStatus::CLOSED->value => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('check_in_date')
                    ->label('Fecha de recepción')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->label('Programado')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable()
                    ->sortable()
                    ->placeholder('Sin programación'),
            ])
            ->filters([
                SelectFilter::make('state')
                    ->label('Estado')
                    ->options($statusLabels),

                // *** 4. AQUÍ ESTÁ LA MEJORA ***
                // Reemplazamos el SelectFilter por un Filter con CheckboxList
                Filter::make('assigned_user_id')
                    ->label('Técnico')
                    ->form([
                        CheckboxList::make('technicians') // 'technicians' es un nombre interno
                            ->label('Seleccionar técnicos')
                            ->options(
                                // Obtenemos los técnicos y usamos el accesor 'full_name'
                                // CORRECCIÓN: Se usa query() para llamar al scope correctamente
                                User::query()->technicians() 
                                    ->get()
                                    ->mapWithKeys(fn ($user) => [$user->id => $user->full_name])
                            )
                            ->searchable() // Añade un campo de búsqueda a la lista
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Si no hay técnicos seleccionados, no hacer nada
                        if (blank($data['technicians'])) {
                            return $query;
                        }
                        // Aplicar el filtro a la consulta
                        return $query->whereIn('assigned_user_id', $data['technicians']);
                    }),

                TernaryFilter::make('sin_tecnico')
                    ->label('Sin técnico asignado')
                    ->nullable()
                    ->attribute('assigned_user_id')
                    ->trueLabel('Sí')
                    ->falseLabel('No'),
            ]);
    }

    private function isValidStatus(string $status): bool
    {
        return in_array($status, EnumServiceOrderStatus::values(), true);
    }
}