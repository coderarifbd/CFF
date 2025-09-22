<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Investment</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('investments.update', $investment) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $investment->title) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" class="mt-1 block w-full border rounded px-3 py-2" required>
                                @foreach(['land'=>'Land','business'=>'Business','bank'=>'Bank','other'=>'Other'] as $k=>$v)
                                    <option value="{{ $k }}" @selected(old('type', $investment->type)===$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            @error('type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount', $investment->amount) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', $investment->date->toDateString()) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agreement Document</label>
                            <input type="file" name="agreement_document" accept=".pdf,image/*" class="mt-1 block w-full border rounded px-3 py-2">
                            @if($investment->agreement_document)
                                <a href="{{ asset('storage/'.$investment->agreement_document) }}" target="_blank" class="text-blue-700 underline text-sm mt-1 inline-block">Current document</a>
                            @endif
                            @error('agreement_document')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full border rounded px-3 py-2">{{ old('notes', $investment->notes) }}</textarea>
                        @error('notes')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full border rounded px-3 py-2" required>
                                @foreach(['active'=>'Active','returned'=>'Returned'] as $k=>$v)
                                    <option value="{{ $k }}" @selected(old('status',$investment->status)===$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            @error('status')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Return Date</label>
                            <input type="date" name="return_date" value="{{ old('return_date', optional($investment->return_date)->toDateString()) }}" class="mt-1 block w-full border rounded px-3 py-2">
                            @error('return_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Return Amount</label>
                            <input type="number" step="0.01" name="return_amount" value="{{ old('return_amount', $investment->return_amount) }}" class="mt-1 block w-full border rounded px-3 py-2">
                            @error('return_amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('investments.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
