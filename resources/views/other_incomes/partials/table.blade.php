<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr class="text-left">
                <th class="px-3 py-2 w-12">SL</th>
                <th class="px-3 py-2">Date</th>
                <th class="px-3 py-2">Category</th>
                <th class="px-3 py-2">Note</th>
                <th class="px-3 py-2 text-right">Amount</th>
                @hasanyrole('Admin|Accountant')
                <th class="px-3 py-2 text-right">Actions</th>
                @endhasanyrole
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($incomes as $inc)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">{{ ($incomes->firstItem() ?? 1) + $loop->index }}</td>
                    <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($inc->date)->format('d-F - Y') }}</td>
                    <td class="px-3 py-2">{{ $inc->category }}</td>
                    <td class="px-3 py-2">{{ $inc->note }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($inc->amount, 2) }}</td>
                    @hasanyrole('Admin|Accountant')
                    <td class="px-3 py-2 text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('other-incomes.edit', $inc) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-indigo-500/20 bg-indigo-50 hover:bg-indigo-100 text-indigo-700">✏️ Edit</a>
                            @role('Admin')
                            <form method="POST" action="{{ route('other-incomes.destroy', $inc) }}" onsubmit="return confirm('Delete this income?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-rose-500/20 bg-rose-50 hover:bg-rose-100 text-rose-700">🗑️ Delete</button>
                            </form>
                            @endrole
                        </div>
                    </td>
                    @endhasanyrole
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-6 text-center text-gray-500">No records</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="border-t font-semibold">
                <td></td>
                <td colspan="3" class="px-3 py-2 text-right">Total</td>
                <td class="px-3 py-2 text-right">{{ number_format($total, 2) }}</td>
                @hasanyrole('Admin|Accountant')
                <td></td>
                @endhasanyrole
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-4">{{ $incomes->links() }}</div>
