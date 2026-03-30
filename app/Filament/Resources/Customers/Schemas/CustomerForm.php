<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->helperText('Unique phone number for loyalty program'),
                Forms\Components\TextInput::make('points')
                    ->numeric()
                    ->default(0)
                    ->label('Loyalty Points')
                    ->helperText('Points accumulated from purchases'),
            ]);
    }
}
