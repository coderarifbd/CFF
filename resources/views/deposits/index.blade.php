<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deposits</h2>
    </x-slot>

    <div class="py-6" x-data="{ modalOpen: {{ $errors->any() ? 'true' : 'false' }} }" x-effect="if(modalOpen){ window.dispatchEvent(new CustomEvent('modal-opened')); }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
                    <form method="GET" action="{{ route('deposits.index') }}" class="flex items-center gap-2 flex-wrap">
                        <select name="member_id" class="border rounded px-3 py-2">
                            <option value="">All Members</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" @selected(request('member_id')==$m->id)>{{ $m->name }}</option>
                            @endforeach
                        </select>
                        <select name="type" class="border rounded px-3 py-2">
                            <option value="">All Types</option>
                            <option value="subscription" @selected(request('type')==='subscription')>Subscription</option>
                            <option value="extra" @selected(request('type')==='extra')>Extra</option>
                            <option value="fine" @selected(request('type')==='fine')>Fine</option>
                        </select>
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search member" class="border rounded px-3 py-2">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
                    </form>

                    @hasanyrole('Admin|Accountant')
                    <div class="flex items-center gap-2">
                        <a href="{{ route('deposits.bulk-create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded">+ Bulk Deposit</a>
                        <button type="button" @click="modalOpen = true; $nextTick(()=>window.dispatchEvent(new CustomEvent('modal-opened')));" class="bg-green-600 text-white px-4 py-2 rounded">+ Add Deposit</button>
                    </div>
                    @endhasanyrole
                </div>

                <div class="mb-4 text-xs text-gray-600">
                    Logged in as: <strong>{{ auth()->user()->email ?? 'guest' }}</strong>
                    @if(auth()->check())
                        | Roles: {{ implode(', ', auth()->user()->getRoleNames()->toArray()) ?: 'none' }}
                    @endif
                </div>

                @if (session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 border border-green-200 px-4 py-2 rounded">{{ session('status') }}</div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="px-4 py-2 w-12">SL</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Member</th>
                                <th class="px-4 py-2">Breakdown</th>
                                <th class="px-4 py-2">Total</th>
                                <th class="px-4 py-2">Method</th>
                                <th class="px-4 py-2">Added By</th>
                                <th class="px-4 py-2">Note</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($receipts as $r)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ ($receipts->firstItem() ?? 1) + $loop->index }}</td>
                                    <td class="px-4 py-2">{{ $r->date->format('d-M - Y') }}</td>
                                    <td class="px-4 py-2">{{ $r->member->name ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-wrap gap-2">
                                            @php
                                                $map = [
                                                    'subscription' => ['Monthly', 'bg-blue-100 text-blue-800'],
                                                    'extra' => ['Extra', 'bg-gray-100 text-gray-800'],
                                                    'fine' => ['Fine', 'bg-red-100 text-red-800'],
                                                ];
                                            @endphp
                                            @foreach($r->items as $it)
                                                @php
                                                    [$label, $style] = $map[$it->type];
                                                @endphp
                                                <span class="px-2 py-1 rounded text-xs {{ $style }}">{{ $label }}: {{ number_format($it->amount,2) }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 font-semibold">{{ number_format($r->total_amount,2) }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $r->payment_method }}</td>
                                    <td class="px-4 py-2">{{ $r->addedBy->name ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $r->note }}</td>
                                    <td class="px-4 py-2">
                                        @if(auth()->check() && auth()->user()->hasRole('Admin'))
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('deposits.edit', $r->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-indigo-500/20 bg-indigo-50 hover:bg-indigo-100 text-indigo-700">✏️ Edit</a>
                                            <form action="{{ route('deposits.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Delete this deposit?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-rose-500/20 bg-rose-50 hover:bg-rose-100 text-rose-700">🗑️ Delete</button>
                                            </form>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">No deposits found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t">
                                <td class="px-4 py-2 font-semibold" colspan="4">Totals</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format($totalAmount,2) }}</td>
                                <td class="px-4 py-2 font-semibold">&nbsp;</td>
                                <td class="px-4 py-2 font-semibold">Fines: {{ number_format($fineTotal,2) }}</td>
                                <td class="px-4 py-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4">{{ $receipts->links() }}</div>
            </div>
        </div>

        <!-- Add Deposit Modal -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="modalOpen=false"></div>
            <div class="relative bg-white w-full max-w-2xl mx-auto rounded shadow-lg" x-data='{
                    types: {{ json_encode(old("types", [])) }},
                }'>
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <h3 class="font-semibold text-lg">Add Deposit</h3>
                    <button class="text-gray-600" @click="modalOpen=false">✕</button>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('deposits.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                                @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Member</label>
                                <select name="member_id" id="modal_member_id" class="mt-1 w-full border rounded px-3 py-2" required>
                                    <option value="">Select member</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}" @selected(old('member_id')==$m->id)>{{ $m->name }}</option>
                                    @endforeach
                                </select>
                                @error('member_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div id="modal_deposit_history" class="hidden mt-3">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-semibold text-gray-700">Last 4 Months (based on selected date)</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <div id="modal_hist_track" class="flex gap-2 min-w-max"></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Green = Paid subscription. Red = Due (no subscription recorded).</p>
                        </div>

                        

                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" class="rounded border-gray-300" value="subscription" x-model="types" name="types[]">
                                    <span>Monthly Subscription</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" class="rounded border-gray-300" value="extra" x-model="types" name="types[]">
                                    <span>Extra Deposit</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" class="rounded border-gray-300" value="fine" x-model="types" name="types[]">
                                    <span>Fine</span>
                                </label>
                            </div>
                            @error('types')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div x-show="types.includes('subscription')" x-cloak>
                                <label class="block text-sm font-medium text-gray-700">Subscription Amount</label>
                                <input type="number" step="0.01" name="amount_subscription" value="{{ old('amount_subscription', $subscriptionAmount) }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('subscription')">
                            </div>
                            <div x-show="types.includes('extra')" x-cloak>
                                <label class="block text-sm font-medium text-gray-700">Extra Amount</label>
                                <input type="number" step="0.01" name="amount_extra" value="{{ old('amount_extra') }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('extra')">
                            </div>
                            <div x-show="types.includes('fine')" x-cloak>
                                <label class="block text-sm font-medium text-gray-700">Fine Amount</label>
                                <input type="number" step="0.01" name="amount_fine" value="{{ old('amount_fine', $fineAmount) }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('fine')">
                            </div>
                        </div>
                        @error('amount')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select name="payment_method" class="mt-1 w-full border rounded px-3 py-2" required>
                                    <option value="cash" @selected(old('payment_method')==='cash')>Cash</option>
                                    <option value="bank" @selected(old('payment_method')==='bank')>Bank</option>
                                    <option value="mobile" @selected(old('payment_method')==='mobile')>Mobile Banking</option>
                                </select>
                                @error('payment_method')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Note (optional)</label>
                                <input type="text" name="note" value="{{ old('note') }}" class="mt-1 w-full border rounded px-3 py-2">
                                @error('note')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="modalOpen=false" class="px-4 py-2 border rounded">Cancel</button>
                            <button class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        const sel = document.getElementById('modal_member_id');
        if(!sel) return;
        const dateInput = document.querySelector('input[name="date"]');
        const hist = { box: document.getElementById('modal_deposit_history'), track: document.getElementById('modal_hist_track') };
        const fmt = (n)=> new Intl.NumberFormat(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}).format(n||0);
        const mon = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        async function loadHistory(){
            const memberId = sel.value; if(!memberId){ hist.box.classList.add('hidden'); return; }
            // Determine end month/year from selected date (use month before selected date)
            let endMonth, endYear;
            if (dateInput && dateInput.value) {
                const d = new Date(dateInput.value);
                if (!isNaN(d)) {
                    let m = d.getMonth() + 1; // 1..12
                    let y = d.getFullYear();
                    m = m - 1; if (m < 1) { m = 12; y = y - 1; }
                    endMonth = m; endYear = y;
                }
            }
            try{
                const params = new URLSearchParams({ member_id: memberId, months: '4' });
                if (endMonth && endYear) { params.set('end_month', String(endMonth)); params.set('end_year', String(endYear)); }
                const res = await fetch(`{{ route('deposits.history') }}?${params.toString()}`, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
                if(!res.ok) throw new Error('History fetch failed: '+res.status);
                const data = await res.json();
                hist.track.innerHTML='';
                (data.items||[]).forEach(item=>{
                    const card=document.createElement('div');
                    card.className = `rounded-lg border px-3 py-2 text-xs w-28 ${item.due ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'}`;
                    const label = (item.month && item.year) ? `${mon[(item.month-1+12)%12]} ${item.year}` : (item.label||'');
                    card.innerHTML = `<div class=\"font-semibold\">${label}</div>
                                      <div>Sub: <span class=\"tabular-nums\">${fmt(item.subscription)}</span></div>`;
                    hist.track.appendChild(card);
                });
                hist.box.classList.remove('hidden');
            }catch(e){ console.error(e); hist.box.classList.add('hidden'); }
        }
        if(sel && sel.value){ loadHistory(); }
        sel.addEventListener('change', ()=>{ loadHistory(); });
        if (dateInput) { dateInput.addEventListener('change', ()=>{ loadHistory(); }); }
        // If using Alpine modalOpen flag, refresh when the modal opens
        document.addEventListener('alpine:init', () => {
            document.addEventListener('modal-opened', () => { if(sel.value){ loadHistory(); } });
        });
    })();
</script>
@endpush
</x-app-layout>
