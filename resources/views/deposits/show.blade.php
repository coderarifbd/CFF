<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deposit Details</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Date</div>
                        <div class="font-medium">{{ $deposit->date->format('Y-m-d') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Member</div>
                        <div class="font-medium">{{ $deposit->member->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Type</div>
                        <div class="font-medium capitalize">{{ $deposit->type }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Amount</div>
                        <div class="font-medium">{{ number_format($deposit->amount,2) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Payment Method</div>
                        <div class="font-medium capitalize">{{ $deposit->payment_method }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Added By</div>
                        <div class="font-medium">{{ $deposit->addedBy->name ?? '-' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="text-sm text-gray-500">Note</div>
                        <div class="font-medium">{{ $deposit->note ?: '-' }}</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('deposits.index') }}" class="px-4 py-2 border rounded">Back</a>
                    @role('Admin')
                    <a href="{{ route('deposits.edit', $deposit) }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Edit</a>
                    @endrole
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
