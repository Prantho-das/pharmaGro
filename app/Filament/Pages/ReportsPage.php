<?php

namespace App\Filament\Pages;

use App\Models\InventoryBatch;
use App\Models\Sale;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReportsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Reports Dashboard';

    protected static ?string $slug = 'reports';

    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    // Summary data (public for view)
    public int $totalSales = 0;

    public float $grossRevenue = 0.0;

    public float $totalPaid = 0.0;

    public float $totalDue = 0.0;

    public float $netProfit = 0.0;

    // Expiring days filter
    public int $expiringDays = 60;

    public function mount(): void
    {
        $this->loadSummary();
    }

    public function loadSummary(): void
    {
        $this->totalSales = Sale::count();
        $this->grossRevenue = (float) Sale::sum('grand_total');
        $this->totalPaid = (float) Sale::sum('paid_amount');
        $this->totalDue = (float) Sale::sum('due_amount');

        $profitQuery = \DB::table('sale_items')
            ->join('inventory_batches', 'sale_items.batch_id', '=', 'inventory_batches.id')
            ->selectRaw('SUM((unit_price - purchase_price) * quantity) as profit');
        $this->netProfit = (float) ($profitQuery->value('profit') ?? 0);
    }

    protected function getTableQuery(): Builder|\Illuminate\Database\Query\Builder
    {
        $cutoffDate = Carbon::now()->addDays($this->expiringDays);

        return InventoryBatch::query()
            ->with(['variant.product', 'unit'])
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->where('expiry_date', '<=', $cutoffDate)
            ->orderBy('expiry_date', 'asc');
    }

    protected function getTableColumns(): array
    {
        return [
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
                ->date()
                ->sortable(),
            TextColumn::make('current_stock')
                ->numeric()
                ->sortable(),
            TextColumn::make('selling_price')
                ->money('BDT')
                ->sortable(),
            TextColumn::make('days_to_expire')
                ->getStateUsing(fn ($record) => $record->expiry_date->diffInDays(now()))
                ->label('Days to Expire')
                ->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getColumns(): int|string|array
    {
        return 12;
    }

    public function getContent(): string
    {
        return view('filament.pages.reports');
    }
}
