<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Filament\Resources\ServiceOrderResource\RelationManagers;
use App\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('state')
                    ->required(),
                Forms\Components\DatePicker::make('check_in_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('scheduled_at'),
                Forms\Components\TextInput::make('assigned_user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('customer_id')
                    ->numeric(),
                Forms\Components\TextInput::make('customer_name_snapshot')
                    ->required(),
                Forms\Components\Textarea::make('customer_address_snapshot')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('customer_phone_snapshot')
                    ->tel(),
                Forms\Components\TextInput::make('customer_email_snapshot')
                    ->email(),
                Forms\Components\DateTimePicker::make('completed_at'),
            ]);
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
