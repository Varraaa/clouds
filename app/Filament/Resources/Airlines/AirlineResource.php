<?php

namespace App\Filament\Resources\Airlines;

use App\Filament\Resources\Airlines\Pages\CreateAirline;
use App\Filament\Resources\Airlines\Pages\EditAirline;
use App\Filament\Resources\Airlines\Pages\ListAirlines;
use App\Filament\Resources\Airlines\Schemas\AirlineForm;
use App\Filament\Resources\Airlines\Tables\AirlinesTable;
use App\Models\Airline;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AirlineResource extends Resource
{
    protected static ?string $model = Airline::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AirlineForm::configure($schema)
        ->schema([
                \Filament\Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->disk('public') // WAJIBB
                    ->directory('airlines')
                    ->required()
                    ->columnSpan(2),
                    \Filament\Forms\Components\TextInput::make('code')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('name')
                        ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return AirlinesTable::configure($table)
        ->columns([
                \Filament\Tables\Columns\ImageColumn::make('logo')
                    ->disk('public'),
                \Filament\Tables\Columns\TextColumn::make('code'),
                \Filament\Tables\Columns\TextColumn::make('name'),
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
            'index' => ListAirlines::route('/'),
            'create' => CreateAirline::route('/create'),
            'edit' => EditAirline::route('/{record}/edit'),
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
