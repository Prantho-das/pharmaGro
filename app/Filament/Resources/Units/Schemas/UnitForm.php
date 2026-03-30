<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('short_name')
                    ->required()
                    ->maxLength(10)
                    ->helperText('Abbreviation: pc, str, bx, kg, etc.'),
            ]);
    }
}
