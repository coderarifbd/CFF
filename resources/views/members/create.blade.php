<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Member</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                        @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NID</label>
                            <input type="text" name="nid" value="{{ old('nid') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('nid')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email (login)</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member Type</label>
                            <select name="member_type" class="mt-1 block w-full border rounded px-3 py-2" required>
                                @foreach(['admin'=>'Admin','accountant'=>'Accountant','member'=>'Member'] as $k=>$v)
                                    <option value="{{ $k }}" @selected(old('member_type','member')===$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            @error('member_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="mt-1 block w-full border rounded px-3 py-2" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="3" class="mt-1 block w-full border rounded px-3 py-2">{{ old('address') }}</textarea>
                        @error('address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Join Date</label>
                            <input type="date" name="join_date" value="{{ old('join_date', now()->toDateString()) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                            @error('join_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full border rounded px-3 py-2" required>
                                @foreach(['active'=>'Active','inactive'=>'Inactive','suspended'=>'Suspended'] as $k=>$v)
                                    <option value="{{ $k }}" @selected(old('status','active')===$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            @error('status')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
                        <input type="file" name="profile_picture" accept="image/*" class="mt-1 block w-full" onchange="previewImage(event)">
                        @error('profile_picture')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        <div class="mt-3">
                            <img id="imgPreview" class="h-24 w-24 rounded object-cover hidden" alt="Preview" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('members.index') }}" class="px-4 py-2 border rounded">Cancel</a>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(e){
            const [file] = e.target.files;
            const img = document.getElementById('imgPreview');
            if(file){ img.src = URL.createObjectURL(file); img.classList.remove('hidden'); }
        }
    </script>
</x-app-layout>
