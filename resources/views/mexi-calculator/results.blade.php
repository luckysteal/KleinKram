            <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mexicalculator Results') }}
        </h2>
    </x-slot>

    <div class="py-12 relative overflow-hidden h-full">
        <!-- Background Drink Tower -->
        <div class="absolute inset-0 -z-1" x-data="{
            drinks: {{ $numberOfDrinksPerVisit }},
            drinkIcon: '{{ str_replace(["\n", "\r"], "", $drink->icon_svg) }}',
            maxDrinksForFullSize: 50, // Max drinks for full size icon
            minIconSize: 0.2, // Minimum scale for icons
            maxIconSize: 1.0, // Maximum scale for icons
            baseAnimationDuration: 2.0, // Base animation duration
            animationDelayMultiplier: 0.005, // Delay per icon
            maxRenderedDrinks: 1500 // Limit for performance
        }">
            <template x-for="i in Math.min(drinks, maxRenderedDrinks)" :key="i">
                <div
                    class="absolute ease-out"
                    :style="`
                        bottom: ${ (i - 1) * 1 }px; /* Reduced stacking height for better visibility */
                        left: ${ Math.random() * 100 }%; /* Wider random spread */
                        animation: fall-down ${ baseAnimationDuration + (i * animationDelayMultiplier) }s ease-out forwards;
                        width: 50px; /* Smaller width for the SVG container */
                        height: 70px; /* Smaller height for the SVG container */
                    `"
                >
                    <x-svg-icon :svg="$drink->icon_svg"></x-svg-icon>
                </div>
            </template>
        </div>

        <div class="max-w-md w-full mx-auto bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 relative z-10" x-data="{ showResults: false }" x-init="setTimeout(() => showResults = true, {{ $maxAnimationDuration }})" x-show="showResults" x-cloak>
            <h1 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-8">{{ __('Mexicalculator Results') }}</h1>

            <div class="text-gray-700 dark:text-gray-300 text-lg mb-6">
                <p class="mb-2">{{ __('Your brutto money') }} ({{ __($period) }}): <span class="font-semibold">€{{ number_format($bruttoMoney, 2) }}</span></p>
                <p class="mb-2">{{ __('Your yearly brutto money') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['yearly_brutto'], 2) }}</span></p>

                @if($applyTax)
                    <p class="mb-2">{{ __('Your netto money') }} ({{ __($period) }}): <span class="font-semibold">€{{ number_format($nettoMoney, 2) }}</span></p>
                    <p class="mb-2">{{ __('Your yearly netto money') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['yearly_netto'], 2) }}</span></p>

                    @if($taxCalculationResults['selected_tax_bracket'])
                        <p class="mb-2 text-sm">{{ __('Selected Tax Bracket') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['selected_tax_bracket']['min'], 0) }} - €{{ number_format($taxCalculationResults['selected_tax_bracket']['max'], 0) }} ({{ $taxCalculationResults['selected_tax_bracket']['description'] }})</span></p>
                    @endif

                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mt-6 mb-4">{{ __('Tax Breakdown (Yearly)') }}</h3>
                    <ul class="list-disc list-inside mb-4">
                        <li>{{ __('Income Tax') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['income_tax'], 2) }}</span></li>
                        <li>{{ __('Solidarity Surcharge') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['solidarity_surcharge'], 2) }}</span></li>
                        @if($applyChurchTax)
                            <li>{{ __('Church Tax') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['church_tax'], 2) }}</span></li>
                        @endif
                        <li>{{ __('Social Security (Est.)') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['social_security_total'], 2) }}</span>
                            <ul class="list-circle list-inside ml-4 text-sm">
                                <li>{{ __('Pension') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['breakdown']['pension'], 2) }}</span></li>
                                <li>{{ __('Unemployment') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['breakdown']['unemployment'], 2) }}</span></li>
                                <li>{{ __('Health') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['breakdown']['health'], 2) }}</span></li>
                                <li>{{ __('Nursing Care') }}: <span class="font-semibold">€{{ number_format($taxCalculationResults['breakdown']['nursingCare'], 2) }}</span></li>
                            </ul>
                        </li>
                    </ul>

                    <p class="mt-4">{{ __('With your yearly netto money, you can buy:') }}</p>
                @else
                    <p class="mb-4">{{ __('With your yearly brutto money, you can buy:') }}</p>
                @endif
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">{{ $numberOfDrinks }} x {{ $drink->name }} ({{ __('yearly') }})</p>
                <p class="text-base">at <span class="font-semibold">{{ $drink->bar->name }}</span></p>
                <p class="mt-4">{{ __('Remaining money') }}: <span class="font-semibold">€{{ number_format($remainingMoney, 2) }}</span> ({{ __('yearly') }})</p>

                @if(!empty($daysPerWeek) && $numberOfDrinksPerVisit > 0)
                    <p class="mt-4">{{ __('You can buy approximately') }} <span class="font-semibold">{{ $numberOfDrinksPerVisit }}</span> {{ $drink->name }} {{ __('per visit') }} ({{ __('based on') }} {{ count($daysPerWeek) }} {{ __('visits per week') }}).</p>
                @endif
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Share your results:') }}</p>
                <button type="button" @click="
                    const linkText = '{{ $shareableLink }}';
                    navigator.clipboard.writeText(linkText).then(() => {
                        $el.nextElementSibling.classList.remove('hidden');
                        setTimeout(() => $el.nextElementSibling.classList.add('hidden'), 2000);
                    });
                " class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Copy Link
                </button>
                <span class="text-sm text-green-600 ml-2 hidden" x-ref="copiedMessage">Copied!</span>
            </div>
        </div>
    </div>
</x-app-layout>
