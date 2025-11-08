<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms\Components\{
    Grid,
    Repeater,
    Section,
    TextInput
};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\{
    DeleteAction,
    ForceDeleteAction,
    RestoreAction
};
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\{
    Builder,
    SoftDeletingScope
};

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'lg' => 2,
            ])->schema([
                static::personalInformationSection(),
                static::contactSection(),
            ]),
            static::addressesSection(),
        ]);
    }

    protected static function personalInformationSection(): Section
    {
        return Section::make('Información personal')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('first_name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('last_name')
                        ->label('Apellidos')
                        ->required()
                        ->maxLength(255),
                ]),
            ])
            ->columns(1);
    }

    protected static function contactSection(): Section
    {
        return Section::make('Contacto')
            ->schema([
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->maxLength(255),
                Grid::make(2)->schema([
                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(25),
                    TextInput::make('secondary_phone')
                        ->label('Teléfono secundario')
                        ->tel()
                        ->maxLength(25),
                ]),
            ])
            ->columns(1);
    }

    protected static function addressesSection(): Section
    {
        return Section::make('Direcciones')
            ->schema([
                Repeater::make('addresses')
                    ->relationship()
                    ->label('Direcciones registradas')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('street')
                                ->label('Dirección')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            TextInput::make('city')
                                ->label('Ciudad')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('state')
                                ->label('Departamento')
                                ->maxLength(255),
                            TextInput::make('zip')
                                ->label('Código postal')
                                ->maxLength(20),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->defaultItems(0)
                    ->itemLabel(fn (array $state): string => $state['street'] ?? 'Dirección')
                    ->addActionLabel('Agregar dirección')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('secondary_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    DeleteAction::make()
                        ->color('danger')
                        ->requiresConfirmation(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
