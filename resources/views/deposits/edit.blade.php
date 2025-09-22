<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Deposit</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('deposits.update', $deposit) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', $deposit->date->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                            @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member</label>
                            <select name="member_id" class="mt-1 w-full border rounded px-3 py-2" required>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" @selected(old('member_id', $deposit->member_id)==$m->id)>{{ $m->name }}</option>
                                @endforeach
                            </select>
                            @error('member_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="subscription" @selected(old('type', $deposit->type)==='subscription')>Monthly Subscription</option>
                                <option value="extra" @selected(old('type', $deposit->type)==='extra')>Extra Deposit</option>
                                <option value="fine" @selected(old('type', $deposit->type)==='fine')>Fine</option>
                            </select>
                            @error('type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount', $deposit->amount) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                            @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="cash" @selected(old('payment_method', $deposit->payment_method)==='cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method', $deposit->payment_method)==='bank')>Bank</option>
                                <option value="mobile" @selected(old('payment_method', $deposit->payment_method)==='mobile')>Mobile Banking</option>
                            </select>
                            @error('payment_method')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Note (optional)</label>
                            <input type="text" name="note" value="{{ old('note', $deposit->note) }}" class="mt-1 w-full border rounded px-3 py-2">
                            @error('note')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('deposits.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
