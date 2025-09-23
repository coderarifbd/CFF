<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 w-full border rounded px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Filters</label>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            @php($types = collect(request('types', ['deposit','interest','other_income','expense'])))
                            <label class="inline-flex items-center gap-2"><input type="checkbox" name="types[]" value="deposit" @checked($types->contains('deposit'))> <span>Deposit</span></label>
                            <label class="inline-flex items-center gap-2"><input type="checkbox" name="types[]" value="interest" @checked($types->contains('interest'))> <span>Interest</span></label>
                            <label class="inline-flex items-center gap-2"><input type="checkbox" name="types[]" value="other_income" @checked($types->contains('other_income'))> <span>Other Income</span></label>
                            <label class="inline-flex items-center gap-2"><input type="checkbox" name="types[]" value="expense" @checked($types->contains('expense'))> <span>Expense</span></label>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Apply</button>
                        <a href="{{ route('reports.index') }}" class="px-4 py-2 border rounded">Clear</a>
                    </div>
                </form>

                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm text-gray-600">Showing {{ count($items) }} records</div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'csv'])) }}" class="px-3 py-2 text-sm rounded border">Export CSV</a>
                        <button onclick="window.print()" class="px-3 py-2 text-sm rounded border">Print</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-3 py-2 w-12">SL</th>
                                <th class="px-3 py-2">Date</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2">Label</th>
                                <th class="px-3 py-2 text-right">In</th>
                                <th class="px-3 py-2 text-right">Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['date'])->format('d-M - Y') }}</td>
                                    <td class="px-3 py-2 capitalize">{{ str_replace('_',' ', $row['type']) }}</td>
                                    <td class="px-3 py-2">{{ $row['label'] }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['in'], 2) }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['out'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-gray-500">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t font-semibold">
                                <td></td>
                                <td colspan="3" class="px-3 py-2 text-right">Totals</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ number_format($totalIn, 2) }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ number_format($totalOut, 2) }}</td>
                            </tr>
                            <tr class="border-t font-semibold">
                                <td></td>
                                <td colspan="3" class="px-3 py-2 text-right">Balance</td>
                                <td class="px-3 py-2 text-right tabular-nums" colspan="2">{{ number_format($balance, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
