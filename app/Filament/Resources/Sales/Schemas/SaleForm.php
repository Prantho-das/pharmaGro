<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->nullable()
                    ->label('Customer'),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'bkash' => 'bKash',
                        'nagad' => 'Nagad',
                        'card' => 'Card',
                    ])
                    ->default('cash')
                    ->required(),
                Forms\Components\Select::make('sold_by_id')
                    ->relationship('soldBy', 'name')
                    ->label('Sold By')
                    ->default(fn () => auth()->id())
                    ->required(),
                Forms\Components\TextInput::make('sub_total')
                    ->numeric()
                    ->required()
                    ->label('Sub Total'),
                Forms\Components\TextInput::make('tax_amount')
                    ->numeric()
                    ->default(0)
                    ->label('Tax'),
                Forms\Components\TextInput::make('discount_amount')
                    ->numeric()
                    ->default(0)
                    ->label('Discount'),
                Forms\Components\TextInput::make('grand_total')
                    ->numeric()
                    ->required()
                    ->label('Grand Total'),
                Forms\Components\TextInput::make('paid_amount')
                    ->numeric()
                    ->required()
                    ->label('Paid Amount'),
                Forms\Components\TextInput::make('due_amount')
                    ->numeric()
                    ->default(0)
                    ->label('Due'),
            ]);
    }
}
