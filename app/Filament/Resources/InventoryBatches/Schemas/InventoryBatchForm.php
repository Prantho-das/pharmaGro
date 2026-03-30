<?php

namespace App\Filament\Resources\InventoryBatches\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class InventoryBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('variant_id')
                    ->relationship('variant', 'sku')
                    ->searchable()
                    ->required()
                    ->label('Product Variant')
                    ->helperText('Select the product variant this batch belongs to')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->product->name} - {$record->variant_name} ({$record->sku})"),
                Forms\Components\TextInput::make('batch_no')
                    ->required()
                    ->maxLength(100)
                    ->label('Batch Number')
                    ->helperText('e.g., B-2026-001')
                    ->default(fn () => 'B-' . now()->format('Y-m') . '-' . str_pad((string) (InventoryBatch::count() + 1), 3, '0', STR_PAD_LEFT)),
                Forms\Components\DatePicker::make('expiry_date')
                    ->required()
                    ->label('Expiry Date')
                    ->helperText('Date when this batch expires'),
                Forms\Components\TextInput::make('purchase_price')
                    ->numeric()
                    ->required()
                    ->label('Purchase Price')
                    ->helperText('Cost price (manager only)')
                    ->visible(fn ($livewire) => $livewire->user()->can('manage_inventory')),
                Forms\Components\TextInput::make('selling_price')
                    ->numeric()
                    ->required()
                    ->label('Selling Price')
                    ->helperText('Retail price per unit'),
                Forms\Components\TextInput::make('current_stock')
                    ->numeric()
                    ->required()
                    ->label('Initial Stock Quantity')
                    ->helperText('Quantity received in this batch'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active')
                    ->helperText('Inactive batches are excluded from stock calculations'),
            ]);
    }
}
