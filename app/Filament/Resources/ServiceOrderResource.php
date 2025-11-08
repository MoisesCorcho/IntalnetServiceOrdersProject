<?php

namespace App\Filament\Resources;

use App\Enums\EnumServiceOrderStatus;
use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Filament\Resources\ServiceOrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Programación y estado')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('state')
                            ->label('Estado')
                            ->options(EnumServiceOrderStatus::labels())
                            ->default(EnumServiceOrderStatus::RECEIVED->value)
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('check_in_date')
                            ->label('Recibido el')
                            ->required()
                            ->minDate(now()->startOfDay()),
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Programado para')
                            ->minDate(fn (callable $get) => $get('check_in_date')
                                ? Carbon::parse($get('check_in_date'))->startOfDay()
                                : now()),
                        Forms\Components\Select::make('assigned_user_id')
                            ->label('Técnico asignado')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(
                                static fn (User $user): string => trim($user->full_name ?: $user->name)
                            )
                            ->native(false),
                    ]),
                Forms\Components\Section::make('Cliente')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'first_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(
                                static fn (Customer $customer): string => trim(
                                    collect([$customer->first_name, $customer->last_name])
                                        ->filter()
                                        ->implode(' ')
                                )
                            )
                            ->createOptionForm([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('first_name')
                                            ->label('Nombre')
                                            ->required(),
                                        Forms\Components\TextInput::make('last_name')
                                            ->label('Apellidos')
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Correo electrónico')
                                            ->email()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->maxLength(25),
                                    ]),
                                Forms\Components\Fieldset::make('Dirección')
                                    ->schema([
                                        Forms\Components\TextInput::make('street')
                                            ->label('Dirección'),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('city')
                                                    ->label('Ciudad'),
                                                Forms\Components\TextInput::make('state')
                                                    ->label('Departamento'),
                                            ]),
                                        Forms\Components\TextInput::make('zip')
                                            ->label('Código postal')
                                            ->maxLength(20),
                                    ]),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $customer = Customer::create([
                                    'first_name' => $data['first_name'],
                                    'last_name' => $data['last_name'],
                                    'email' => $data['email'] ?? null,
                                    'phone' => $data['phone'] ?? null,
                                ]);

                                $addressData = collect([
                                    'street' => $data['street'] ?? null,
                                    'city' => $data['city'] ?? null,
                                    'state' => $data['state'] ?? null,
                                    'zip' => $data['zip'] ?? null,
                                ])->filter();

                                if ($addressData->isNotEmpty()) {
                                    $customer->addresses()->create($addressData->toArray());
                                }

                                return $customer->getKey();
                            })
                            ->live()
                            ->afterStateUpdated(function (int|string|null $state, callable $set): void {
                                static::fillCustomerSnapshots($set, $state);
                            })
                            ->native(false),
                        Forms\Components\TextInput::make('customer_name_snapshot')
                            ->label('Nombre del cliente')
                            ->required()
                            ->readOnly(),
                        Forms\Components\TextInput::make('customer_phone_snapshot')
                            ->label('Teléfono del cliente')
                            ->tel()
                            ->readOnly(),
                        Forms\Components\TextInput::make('customer_email_snapshot')
                            ->label('Correo del cliente')
                            ->email()
                            ->readOnly(),
                        Forms\Components\Textarea::make('customer_address_snapshot')
                            ->label('Dirección del cliente')
                            ->columnSpanFull()
                            ->rows(3)
                            ->readOnly(),
                    ]),
            ]);
    }

    protected static function fillCustomerSnapshots(callable $set, int|string|null $customerId): void
    {
        $id = $customerId !== null ? (int) $customerId : null;

        $customer = $id
            ? Customer::withTrashed()->with('addresses')->find($id)
            : null;

        $fullName = $customer
            ? trim(collect([$customer->first_name, $customer->last_name])->filter()->implode(' '))
            : null;

        $primaryAddress = $customer?->addresses->first();

        $formattedAddress = $primaryAddress
            ? collect([
                $primaryAddress->street,
                $primaryAddress->city,
                $primaryAddress->state,
                $primaryAddress->zip,
            ])->filter()->implode(', ')
            : null;

        $set('customer_name_snapshot', $fullName);
        $set('customer_phone_snapshot', $customer?->phone);
        $set('customer_email_snapshot', $customer?->email);
        $set('customer_address_snapshot', $formattedAddress);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable(),
                Tables\Columns\TextColumn::make('check_in_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assigned_user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name_snapshot')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_phone_snapshot')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_email_snapshot')
                    ->searchable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'create' => Pages\CreateServiceOrder::route('/create'),
            'view' => Pages\ViewServiceOrder::route('/{record}'),
            'edit' => Pages\EditServiceOrder::route('/{record}/edit'),
        ];
    }
}
