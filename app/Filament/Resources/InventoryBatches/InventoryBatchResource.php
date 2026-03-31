<?php

namespace App\Filament\Resources\InventoryBatches;

use App\Filament\Resources\InventoryBatches\Pages\CreateInventoryBatch;
use App\Filament\Resources\InventoryBatches\Pages\EditInventoryBatch;
use App\Filament\Resources\InventoryBatches\Pages\ListInventoryBatches;
use App\Filament\Resources\InventoryBatches\Schemas\InventoryBatchForm;
use App\Filament\Resources\InventoryBatches\Tables\InventoryBatchesTable;
use App\Models\InventoryBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class InventoryBatchResource extends Resource
{
    protected static ?string $model = InventoryBatch::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Inventory Batches';

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'batch_no';

    public static function form(Schema $schema): Schema
    {
        return InventoryBatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryBatches::route('/'),
            'create' => CreateInventoryBatch::route('/create'),
            'edit' => EditInventoryBatch::route('/{record}/edit'),
        ];
    }
}
