<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Investments</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                {{-- Summary KPIs --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
                    <div class="p-4 rounded-xl ring-1 ring-black/5 bg-gradient-to-br from-slate-800 to-slate-900 text-white">
                        <div class="text-[11px] uppercase tracking-[.14em]">Total Investments</div>
                        <div class="text-2xl font-bold mt-1 tabular-nums">{{ number_format($summary['amountSum'] ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-300">Count: {{ number_format($summary['count'] ?? 0) }}</div>
                    </div>
                    <div class="p-4 rounded-xl ring-1 ring-black/5 bg-white">
                        <div class="text-[11px] uppercase tracking-[.14em] text-gray-500">Active</div>
                        <div class="text-xl font-semibold mt-1 tabular-nums">{{ number_format($summary['activeAmountSum'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Count: {{ number_format($summary['activeCount'] ?? 0) }}</div>
                    </div>
                    <div class="p-4 rounded-xl ring-1 ring-black/5 bg-white">
                        <div class="text-[11px] uppercase tracking-[.14em] text-gray-500">Returned</div>
                        <div class="text-xl font-semibold mt-1 tabular-nums">{{ number_format($summary['returnedAmountSum'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">Count: {{ number_format($summary['returnedCount'] ?? 0) }}</div>
                    </div>
                    <div class="p-4 rounded-xl ring-1 ring-black/5 bg-white">
                        <div class="text-[11px] uppercase tracking-[.14em] text-gray-500">Total Interest</div>
                        <div class="text-xl font-semibold mt-1 tabular-nums">{{ number_format($summary['interestSum'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500">All time: {{ number_format($totalInterest ?? 0, 2) }}</div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
                    <form method="GET" action="{{ route('investments.index') }}" class="flex items-center gap-2 flex-wrap">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-slate-300">
                        <select name="type" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-slate-300">
                            <option value="">All Types</option>
                            @foreach(['land'=>'Land','business'=>'Business','bank'=>'Bank','other'=>'Other'] as $k=>$v)
                                <option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-slate-300">
                            <option value="">All Status</option>
                            @foreach(['active'=>'Active','returned'=>'Returned'] as $k=>$v)
                                <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        <select name="month" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-slate-300">
                            <option value="">Month</option>
                            @for($m=1;$m<=12;$m++)
                                <option value="{{ $m }}" @selected(request('month')==$m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endfor
                        </select>
                        <select name="year" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-slate-300">
                            <option value="">Year</option>
                            @php($ys = isset($yearStart) ? (int)$yearStart : 2019)
                            @php($ye = isset($yearEnd) ? (int)$yearEnd : (date('Y')+1))
                            @for($y=$ys;$y<=$ye;$y++)
                                <option value="{{ $y }}" @selected(request('year')==$y)>{{ $y }}</option>
                            @endfor
                        </select>
                        <button class="btn">Filter</button>
                        @if(request()->hasAny(['search','type','status','month','year']))
                            <a href="{{ route('investments.index') }}" class="px-3 py-2 text-sm text-gray-600">Clear</a>
                        @endif
                    </form>
                    <!-- TEMP: Show button for any authenticated user to debug role visibility -->
                    @auth
                    <a href="{{ route('investments.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">+ Add Investment</a>
                    @endauth
                </div>

    <script>
        (function(){
            const form = document.querySelector('form[action="{{ route('investments.index') }}"]');
            const tableWrap = document.getElementById('investments-table');
            if(!form || !tableWrap) return;

            const debounce = (fn, d=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); } };

            const fetchResults = () => {
                const params = new URLSearchParams(new FormData(form));
                params.set('ajax','1');
                fetch(`{{ route('investments.index') }}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                    .then(r => r.text())
                    .then(html => { tableWrap.innerHTML = html; window.history.replaceState({}, '', `?${params.toString().replace(/&?ajax=1/,'')}`); })
                    .catch(() => {});
            };

            const debounced = debounce(fetchResults, 300);
            form.querySelectorAll('input[name="search"], select').forEach(el => {
                const evt = el.tagName === 'INPUT' ? 'input' : 'change';
                el.addEventListener(evt, debounced);
            });
            form.addEventListener('submit', e => { e.preventDefault(); fetchResults(); });
        })();
    </script>

                

                <div class="mb-4 text-xs text-gray-600">
                    Logged in as: <strong>{{ auth()->user()->email ?? 'guest' }}</strong>
                    @if(auth()->check())
                        | Roles: {{ implode(', ', auth()->user()->getRoleNames()->toArray()) ?: 'none' }}
                    @endif
                </div>

                @if (session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 border border-green-200 px-4 py-2 rounded">{{ session('status') }}</div>
                @endif

                <div id="investments-table">
                    @include('investments.partials.table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
