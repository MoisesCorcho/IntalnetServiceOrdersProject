<?php

namespace App\Filament\Technician\Resources;

use App\Enums\EnumServiceOrderStatus;
use App\Filament\Technician\Resources\ServiceOrderResource\Pages;
use App\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static ?string $modelLabel = 'Orden de Servicio';

    protected static ?string $pluralModelLabel = 'Ordenes de Servicio';

    protected static ?string $navigationLabel = 'Ordenes de Servicio';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles de la orden')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Número de orden')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Programación y estado')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('state')
                            ->label('Estado')
                            ->options(EnumServiceOrderStatus::labels())
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\DatePicker::make('check_in_date')
                            ->label('Recibido el')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Programado para')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completado el')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Placeholder::make('assigned_user')
                            ->label('Técnico asignado')
                            ->content(fn (?ServiceOrder $record): string => $record?->assignedUser?->full_name ?? 'Sin asignar'),
                    ]),
                Forms\Components\Section::make('Cliente')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('customer_name_snapshot')
                            ->label('Nombre del cliente')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('customer_phone_snapshot')
                            ->label('Teléfono del cliente')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('customer_email_snapshot')
                            ->label('Correo del cliente')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('customer_address_snapshot')
                            ->label('Dirección del cliente')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Número de orden')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(function (?string $state): ?string {
                        $status = $state ? EnumServiceOrderStatus::tryFrom($state) : null;

                        return $status?->label();
                    }),
                Tables\Columns\TextColumn::make('check_in_date')
                    ->label('Recibido el')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Programado para')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name_snapshot')
                    ->label('Cliente')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('advance-state')
                    ->label(function (ServiceOrder $record): string {
                        $status = EnumServiceOrderStatus::tryFrom($record->state);
                        $next = $status?->next();

                        return $next?->label() ?? 'Estado finalizado';
                    })
                    ->visible(function (ServiceOrder $record): bool {
                        $status = EnumServiceOrderStatus::tryFrom($record->state);

                        return $status?->next() !== null && $status !== EnumServiceOrderStatus::CLOSED;
                    })
                    ->button()
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalDescription(function (ServiceOrder $record): string {
                        $status = EnumServiceOrderStatus::tryFrom($record->state);
                        $next = $status?->next();

                        return sprintf(
                            'Cambiar el estado de la orden de servicio a %s, no podras volver una vez hecho.',
                            $next?->label() ?? 'cerrado'
                        );
                    })
                    ->action(function (ServiceOrder $record): void {
                        $status = EnumServiceOrderStatus::tryFrom($record->state);
                        $next = $status?->next();

                        if (! $next) {
                            return;
                        }

                        $record->update([
                            'state' => $next->value,
                        ]);
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceOrders::route('/'),
            'view' => Pages\ViewServiceOrder::route('/{record}'),
        ];
    }
}
