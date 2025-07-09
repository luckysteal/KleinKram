            <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mexicalculator') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md w-full mx-auto bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8" x-data="{ incomePeriod: 'monthly', showTaxOptions: false }">
            <h1 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-8">{{ __('Mexicalculator') }}</h1>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('mexi-calculator.calculate') }}" method="POST" class="space-y-8">
                @csrf

                <div class="mb-6">
                    <label for="money" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" x-text="moneyLabel"></label>
                    <input type="number" step="0.01" name="money" id="money" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200 transition duration-150 ease-in-out" required>
                </div>

                <div class="mb-6">
                    <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Money Period') }}</label>
                    <select name="period" id="period" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200 transition duration-150 ease-in-out" x-model="incomePeriod">
                        <option value="yearly">{{ __('Yearly') }}</option>
                        <option value="monthly">{{ __('Monthly') }}</option>
                        <option value="daily">{{ __('Daily') }}</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="drink_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Choose a Drink') }}</label>
                    <select name="drink_id" id="drink_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200 transition duration-150 ease-in-out" required>
                        @foreach($bars as $bar)
                            <optgroup label="{{ $bar->name }}">
                                @foreach($bar->drinks as $drink)
                                    <option value="{{ $drink->id }}">{{ $drink->name }} (€{{ number_format($drink->price, 2) }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6 flex items-center">
                    <input type="checkbox" name="apply_tax" id="apply_tax" x-model="showTaxOptions" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out">
                    <label for="apply_tax" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Enable Tax Calculation') }}</label>
                </div>

                <div x-show="showTaxOptions" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="mb-6">
                        <label for="allowance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Tax Allowance (€)') }}</label>
                        <input type="number" step="0.01" name="allowance" id="allowance" value="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200 transition duration-150 ease-in-out">
                    </div>

                    <div class="mb-6 flex items-center">
                        <input type="checkbox" name="apply_church_tax" id="apply_church_tax" value="1" {{ $churchTaxEnabled ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out">
                        <label for="apply_church_tax" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Apply Church Tax') }}</label>
                    </div>

                    <div class="mb-6">
                        <label for="tax_class" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Tax Class') }}</label>
                        <select name="tax_class" id="tax_class" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200 transition duration-150 ease-in-out">
                            @for ($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-6 flex items-center">
                        <input type="checkbox" name="has_children" id="has_children" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out">
                        <label for="has_children" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Has Children') }}</label>
                    </div>

                    <div class="mb-6 flex items-center">
                        <input type="checkbox" name="is_single_parent" id="is_single_parent" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out">
                        <label for="is_single_parent" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Is Single Parent') }}</label>
                    </div>

                    <div class="mb-6">
                        <label for="health_insurance_additional_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Health Insurance Additional Rate (%)') }}</label>
                        <input type="number" step="0.001" name="health_insurance_additional_rate" id="health_insurance_additional_rate" value="0.0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200 transition duration-150 ease-in-out">
                    </div>

                    <div class="mb-6 flex items-center">
                        <input type="checkbox" name="is_childless" id="is_childless" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out">
                        <label for="is_childless" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Is Childless (for nursing care)') }}</label>
                    </div>

                    <div class="mb-6 flex items-center">
                        <input type="checkbox" name="is_over23" id="is_over23" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out">
                        <label for="is_over23" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Is Over 23 (for nursing care)') }}</label>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Days per Week You Go Out') }}</label>
                    <div class="grid grid-cols-3 gap-2">
                        @php
                            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        @endphp
                        @foreach($weekdays as $day)
                            <div class="flex items-center">
                                <input type="checkbox" name="days_per_week[]" id="{{ strtolower($day) }}" value="{{ strtolower($day) }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out">
                                <label for="{{ strtolower($day) }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __($day) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-center">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out">
                        {{ __('Calculate') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

