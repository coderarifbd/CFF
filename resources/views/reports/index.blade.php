<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                <div class="px-6 pt-6 pb-3 border-b">
                    <form id="report-filters" method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-indigo-300 focus:border-indigo-300">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-indigo-300 focus:border-indigo-300">
                        </div>
                        
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Filters</label>
                            <div class="mt-1 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @php($types = collect(request('types', ['deposit','interest','invest_out','invest_return','other_income','expense'])))
                                <label class="inline-flex items-center gap-2"><input type="checkbox" class="rounded border-gray-300" name="types[]" value="deposit" @checked($types->contains('deposit'))> <span>Deposit</span></label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" class="rounded border-gray-300" name="types[]" value="interest" @checked($types->contains('interest'))> <span>Interest</span></label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" class="rounded border-gray-300" name="types[]" value="invest_out" @checked($types->contains('invest_out'))> <span>Invest Outflow</span></label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" class="rounded border-gray-300" name="types[]" value="invest_return" @checked($types->contains('invest_return'))> <span>Invest Return</span></label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" class="rounded border-gray-300" name="types[]" value="other_income" @checked($types->contains('other_income'))> <span>Other Income</span></label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" class="rounded border-gray-300" name="types[]" value="expense" @checked($types->contains('expense'))> <span>Expense</span></label>
                            </div>
                        </div>
                        <div class="md:col-span-2 flex gap-2 md:justify-end">
                            <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-700">
                                <span>Apply</span>
                            </button>
                            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">Clear</a>
                        </div>
                    </form>
                </div>

                @if (session('status'))
                    <div class="px-6 pt-3 text-sm text-emerald-700">{{ session('status') }}</div>
                @endif
                <div class="px-6 py-3 flex items-center justify-between print:hidden">
                    <div class="text-sm text-gray-600">Showing {{ count($items) }} records</div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'csv'])) }}" class="px-3 py-2 text-sm rounded-lg border hover:bg-gray-50">Export CSV</a>
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="px-3 py-2 text-sm rounded-lg border hover:bg-gray-50">Export PDF</a>
                        <button onclick="window.print()" class="px-3 py-2 text-sm rounded-lg border hover:bg-gray-50">Print</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-separate border-spacing-0 text-sm">
                        <thead class="sticky top-0">
                            <tr class="bg-gray-50 text-left">
                                <th class="px-3 py-2 w-12">SL</th>
                                <th class="px-3 py-2"><button type="button" data-sort="date" class="hover:underline">Date</button></th>
                                <th class="px-3 py-2"><button type="button" data-sort="type" class="hover:underline">Type</button></th>
                                <th class="px-3 py-2">Label</th>
                                <th class="px-3 py-2 text-right"><button type="button" data-sort="in" class="hover:underline">In</button></th>
                                <th class="px-3 py-2 text-right"><button type="button" data-sort="out" class="hover:underline">Out</button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                                <tr class="border-t odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap" data-date="{{ $row['date'] }}">{{ \Carbon\Carbon::parse($row['date'])->format('d-M - Y') }}</td>
                                    <td class="px-4 py-2 capitalize" data-type="{{ str_replace('_',' ', $row['type']) }}">
                                        @php($t = str_replace('_',' ', $row['type']))
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $t==='deposit' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $t==='interest' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $t==='invest out' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                            {{ $t==='invest return' ? 'bg-purple-100 text-purple-800' : '' }}
        									{{ $t==='other income' ? 'bg-slate-100 text-slate-800' : '' }}
                                            {{ $t==='expense' ? 'bg-rose-100 text-rose-800' : '' }}">{{ $t }}</span>
                                    </td>
                                    <td class="px-4 py-2">{{ $row['label'] }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums font-semibold" data-in="{{ number_format($row['in'], 2, '.', '') }}">{{ number_format($row['in'], 2) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums font-semibold" data-out="{{ number_format($row['out'], 2, '.', '') }}">{{ number_format($row['out'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t">
                                <td class="px-4 py-2 font-semibold"></td>
                                <td class="px-4 py-2 font-semibold" colspan="2">Totals</td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2 text-right font-semibold">{{ number_format($totalIn, 2) }}</td>
                                <td class="px-4 py-2 text-right font-semibold">{{ number_format($totalOut, 2) }}</td>
                            </tr>
                            <tr class="border-t">
                                <td class="px-4 py-2 font-semibold"></td>
                                <td class="px-4 py-2 font-semibold" colspan="2">Balance</td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2 text-right font-semibold" colspan="2">{{ number_format($balance, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            nav, .print\:hidden, .shadow-sm, .ring-1, .ring-black\/5 { display:none !important; }
            table { font-size: 12px; }
            thead tr { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tfoot tr { background: #f9fafb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .px-6 { padding-left: 0; padding-right: 0; }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        (function(){
            const form = document.getElementById('report-filters');
            if(!form) return;
            // Submit on Enter
            form.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); form.submit(); } });

            // Client-side sorting for table
            const table = document.querySelector('table');
            const tbody = table ? table.querySelector('tbody') : null;
            const headers = table ? table.querySelectorAll('thead [data-sort]') : [];
            let sortState = { key: null, dir: 1 }; // 1 asc, -1 desc

            function getCellValue(tr, key){
                if(key==='date') return tr.querySelector('[data-date]')?.getAttribute('data-date') || '';
                if(key==='type') return (tr.querySelector('[data-type]')?.getAttribute('data-type')||'').toLowerCase();
                if(key==='in') return parseFloat(tr.querySelector('[data-in]')?.getAttribute('data-in')||'0');
                if(key==='out') return parseFloat(tr.querySelector('[data-out]')?.getAttribute('data-out')||'0');
                return '';
            }
            function cmp(a,b){
                const va = a.v, vb = b.v;
                if(typeof va === 'number' && typeof vb === 'number') return (va - vb) * sortState.dir;
                return String(va).localeCompare(String(vb)) * sortState.dir;
            }
            function sortBy(key){
                if(!tbody) return;
                sortState.dir = (sortState.key === key) ? -sortState.dir : 1;
                sortState.key = key;
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const enriched = rows.map(tr=>({ tr, v: getCellValue(tr, key) }));
                enriched.sort(cmp);
                const frag = document.createDocumentFragment();
                enriched.forEach(e=>frag.appendChild(e.tr));
                tbody.appendChild(frag);
            }
            headers.forEach(h=> h.addEventListener('click', ()=> sortBy(h.getAttribute('data-sort'))));
        })();
    </script>
    @endpush
</x-app-layout>
