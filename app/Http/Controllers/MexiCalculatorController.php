<?php

namespace App\Http\Controllers;

use App\Models\Bar;
use App\Models\Drink;
use App\Models\Page;
use App\Services\GermanTaxCalculatorService;
use Illuminate\Http\Request;

class MexiCalculatorController extends Controller
{
    protected $taxCalculatorService;

    public function __construct(GermanTaxCalculatorService $taxCalculatorService)
    {
        $this->taxCalculatorService = $taxCalculatorService;
    }

    public function index()
    {
        $bars = Bar::with('drinks')->get();
        $page = Page::first();
        $globalTaxEnabled = $page ? $page->global_tax_enabled : false;
        $germanTaxEnabled = $page ? $page->german_tax_enabled : false;
        $churchTaxEnabled = $page ? $page->church_tax_enabled : false;
        $taxBrackets = GermanTaxCalculatorService::getTaxBrackets();
        return view('mexi-calculator.index', compact('bars', 'globalTaxEnabled', 'germanTaxEnabled', 'churchTaxEnabled', 'taxBrackets'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'money' => 'required|numeric|min:0',
            'drink_id' => 'required|exists:drinks,id',
            'period' => 'required|in:yearly,monthly,daily',
            'apply_tax' => 'nullable|boolean',
            'apply_church_tax' => 'nullable|boolean',
            'allowance' => 'numeric|min:0',
            'tax_class' => 'required_if:apply_tax,true|integer|min:1|max:6',
            'has_children' => 'nullable|boolean',
            'is_single_parent' => 'nullable|boolean',
            'health_insurance_additional_rate' => 'numeric|min:0',
            'is_childless' => 'nullable|boolean',
            'is_over23' => 'nullable|boolean',
            'days_per_week' => 'nullable|array',
            'days_per_week.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ]);

        $bruttoMoney = $request->input('money');
        $applyTax = (bool) $request->input('apply_tax');
        $applyChurchTax = (bool) $request->input('apply_church_tax');
        $period = $request->input('period');
        $allowance = $request->input('allowance', 0);
        $taxClass = $request->input('tax_class');
        $hasChildren = $request->has('has_children');
        $isSingleParent = $request->has('is_single_parent');
        $healthInsuranceAdditionalRate = $request->input('health_insurance_additional_rate', 0.0);
        $isChildless = $request->has('is_childless');
        $isOver23 = $request->has('is_over23');
        $drinkId = $request->input('drink_id');
        $daysPerWeek = $request->input('days_per_week', []);

        return redirect()->route('mexi-calculator.show-results', [
            'money' => $bruttoMoney,
            'drink_id' => $drinkId,
            'apply_tax' => $applyTax,
            'apply_church_tax' => $applyChurchTax,
            'period' => $period,
            'allowance' => $allowance,
            'tax_class' => $taxClass,
            'has_children' => $hasChildren,
            'is_single_parent' => $isSingleParent,
            'health_insurance_additional_rate' => $healthInsuranceAdditionalRate,
            'is_childless' => $isChildless,
            'is_over23' => $isOver23,
            'days_per_week' => $daysPerWeek,
        ]);
    }

    public function showResults(Request $request)
    {
        $request->validate([
            'money' => 'required|numeric|min:0',
            'drink_id' => 'required|exists:drinks,id',
            'period' => 'required|in:yearly,monthly,daily',
            'apply_tax' => 'nullable|boolean',
            'apply_church_tax' => 'nullable|boolean',
            'allowance' => 'numeric|min:0',
            'tax_class' => 'required_if:apply_tax,true|integer|min:1|max:6',
            'has_children' => 'nullable|boolean',
            'is_single_parent' => 'nullable|boolean',
            'health_insurance_additional_rate' => 'numeric|min:0',
            'is_childless' => 'nullable|boolean',
            'is_over23' => 'nullable|boolean',
            'days_per_week' => 'nullable|array',
            'days_per_week.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ]);

        $bruttoMoney = $request->input('money');
        $applyTax = (bool) $request->input('apply_tax');
        $applyChurchTax = (bool) $request->input('apply_church_tax');
        $period = $request->input('period');
        $allowance = $request->input('allowance', 0);
        $taxClass = $request->input('tax_class');
        $hasChildren = $request->has('has_children');
        $isSingleParent = $request->has('is_single_parent');
        $healthInsuranceAdditionalRate = $request->input('health_insurance_additional_rate', 0.0);
        $isChildless = $request->has('is_childless');
        $isOver23 = $request->has('is_over23');
        $daysPerWeek = $request->input('days_per_week', []);

        $taxCalculationResults = $this->taxCalculatorService->calculateNetto(
            $bruttoMoney,
            $period,
            $applyTax,
            $applyChurchTax,
            $allowance,
            $taxClass,
            $hasChildren,
            $isSingleParent,
            $healthInsuranceAdditionalRate,
            $isChildless,
            $isOver23
        );

        $nettoMoney = $taxCalculationResults['netto'];
        $yearlyNettoMoney = $taxCalculationResults['yearly_netto'];

        $drink = Drink::find($request->input('drink_id'));

        $numberOfDrinks = floor($yearlyNettoMoney / $drink->price); // Calculate total drinks based on yearly netto

        $numberOfDrinksPerVisit = 0;
        $visitsPerWeek = count($daysPerWeek);

        if ($visitsPerWeek > 0) {
            $totalVisitsInYear = $visitsPerWeek * 52; // Total visits in a year
            $nettoMoneyPerVisit = $yearlyNettoMoney / $totalVisitsInYear;
            $numberOfDrinksPerVisit = floor($nettoMoneyPerVisit / $drink->price);
        } else {
            $numberOfDrinksPerVisit = $numberOfDrinks; // If no visit frequency, assume all drinks can be bought at once
        }
        $remainingMoney = $yearlyNettoMoney - ($numberOfDrinks * $drink->price);

        $dailyNettoMoney = $nettoMoney; // Assuming nettoMoney is already daily if period is daily, otherwise convert
        if ($period === 'yearly') {
            $dailyNettoMoney = $yearlyNettoMoney / 365;
        } elseif ($period === 'monthly') {
            $dailyNettoMoney = ($nettoMoney * 12) / 365;
        }
        $dailyNumberOfDrinks = floor($dailyNettoMoney / $drink->price);

        $shareableLink = route('mexi-calculator.show-results', [
            'money' => $bruttoMoney,
            'drink_id' => $drink->id,
            'apply_tax' => $applyTax,
            'apply_church_tax' => $applyChurchTax,
            'period' => $period,
            'allowance' => $allowance,
            'tax_class' => $taxClass,
            'has_children' => $hasChildren,
            'is_single_parent' => $isSingleParent,
            'health_insurance_additional_rate' => $healthInsuranceAdditionalRate,
            'is_childless' => $isChildless,
            'is_over23' => $isOver23,
            'days_per_week' => $daysPerWeek,
        ]);

        $baseAnimationDuration = 2.0; // Base animation duration in seconds
        $animationDelayMultiplier = 0.002; // Delay per icon in seconds
        $maxRenderedDrinks = 1500; // Limit for performance

        $actualRenderedDrinks = min($numberOfDrinksPerVisit, $maxRenderedDrinks);
        $maxAnimationDuration = min(($baseAnimationDuration + ($actualRenderedDrinks * $animationDelayMultiplier)), 10) * 1000; // Convert to milliseconds and cap at 10 seconds

        return view('mexi-calculator.results', compact(
            'bruttoMoney',
            'nettoMoney',
            'drink',
            'numberOfDrinks',
            'remainingMoney',
            'applyTax',
            'applyChurchTax',
            'period',
            'allowance',
            'shareableLink',
            'taxCalculationResults',
            'daysPerWeek',
            'numberOfDrinksPerVisit',
            'yearlyNettoMoney',
            'taxClass',
            'hasChildren',
            'isSingleParent',
            'healthInsuranceAdditionalRate',
            'isChildless',
            'isOver23',
            'dailyNumberOfDrinks',
            'maxAnimationDuration'
        ));
    }
}
