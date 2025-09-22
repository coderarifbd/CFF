<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-900 dark:text-gray-700 tracking-tight">
                {{ __('Dashboard') }}
                check again
            </h2>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Updated {{ now()->format('M d, Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @php
                $money = fn($n) => number_format($n, 2);
            @endphp

            {{-- KPI Grid (only required metrics) --}}
            <section aria-labelledby="kpi-heading">
                <h3 id="kpi-heading" class="sr-only">Key performance indicators</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-[20px]">
                    {{-- Total Balance: Deposits + Returns + Interest --}}
                    <div class="rounded-2xl p-6 shadow-sm ring-1 ring-black/5 bg-gradient-to-br from-slate-800 to-slate-900 text-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[11px] uppercase tracking-[.14em] text-white">Total Balance</p>
                                <p class="text-4xl font-extrabold mt-1 tabular-nums">{{ $money($totalBalance ?? 0) }}</p>
                                <p class="text-xs text-slate-400 mt-1">Deposits + Interest</p>
                            </div>
                            <div class="w-11 h-11 rounded-xl bg-white/10 grid place-items-center text-lg">💰</div>
                        </div>
                    </div>

                    {{-- Total Invest --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                        <p class="text-[11px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-400">Total Invest</p>
                        <p class="text-3xl font-bold mt-1 tabular-nums text-white">{{ $money($totalInvestOutflow ?? 0) }}</p>
                    </div>

                    {{-- Total Interest --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                        <p class="text-[11px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-400">Total Interest</p>
                        <p class="text-3xl font-bold mt-1 tabular-nums text-white">{{ $money($totalInvestInterest ?? 0) }}</p>
                    </div>

                    {{-- Total Deposit (all receipts) --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                        <p class="text-[11px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-400">Total Deposit</p>
                        <p class="text-3xl font-bold mt-1 tabular-nums text-white">{{ $money($totalReceipts ?? 0) }}</p>
                    </div>

                    {{-- Total Fine --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                        <p class="text-[11px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-400">Total Fine</p>
                        <p class="text-3xl font-bold mt-1 tabular-nums text-white">{{ $money($totalFine ?? 0) }}</p>
                    </div>

                    {{-- Extra Deposit --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                        <p class="text-[11px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-400">Extra Deposit</p>
                        <p class="text-3xl font-bold mt-1 tabular-nums text-white">{{ $money($totalExtra ?? 0) }}</p>
                    </div>

                    {{-- Total Expense --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                        <p class="text-[11px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-400">Total Expense</p>
                        <p class="text-3xl font-bold mt-1 tabular-nums text-white">{{ $money($totalExpense ?? 0) }}</p>
                    </div>

                    {{-- Other Income --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                        <p class="text-[11px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-400">Other Income</p>
                        <p class="text-3xl font-bold mt-1 tabular-nums text-white">{{ $money($otherIncome ?? 0) }}</p>
                        <p class="text-xs text-slate-400 mt-1">Manual income (excludes deposits and investment returns)</p>
                    </div>

                    {{-- Investments: Active / Returned --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                        <p class="text-[11px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-400">Investments</p>
                        <p class="text-2xl font-bold mt-1">
                            <span class="tabular-nums text-white">{{ $activeInvestments ?? 0 }}</span>
                            <span class="text-gray-400 dark:text-slate-400 text-base"> Active - <small class="text-[10px]">{{ $money($activeInvestAmount ?? 0) }}</small></span>
                            <span class="mx-2 text-gray-300 dark:text-slate-500">/</span>
                            <span class="tabular-nums text-white">{{ $returnedInvestments ?? 0 }} </span>
                            <span class="text-gray-400 dark:text-slate-400 text-base"> Returned - <small class="text-[10px]">{{ $money($returnedInvestAmount ?? 0) }}</small></span>
                        </p>
                    </div>
                </div>
            </section>

            {{-- Remaining Balance --}}
            <section class="mb-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm ring-1 ring-black/5">
                    <div class="flex items-center justify-between">
                        <!-- Left -->
                        <div>
                            <p class="text-[16px] uppercase tracking-[.14em] text-gray-500 dark:text-slate-200 font-semibold">
                                Remaining Balance
                            </p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">
                                Total + Returns + Other Income − Invest − Expense
                            </p>
                        </div>

                        <!-- Right -->
                        <p class="text-3xl font-extrabold tabular-nums {{ ($remainingBalance ?? 0) < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $money($remainingBalance ?? 0) }} /-
                        </p>
                    </div>
                </div>
            </section>

            {{-- Recent activity --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-[20px]">
                {{-- Recent Deposits --}}
                <div class="bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/5 rounded-2xl">
                    <div class="px-5 pt-5 pb-3 border-b border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold text-gray-900 dark:text-white">Recent Deposits</h4>
                            <span class="text-xs text-gray-500 dark:text-slate-400">Last 10 records</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-slate-700/40 text-gray-600 dark:text-slate-300">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">Date</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">Member</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">Breakdown</th>
                                    <th scope="col" class="px-4 py-2 text-right font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @forelse($recentReceipts as $r)
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-slate-700/30">
                                        <td class="px-4 py-3 text-gray-700 dark:text-slate-200 whitespace-nowrap">{{ $r->date->format('Y-m-d') }}</td>
                                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $r->member->name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1.5">
                                                @php
                                                    $map = [
                                                        'subscription' => ['Monthly', 'bg-blue-100 text-blue-800 dark:bg-blue-400/20 dark:text-blue-300 ring-1 ring-inset ring-blue-500/10'],
                                                        'extra'        => ['Extra',   'bg-gray-100 text-gray-800 dark:bg-slate-400/20 dark:text-slate-200 ring-1 ring-inset ring-gray-500/10'],
                                                        'fine'         => ['Fine',    'bg-red-100 text-red-800 dark:bg-rose-400/20 dark:text-rose-300 ring-1 ring-inset ring-rose-500/10'],
                                                    ];
                                                @endphp
                                                @foreach($r->items as $it)
                                                    @php
                                                        [$label, $style] = $map[$it->type] ?? ['Other', 'bg-gray-100 text-gray-800 dark:bg-slate-400/20 dark:text-slate-200 ring-1 ring-inset ring-gray-500/10'];
                                                    @endphp
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $style }}">
                                                        {{ $label }}: {{ number_format($it->amount, 2) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white tabular-nums">
                                            {{ number_format($r->total_amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center">
                                            <div class="mx-auto w-fit rounded-xl border border-dashed border-gray-200 dark:border-slate-600 p-6">
                                                <div class="text-3xl mb-1">🗂️</div>
                                                <p class="text-sm text-gray-500 dark:text-slate-400">No recent deposits</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Recent Investments --}}
                <div class="bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/5 rounded-2xl">
                    <div class="px-5 pt-5 pb-3 border-b border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold text-gray-900 dark:text-white">Recent Investments</h4>
                            <span class="text-xs text-gray-500 dark:text-slate-400">Latest activity</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-slate-700/40 text-gray-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium">Date</th>
                                    <th class="px-4 py-2 text-left font-medium">Title</th>
                                    <th class="px-4 py-2 text-left font-medium">Type</th>
                                    <th class="px-4 py-2 text-right font-medium">Amount</th>
                                    <th class="px-4 py-2 text-left font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @forelse($recentInvestments as $inv)
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-slate-700/30">
                                        <td class="px-4 py-3 text-gray-700 dark:text-slate-200 whitespace-nowrap">{{ $inv->date->format('Y-m-d') }}</td>
                                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $inv->title }}</td>
                                        <td class="px-4 py-3 capitalize text-gray-700 dark:text-slate-200">{{ $inv->type }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ number_format($inv->amount, 2) }}</td>
                                        <td class="px-4 py-3">
                                            @php
                                                $isActive = $inv->status === 'active';
                                                $badge = $isActive
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/20 dark:text-emerald-300 ring-1 ring-inset ring-emerald-500/10'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-slate-400/20 dark:text-slate-200 ring-1 ring-inset ring-gray-500/10';
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }} capitalize">
                                                {{ $inv->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center">
                                            <div class="mx-auto w-fit rounded-xl border border-dashed border-gray-200 dark:border-slate-600 p-6">
                                                <div class="text-3xl mb-1">📉</div>
                                                <p class="text-sm text-gray-500 dark:text-slate-400">No recent investments</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
