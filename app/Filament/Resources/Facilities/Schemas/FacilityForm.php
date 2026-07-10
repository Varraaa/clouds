<?php

namespace App\Filament\Resources\Facilities\Schemas;

use Filament\Schemas\Schema;

class FacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('facilities')
                    ->required()
                    ->columnSpan(2),
                \Filament\Forms\Components\TextInput::make('name')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('description')
                    ->required(),
            ]);
    }
}