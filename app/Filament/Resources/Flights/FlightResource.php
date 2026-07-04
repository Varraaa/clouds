<?php

namespace App\Filament\Resources\Flights;
use App\Filament\Resources\Flights\Pages\CreateFlight;
use App\Filament\Resources\Flights\Pages\EditFlight;
use App\Filament\Resources\Flights\Pages\ListFlights;
use App\Filament\Resources\Flights\Schemas\FlightForm;
use App\Filament\Resources\Flights\Tables\FlightsTable;
use App\Models\Flight;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\Repeater;


class FlightResource extends Resource
{
    protected static ?string $model = Flight::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FlightForm::configure($schema)
            ->schema([
                Wizard::make([
                    Step::make('Flight Information')
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('flight_number')
                                ->required()
                                ->unique(ignoreRecord: true),
                            \Filament\Forms\Components\Select::make('airline_id')
                                ->relationship('airline', 'name')
                                ->required(),
                        ]),
                    Step::make('Flight Segment')
                        ->schema([
                            Repeater::make('flight_segments')
                                ->relationship('segments')

                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('sequence')
                                        ->numeric()
                                        ->required(),
                                    \Filament\Forms\Components\Select::make('airport_id')
                                        ->relationship('airport', 'name')
                                        ->required(),
                                    \Filament\Forms\Components\DateTimePicker::make('time')
                                        ->required()
                                ])
                                ->collapsed(false)
                                ->minItems(1),
                        ]),
                    Step::make('Flight Class')
                        ->schema([
                            Repeater::make('flight_classes')
                                ->relationship('classes')
                                ->schema([
                                    \Filament\Forms\Components\Select::make('class_type')
                                        ->options([
                                            'Economy' => 'Economy',
                                            'Business' => 'Business',
                                        ])
                                        ->required(),
                                    \Filament\Forms\Components\TextInput::make('price')
                                        ->required()
                                        ->prefix('IDR')
                                        ->numeric()
                                        ->minValue(0),
                                    \Filament\Forms\Components\TextInput::make('total_seats')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->label('Total Seats')
                                        ->minValue(0),
                                    \Filament\Forms\Components\Select::make('facilities')
                                        ->relationship('facilities', 'name')
                                        ->multiple()
                                        ->required(),
                                ])
                        ]),
                ])->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
         return FlightsTable::configure($table)
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('flight_number'),
                \Filament\Tables\Columns\TextColumn::make('airline.name'),
                
                // DI SINI PERUBAHANNYA, BREE!
                \Filament\Tables\Columns\TextColumn::make('route_and_duration') 
                    ->label('Route & Duration')
                    ->getStateUsing(function (Flight $record): string {
                        // Cek jaga-jaga kalau segmen penerbangan masih kosong biar gak error
                        if ($record->segments->isEmpty()) {
                            return '-';
                        }

                        $firstSegment = $record->segments->first();
                        $lastSegment = $record->segments->last();

                        $route = $firstSegment->airport->iata_code . ' - ' . $lastSegment->airport->iata_code;
                        $duration = (new \DateTime($firstSegment->time))->format('d F Y H:i') . ' - ' . (new \DateTime($lastSegment->time))->format('d F Y H:i');

                        return $route . ' | ' . $duration;
                    })
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
            'index' => ListFlights::route('/'),
            'create' => CreateFlight::route('/create'),
            'edit' => EditFlight::route('/{record}/edit'),
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