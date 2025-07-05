<?php

namespace App\Services;

class GermanTaxCalculatorService
{
    // INPUT PARAMETERS (for context, not directly as constants)
    // grossSalary: decimal (monthly gross salary in €)
    // taxClass: integer (1 to 6)
    // hasChildren: boolean
    // isSingleParent: boolean
    // churchMember: boolean
    // healthInsuranceAdditionalRate: decimal (e.g. 0.013 for 1.3%)
    // isChildless: boolean (for nursing care)
    // isOver23: boolean (age relevant for nursing care)

    // CONSTANTS (2025 approx. - annual values converted to monthly where applicable)
    const BASIC_PERSONAL_ALLOWANCE_ANNUAL = 10908.00;
    const PENSION_INSURANCE_RATE_EMPLOYEE = 0.093; // 9.3%
    const UNEMPLOYMENT_INSURANCE_RATE_EMPLOYEE = 0.012; // 1.2%
    const HEALTH_INSURANCE_RATE_EMPLOYEE = 0.073; // 7.3%
    const NURSING_CARE_INSURANCE_RATE_EMPLOYEE = 0.01525; // 1.525%
    const NURSING_CARE_ADDITIONAL_CHILDLESS = 0.0035; // 0.35%
    const SOLIDARITY_SURCHARGE_RATE = 0.055; // 5.5% of income tax, conditional
    const CHURCH_TAX_RATE = 0.08; // 8% (default, varies by state)

    // Contribution caps (monthly)
    const PENSION_INSURANCE_CAP_MONTHLY = 7300.00;
    const UNEMPLOYMENT_INSURANCE_CAP_MONTHLY = 7300.00;
    const HEALTH_INSURANCE_CAP_MONTHLY = 4987.50;
    const NURSING_CARE_INSURANCE_CAP_MONTHLY = 4987.50;

    // Solidarity Surcharge Threshold (annual, converted to monthly for comparison)
    const SOLIDARITY_THRESHOLD_ANNUAL = 16956.00;

    // Simplified German tax brackets for 2024 (Einkommensteuertarif)
    // This is a highly simplified model and does not account for all complexities
    // (e.g., family status, specific health insurance, church tax, etc.)
    const TAX_BRACKETS = [
        ['min' => 0, 'max' => 11604, 'rate' => 0.0, 'description' => 'Tax-free allowance (Grundfreibetrag)'],
        ['min' => 11605, 'max' => 17000, 'rate' => 0.14, 'description' => 'Progressive zone 1 (linear increase)'],
        ['min' => 17001, 'max' => 66760, 'rate' => 0.24, 'description' => 'Progressive zone 2 (linear increase)'],
        ['min' => 66761, 'max' => 277825, 'rate' => 0.42, 'description' => 'Proportional zone (Spitzensteuersatz)'],
        ['min' => 277826, 'max' => PHP_INT_MAX, 'rate' => 0.45, 'description' => 'Top rate (Reichensteuer)'],
    ];

    public function calculateNetto(
        float $bruttoAmount,
        string $period = 'yearly',
        bool $applyTax = true,
        bool $applyChurchTax = false,
        float $allowance = 0,
        int $taxClass = 1,
        bool $hasChildren = false,
        bool $isSingleParent = false,
        float $healthInsuranceAdditionalRate = 0.0,
        bool $isChildless = false,
        bool $isOver23 = false
    ): array
    {
        // Convert input brutto amount to monthly for calculation consistency
        $monthlyBrutto = $this->convertToMonthly($bruttoAmount, $period);

        $pensionContribution = 0;
        $unemploymentContribution = 0;
        $healthContribution = 0;
        $nursingCareContribution = 0;
        $SSContribution = 0;
        $incomeTax = 0;
        $solidaritySurcharge = 0;
        $churchTax = 0;
        $selectedTaxBracket = null;

        if ($applyTax) {
            // STEP 1: Calculate Social Security Contributions (Monthly)
            $pensionContribution = min($monthlyBrutto, self::PENSION_INSURANCE_CAP_MONTHLY) * self::PENSION_INSURANCE_RATE_EMPLOYEE;
            $unemploymentContribution = min($monthlyBrutto, self::UNEMPLOYMENT_INSURANCE_CAP_MONTHLY) * self::UNEMPLOYMENT_INSURANCE_RATE_EMPLOYEE;
            $healthContribution = min($monthlyBrutto, self::HEALTH_INSURANCE_CAP_MONTHLY) * (self::HEALTH_INSURANCE_RATE_EMPLOYEE + $healthInsuranceAdditionalRate);

            $nursingCareRate = self::NURSING_CARE_INSURANCE_RATE_EMPLOYEE;
            if ($isChildless && $isOver23) {
                $nursingCareRate += self::NURSING_CARE_ADDITIONAL_CHILDLESS;
            }
            $nursingCareContribution = min($monthlyBrutto, self::NURSING_CARE_INSURANCE_CAP_MONTHLY) * $nursingCareRate;

            $SSContribution = $pensionContribution + $unemploymentContribution + $healthContribution + $nursingCareContribution;

            // STEP 2: Calculate Taxable Income (Monthly)
            $monthlyBasicAllowance = self::BASIC_PERSONAL_ALLOWANCE_ANNUAL / 12;
            $monthlyAllowance = $monthlyBasicAllowance + ($allowance / 12); // Add user-defined allowance

            $taxableIncomeMonthly = $monthlyBrutto - $SSContribution - $monthlyAllowance;
            if ($taxableIncomeMonthly < 0) {
                $taxableIncomeMonthly = 0;
            }

            // STEP 3: Calculate Income Tax using Progressive Rates (Monthly)
            // NOTE: This is a simplified progressive tax calculation. Official calculation is more complex.
            $incomeTax = $this->calculateMonthlyIncomeTax($taxableIncomeMonthly, $taxClass);

            // Determine the selected tax bracket for informational purposes
            $yearlyTaxableIncome = $taxableIncomeMonthly * 12;
            foreach (self::TAX_BRACKETS as $bracket) {
                if ($yearlyTaxableIncome >= $bracket['min'] && $yearlyTaxableIncome <= $bracket['max']) {
                    $selectedTaxBracket = $bracket;
                    break;
                }
            }


            // STEP 4: Calculate Solidarity Surcharge (Monthly)
            $solidarityThresholdMonthly = self::SOLIDARITY_THRESHOLD_ANNUAL / 12;
            if ($incomeTax <= $solidarityThresholdMonthly) {
                $solidaritySurcharge = 0;
            } else {
                $solidaritySurcharge = $incomeTax * self::SOLIDARITY_SURCHARGE_RATE;
            }

            // STEP 5: Calculate Church Tax (Monthly)
            if ($applyChurchTax) {
                $churchTax = $incomeTax * self::CHURCH_TAX_RATE;
            }
        }

        // STEP 6: Calculate Net Salary (Monthly)
        $monthlyNetto = $monthlyBrutto - $SSContribution - $incomeTax - $solidaritySurcharge - $churchTax;

        // Convert all monthly results to yearly for consistency in output
        $yearlyBrutto = $monthlyBrutto * 12;
        $yearlyNetto = $monthlyNetto * 12;

        return [
            'brutto' => $bruttoAmount,
            'netto' => $this->convertFromMonthly($monthlyNetto, $period),
            'income_tax' => $this->convertFromMonthly($incomeTax, $period),
            'solidarity_surcharge' => $this->convertFromMonthly($solidaritySurcharge, $period),
            'church_tax' => $this->convertFromMonthly($churchTax, $period),
            'social_security_total' => $this->convertFromMonthly($SSContribution, $period),
            'breakdown' => [
                'pension' => $this->convertFromMonthly($pensionContribution, $period),
                'unemployment' => $this->convertFromMonthly($unemploymentContribution, $period),
                'health' => $this->convertFromMonthly($healthContribution, $period),
                'nursingCare' => $this->convertFromMonthly($nursingCareContribution, $period),
            ],
            'yearly_brutto' => $yearlyBrutto,
            'yearly_netto' => max(0, $yearlyNetto),
            'selected_tax_bracket' => $selectedTaxBracket,
        ];
    }

    private function calculateMonthlyIncomeTax(float $taxableIncomeMonthly, int $taxClass): float
    {
        // This is a highly simplified example of German progressive tax calculation.
        // Official calculation involves complex formulas (e.g., using y, z variables) and depends heavily on tax class.
        // For a real-world application, consider using a dedicated tax calculation library or API.

        // Adjust taxable income based on tax class (very simplified approximation)
        // In reality, tax classes affect allowances and multipliers, not directly the brackets.
        $adjustedTaxableIncome = $taxableIncomeMonthly;
        switch ($taxClass) {
            case 1: // Single
            case 4: // Married, both earn similar
                // No adjustment for this simplified model
                break;
            case 2: // Single with child
                $adjustedTaxableIncome -= 100; // Example allowance
                break;
            case 3: // Married, one earns significantly more
                $adjustedTaxableIncome *= 0.5; // Example splitting effect
                break;
            case 5: // Married, one earns significantly less
                $adjustedTaxableIncome *= 2.0; // Example multiplier
                break;
            case 6: // Second job
                // No adjustment for this simplified model
                break;
        }
        $adjustedTaxableIncome = max(0, $adjustedTaxableIncome);

        $incomeTax = 0;
        // Apply the simplified progressive rates to the adjusted monthly taxable income
        // Note: The TAX_BRACKETS are annual. We need to convert them to monthly for this calculation.
        // This is a further simplification for demonstration.
        $taxableIncomeAnnualized = $adjustedTaxableIncome * 12; // Convert to annual for bracket comparison

        foreach (self::TAX_BRACKETS as $bracket) {
            $minAnnual = $bracket['min'];
            $maxAnnual = $bracket['max'];
            $rate = $bracket['rate'];

            if ($taxableIncomeAnnualized > $minAnnual) {
                $taxableAmountInBracket = min($taxableIncomeAnnualized, $maxAnnual) - $minAnnual;
                $incomeTax += $taxableAmountInBracket * $rate;
            }
        }

        return $incomeTax / 12; // Convert back to monthly income tax
    }

    public static function getTaxBrackets(): array
    {
        return self::TAX_BRACKETS;
    }

    private function convertToMonthly(float $amount, string $period): float
    {
        return match ($period) {
            'daily' => $amount * (365 / 12),
            'weekly' => $amount * (52 / 12),
            'monthly' => $amount,
            'yearly' => $amount / 12,
            default => $amount,
        };
    }

    private function convertFromMonthly(float $amount, string $period): float
    {
        return match ($period) {
            'daily' => $amount / (365 / 12),
            'weekly' => $amount / (52 / 12),
            'monthly' => $amount,
            'yearly' => $amount * 12,
            default => $amount,
        };
    }
}
