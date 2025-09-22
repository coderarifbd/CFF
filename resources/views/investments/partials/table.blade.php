<div class="overflow-x-auto">
    <table class="min-w-full table-auto border-separate border-spacing-0">
        <thead class="sticky top-0 z-10">
            <tr class="bg-gray-50/90 backdrop-blur text-left shadow">
                <th class="px-4 py-2 font-semibold text-gray-700">Title</th>
                <th class="px-4 py-2 font-semibold text-gray-700">Type</th>
                <th class="px-4 py-2 font-semibold text-gray-700 text-right">Amount</th>
                <th class="px-4 py-2 font-semibold text-gray-700">Date</th>
                <th class="px-4 py-2 font-semibold text-gray-700">Status</th>
                <th class="px-4 py-2 font-semibold text-gray-700 text-right">Total Interest</th>
                <th class="px-4 py-2 font-semibold text-gray-700">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($investments as $inv)
                <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('investments.show',$inv) }}" class="text-blue-700 hover:underline font-medium">{{ $inv->title }}</a>
                        <div class="text-xs text-gray-500">Added: {{ optional($inv->addedBy)->name ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3 capitalize">
                        @php
                            $typeBadge = [
                                'land' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-500/10',
                                'business' => 'bg-indigo-100 text-indigo-800 ring-1 ring-indigo-500/10',
                                'bank' => 'bg-sky-100 text-sky-800 ring-1 ring-sky-500/10',
                                'other' => 'bg-gray-100 text-gray-800 ring-1 ring-gray-500/10',
                            ][$inv->type] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">{{ $inv->type }}</span>
                    </td>
                    <td class="px-4 py-3 font-semibold text-right tabular-nums">{{ number_format($inv->amount,2) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $inv->date->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $badge = $inv->status==='active'
                                ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-500/10'
                                : 'bg-gray-100 text-gray-800 ring-1 ring-gray-500/10';
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }} capitalize">{{ $inv->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($inv->total_interest,2) }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('investments.show',$inv) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-black/5 bg-white hover:bg-gray-50 text-gray-700">👁️ View</a>
                            @if(auth()->check() && auth()->user()->hasRole('Admin'))
                            <a href="{{ route('investments.edit',$inv) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-indigo-500/20 bg-indigo-50 hover:bg-indigo-100 text-indigo-700">✏️ Edit</a>
                            <form action="{{ route('investments.destroy',$inv) }}" method="POST" class="inline" onsubmit="return confirm('Delete this investment? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-rose-500/20 bg-rose-50 hover:bg-rose-100 text-rose-700">🗑️ Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center">
                        <div class="mx-auto w-fit rounded-xl border border-dashed border-gray-200 p-6 text-gray-500">
                            <div class="text-3xl mb-1">📉</div>
                            <div>No investments found.</div>
                        </div>
                        @auth
                        <div class="mt-3">
                            <a href="{{ route('investments.create') }}" class="btn">+ Add your first investment</a>
                        </div>
                        @endauth
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(!empty($summary))
        <tfoot>
            <tr class="bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-600" colspan="2">Totals (filtered)</td>
                <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format($summary['amountSum'] ?? 0, 2) }}</td>
                <td></td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    <span class="mr-4">Active: {{ number_format($summary['activeCount'] ?? 0) }}</span>
                    <span>Returned: {{ number_format($summary['returnedCount'] ?? 0) }}</span>
                </td>
                <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format($summary['interestSum'] ?? 0, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<div class="mt-4">{{ $investments->links() }}</div>
