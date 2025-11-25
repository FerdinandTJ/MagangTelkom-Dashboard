<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Custom Validation Rule: LiniWaktuTargetAchievementValidation
 * 
 * Validates achievement constraints for lini_waktu_target table:
 * 1. ach_result must equal the sum of 10 result achievement fields
 * 2. ach_proses must equal the sum of 4 process achievement fields
 * 
 * Usage:
 * ```php
 * 'ach_result' => [new LiniWaktuTargetAchievementValidation($data, 'result')],
 * 'ach_proses' => [new LiniWaktuTargetAchievementValidation($data, 'proses')],
 * ```
 */
class LiniWaktuTargetAchievementValidation implements ValidationRule
{
    /**
     * The data being validated
     */
    protected array $data;

    /**
     * The validation type: 'result' or 'proses'
     */
    protected string $type;

    /**
     * Result achievement fields that should sum to ach_result
     */
    protected array $resultFields = [
        'ach_revenue_plan',
        'ach_scaling',
        'ach_sales_datin',
        'ach_hsi',
        'ach_wireline',
        'ach_wifi',
        'ach_cyc',
        'ach_cr',
        'ach_profit',
        'ach_nps',
    ];

    /**
     * Process achievement fields that should sum to ach_proses
     */
    protected array $prosesFields = [
        'ach_maps',
        'ach_lop',
        'ach_capability',
        'ach_cc',
    ];

    /**
     * Create a new rule instance.
     *
     * @param array $data The validation data
     * @param string $type Either 'result' or 'proses'
     */
    public function __construct(array $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->type === 'result') {
            $this->validateResultAchievement($value, $fail);
        } elseif ($this->type === 'proses') {
            $this->validateProsesAchievement($value, $fail);
        }
    }

    /**
     * Validate that ach_result equals sum of result achievement fields
     */
    protected function validateResultAchievement(mixed $value, Closure $fail): void
    {
        $sum = 0;
        $missingFields = [];

        foreach ($this->resultFields as $field) {
            if (!isset($this->data[$field])) {
                $missingFields[] = $field;
                continue;
            }
            $sum += (float) $this->data[$field];
        }

        if (!empty($missingFields)) {
            $fail("Field {$missingFields[0]} harus diisi untuk validasi ach_result.");
            return;
        }

        $sum = round($sum, 3);
        $expectedValue = round((float) $value, 3);

        if ($sum !== $expectedValue) {
            $fail("Total dari achievement result ({$sum}%) harus sama dengan ach_result ({$expectedValue}%). Field yang dijumlahkan: " . implode(', ', $this->resultFields));
        }
    }

    /**
     * Validate that ach_proses equals sum of process achievement fields
     */
    protected function validateProsesAchievement(mixed $value, Closure $fail): void
    {
        $sum = 0;
        $missingFields = [];

        foreach ($this->prosesFields as $field) {
            if (!isset($this->data[$field])) {
                $missingFields[] = $field;
                continue;
            }
            $sum += (float) $this->data[$field];
        }

        if (!empty($missingFields)) {
            $fail("Field {$missingFields[0]} harus diisi untuk validasi ach_proses.");
            return;
        }

        $sum = round($sum, 3);
        $expectedValue = round((float) $value, 3);

        if ($sum !== $expectedValue) {
            $fail("Total dari achievement proses ({$sum}%) harus sama dengan ach_proses ({$expectedValue}%). Field yang dijumlahkan: " . implode(', ', $this->prosesFields));
        }
    }

    /**
     * Get the field names for a specific type
     */
    public static function getFieldsForType(string $type): array
    {
        $instance = new self([], $type);
        
        return match ($type) {
            'result' => $instance->resultFields,
            'proses' => $instance->prosesFields,
            default => [],
        };
    }
}
