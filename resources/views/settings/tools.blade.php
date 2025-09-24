<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings · Tools</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if (session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 border border-green-200 px-4 py-2 rounded">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('settings.tools.update') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <h3 class="font-semibold">Accountant Edit Permissions</h3>
                        <p class="text-sm text-gray-500">Use these switches to temporarily allow edit actions for the Accountant role.</p>
                        <div class="divide-y divide-gray-100 border rounded">
                            <label class="flex items-center justify-between p-4">
                                <span>Deposits: allow edit/update</span>
                                <input type="checkbox" name="allow_accountant_edit_deposits" value="1" @checked(optional($settings)->allow_accountant_edit_deposits) class="h-5 w-5">
                            </label>
                            <label class="flex items-center justify-between p-4">
                                <span>Expenses: allow edit/update</span>
                                <input type="checkbox" name="allow_accountant_edit_expenses" value="1" @checked(optional($settings)->allow_accountant_edit_expenses) class="h-5 w-5">
                            </label>
                            <label class="flex items-center justify-between p-4">
                                <span>Other Income: allow edit/update</span>
                                <input type="checkbox" name="allow_accountant_edit_other_income" value="1" @checked(optional($settings)->allow_accountant_edit_other_income) class="h-5 w-5">
                            </label>
                            <label class="flex items-center justify-between p-4">
                                <span>Investments · Interest history: allow edit/update</span>
                                <input type="checkbox" name="allow_accountant_edit_investment_interest" value="1" @checked(optional($settings)->allow_accountant_edit_investment_interest) class="h-5 w-5">
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
