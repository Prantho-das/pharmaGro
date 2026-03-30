<?php

namespace App\Filament\Resources\ProductVariants\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class ProductVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'short_name')
                    ->searchable()
                    ->required()
                    ->label('Unit'),
                Forms\Components\TextInput::make('variant_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Variant Name')
                    ->helperText('e.g., 500mg, Strip, 100g'),
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->label('SKU / Barcode')
                    ->helperText('Unique identifier for scanning'),
                Forms\Components\TextInput::make('min_stock_alert')
                    ->numeric()
                    ->default(10)
                    ->label('Low Stock Alert Threshold'),
                Forms\Components\Placeholder::make('total_stock')
                    ->label('Current Total Stock')
                    ->content(fn ($record) => $record ? $record->total_stock : 0)
                    ->helperText('Sum of all active batches')
                    ->columnSpanFull(),
            ]);
    }
}
