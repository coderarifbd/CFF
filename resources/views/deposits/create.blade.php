<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Deposit</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('deposits.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                            @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member</label>
                            <select name="member_id" id="member_id" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="">Select member</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" @selected(old('member_id')==$m->id)>{{ $m->name }}</option>
                                @endforeach
                            </select>
                            @error('member_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div id="deposit-history" class="hidden mt-3">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-semibold text-gray-700">Last 12 Months</h4>
                            <div class="flex items-center gap-2">
                                <button type="button" id="hist-prev" class="px-2 py-1 rounded border text-xs">‹</button>
                                <button type="button" id="hist-next" class="px-2 py-1 rounded border text-xs">›</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <div id="hist-track" class="flex gap-2 min-w-max">
                                <!-- items injected here -->
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Green = Paid subscription. Red = Due (no subscription recorded).</p>
                    </div>

                    <div id="last-month-summary" class="hidden mt-2">
                        <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                            <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-gray-700">Payment Summary</h3>
                                    <span id="lms-period" class="text-xs text-gray-500"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="lms-prev" class="px-2 py-1 rounded border text-xs">Prev</button>
                                    <select id="lms-month" class="border rounded px-2 py-1 text-sm">
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
                                    <select id="lms-year" class="border rounded px-2 py-1 text-sm"></select>
                                    <button type="button" id="lms-next" class="px-2 py-1 rounded border text-xs">Next</button>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3 text-sm">
                                <div>
                                    <p class="text-gray-500">Subscription</p>
                                    <p id="lms-sub" class="font-semibold tabular-nums">—</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Extra</p>
                                    <p id="lms-extra" class="font-semibold tabular-nums">—</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Fine</p>
                                    <p id="lms-fine" class="font-semibold tabular-nums">—</p>
                                </div>
                            </div>
                            <div class="mt-3 text-right text-sm">
                                <span class="text-gray-600">Total: </span>
                                <span id="lms-total" class="font-semibold tabular-nums">—</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="subscription" @selected(old('type')==='subscription')>Monthly Subscription</option>
                                <option value="extra" @selected(old('type')==='extra')>Extra Deposit</option>
                                <option value="fine" @selected(old('type')==='fine')>Fine</option>
                            </select>
                            @error('type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="mt-1 w-full border rounded px-3 py-2" required>
                            @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

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

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('deposits.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const sel = document.getElementById('member_id');
            const box = document.getElementById('last-month-summary');
            const el = {
                period: document.getElementById('lms-period'),
                sub: document.getElementById('lms-sub'),
                extra: document.getElementById('lms-extra'),
                fine: document.getElementById('lms-fine'),
                total: document.getElementById('lms-total'),
                month: document.getElementById('lms-month'),
                year: document.getElementById('lms-year'),
                prev: document.getElementById('lms-prev'),
                next: document.getElementById('lms-next'),
            };
            const fmt = (n)=> new Intl.NumberFormat(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}).format(n||0);
            const hist = { box: document.getElementById('deposit-history'), track: document.getElementById('hist-track'), prev: document.getElementById('hist-prev'), next: document.getElementById('hist-next') };
            function setDefaultPeriod(){
                const now = new Date();
                // previous month
                const d = new Date(now.getFullYear(), now.getMonth()-1, 1);
                el.month.value = String(d.getMonth()+1);
                // populate years around current
                if(el.year.options.length === 0){
                    const start = now.getFullYear()-5, end = now.getFullYear()+1;
                    for(let y=start; y<=end; y++){
                        const opt = document.createElement('option');
                        opt.value = String(y); opt.textContent = String(y);
                        el.year.appendChild(opt);
                    }
                }
                el.year.value = String(d.getFullYear());
            }
            async function loadSummary(){
                const memberId = sel.value;
                if(!memberId){ box.classList.add('hidden'); return; }
                const month = el.month ? el.month.value : '';
                const year = el.year ? el.year.value : '';
                try {
                    const q = new URLSearchParams({ member_id: memberId });
                    if(month) q.set('month', month);
                    if(year) q.set('year', year);
                    const res = await fetch(`{{ route('deposits.last-month') }}?${q.toString()}`, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
                    if(!res.ok) throw new Error('Failed');
                    const d = await res.json();
                    el.period.textContent = `${d.year}-${String(d.month).padStart(2,'0')}`;
                    el.sub.textContent = fmt(d.subscription);
                    el.extra.textContent = fmt(d.extra);
                    el.fine.textContent = fmt(d.fine);
                    el.total.textContent = fmt(d.total);
                    box.classList.remove('hidden');
                } catch(e){ box.classList.add('hidden'); }
            }
            async function loadHistory(){
                const memberId = sel.value; if(!memberId){ hist.box.classList.add('hidden'); return; }
                try{
                    const res = await fetch(`{{ route('deposits.history') }}?member_id=${memberId}&months=12`, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
                    if(!res.ok) throw new Error('Failed');
                    const data = await res.json();
                    hist.track.innerHTML = '';
                    (data.items||[]).forEach(item => {
                        const card = document.createElement('div');
                        card.className = `rounded-lg border px-3 py-2 text-xs w-28 ${item.due ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'}`;
                        card.innerHTML = `<div class="font-semibold">${item.label}</div>
                                          <div>Sub: <span class="tabular-nums">${fmt(item.subscription)}</span></div>
                                          <div>Ext: <span class="tabular-nums">${fmt(item.extra)}</span></div>
                                          <div>Fine: <span class="tabular-nums">${fmt(item.fine)}</span></div>`;
                        hist.track.appendChild(card);
                    });
                    hist.box.classList.remove('hidden');
                }catch(e){ hist.box.classList.add('hidden'); }
            }
            sel && sel.addEventListener('change', loadSummary);
            if(el.month && el.year){ setDefaultPeriod(); }
            if(sel && sel.value){ loadSummary(); loadHistory(); }
            if(el.month){ el.month.addEventListener('change', loadSummary); }
            if(el.year){ el.year.addEventListener('change', loadSummary); }
            if(el.prev){ el.prev.addEventListener('click', ()=>{ 
                const m = parseInt(el.month.value,10); let y = parseInt(el.year.value,10);
                let nm = m-1; if(nm<1){ nm=12; y=y-1; }
                el.month.value = String(nm); el.year.value = String(y); loadSummary();
            }); }
            if(el.next){ el.next.addEventListener('click', ()=>{ 
                const m = parseInt(el.month.value,10); let y = parseInt(el.year.value,10);
                let nm = m+1; if(nm>12){ nm=1; y=y+1; }
                el.month.value = String(nm); el.year.value = String(y); loadSummary();
            }); }
            hist.prev && hist.prev.addEventListener('click', ()=>{ document.getElementById('deposit-history').querySelector('.overflow-x-auto').scrollBy({ left: -200, behavior: 'smooth' }); });
            hist.next && hist.next.addEventListener('click', ()=>{ document.getElementById('deposit-history').querySelector('.overflow-x-auto').scrollBy({ left: 200, behavior: 'smooth' }); });
            sel && sel.addEventListener('change', loadHistory);
        })();
    </script>
</x-app-layout>
