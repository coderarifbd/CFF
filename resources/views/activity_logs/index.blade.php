<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Activity Logs</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                <div class="px-6 pt-6 pb-3 border-b">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-4">
                            <label class="block text-sm text-gray-700">Model Type</label>
                            <input name="model" value="{{ request('model') }}" placeholder="e.g. App\\Models\\Cashbook" class="mt-1 w-full border rounded px-3 py-2" />
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm text-gray-700">Action</label>
                            <input name="action" value="{{ request('action') }}" placeholder="updated" class="mt-1 w-full border rounded px-3 py-2" />
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm text-gray-700">User ID</label>
                            <input name="user_id" value="{{ request('user_id') }}" class="mt-1 w-full border rounded px-3 py-2" />
                        </div>
                        <div class="md:col-span-2 flex gap-2 md:justify-end">
                            <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-700">Apply</button>
                            <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">Clear</a>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-4 py-2">Date/Time</th>
                                <th class="px-4 py-2">User</th>
                                <th class="px-4 py-2">Page</th>
                                <th class="px-4 py-2">Entry</th>
                                <th class="px-4 py-2">Action</th>
                                <th class="px-4 py-2">Changes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $map = [
                                        'App\\Models\\DepositReceipt' => 'Deposits',
                                        'App\\Models\\InvestmentInterest' => 'Investment Interest',
                                        'App\\Models\\Investment' => 'Investments',
                                    ];
                                    // Normalize changes first (used below for page/entry inference)
                                    $changes = is_array($log->changes) ? $log->changes : [];
                                    $before = $changes['before'] ?? [];
                                    $after = $changes['after'] ?? [];

                                    $entry = '';
                                    if ($log->model_type === 'App\\Models\\Cashbook') {
                                        $cb = \App\Models\Cashbook::find($log->model_id);
                                        $isExpense = ($cb && $cb->type === 'expense') || (($before['type'] ?? null) === 'expense') || (($after['type'] ?? null) === 'expense');
                                        $page = $isExpense ? 'Expenses' : 'Other Income';
                                        $d = $cb?->date ? optional($cb->date)->format('d-M - Y') : ($before['date'] ?? $after['date'] ?? '');
                                        $cat = $cb->category ?? ($before['category'] ?? $after['category'] ?? '');
                                        $amt = number_format((float)($cb->amount ?? ($before['amount'] ?? $after['amount'] ?? 0)), 2);
                                        $entry = trim(($d? $d.' · ' : '').($cat ?: '').($amt ? ' · '.$amt : ''));
                                    } elseif ($log->model_type === 'App\\Models\\DepositReceipt') {
                                        $r = \App\Models\DepositReceipt::with('member')->find($log->model_id);
                                        $page = $map[$log->model_type] ?? 'Deposits';
                                        $d = $r?->date ? optional($r->date)->format('d-M - Y') : ($before['date'] ?? $after['date'] ?? '');
                                        $who = $r->member->name ?? ('Member #'.($before['member_id'] ?? $after['member_id'] ?? ''));
                                        $total = number_format((float)($r->total_amount ?? ($after['total_amount'] ?? $before['total_amount'] ?? 0)),2);
                                        $entry = trim(($d? $d.' · ' : '').$who.' · '.$total);
                                    } elseif ($log->model_type === 'App\\Models\\InvestmentInterest') {
                                        $ii = \App\Models\InvestmentInterest::with('investment')->find($log->model_id);
                                        $page = $map[$log->model_type] ?? 'Investment Interest';
                                        $d = $ii?->date ? optional($ii->date)->format('d-M - Y') : ($before['date'] ?? $after['date'] ?? '');
                                        $title = $ii->investment->title ?? '';
                                        $amt = number_format((float)($ii->amount ?? ($after['amount'] ?? $before['amount'] ?? 0)),2);
                                        $entry = trim(($d? $d.' · ' : '').($title? $title.' · ' : '').$amt);
                                    } elseif ($log->model_type === 'App\\Models\\Investment') {
                                        $inv = \App\Models\Investment::find($log->model_id);
                                        $page = $map[$log->model_type] ?? 'Investments';
                                        $d = $inv?->date ? optional($inv->date)->format('d-M - Y') : ($before['date'] ?? $after['date'] ?? '');
                                        $title = $inv->title ?? ($before['title'] ?? $after['title'] ?? '');
                                        $amt = number_format((float)($inv->amount ?? ($after['amount'] ?? $before['amount'] ?? 0)),2);
                                        $entry = trim(($d? $d.' · ' : '').($title? $title.' · ' : '').$amt);
                                    } else {
                                        $page = $map[$log->model_type] ?? class_basename($log->model_type);
                                    }
                                    // Compute diffs for changed fields only
                                    $keys = array_unique(array_merge(array_keys((array)$before), array_keys((array)$after)));
                                    $diffs = [];
                                    foreach ($keys as $k) {
                                        $bv = $before[$k] ?? null;
                                        $av = $after[$k] ?? null;
                                        if (json_encode($bv) !== json_encode($av)) {
                                            $label = ucwords(str_replace(['_','-'], ' ', $k));
                                            $diffs[] = [ 'label' => $label, 'before' => $bv, 'after' => $av ];
                                        }
                                    }
                                @endphp
                                <tr class="border-t align-top">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ optional($log->created_at)->format('d-M - Y H:i:s') }}</td>
                                    <td class="px-4 py-2">{{ $log->user->name ?? '-' }} ({{ $log->user_id ?? '-' }})</td>
                                    <td class="px-4 py-2">{{ $page }}</td>
                                    <td class="px-4 py-2">{{ $entry }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($log->action) }}</td>
                                    <td class="px-4 py-2">
                                        @if(empty($diffs))
                                            <span class="text-gray-500">No field changes detected</span>
                                        @else
                                            <ul class="list-disc ms-5 space-y-1">
                                                @foreach($diffs as $d)
                                                    <li>
                                                        <span class="font-medium">{{ $d['label'] }}:</span>
                                                        <span class="text-gray-500 line-through">{{ is_array($d['before']) ? json_encode($d['before']) : (is_null($d['before']) ? '-' : $d['before']) }}</span>
                                                        <span class="mx-1">→</span>
                                                        <span class="text-emerald-700 font-semibold">{{ is_array($d['after']) ? json_encode($d['after']) : (is_null($d['after']) ? '-' : $d['after']) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-10 text-center text-gray-500" colspan="5">No logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
