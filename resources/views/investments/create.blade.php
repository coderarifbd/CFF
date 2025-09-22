<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Investment</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('investments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" class="mt-1 block w-full border rounded px-3 py-2" required>
                                @foreach(['land'=>'Land','business'=>'Business','bank'=>'Bank','other'=>'Other'] as $k=>$v)
                                    <option value="{{ $k }}" @selected(old('type')===$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            @error('type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agreement Document</label>
                            <input type="file" name="agreement_document" accept=".pdf,image/*" class="mt-1 block w-full border rounded px-3 py-2">
                            @error('agreement_document')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
                        @error('notes')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('investments.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
