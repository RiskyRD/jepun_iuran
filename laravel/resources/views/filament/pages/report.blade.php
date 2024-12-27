<x-filament::page>
    <div class="space-y-4">
        <div class="flex gap-4">
            <!-- Date Range Form -->
            <div class="col-span-1" style="width: 30%">
                {{ $this->form }}

                <!-- Generate Report Button -->
                <div class="mt-3">
                    <x-filament::button wire:click="generateReport"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 mt-3
                        dark:bg-blue-700 dark:hover:bg-blue-800 dark:text-gray-200">
                        Generate Report
                    </x-filament::button>
                </div>
            </div>

            <!-- Display Results (Report Preview) -->
            <div class="col-span-2 bg-white p-4 rounded-lg shadow dark:bg-gray-900 dark:text-gray-200" style="width: 100%">
                <!-- Kas Awal -->
                <div class="mb-4">
                    <h2 class="font-bold text-lg">Kas Awal: Rp. {{ number_format($previousBalance ?? 0, 0, ',', '.') }}</h2>
                </div>

                <!-- Pemasukan Table -->
                <div class="mb-4">
                    <h3 class="font-semibold text-md mb-2">Pemasukan</h3>
                    <table class="min-w-full text-left table-auto border border-gray-300 dark:border-gray-700" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Kategori</th>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Banyak</th>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic rows here -->
                            @foreach ($incomes as $income)
                            <tr>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $income['category'] }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $income['quantity'] }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Rp. {{ number_format($income['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            <tr class="font-bold">
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Total</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $totalIncomesQuantity }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Rp. {{ number_format($totalIncomesAmount, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pengeluaran Table -->
                <div class="mb-4">
                    <h3 class="font-semibold text-md mb-2">Pengeluaran</h3>
                    <table class="min-w-full text-left table-auto border border-gray-300 dark:border-gray-700" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Kategori</th>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Banyak</th>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic rows here -->
                            @foreach ($expenses as $expense)
                            <tr>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $expense['category'] }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $expense['quantity'] }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Rp. {{ number_format($expense['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            <tr class="font-bold">
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Total</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $totalExpensesQuantity }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Rp. {{ number_format($totalExpensesAmount, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tunggakan Table -->
                <div class="mb-4">
                    <h3 class="font-semibold text-md mb-2">Tunggakan</h3>
                    <table class="min-w-full text-left table-auto border border-gray-300 dark:border-gray-700" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Kategori</th>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Banyak</th>
                                <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic rows here -->
                            <tr>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Wajib</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $arrearsData['arrears_wajib'] ?? 0 }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Rp. {{ number_format($arrearsData['arrears_wajib_nominal'] ?? 0, 0, ',', '.') ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Sampah</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $arrearsData['arrears_sampah'] ?? 0 }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">Rp. {{ number_format($arrearsData['arrears_sampah_nominal'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Total Tunggakan and Kas Terbaru -->
                <div class="flex gap-4">
                    <!-- Total Tunggakan -->
                    <div class="w-1/2 p-4 border border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 rounded-lg">
                        <h4 class="font-bold text-md">Total Tunggakan</h4>
                        <p class="text-lg">Rp. {{ number_format($arrearsData['total_tunggakan_nominal'] ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <!-- Kas Terbaru -->
                    <div class="w-1/2 p-4 border border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 rounded-lg">
                        <h4 class="font-bold text-md">Kas Terbaru</h4>
                        <p class="text-lg">Rp. {{ number_format($latestBalance, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if($isReportGenerated)
                <div class="mt-6">
                    <x-filament::button wire:click="makeReport"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 mt-3
                        dark:bg-green-700 dark:hover:bg-green-800 dark:text-gray-200">
                        Make Report
                    </x-filament::button>
                    <x-filament::button wire:click="storeBalance"
                        class="px-4 py-2 text-white rounded-lg  mt-3">
                        Add Balance
                    </x-filament::button>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-filament::page>
