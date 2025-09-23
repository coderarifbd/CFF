<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Expense</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @php
                    [$__title, $__rest] = array_pad(preg_split('/\s+—\s+/', (string)($expense->note ?? ''), 2), 2, '');
                @endphp
                <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Expense Title</label>
                        <input type="text" name="title" value="{{ old('title', $__title) }}" placeholder="e.g., Office Rent, Utility Bill" class="mt-1 w-full border rounded px-3 py-2" required>
                        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" value="{{ old('date', optional($expense->date)->format('Y-m-d')) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            @if(isset($categories) && count($categories))
                                <small class="text-gray-500">Existing: {{ implode(', ', $categories->toArray()) }}</small>
                            @endif
                        </div>
                        <input list="expense-categories" name="category" value="{{ old('category', $expense->category) }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g. Office Rent" required>
                        <datalist id="expense-categories">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                        @error('category')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Note (optional)</label>
                        <input type="text" name="note" value="{{ old('note', $__rest) }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="Any short note (optional)">
        					@error('note')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('expenses.index') }}" class="btn px-4 py-2 border rounded">Back</a>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('expenses.index') }}" class="btn px-4 py-2 border rounded">Cancel</a>
                            <button class="btn px-4 py-2 bg-blue-600 text-white rounded">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
