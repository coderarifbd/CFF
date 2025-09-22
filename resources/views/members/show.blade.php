<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Member Profile</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="flex flex-col items-center">
                            <img src="{{ $member->profile_picture ? asset('storage/'.$member->profile_picture) : 'https://placehold.co/200x200?text=No+Image' }}" class="h-40 w-40 rounded-full object-cover" alt="Profile">
                            <h3 class="mt-4 text-lg font-semibold">{{ $member->name }}</h3>
                            <p class="text-gray-600">{{ $member->phone }}</p>
                            @php
                                $color = match($member->status){
                                    'active' => 'bg-green-100 text-green-800',
                                    'inactive' => 'bg-gray-100 text-gray-800',
                                    'suspended' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="mt-2 px-2 py-1 rounded text-sm {{ $color }} capitalize">{{ $member->status }}</span>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">NID</div>
                                <div class="font-medium">{{ $member->nid }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Join Date</div>
                                <div class="font-medium">{{ $member->join_date->format('Y-m-d') }}</div>
                            </div>
                            <div class="sm:col-span-2">
                                <div class="text-sm text-gray-500">Address</div>
                                <div class="font-medium">{{ $member->address ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 bg-gray-50 rounded">
                                <div class="text-sm text-gray-500">Total Deposit</div>
                                <div class="text-xl font-bold">{{ number_format($totalDeposit, 2) }}</div>
                                <div class="text-xs text-gray-500 mt-1">Subscription: {{ number_format($subscriptionSum,2) }}, Extra: {{ number_format($extraSum,2) }}, Fines: {{ number_format($fineSum,2) }}</div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded">
                                <div class="text-sm text-gray-500">Total Invest + Interest</div>
                                <div class="text-xl font-bold">{{ number_format($investPlusInterest, 2) }}</div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded">
                                <div class="text-sm text-gray-500">Balance</div>
                                <div class="text-xl font-bold {{ $balance < 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format($balance, 2) }}</div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold mb-2">Monthly Subscription History</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto">
                                    <thead>
                                        <tr class="bg-gray-50 text-left">
                                            <th class="px-4 py-2">Month</th>
                                            <th class="px-4 py-2">Subscription</th>
                                            <th class="px-4 py-2">Extra</th>
                                            <th class="px-4 py-2">Fine</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($monthlyRows as $row)
                                            <tr>
                                                <td class="px-4 py-2">{{ $row['month'] }}</td>
                                                <td class="px-4 py-2">{{ number_format($row['subscription'],2) }}</td>
                                                <td class="px-4 py-2">{{ number_format($row['extra'],2) }}</td>
                                                <td class="px-4 py-2">{{ number_format($row['fine'],2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">No deposit history found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('members.index') }}" class="px-4 py-2 border rounded">Back</a>
                            @role('Admin')
                                <a href="{{ route('members.edit', $member) }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Edit</a>
                            @endrole
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
