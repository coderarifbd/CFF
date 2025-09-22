<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-900">Members</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6">
                @if (session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 border border-green-200 px-4 py-2 rounded">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 text-red-700 bg-red-100 border border-red-200 px-4 py-2 rounded">{{ $errors->first() }}</div>
                @endif

                <!-- Toolbar -->
                <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                <form method="GET" action="{{ route('members.index') }}" class="flex items-center gap-3 flex-wrap">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone" class="border rounded-lg px-3 py-2 w-64" />
                    <select name="status" class="border rounded-lg px-3 py-2">
                        <option value="">All Status</option>
                        @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $key => $label)
                            <option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="type" class="border rounded-lg px-3 py-2">
                        <option value="">All Types</option>
                        @foreach (['admin'=>'Admin','accountant'=>'Accountant','member'=>'Member'] as $k=>$v)
                            <option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    <button class="btn">Filter</button>
                    @if(request()->hasAny(['search','status','type']))
                        <a href="{{ route('members.index') }}" class="px-3 py-2 text-sm text-gray-600">Clear</a>
                    @endif
                </form>
                @auth
                <a href="{{ route('members.create') }}" class="btn btn-sm bg-green-600 text-white px-4 py-2 rounded">
                    + New User
                </a>
                @endauth
                </div>

                

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="px-4 py-2">Member</th>
                                <th class="px-4 py-2">Contact</th>
                                <th class="px-4 py-2">Type</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Join Date</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($members as $member)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            <a href="{{ route('members.show', $member) }}" class="hover:underline">{{ $member->name }}</a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-700">{{ $member->phone }}</div>
                                        <div class="text-xs text-gray-500">{{ $member->user->email ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $typeMap = ['admin'=>['Admin','bg-indigo-100 text-indigo-800'], 'accountant'=>['Accountant','bg-amber-100 text-amber-800'], 'member'=>['Member','bg-gray-100 text-gray-800']];
                                            [$label,$cls] = $typeMap[$member->member_type ?? 'member'];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cls }}">{{ $label }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusCls = match($member->status){
                                                'active' => 'bg-emerald-100 text-emerald-800',
                                                'inactive' => 'bg-gray-100 text-gray-800',
                                                'suspended' => 'bg-rose-100 text-rose-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusCls }} capitalize">{{ $member->status }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ $member->join_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('members.show', $member) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-black/5 bg-white hover:bg-gray-50 text-gray-700">
                                                👁️ View
                                            </a>
                                            @hasanyrole('Admin|Super Admin')
                                                <a href="{{ route('members.edit', $member) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-indigo-500/20 bg-indigo-50 hover:bg-indigo-100 text-indigo-700">
                                                    ✏️ Edit
                                                </a>
                                                @if($member->status !== 'suspended')
                                                    <form action="{{ route('members.suspend', $member) }}" method="POST" onsubmit="return confirm('Suspend this member?')">
                                                        @csrf
                                                        <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-amber-500/20 bg-amber-50 hover:bg-amber-100 text-amber-700">⏸️ Suspend</button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('members.activate', $member) }}" method="POST" onsubmit="return confirm('Activate this member?')">
                                                        @csrf
                                                        <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-emerald-500/20 bg-emerald-50 hover:bg-emerald-100 text-emerald-700">▶️ Activate</button>
                                                    </form>
                                                @endif
                                                @if(!($member->user_id && auth()->id() === $member->user_id))
                                                    <form action="{{ route('members.destroy', $member) }}" method="POST" onsubmit="return confirm('Delete this member? This cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium ring-1 ring-rose-500/20 bg-rose-50 hover:bg-rose-100 text-rose-700">🗑️ Delete</button>
                                                    </form>
                                                @endif
                                            @endhasanyrole
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">No members found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $members->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
