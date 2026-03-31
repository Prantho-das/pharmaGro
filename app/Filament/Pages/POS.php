<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\PointsTransaction;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class POS extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cash-register';

    protected static ?string $navigationLabel = 'POS';

    protected static ?string $title = 'Point of Sale';

    protected static ?string $slug = 'pos';

    protected static string|UnitEnum|null $navigationGroup = 'POS';

    protected static ?int $navigationSort = 1;

    // Search and selection
    public string $search = '';

    public bool $showSearchResults = false;

    public Collection $searchResults;

    // Cart items: each item has variant, batch, quantity, unit_price, total_price
    public Collection $cart;

    // Sale header fields
    public ?int $selectedCustomerId = null;

    public string $paymentMethod = 'cash';

    public float $paidAmount = 0.0;

    public float $discountAmount = 0.0;

    // Computed totals
    public float $subTotal = 0.0;

    public float $taxAmount = 0.0;

    public float $grandTotal = 0.0;

    public float $dueAmount = 0.0;

    // UI state
    public bool $showPaymentModal = false;

    public ?string $lastInvoice = null; // For receipt printing

    // Settings
    public bool $isPharmacyMode = true;

    // Polling interval (every 15 seconds)
    public $poll = 'refreshStock';

    public $pollInterval = 15000;

    public function mount(): void
    {
        $this->cart = collect([]);
        $this->searchResults = collect([]);
        $this->isPharmacyMode = Setting::get('is_pharmacy_active', true);
        $this->paidAmount = 0.0;
    }

    public function getCartTotal(): float
    {
        return $this->cart->sum(fn ($item) => $item['total_price']);
    }

    public function updatedSearch(string $value): void
    {
        if (strlen(trim($value)) < 2) {
            $this->showSearchResults = false;

            return;
        }

        $this->searchResults = ProductVariant::with(['product', 'unit', 'batches' => function ($q) {
            $q->where('is_active', true)
                ->where('current_stock', '>', 0)
                ->orderBy('expiry_date', 'asc'); // FEFO: earliest expiry first
        }])
            ->whereHas('product', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%")
                    ->orWhere('generic_name', 'like', "%{$value}%");
            })
            ->orWhere('sku', 'like', "%{$value}%")
            ->orWhere('variant_name', 'like', "%{$value}%")
            ->limit(10)
            ->get()
            ->map(function ($variant) {
                $earliestBatch = $variant->batches->first(); // Already ordered by expiry asc
                $price = $earliestBatch ? (float) $earliestBatch->selling_price : 0;
                $stock = $variant->total_stock;

                return [
                    'variant' => $variant,
                    'sku' => $variant->sku,
                    'name' => $variant->product->name.' - '.$variant->variant_name,
                    'price' => $price,
                    'stock' => $stock,
                    'batch' => $earliestBatch,
                ];
            })
            ->filter(fn ($item) => $item['price'] > 0 && $item['stock'] > 0);

        $this->showSearchResults = $this->searchResults->isNotEmpty();
    }

    public function selectSearchItem(array $item): void
    {
        $variant = $item['variant'];
        $batch = $item['batch'] ?? null;
        $this->addToCart($variant, 1, $batch);
        $this->search = '';
        $this->showSearchResults = false;
    }

    public function addToCart(ProductVariant $variant, int $quantity = 1, ?InventoryBatch $batch = null): void
    {
        // Find earliest available batch if not provided (FEFO)
        if (! $batch) {
            $batch = $variant->batches()
                ->where('is_active', true)
                ->where('current_stock', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->first();
        }

        if (! $batch) {
            Notification::make()
                ->title('Out of Stock')
                ->body('No stock available for this item.')
                ->warning()
                ->send();

            return;
        }

        // Check if already in cart with same batch
        $existingIndex = $this->cart->search(function ($item) use ($variant, $batch) {
            return $item['variant_id'] == $variant->id && $item['batch_id'] == $batch->id;
        });

        if ($existingIndex !== false) {
            // Increase quantity
            $this->cart[$existingIndex]['quantity'] += $quantity;
            $this->cart[$existingIndex]['total_price'] = $this->cart[$existingIndex]['quantity'] * $batch->selling_price;
        } else {
            // Add new item
            $this->cart->push([
                'variant_id' => $variant->id,
                'variant_name' => $variant->product->name.' - '.$variant->variant_name,
                'sku' => $variant->sku,
                'batch_id' => $batch->id,
                'batch_no' => $batch->batch_no,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'quantity' => $quantity,
                'unit_price' => (float) $batch->selling_price,
                'total_price' => (float) ($quantity * $batch->selling_price),
            ]);
        }

        $this->calculateTotals();
        $this->search = '';
        $this->showSearchResults = false;
    }

    public function updateCartItemQuantity(int $index, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeCartItem($index);

            return;
        }

        if (isset($this->cart[$index])) {
            $this->cart[$index]['quantity'] = $quantity;
            $this->cart[$index]['total_price'] = $quantity * $this->cart[$index]['unit_price'];
            $this->calculateTotals();
        }
    }

    public function removeCartItem(int $index): void
    {
        if (isset($this->cart[$index])) {
            $this->cart->splice($index, 1);
            $this->calculateTotals();
        }
    }

    public function calculateTotals(): void
    {
        $this->subTotal = $this->getCartTotal();

        // Get tax rate from settings (as percentage, e.g., 15 for 15%)
        $taxRate = (float) Setting::get('tax_rate', 0);
        $this->taxAmount = $this->subTotal * ($taxRate / 100);

        $this->grandTotal = $this->subTotal + $this->taxAmount - $this->discountAmount;
        $this->dueAmount = max(0, $this->grandTotal - $this->paidAmount);
    }

    public function updatedPaidAmount(): void
    {
        $this->calculateTotals();
    }

    public function updatedDiscountAmount(): void
    {
        $this->calculateTotals();
    }

    public function openPaymentModal(): void
    {
        if ($this->cart->isEmpty()) {
            Notification::make()
                ->title('Empty Cart')
                ->body('Cannot checkout with an empty cart.')
                ->warning()
                ->send();

            return;
        }
        $this->showPaymentModal = true;
        $this->paidAmount = $this->grandTotal;
    }

    public function completeSale(): void
    {
        // Validate
        if ($this->cart->isEmpty()) {
            Notification::make()
                ->title('Error')
                ->body('Cannot complete sale: cart is empty.')
                ->danger()
                ->send();

            return;
        }

        if ($this->grandTotal <= 0) {
            Notification::make()
                ->title('Error')
                ->body('Invalid total amount.')
                ->danger()
                ->send();

            return;
        }

        if ($this->paidAmount < $this->grandTotal) {
            $this->dueAmount = $this->grandTotal - $this->paidAmount;
        }

        // Generate invoice number
        $invoiceNo = Sale::generateInvoiceNumber();

        // Start transaction
        \DB::transaction(function () use ($invoiceNo) {
            // Create sale
            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'customer_id' => $this->selectedCustomerId,
                'sub_total' => $this->subTotal,
                'tax_amount' => $this->taxAmount,
                'discount_amount' => $this->discountAmount,
                'grand_total' => $this->grandTotal,
                'paid_amount' => $this->paidAmount,
                'due_amount' => $this->dueAmount,
                'payment_method' => $this->paymentMethod,
                'sold_by_id' => auth()->id(),
            ]);

            // Create sale items and decrement batch stock
            foreach ($this->cart as $item) {
                $batch = InventoryBatch::find($item['batch_id']);
                if (! $batch) {
                    throw new \Exception("Batch not found: {$item['batch_no']}");
                }

                $quantity = $item['quantity'];
                if ($batch->current_stock < $quantity) {
                    throw new \Exception("Insufficient stock for batch {$item['batch_no']}");
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'variant_id' => $item['variant_id'],
                    'batch_id' => $item['batch_id'],
                    'quantity' => $quantity,
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Decrement batch stock
                $batch->decrement('current_stock', $quantity);
            }

            // Award loyalty points if customer selected
            if ($this->selectedCustomerId && $this->grandTotal > 0) {
                $pointsRate = (float) Setting::get('points_rate', 0.01);
                $pointsEarned = (int) floor($this->grandTotal * $pointsRate);

                if ($pointsEarned > 0) {
                    $customer = Customer::find($this->selectedCustomerId);
                    if ($customer) {
                        $newBalance = $customer->points + $pointsEarned;
                        $customer->increment('points', $pointsEarned);

                        // Log points transaction
                        PointsTransaction::create([
                            'customer_id' => $customer->id,
                            'sale_id' => $sale->id,
                            'points' => $pointsEarned,
                            'type' => 'earned',
                            'balance_after' => $newBalance,
                            'notes' => "Earned from sale {$invoiceNo}",
                        ]);
                    }
                }
            }
        });

        Notification::make()
            ->title('Sale Completed')
            ->body("Invoice: {$invoiceNo}")
            ->success()
            ->send();

        // Store invoice for printing
        $this->lastInvoice = $invoiceNo;

        // Print receipt if enabled (will be available via print button)
        if ($this->printReceipt) {
            // The receipt URL will be generated in the UI
        }

        // Reset cart and state
        $this->cart = collect([]);
        $this->selectedCustomerId = null;
        $this->paidAmount = 0.0;
        $this->discountAmount = 0.0;
        $this->showPaymentModal = false;
        $this->calculateTotals();
    }

    public function refreshStock(): void
    {
        // Called by polling; we could refresh batch info if needed
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('hold')
                ->label('Hold')
                ->icon('heroicon-o-pause')
                ->color('warning')
                ->modalHeading('Hold Sale')
                ->form([
                    TextInput::make('reason')
                        ->label('Reason (optional)'),
                ])
                ->action(function (array $data) {
                    // TODO: Implement hold functionality (save to sessions or holds table)
                    Notification::make()
                        ->title('Hold')
                        ->body('Hold feature not implemented yet.')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getColumns(): int|string|array
    {
        return 12; // Full width
    }
}
