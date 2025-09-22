<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Expenses</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
                    <form method="GET" action="{{ route('expenses.index') }}" class="flex items-center gap-2 flex-wrap">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search note/category" class="border rounded px-3 py-2">
                        <select name="category" class="border rounded px-3 py-2">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
                            @endforeach
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
                        <button class="btn">Filter</button>
                        @if(request()->hasAny(['search','category','month','year']))
                            <a href="{{ route('expenses.index') }}" class="px-3 py-2 text-sm text-gray-600">Clear</a>
                        @endif
                    </form>
                    @hasanyrole('Admin|Accountant')
                    <a href="{{ route('expenses.create') }}" class="btn btn-sm bg-green-600 text-white px-4 py-2 rounded">+ Add Expense</a>
                    @endhasanyrole
                </div>

                @if (session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 border border-green-200 px-4 py-2 rounded">{{ session('status') }}</div>
                @endif

                <div id="expenses-table">
                    @include('expenses.partials.table')
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const form = document.querySelector('form[action="{{ route('expenses.index') }}"]');
            const tableWrap = document.getElementById('expenses-table');
            if(!form || !tableWrap) return;
            const debounce = (fn, d=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); } };
            const fetchResults = () => {
                const params = new URLSearchParams(new FormData(form));
                params.set('ajax','1');
                fetch(`{{ route('expenses.index') }}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
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
</x-app-layout>
