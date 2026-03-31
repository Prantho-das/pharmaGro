<div class="space-y-6 p-4">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Sales</h3>
            <p class="text-2xl font-bold">{{ number_format($totalSales) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Gross Revenue</h3>
            <p class="text-2xl font-bold">৳{{ number_format($grossRevenue, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Paid</h3>
            <p class="text-2xl font-bold text-green-600">৳{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Due</h3>
            <p class="text-2xl font-bold text-red-600">৳{{ number_format($totalDue, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Profit</h3>
            <p class="text-2xl font-bold text-blue-600">৳{{ number_format($netProfit, 2) }}</p>
        </div>
    </div>

    <!-- Expiring Products Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-bold">Expiring Products (Next {{ $expiringDays }} Days)</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Batches with stock that will expire soon. Take action to avoid waste.
            </p>
        </div>
        {{ $this->table }}
    </div>
</div>
