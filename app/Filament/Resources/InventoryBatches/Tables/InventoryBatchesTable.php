<?php

namespace App\Filament\Resources\InventoryBatches\Tables;

use App\Models\InventoryBatch;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;

class InventoryBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variant.variant_name')
                    ->label('Variant')
                    ->searchable(),
                TextColumn::make('batch_no')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Expiry')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state->isPast() => 'danger',
                        $state->lte(now()->addDays(90)) => 'danger',
                        $state->lte(now()->addDays(180)) => 'warning',
                        default => 'success',
                    })
                    ->badge(),
                TextColumn::make('purchase_price')
                    ->label('Cost')
                    ->money('BDT')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true) // Hidden by default for staff
                    ->visible(fn () => auth()->user()->can('manage_inventory')),
                TextColumn::make('selling_price')
                    ->label('Price')
                    ->money('BDT')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(['expiry_date', 'asc'])
            ->filters([
                SelectFilter::make('variant')
                    ->relationship('variant', 'sku')
                    ->label('Product Variant'),
                SelectFilter::make('product')
                    ->relationship('variant.product', 'name')
                    ->label('Product'),
                SelectFilter::make('expiry_year')
                    ->options(fn () => InventoryBatch::selectRaw('YEAR(expiry_date) as year')
                        ->distinct()
                        ->orderBy('year')
                        ->pluck('year', 'year')
                        ->toArray())
                    ->label('Expiry Year'),
                TernaryFilter::make('is_active')->label('Active'),
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
