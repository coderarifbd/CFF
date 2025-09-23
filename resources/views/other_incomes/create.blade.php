<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Other Income</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('other-incomes.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Income Title</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g., Donation, Misc Income" class="mt-1 w-full border rounded px-3 py-2" required>
                        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            @if(isset($categories) && count($categories))
                                <small class="text-gray-500">Existing: {{ implode(', ', $categories->toArray()) }}</small>
                            @endif
                        </div>
                        <input list="income-categories" name="category" value="{{ old('category') }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g. Donation, Misc Income" required>
                        <datalist id="income-categories">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                        @error('category')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Note (optional)</label>
                        <input type="text" name="note" value="{{ old('note') }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="Any short note (optional)">
                        @error('note')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('other-incomes.index') }}" class="btn px-4 py-2 border rounded">Cancel</a>
                        <button class="btn px-4 py-2 bg-green-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
