<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Deposits</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-sm text-gray-500">Subscription</div>
                        <div class="text-xl font-bold">{{ number_format($subscriptionSum,2) }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-sm text-gray-500">Extra</div>
                        <div class="text-xl font-bold">{{ number_format($extraSum,2) }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-sm text-gray-500">Fines</div>
                        <div class="text-xl font-bold text-red-600">{{ number_format($fineSum,2) }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-sm text-gray-500">My Net (Deposit - Fine)</div>
                        <div class="text-xl font-bold">{{ number_format($myNet,2) }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-sm text-gray-500">Company Balance</div>
                        <div class="text-xl font-bold">{{ number_format($companyBalance,2) }}</div>
                        <div class="text-xs text-gray-500">Active Members: {{ $activeMembers }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded">
                        <div class="text-sm text-gray-500">My Equal Share</div>
                        <div class="text-xl font-bold">{{ number_format($equalShare,2) }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 mb-4">
                    <form method="GET" action="{{ route('deposits.my') }}" class="flex items-center gap-2 flex-wrap">
                        <select name="month" class="border rounded px-3 py-2">
                            <option value="">Month</option>
                            @for($m=1;$m<=12;$m++)
                                <option value="{{ $m }}" @selected(request('month')==$m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endfor
                        </select>
                        <select name="year" class="border rounded px-3 py-2">
                            <option value="">Year</option>
                            @for($y=2019;$y<=date('Y')+1;$y++)
                                <option value="{{ $y }}" @selected(request('year')==$y)>{{ $y }}</option>
                            @endfor
                        </select>
                        <button class="btn">Filter</button>
                        @if(request()->hasAny(['month','year']))
                            <a href="{{ route('deposits.my') }}" class="px-3 py-2 text-sm text-gray-600">Clear</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Breakdown</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipts as $r)
                                <tr class="odd:bg-white even:bg-gray-50">
                                    <td class="px-4 py-2">{{ $r->date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($r->items as $it)
                                                @php
                                                    $label = ucfirst($it->type);
                                                    $cls = match($it->type){
                                                        'subscription' => 'bg-blue-100 text-blue-800',
                                                        'extra' => 'bg-gray-100 text-gray-800',
                                                        'fine' => 'bg-rose-100 text-rose-800',
                                                        default => 'bg-gray-100 text-gray-800',
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cls }}">{{ $label }}: {{ number_format($it->amount,2) }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-right">{{ number_format($r->total_amount,2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-6 text-center text-gray-500" colspan="3">No deposits found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $receipts->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
