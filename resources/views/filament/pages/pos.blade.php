<x-filament::page>
    <div class="h-full flex flex-col lg:flex-row gap-4 p-4 bg-gray-100 dark:bg-gray-900" wire:poll.15000ms="refreshStock">
        <!-- Left Panel: Search & Products -->
        <div class="flex-1 flex flex-col gap-4">
            <!-- Search Bar -->
            <div class="relative">
                <x-filament::input
                    wire:model="search"
                    wire:keydown.enter=""
                    placeholder="Scan barcode or search product..."
                    class="w-full text-lg py-3 px-4"
                    autofocus
                />
                @if($showSearchResults && $searchResults->isNotEmpty())
                    <div class="absolute z-10 w-full bg-white dark:bg-gray-800 shadow-lg rounded-b-lg mt-1 max-h-96 overflow-y-auto">
                        @foreach($searchResults as $item)
                            <div
                                class="p-3 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                wire:click="selectSearchItem({{ $item }})"
                            >
                                <div class="flex justify-between">
                                    <span class="font-medium">{{ $item['name'] }}</span>
                                    <span class="text-gray-500">{{ $item['sku'] }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>Batch: {{ $item['batch']->batch_no }} (Exp: {{ $item['batch']->expiry_date->format('Y-m-d') }})</span>
                                    <span>Stock: {{ $item['stock'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-bold">৳{{ number_format($item['price'], 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quick Stats / Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-bold mb-2">Pharmacy Mode: {{ $isPharmacyMode ? 'ON' : 'OFF' }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @if($isPharmacyMode)
                        Generic names and expiry dates will be shown.
                    @else
                        Generic mode - simplified display.
                    @endif
                </p>
            </div>

            <!-- Placeholder for frequently used products grid -->
            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg shadow p-4 overflow-auto">
                <h3 class="font-bold mb-2">Quick Add (Top Products)</h3>
                <p class="text-gray-500">Implement quick-add product grid here later.</p>
            </div>
        </div>

        <!-- Right Panel: Cart & Checkout -->
        <div class="w-full lg:w-96 flex flex-col gap-4">
            <!-- Cart -->
            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg shadow flex flex-col overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold">Cart</h2>
                </div>
                @if($cart->isEmpty())
                    <div class="flex-1 flex items-center justify-center text-gray-500">
                        <p>Cart is empty. Scan or search to add items.</p>
                    </div>
                @else
                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        @foreach($cart as $index => $item)
                            <div class="border border-gray-200 dark:border-gray-700 rounded p-3 bg-gray-50 dark:bg-gray-700">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium">{{ $item['variant_name'] }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            SKU: {{ $item['sku'] }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Batch: {{ $item['batch_no'] }} | Exp: {{ $item['expiry_date'] }}
                                        </p>
                                    </div>
                                    <button
                                        class="text-red-500 hover:text-red-700"
                                        wire:click="removeCartItem({{ $index }})"
                                        title="Remove"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <button
                                            class="px-2 py-1 border rounded bg-gray-200 dark:bg-gray-600"
                                            wire:click="updateCartItemQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                        >-</button>
                                        <span class="w-8 text-center">{{ $item['quantity'] }}</span>
                                        <button
                                            class="px-2 py-1 border rounded bg-gray-200 dark:bg-gray-600"
                                            wire:click="updateCartItemQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                        >+</button>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold">৳{{ number_format($item['total_price'], 2) }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">৳{{ number_format($item['unit_price'], 2) }} each</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Totals & Checkout -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-4">
                <!-- Customer Select -->
                <div>
                    <label class="block text-sm font-medium mb-1">Customer (Optional)</label>
                    <select wire:model="selectedCustomerId" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Walking Customer</option>
                        @foreach(\App\Models\Customer::orderBy('name')->get() as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Discount -->
                <div>
                    <label class="block text-sm font-medium mb-1">Discount (৳)</label>
                    <input
                        type="number"
                        wire:model.number="discountAmount"
                        min="0"
                        step="0.01"
                        class="w-full border-gray-300 rounded-md shadow-sm"
                    />
                </div>

                <div class="space-y-2 border-t pt-2">
                    <div class="flex justify-between">
                        <span>Sub Total:</span>
                        <span class="font-mono">৳{{ number_format($subTotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tax:</span>
                        <span class="font-mono">৳{{ number_format($taxAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-green-600">
                        <span>Discount:</span>
                        <span class="font-mono">-৳{{ number_format($discountAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                        <span>Grand Total:</span>
                        <span class="font-mono">৳{{ number_format($grandTotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-red-600 font-bold">
                        <span>Due:</span>
                        <span class="font-mono">৳{{ number_format($dueAmount, 2) }}</span>
                    </div>
                </div>

                <!-- Payment Controls -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="cash">Cash</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Paid Amount</label>
                        <input
                            type="number"
                            wire:model.number="paidAmount"
                            min="0"
                            step="0.01"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button
                            wire:click="openPaymentModal"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded"
                        >
                            Complete Sale
                        </button>
                    </div>

                    @if($lastInvoice)
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded">
                            <p class="font-bold text-blue-800 dark:text-blue-200 mb-2">
                                ✅ Last Sale: {{ $lastInvoice }}
                            </p>
                            <a
                                href="{{ url('/receipt/' . $lastInvoice) }}"
                                target="_blank"
                                class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded"
                            >
                                🖨️ Print Receipt
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold mb-4">Confirm Payment</h3>
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between">
                        <span>Total:</span>
                        <span class="font-mono">৳{{ number_format($grandTotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Paid:</span>
                        <span class="font-mono">৳{{ number_format($paidAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-red-600">
                        <span>Change Due:</span>
                        <span class="font-mono">৳{{ number_format(max(0, $paidAmount - $grandTotal), 2) }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button
                        wire:click="completeSale"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Confirm & Print
                    </button>
                    <button
                        wire:click="$set('showPaymentModal', false)"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
