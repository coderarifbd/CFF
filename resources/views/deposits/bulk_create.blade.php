<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bulk Create Deposits</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('deposits.bulk-store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @csrf

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700">Payment Method</label>
                            <select name="payment_method" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="cash" {{ old('payment_method')==='cash' ? 'selected':'' }}>Cash</option>
                                <option value="bank" {{ old('payment_method')==='bank' ? 'selected':'' }}>Bank</option>
                                <option value="mobile" {{ old('payment_method')==='mobile' ? 'selected':'' }}>Mobile</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700">Type</label>
                            <select name="type" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="subscription" {{ old('type')==='subscription' ? 'selected':'' }}>Subscription</option>
                                <option value="extra" {{ old('type')==='extra' ? 'selected':'' }}>Extra</option>
                                <option value="fine" {{ old('type')==='fine' ? 'selected':'' }}>Fine</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700">Amount</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="0.00" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700">Note (optional)</label>
                            <input type="text" name="note" value="{{ old('note') }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="Optional note for all receipts">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm text-gray-700">Members</label>
                        <div class="border rounded p-3 max-h-96 overflow-auto">
                            <div class="mb-2 flex items-center gap-2">
                                <input id="check_all" type="checkbox" class="h-4 w-4" onclick="document.querySelectorAll('[name=\'member_ids[]\']').forEach(cb=>cb.checked=this.checked)" />
                                <label for="check_all" class="text-sm">Select All</label>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($members as $m)
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="member_ids[]" value="{{ $m->id }}" class="h-4 w-4" {{ in_array($m->id, old('member_ids', [])) ? 'checked' : '' }}>
                                        <span>{{ $m->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Tip: Subscription duplicates for the same month will be skipped automatically.</p>
                    </div>

                    <div class="md:col-span-2 flex items-center justify-end gap-3">
                        <a href="{{ route('deposits.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Create Deposits</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
