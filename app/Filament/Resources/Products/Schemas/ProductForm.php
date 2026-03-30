<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'create') {
                                    // Auto-generate SKU prefix from product name
                                    $set->add('sku_prefix', Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('generic_name')
                            ->label('Generic Name')
                            ->maxLength(255)
                            ->helperText('Scientific/generic name for pharmacy products'),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Toggle::make('is_medicine')
                            ->label('Medicine')
                            ->helperText('Enables pharmacy-specific features like expiry tracking'),
                        Forms\Components\Toggle::make('has_variants')
                            ->label('Has Variants')
                            ->helperText('Enable if product comes in multiple sizes/packaging'),
                        Forms\Components\FileUpload::make('image_path')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Variants')
                    ->schema([
                        Forms\Components\Repeater::make('variants')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('variant_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Variant Name')
                                    ->helperText('e.g., 500mg Strip, 100g Soap'),
                                Forms\Components\Select::make('unit_id')
                                    ->relationship('unit', 'short_name')
                                    ->searchable()
                                    ->required()
                                    ->label('Unit')
                                    ->helperText('Select measurement unit'),
                                Forms\Components\TextInput::make('sku')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->label('SKU / Barcode')
                                    ->helperText('Unique stock keeping unit for barcode scanning'),
                                Forms\Components\TextInput::make('min_stock_alert')
                                    ->numeric()
                                    ->default(10)
                                    ->label('Low Stock Alert'),
                            ])
                            ->columns(2)
                            ->columnSpan('full')
                            ->addable(true)
                            ->reorderable(false)
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                            )
                            ->collapsible()
                            ->itemWeight(fn (array $state): int => isset($state['order']) ? (int) $state['order'] : 0),
                    ])
                    ->hidden(fn (Forms\Get $get) => ! $get('has_variants'))
                    ->columns(1),
            ]);
    }
}
