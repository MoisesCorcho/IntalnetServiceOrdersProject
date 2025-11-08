<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Técnico';

    protected static ?string $pluralModelLabel = 'Técnicos';

    protected static ?string $navigationLabel = 'Técnicos';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'lg' => 2,
            ])->schema([
                static::personalInformationSection(),
                static::settingsSection(),
            ]),
            static::addressesSection(),
        ]);
    }

    protected static function personalInformationSection(): Section
    {
        return Section::make('Información personal')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('last_name')
                        ->label('Apellidos')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->required()
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

    protected static function settingsSection(): Section
    {
        return Section::make('Ajustes')
            ->schema([
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn(?string $state): bool => filled($state))
                    ->revealable(),
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
                                ->label('Estado / Provincia')
                                ->maxLength(255),
                            TextInput::make('zip')
                                ->label('Código postal')
                                ->maxLength(20),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->defaultItems(0)
                    ->itemLabel(fn(array $state): string => $state['street'] ?? 'Dirección')
                    ->addActionLabel('Agregar dirección')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nombre completo')
                    ->searchable(['name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('secondary_phone')
                    ->label('Teléfono secundario')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger')
                        ->requiresConfirmation()
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
