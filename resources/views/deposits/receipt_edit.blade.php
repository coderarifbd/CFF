<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Deposit</h2>
    </x-slot>

    <div class="py-6" x-data='{
        types: @json($receipt->items->pluck("type")),
    }'>
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 text-red-700 bg-red-100 border border-red-200 px-4 py-2 rounded">
                        <ul class="list-disc ms-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('deposits.update', $receipt->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', $receipt->date->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member</label>
                            <select name="member_id" class="mt-1 w-full border rounded px-3 py-2" required>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" @selected(old('member_id', $receipt->member_id)==$m->id)>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="cash" @selected(old('payment_method',$receipt->payment_method)==='cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method',$receipt->payment_method)==='bank')>Bank</option>
                                <option value="mobile" @selected(old('payment_method',$receipt->payment_method)==='mobile')>Mobile Banking</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Note (optional)</label>
                            <input type="text" name="note" value="{{ old('note', $receipt->note) }}" class="mt-1 w-full border rounded px-3 py-2">
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

                    @php
                        $amounts = [
                            'subscription' => old('amount_subscription', optional($receipt->items->firstWhere('type','subscription'))->amount ?? $subscriptionAmount),
                            'extra' => old('amount_extra', optional($receipt->items->firstWhere('type','extra'))->amount ?? ''),
                            'fine' => old('amount_fine', optional($receipt->items->firstWhere('type','fine'))->amount ?? $fineAmount),
                        ];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div x-show="types.includes('subscription')" x-cloak>
                            <label class="block text-sm font-medium text-gray-700">Subscription Amount</label>
                            <input type="number" step="0.01" name="amount_subscription" value="{{ $amounts['subscription'] }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('subscription')">
                        </div>
                        <div x-show="types.includes('extra')" x-cloak>
                            <label class="block text-sm font-medium text-gray-700">Extra Amount</label>
                            <input type="number" step="0.01" name="amount_extra" value="{{ $amounts['extra'] }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('extra')">
                        </div>
                        <div x-show="types.includes('fine')" x-cloak>
                            <label class="block text-sm font-medium text-gray-700">Fine Amount</label>
                            <input type="number" step="0.01" name="amount_fine" value="{{ $amounts['fine'] }}" class="mt-1 w-full border rounded px-3 py-2" :required="types.includes('fine')">
                        </div>
                    </div>

                    @error('amount')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror

                    <div id="receipt_edit_history" class="mt-3">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-semibold text-gray-700">Last 4 Months (based on selected date)</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <div id="receipt_edit_hist_track" class="flex gap-2 min-w-max"></div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Green = Paid subscription. Red = Due (no subscription recorded).</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('deposits.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
function __receiptEditInit(){
    const memberSel = document.querySelector('select[name="member_id"]');
    const dateInput = document.querySelector('input[name="date"]');
    const box = document.getElementById('receipt_edit_history');
    const track = document.getElementById('receipt_edit_hist_track');
    if(!memberSel || !dateInput || !box || !track) return;
    // Initial placeholder
    track.innerHTML = '<div class="text-xs text-gray-500 px-3 py-2">Waiting for member/date…</div>';
    const fmt = (n)=> new Intl.NumberFormat(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}).format(n||0);
    const mon = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    async function loadHistory(){
        const memberId = memberSel.value; if(!memberId){ box.classList.add('hidden'); return; }
        // Show panel immediately with loading state
        box.classList.remove('hidden');
        track.innerHTML = '<div class="text-xs text-gray-500 px-3 py-2">Loading history…</div>';
        let endMonth, endYear;
        if (dateInput && dateInput.value) {
            const d = new Date(dateInput.value);
            if (!isNaN(d)) {
                let m = d.getMonth()+1, y = d.getFullYear();
                m = m - 1; if (m < 1) { m = 12; y = y - 1; }
                endMonth = m; endYear = y;
            }
        }
        try{
            const params = new URLSearchParams({ member_id: memberId, months: '4' });
            if (endMonth && endYear) { params.set('end_month', String(endMonth)); params.set('end_year', String(endYear)); }
            let url = `{{ route('deposits.history') }}?${params.toString()}`;
            console.log('History URL:', url);
            let res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
            if(!res.ok){
                // Retry without end anchor
                const fallback = new URLSearchParams({ member_id: memberId, months: '4' });
                url = `{{ route('deposits.history') }}?${fallback.toString()}`;
                console.log('History Fallback URL:', url);
                res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
            }
            if(!res.ok) throw new Error('History fetch failed: '+res.status);
            const data = await res.json();
            track.innerHTML='';
            (data.items||[]).forEach(item=>{
                const card=document.createElement('div');
                card.className = `rounded-lg border px-3 py-2 text-xs w-28 ${item.due ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'}`;
                const label = (item.month && item.year) ? `${mon[(item.month-1+12)%12]} ${item.year}` : (item.label||'');
                card.innerHTML = `<div class=\"font-semibold\">${label}</div>
                                  <div>Sub: <span class=\"tabular-nums\">${fmt(item.subscription)}</span></div>`;
                track.appendChild(card);
            });
            if (!track.children.length) {
                track.innerHTML = '<div class="text-xs text-gray-500 px-3 py-2">No data for the selected range.</div>';
            }
        }catch(e){
            console.error(e);
            track.innerHTML = '<div class="text-xs text-rose-700 bg-rose-50 border border-rose-100 rounded px-3 py-2">Failed to load history.</div>';
        }
    }
    memberSel.addEventListener('change', loadHistory);
    dateInput.addEventListener('change', loadHistory);
    // Initial
    loadHistory();
    // If Alpine is present and uses modal events (consistency with other screens)
    document.addEventListener('modal-opened', ()=>{ loadHistory(); });
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', __receiptEditInit);
} else {
  __receiptEditInit();
}
</script>
