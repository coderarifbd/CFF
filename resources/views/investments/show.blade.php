<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Investment Details</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                @if (session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 border border-green-200 px-4 py-2 rounded">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 text-red-700 bg-red-100 border border-red-200 px-4 py-2 rounded">
                        <ul class="list-disc ms-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-3">
                        <div>
                            <div class="text-sm text-gray-500">Title</div>
                            <div class="font-semibold">{{ $investment->title }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Type</div>
                            <div class="font-medium capitalize">{{ $investment->type }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Amount</div>
                            <div class="font-medium">{{ number_format($investment->amount, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Date</div>
                            <div class="font-medium">{{ $investment->date->format('Y-m-d') }}</div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <div class="text-sm text-gray-500">Status</div>
                            @php $badge = $investment->status==='active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; @endphp
                            <span class="px-2 py-1 rounded text-sm {{ $badge }} capitalize inline-block">{{ $investment->status }}</span>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Total Interest</div>
                            <div class="font-medium">{{ number_format($investment->total_interest, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Agreement</div>
                            @if($investment->agreement_document)
                                <a href="{{ asset('storage/'.$investment->agreement_document) }}" target="_blank" class="text-blue-700 underline">View Document</a>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <div class="text-sm text-gray-500">Notes</div>
                            <div class="font-medium">{{ $investment->notes ?: '-' }}</div>
                        </div>
                        @if($investment->status === 'returned')
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Return Date</div>
                                <div class="font-medium">{{ optional($investment->return_date)->format('Y-m-d') }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Return Amount</div>
                                <div class="font-medium">{{ number_format($investment->return_amount, 2) }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('investments.index') }}" class="px-4 py-2 border rounded">Back</a>
                    @role('Admin')
                        <a href="{{ route('investments.edit', $investment) }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Edit</a>
                    @endrole
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold mb-2">Interest History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50 text-left">
                                        <th class="px-4 py-2">Date</th>
                                        <th class="px-4 py-2">Amount</th>
                                        <th class="px-4 py-2">Note</th>
                                        <th class="px-4 py-2">Added By</th>
                                        @hasanyrole('Admin|Accountant')
                                        <th class="px-4 py-2">Actions</th>
                                        @endhasanyrole
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($investment->interests as $it)
                                        <tr class="border-t">
                                            <td class="px-4 py-2">{{ $it->date->format('Y-m-d') }}</td>
                                            <td class="px-4 py-2">{{ number_format($it->amount, 2) }}</td>
                                            <td class="px-4 py-2">{{ $it->note }}</td>
                                            <td class="px-4 py-2">{{ $it->addedBy->name ?? '-' }}</td>
                                            @hasanyrole('Admin|Accountant')
                                            <td class="px-4 py-2">
                                                @role('Admin')
                                                <a href="{{ route('investments.interest.edit', [$investment, $it]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-indigo-500/20 bg-indigo-50 hover:bg-indigo-100 text-indigo-700">✏️ Edit</a>
                                                @endrole
                                            </td>
                                            @endhasanyrole
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">No interests added yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="border-t">
                                        <td class="px-4 py-2 font-semibold" colspan="1">Total</td>
                                        <td class="px-4 py-2 font-semibold">{{ number_format($investment->total_interest, 2) }}</td>
                                        <td class="px-4 py-2" colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div>
                        @auth
                        <div class="bg-gray-50 p-4 rounded mb-6">
                            <h4 class="font-semibold mb-3">Add Interest</h4>
                            @if($investment->status === 'returned')
                                <p class="text-sm text-gray-600">Investment has been returned. Adding interest is disabled.</p>
                            @else
                                <form method="POST" action="{{ route('investments.interest.store', $investment) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-sm text-gray-700">Date</label>
                                        <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700">Amount</label>
                                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="mt-1 w-full border rounded px-3 py-2" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700">Note (optional)</label>
                                        <input type="text" name="note" value="{{ old('note') }}" class="mt-1 w-full border rounded px-3 py-2">
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                        @endauth

                        @if(auth()->check() && auth()->user()->hasRole('Admin'))
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-semibold mb-3">Mark as Returned</h4>
                            @if($investment->status === 'returned')
                                <p class="text-sm text-gray-600">Already marked as returned.</p>
                            @else
                                <form method="POST" action="{{ route('investments.return', $investment) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-sm text-gray-700">Return Date</label>
                                        <input type="date" name="return_date" value="{{ old('return_date', now()->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700">Return Amount</label>
                                        <input type="number" step="0.01" name="return_amount" value="{{ old('return_amount') }}" class="mt-1 w-full border rounded px-3 py-2" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700">Note (optional)</label>
                                        <input type="text" name="note" value="{{ old('note') }}" class="mt-1 w-full border rounded px-3 py-2">
                                    </div>
                                    <div class="text-right">
                                        <button class="bg-emerald-600 text-white px-4 py-2 rounded">Mark Returned</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
