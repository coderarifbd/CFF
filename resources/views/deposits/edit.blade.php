<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Deposit</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('deposits.update', $deposit) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', $deposit->date->toDateString()) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                            @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member</label>
                            <select name="member_id" class="mt-1 w-full border rounded px-3 py-2" required>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" @selected(old('member_id', $deposit->member_id)==$m->id)>{{ $m->name }}</option>
                                @endforeach
                            </select>
                            @error('member_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="subscription" @selected(old('type', $deposit->type)==='subscription')>Monthly Subscription</option>
                                <option value="extra" @selected(old('type', $deposit->type)==='extra')>Extra Deposit</option>
                                <option value="fine" @selected(old('type', $deposit->type)==='fine')>Fine</option>
                            </select>
                            @error('type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount', $deposit->amount) }}" class="mt-1 w-full border rounded px-3 py-2" required>
                            @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" class="mt-1 w-full border rounded px-3 py-2" required>
                                <option value="cash" @selected(old('payment_method', $deposit->payment_method)==='cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method', $deposit->payment_method)==='bank')>Bank</option>
                                <option value="mobile" @selected(old('payment_method', $deposit->payment_method)==='mobile')>Mobile Banking</option>
                            </select>
                            @error('payment_method')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Note (optional)</label>
                            <input type="text" name="note" value="{{ old('note', $deposit->note) }}" class="mt-1 w-full border rounded px-3 py-2">
                            @error('note')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div id="edit_deposit_history" class="mt-3 hidden">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-semibold text-gray-700">Last 4 Months (based on selected date)</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <div id="edit_hist_track" class="flex gap-2 min-w-max"></div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Green = Paid subscription. Red = Due (no subscription recorded).</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('deposits.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@push('scripts')
<script>
(function(){
    const memberSel = document.querySelector('select[name="member_id"]');
    const dateInput = document.querySelector('input[name="date"]');
    const box = document.getElementById('edit_deposit_history');
    const track = document.getElementById('edit_hist_track');
    if(!memberSel || !dateInput || !box || !track) return;
    const fmt = (n)=> new Intl.NumberFormat(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}).format(n||0);
    async function loadHistory(){
        const memberId = memberSel.value; if(!memberId){ box.classList.add('hidden'); return; }
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
            const res = await fetch(`{{ route('deposits.history') }}?${params.toString()}`, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
            if(!res.ok) throw new Error('History fetch failed: '+res.status);
            const data = await res.json();
            track.innerHTML='';
            (data.items||[]).forEach(item=>{
                const card=document.createElement('div');
                card.className = `rounded-lg border px-3 py-2 text-xs w-28 ${item.due ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'}`;
                card.innerHTML = `<div class=\"font-semibold\">${item.label}</div>
                                  <div>Sub: <span class=\"tabular-nums\">${fmt(item.subscription)}</span></div>`;
                track.appendChild(card);
            });
            box.classList.remove('hidden');
        }catch(e){ console.error(e); box.classList.add('hidden'); }
    }
    memberSel.addEventListener('change', loadHistory);
    dateInput.addEventListener('change', loadHistory);
    // Initial load
    loadHistory();
})();
</script>
@endpush
</x-app-layout>
