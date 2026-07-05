<?php

namespace App\Filament\Resources\PromoCodes;

use App\Filament\Resources\PromoCodes\Pages\CreatePromoCode;
use App\Filament\Resources\PromoCodes\Pages\EditPromoCode;
use App\Filament\Resources\PromoCodes\Pages\ListPromoCodes;
use App\Filament\Resources\PromoCodes\Schemas\PromoCodeForm;
use App\Filament\Resources\PromoCodes\Tables\PromoCodesTable;
use App\Models\PromoCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PromoCodeForm::configure($schema)
        ->schema([
                \Filament\Forms\Components\TextInput::make('code')
                    ->required(),
                \Filament\Forms\Components\Select::make('discount_type')
                    ->required()
                    ->options([
                        'fixed' => 'Fixed',
                        'percentage' => 'Percentage',
                    ]),
                \Filament\Forms\Components\TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                \Filament\Forms\Components\DateTimePicker::make('valid_until')
                    ->required(),
                \Filament\Forms\Components\Toggle::make('is_used')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return PromoCodesTable::configure($table)
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('code'),
                \Filament\Tables\Columns\TextColumn::make('discount_type'),
                \Filament\Tables\Columns\TextColumn::make('discount')
                    ->formatStateUsing(fn(PromoCode $record): string => match ($record->discount_type) {
                        'fixed' => 'Rp' . number_format($record->discount, 0, ',', '.'),
                        'percentage' => $record->discount . '%',
                    }),
                \Filament\Tables\Columns\ToggleColumn::make('is_used'),
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
            'index' => ListPromoCodes::route('/'),
            'create' => CreatePromoCode::route('/create'),
            'edit' => EditPromoCode::route('/{record}/edit'),
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
