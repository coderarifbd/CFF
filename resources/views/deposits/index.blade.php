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

                    @auth
                    <button type="button" @click="modalOpen = true; $nextTick(()=>window.dispatchEvent(new CustomEvent('modal-opened')));" class="bg-green-600 text-white px-4 py-2 rounded">+ Add Deposit</button>
                    @endauth
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
                    <table class="min-w-full table-auto border-separate border-spacing-0">
                        <thead class="sticky top-0">
                            <tr class="bg-gray-50 text-left">
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
                        <tbody>
                            @forelse($receipts as $r)
                                <tr class="border-t odd:bg-white even:bg-gray-50 hover:bg-gray-100">
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
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('deposits.edit', $r->id) }}" class="text-indigo-700 hover:underline">Edit</a>
                                            <form action="{{ route('deposits.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Delete this deposit?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-700 hover:underline">Delete</button>
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
                                <h4 class="text-sm font-semibold text-gray-700">Last 12 Months</h4>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="modal_hist_prev" class="px-2 py-1 rounded border text-xs">‹</button>
                                    <button type="button" id="modal_hist_next" class="px-2 py-1 rounded border text-xs">›</button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <div id="modal_hist_track" class="flex gap-2 min-w-max"></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Green = Paid subscription. Red = Due (no subscription recorded).</p>
                        </div>

                        <div id="modal_last_month_summary" class="hidden mt-2">
                            <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                                <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-gray-700">Payment Summary</h3>
                                        <span id="modal_lms_period" class="text-xs text-gray-500"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="modal_lms_prev" class="px-2 py-1 rounded border text-xs">Prev</button>
                                        <select id="modal_lms_month" class="border rounded px-2 py-1 text-sm">
                                            <option value="1">Jan</option>
                                            <option value="2">Feb</option>
                                            <option value="3">Mar</option>
                                            <option value="4">Apr</option>
                                            <option value="5">May</option>
                                            <option value="6">Jun</option>
                                            <option value="7">Jul</option>
                                            <option value="8">Aug</option>
                                            <option value="9">Sep</option>
                                            <option value="10">Oct</option>
                                            <option value="11">Nov</option>
                                            <option value="12">Dec</option>
                                        </select>
                                        <select id="modal_lms_year" class="border rounded px-2 py-1 text-sm"></select>
                                        <button type="button" id="modal_lms_next" class="px-2 py-1 rounded border text-xs">Next</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-gray-500">Subscription</p>
                                        <p id="modal_lms_sub" class="font-semibold tabular-nums">—</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Extra</p>
                                        <p id="modal_lms_extra" class="font-semibold tabular-nums">—</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Fine</p>
                                        <p id="modal_lms_fine" class="font-semibold tabular-nums">—</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-right text-sm">
                                    <span class="text-gray-600">Total: </span>
                                    <span id="modal_lms_total" class="font-semibold tabular-nums">—</span>
                                </div>
                            </div>
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
        const box = document.getElementById('modal_last_month_summary');
        const hist = { box: document.getElementById('modal_deposit_history'), track: document.getElementById('modal_hist_track'), prev: document.getElementById('modal_hist_prev'), next: document.getElementById('modal_hist_next') };
        const el = {
            period: document.getElementById('modal_lms_period'),
            sub: document.getElementById('modal_lms_sub'),
            extra: document.getElementById('modal_lms_extra'),
            fine: document.getElementById('modal_lms_fine'),
            total: document.getElementById('modal_lms_total'),
            month: document.getElementById('modal_lms_month'),
            year: document.getElementById('modal_lms_year'),
            prev: document.getElementById('modal_lms_prev'),
            next: document.getElementById('modal_lms_next'),
        };
        const fmt = (n)=> new Intl.NumberFormat(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}).format(n||0);
        function setDefaultPeriod(){
            const now = new Date();
            const d = new Date(now.getFullYear(), now.getMonth()-1, 1);
            if(el.month) el.month.value = String(d.getMonth()+1);
            if(el.year && el.year.options.length===0){
                const start = now.getFullYear()-5, end = now.getFullYear()+1;
                for(let y=start;y<=end;y++){ const opt=document.createElement('option'); opt.value=String(y); opt.textContent=String(y); el.year.appendChild(opt);} }
            if(el.year) el.year.value = String(d.getFullYear());
        }
        async function loadSummary(){
            const memberId = sel.value; if(!memberId){ box.classList.add('hidden'); return; }
            const month = el.month ? el.month.value : ''; const year = el.year ? el.year.value : '';
            try{
                const q = new URLSearchParams({ member_id: memberId }); if(month) q.set('month', month); if(year) q.set('year', year);
                const res = await fetch(`{{ route('deposits.last-month') }}?${q.toString()}`, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
                if(!res.ok) throw new Error('Last-month fetch failed: '+res.status);
                const d = await res.json();
                if(el.period) el.period.textContent = `${d.year}-${String(d.month).padStart(2,'0')}`;
                if(el.sub) el.sub.textContent = fmt(d.subscription);
                if(el.extra) el.extra.textContent = fmt(d.extra);
                if(el.fine) el.fine.textContent = fmt(d.fine);
                if(el.total) el.total.textContent = fmt(d.total);
                box.classList.remove('hidden');
            }catch(e){ console.error(e); box.classList.add('hidden'); }
        }
        async function loadHistory(){
            const memberId = sel.value; if(!memberId){ hist.box.classList.add('hidden'); return; }
            try{
                const res = await fetch(`{{ route('deposits.history') }}?member_id=${memberId}&months=12`, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
                if(!res.ok) throw new Error('History fetch failed: '+res.status);
                const data = await res.json();
                hist.track.innerHTML='';
                (data.items||[]).forEach(item=>{
                    const card=document.createElement('div');
                    card.className = `rounded-lg border px-3 py-2 text-xs w-28 ${item.due ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'}`;
                    card.innerHTML = `<div class=\"font-semibold\">${item.label}</div>
                                      <div>Sub: <span class=\"tabular-nums\">${fmt(item.subscription)}</span></div>`;
                    hist.track.appendChild(card);
                });
                hist.box.classList.remove('hidden');
            }catch(e){ console.error(e); hist.box.classList.add('hidden'); }
        }
        function bindNav(){
            const sc = document.getElementById('modal_deposit_history').querySelector('.overflow-x-auto');
            hist.prev && hist.prev.addEventListener('click', ()=> sc.scrollBy({ left: -200, behavior: 'smooth' }));
            hist.next && hist.next.addEventListener('click', ()=> sc.scrollBy({ left: +200, behavior: 'smooth' }));
            el.month && el.month.addEventListener('change', loadSummary);
            el.year && el.year.addEventListener('change', loadSummary);
            el.prev && el.prev.addEventListener('click', ()=>{
                const m=parseInt(el.month.value,10); let y=parseInt(el.year.value,10); let nm=m-1; if(nm<1){ nm=12; y=y-1; }
                el.month.value=String(nm); el.year.value=String(y); loadSummary();
            });
            el.next && el.next.addEventListener('click', ()=>{
                const m=parseInt(el.month.value,10); let y=parseInt(el.year.value,10); let nm=m+1; if(nm>12){ nm=1; y=y+1; }
                el.month.value=String(nm); el.year.value=String(y); loadSummary();
            });
        }
        setDefaultPeriod();
        if(sel && sel.value){ loadSummary(); loadHistory(); }
        sel.addEventListener('change', ()=>{ loadSummary(); loadHistory(); });
        bindNav();
        // If using Alpine modalOpen flag, refresh when the modal opens
        document.addEventListener('alpine:init', () => {
            document.addEventListener('modal-opened', () => { if(sel.value){ loadSummary(); loadHistory(); } });
        });
    })();
</script>
@endpush
</x-app-layout>
