<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_no')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('BDT')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('BDT')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('due_amount')
                    ->label('Due')
                    ->money('BDT')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'cash' => 'success',
                        'bkash' => 'primary',
                        'nagad' => 'info',
                        'card' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('soldBy.name')
                    ->label('Sold By')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today()))
                    ->toggle(),
                SelectFilter::make('customer')
                    ->relationship('customer', 'name'),
                SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'bkash' => 'bKash',
                        'nagad' => 'Nagad',
                        'card' => 'Card',
                    ]),
                SelectFilter::make('sold_by')
                    ->relationship('soldBy', 'name')
                    ->label('Staff'),
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
