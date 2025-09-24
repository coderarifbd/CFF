<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr class="text-left">
                <th class="px-4 py-2 w-12">SL</th>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Title</th>
                <th class="px-4 py-2">Note</th>
                <th class="px-4 py-2">Amount</th>
                <th class="px-4 py-2">Added By</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($expenses as $e)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ ($expenses->firstItem() ?? 1) + $loop->index }}</td>
                    <td class="px-4 py-2">{{ $e->date->format('d-M - Y') }}</td>
                    @php([$__title, $__rest] = array_pad(preg_split('/\s+—\s+/',(string)($e->note??''),2),2,''))
                    <td class="px-4 py-2">{{ $__title }}</td>
                    <td class="px-4 py-2">{{ $__rest }}</td>
                    <td class="px-4 py-2 font-semibold">{{ number_format($e->amount,2) }}</td>
                    <td class="px-4 py-2">{{ $e->addedBy->name ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <div class="flex flex-wrap items-center gap-2">
                            @php($tools = optional(\App\Models\Setting::first()))
                            @if(auth()->check() && (auth()->user()->hasRole('Admin') || (auth()->user()->hasRole('Accountant') && ($tools->allow_accountant_edit_expenses ?? false))))
                                <a href="{{ route('expenses.edit',$e) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-indigo-500/20 bg-indigo-50 hover:bg-indigo-100 text-indigo-700">✏️ Edit</a>
                            @endif
                            @role('Admin')
                            <form action="{{ route('expenses.destroy',$e) }}" method="POST" onsubmit="return confirm('Delete this expense?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-rose-500/20 bg-rose-50 hover:bg-rose-100 text-rose-700">🗑️ Delete</button>
                            </form>
                            @endrole
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">No expenses found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="border-t">
                <td class="px-4 py-2 font-semibold"></td>
                <td class="px-4 py-2 font-semibold" colspan="3">Total</td>
                <td class="px-4 py-2 font-semibold">{{ number_format($total,2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-4">{{ $expenses->links() }}</div>
