<div class="overflow-x-auto">
    <table class="min-w-full table-auto border-separate border-spacing-0">
        <thead class="bg-gray-50">
            <tr class="text-left">
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Category</th>
                <th class="px-4 py-2">Amount</th>
                <th class="px-4 py-2">Note</th>
                <th class="px-4 py-2">Added By</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $e)
                <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                    <td class="px-4 py-2">{{ $e->date->format('Y-m-d') }}</td>
                    <td class="px-4 py-2">{{ $e->category }}</td>
                    <td class="px-4 py-2 font-semibold">{{ number_format($e->amount,2) }}</td>
                    <td class="px-4 py-2">{{ $e->note }}</td>
                    <td class="px-4 py-2">{{ $e->addedBy->name ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-3">
                            @hasanyrole('Admin|Accountant')
                            <a href="{{ route('expenses.edit',$e) }}" class="btn btn-sm">Edit</a>
                            @endhasanyrole
                            @role('Admin')
                            <form action="{{ route('expenses.destroy',$e) }}" method="POST" onsubmit="return confirm('Delete this expense?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm">Delete</button>
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
                <td class="px-4 py-2 font-semibold" colspan="2">Total</td>
                <td class="px-4 py-2 font-semibold">{{ number_format($total,2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-4">{{ $expenses->links() }}</div>
