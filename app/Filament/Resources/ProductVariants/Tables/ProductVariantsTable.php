<?php

namespace App\Filament\Resources\ProductVariants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ProductVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variant_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.short_name')
                    ->label('Unit')
                    ->searchable(),
                TextColumn::make('sku')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('SKU copied!'),
                TextColumn::make('min_stock_alert')
                    ->label('Min Stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Total Stock')
                    ->getStateUsing(fn ($record) => $record->total_stock)
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state <= 10 ? 'danger' : ($state <= 20 ? 'warning' : 'success')),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('product_id', 'asc')
            ->filters([
                SelectFilter::make('product')
                    ->relationship('product', 'name'),
                SelectFilter::make('unit')
                    ->relationship('unit', 'short_name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
