<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Components\Section; //PENTING 
use Filament\Forms\Components\Repeater; //PENTING
 

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema)
            ->schema([
                Section::make('Informasi Umum')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('code'),
                        \Filament\Forms\Components\Select::make('flight_id')
                            ->relationship('flight', 'flight_number'),
                        \Filament\Forms\Components\Select::make('flight_class_id')
                            ->relationship('class', 'class_type'),
                    ])->columnspan(2),

                Section::make('Informasi Penumpang')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('number_of_passengers'),
                        \Filament\Forms\Components\TextInput::make('name'),
                        \Filament\Forms\Components\TextInput::make('email'),
                        \Filament\Forms\Components\TextInput::make('phone'),
                        Section::make('Daftar Penumpang')
                            ->schema([
                                Repeater::make('passenger')
                                ->relationship('passengers')
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('seat.name'),
                                    \Filament\Forms\Components\TextInput::make('name'),
                                    \Filament\Forms\Components\TextInput::make('date_of_birth'),
                                    \Filament\Forms\Components\TextInput::make('nationality'),
                                ])
                            ])
                    ])->columnspan(2),

                Section::make('Pembayaran')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('promo.code'),
                        \Filament\Forms\Components\TextInput::make('promo.discount_type'),
                        \Filament\Forms\Components\TextInput::make('promo.discount'),
                        \Filament\Forms\Components\TextInput::make('payment_status'),
                        \Filament\Forms\Components\TextInput::make('subtotal'),
                        \Filament\Forms\Components\TextInput::make('grandtotal'),

                    ])->columnspan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table)
        ->columns([
                \Filament\Tables\Columns\TextColumn::make('code'),
                \Filament\Tables\Columns\TextColumn::make('flight.flight_number'),
                \Filament\Tables\Columns\TextColumn::make('name'),
                \Filament\Tables\Columns\TextColumn::make('email'),
                \Filament\Tables\Columns\TextColumn::make('phone'),
                \Filament\Tables\Columns\TextColumn::make('number_of_passengers'),
                \Filament\Tables\Columns\TextColumn::make('promo.code'),
                \Filament\Tables\Columns\TextColumn::make('payment_status'),
                \Filament\Tables\Columns\TextColumn::make('subtotal'),
                \Filament\Tables\Columns\TextColumn::make('grandtotal'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
