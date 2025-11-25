<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Custom Validation Rule for LiniWaktu Percentage Constraints
 * 
 * Validates that percentage fields sum up correctly according to business rules:
 * 1. percentage_result + percentage_proses = 100%
 * 2. Result sub-percentages sum to percentage_result
 * 3. Process sub-percentages sum to percentage_proses
 */
class LiniWaktuPercentageValidation implements Rule
{
    protected $message;
    protected $validationType;

    /**
     * Constructor
     * 
     * @param string $type - 'result_process', 'result_breakdown', or 'process_breakdown'
     */
    public function __construct(string $type = 'result_process')
    {
        $this->validationType = $type;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        $data = request()->all();
        
        switch ($this->validationType) {
            case 'result_process':
                return $this->validateResultAndProcess($data);
            
            case 'result_breakdown':
                return $this->validateResultBreakdown($data);
            
            case 'process_breakdown':
                return $this->validateProcessBreakdown($data);
            
            default:
                return true;
        }
    }

    /**
     * Validate that percentage_result + percentage_proses = 100%
     */
    protected function validateResultAndProcess(array $data): bool
    {
        $percentageResult = floatval($data['percentage_result'] ?? 0);
        $percentageProses = floatval($data['percentage_proses'] ?? 0);
        
        $total = round($percentageResult + $percentageProses, 3);
        
        if ($total !== 100.0) {
            $this->message = "The sum of percentage_result ({$percentageResult}%) and percentage_proses ({$percentageProses}%) must equal 100%. Current total: {$total}%";
            return false;
        }
        
        return true;
    }

    /**
     * Validate that result sub-percentages sum to percentage_result
     */
    protected function validateResultBreakdown(array $data): bool
    {
        $percentageResult = floatval($data['percentage_result'] ?? 0);
        
        $subPercentages = [
            'percentage_revenue' => floatval($data['percentage_revenue'] ?? 0),
            'percentage_scaling' => floatval($data['percentage_scaling'] ?? 0),
            'percentage_datin' => floatval($data['percentage_datin'] ?? 0),
            'percentage_hsi' => floatval($data['percentage_hsi'] ?? 0),
            'percentage_wireline' => floatval($data['percentage_wireline'] ?? 0),
            'percentage_wifi' => floatval($data['percentage_wifi'] ?? 0),
            'percentage_cyc' => floatval($data['percentage_cyc'] ?? 0),
            'percentage_cr' => floatval($data['percentage_cr'] ?? 0),
            'percentage_profit' => floatval($data['percentage_profit'] ?? 0),
            'percentage_customer' => floatval($data['percentage_customer'] ?? 0),
        ];
        
        $total = round(array_sum($subPercentages), 3);
        
        if ($total !== $percentageResult) {
            $breakdown = implode(' + ', array_map(fn($k, $v) => "{$k}({$v}%)", array_keys($subPercentages), $subPercentages));
            $this->message = "The sum of result sub-percentages ({$breakdown}) must equal percentage_result ({$percentageResult}%). Current total: {$total}%";
            return false;
        }
        
        return true;
    }

    /**
     * Validate that process sub-percentages sum to percentage_proses
     */
    protected function validateProcessBreakdown(array $data): bool
    {
        $percentageProses = floatval($data['percentage_proses'] ?? 0);
        
        $subPercentages = [
            'percentage_maps' => floatval($data['percentage_maps'] ?? 0),
            'percentage_lop' => floatval($data['percentage_lop'] ?? 0),
            'percentage_capability' => floatval($data['percentage_capability'] ?? 0),
            'percentage_cc' => floatval($data['percentage_cc'] ?? 0),
        ];
        
        $total = round(array_sum($subPercentages), 3);
        
        if ($total !== $percentageProses) {
            $breakdown = implode(' + ', array_map(fn($k, $v) => "{$k}({$v}%)", array_keys($subPercentages), $subPercentages));
            $this->message = "The sum of process sub-percentages ({$breakdown}) must equal percentage_proses ({$percentageProses}%). Current total: {$total}%";
            return false;
        }
        
        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->message;
    }
}
