<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Interest</h2>
    </x-slot>

    <div class="py-6">
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

                <div class="mb-4 text-sm text-gray-600">
                    <div><span class="text-gray-500">Investment:</span> <span class="font-medium">{{ $investment->title }}</span></div>
                    <div><span class="text-gray-500">Type:</span> <span class="font-medium capitalize">{{ $investment->type }}</span></div>
                </div>

                <form method="POST" action="{{ route('investments.interest.update', [$investment, $interest]) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" value="{{ old('date', optional($interest->date)->format('Y-m-d')) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount', $interest->amount) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Note (optional)</label>
                        <input type="text" name="note" value="{{ old('note', $interest->note) }}" class="mt-1 w-full border rounded px-3 py-2">
                        @error('note')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('investments.show', $investment) }}" class="btn px-4 py-2 border rounded">Back</a>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('investments.show', $investment) }}" class="btn px-4 py-2 border rounded">Cancel</a>
                            <button class="btn px-4 py-2 bg-blue-600 text-white rounded">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
