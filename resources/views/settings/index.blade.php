<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 relative">
                @if (session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 border border-green-200 px-4 py-2 rounded">{{ session('status') }}</div>
                @endif

                <form id="settingsForm" method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                    @csrf
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Monthly Subscription Amount</label>
                        <input type="number" step="0.01" name="monthly_subscription_amount" value="{{ old('monthly_subscription_amount', optional($settings)->monthly_subscription_amount) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                        @error('monthly_subscription_amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Fine Amount</label>
                        <input type="number" step="0.01" name="fine_amount" value="{{ old('fine_amount', optional($settings)->fine_amount) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                        @error('fine_amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Top Save button for quick access -->
                    <div class="flex justify-end gap-3">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
