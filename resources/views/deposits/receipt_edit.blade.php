<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Deposit</h2>
    </x-slot>

    <div class="py-6" x-data='{
        types: @json($receipt->items->pluck("type")),
    }'>
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 text-red-700 bg-red-100 border border-red-200 px-4 py-2 rounded">
                        <ul class="list-disc ms-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('deposits.update', $receipt->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', $receipt->date->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member</label>
                            <select name="member_id" class="mt-1 w-full border rounded px-3 py-2" required>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" @selected(old('member_id', $receipt->member_id)==$m->id)>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="cash" @selected(old('payment_method',$receipt->payment_method)==='cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method',$receipt->payment_method)==='bank')>Bank</option>
                                <option value="mobile" @selected(old('payment_method',$receipt->payment_method)==='mobile')>Mobile Banking</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Note (optional)</label>
                            <input type="text" name="note" value="{{ old('note', $receipt->note) }}" class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" class="rounded border-gray-300" value="subscription" x-model="types" name="types[]">
                                <span>Monthly Subscription</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" class="rounded border-gray-300" value="extra" x-model="types" name="types[]">
                                <span>Extra Deposit</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" class="rounded border-gray-300" value="fine" x-model="types" name="types[]">
                                <span>Fine</span>
                            </label>
                        </div>
                        @error('types')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    @php
                        $amounts = [
                            'subscription' => old('amount_subscription', optional($receipt->items->firstWhere('type','subscription'))->amount ?? $subscriptionAmount),
                            'extra' => old('amount_extra', optional($receipt->items->firstWhere('type','extra'))->amount ?? ''),
                            'fine' => old('amount_fine', optional($receipt->items->firstWhere('type','fine'))->amount ?? $fineAmount),
                        ];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div x-show="types.includes('subscription')" x-cloak>
                            <label class="block text-sm font-medium text-gray-700">Subscription Amount</label>
                            <input type="number" step="0.01" name="amount_subscription" value="{{ $amounts['subscription'] }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('subscription')">
                        </div>
                        <div x-show="types.includes('extra')" x-cloak>
                            <label class="block text-sm font-medium text-gray-700">Extra Amount</label>
                            <input type="number" step="0.01" name="amount_extra" value="{{ $amounts['extra'] }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('extra')">
                        </div>
                        <div x-show="types.includes('fine')" x-cloak>
                            <label class="block text-sm font-medium text-gray-700">Fine Amount</label>
                            <input type="number" step="0.01" name="amount_fine" value="{{ $amounts['fine'] }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('fine')">
                        </div>
                    </div>

                    @error('amount')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('deposits.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
