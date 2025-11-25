<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\LiniWaktuTargetAchievementValidation;

/**
 * Form Request: LiniWaktuTargetRequest
 * 
 * Validation untuk data lini_waktu_target (pivot table)
 * Termasuk validation untuk achievement constraints
 */
class LiniWaktuTargetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Foreign keys
            'lini_waktu_id' => ['required', 'integer', 'exists:lini_waktu,id'],
            'target_id' => ['required', 'integer', 'exists:target_account_m,id'],

            // Realization fields (r_*)
            'r_revenue' => ['nullable', 'numeric', 'min:0'],
            'r_scalling' => ['nullable', 'numeric', 'min:0'],
            'r_datin' => ['nullable', 'numeric', 'min:0'],
            'r_hsi' => ['nullable', 'numeric', 'min:0'],
            'r_wireline' => ['nullable', 'numeric', 'min:0'],
            'r_wifi' => ['nullable', 'numeric', 'min:0'],
            'r_cyc' => ['nullable', 'numeric', 'min:0'],
            'r_cr' => ['nullable', 'numeric', 'min:0'],
            'r_profit' => ['nullable', 'numeric', 'min:0'],
            'r_nps' => ['nullable', 'numeric', 'min:0'],
            'r_maps' => ['nullable', 'numeric', 'min:0'],
            'r_lop' => ['nullable', 'numeric', 'min:0'],
            'r_capability' => ['nullable', 'numeric', 'min:0'],
            'r_cc' => ['nullable', 'numeric', 'min:0'],

            // Achievement fields - Result (ach_*)
            'ach_revenue_plan' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_scaling' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_sales_datin' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_hsi' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_wireline' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_wifi' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_cyc' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_cr' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_profit' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_nps' => ['required', 'numeric', 'min:0', 'max:200'],

            // Achievement fields - Process (ach_*)
            'ach_maps' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_lop' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_capability' => ['required', 'numeric', 'min:0', 'max:200'],
            'ach_cc' => ['required', 'numeric', 'min:0', 'max:200'],

            // Achievement totals with custom validation
            'ach_result' => [
                'required',
                'numeric',
                'min:0',
                'max:2000',
                new LiniWaktuTargetAchievementValidation($this->all(), 'result')
            ],
            'ach_proses' => [
                'required',
                'numeric',
                'min:0',
                'max:800',
                new LiniWaktuTargetAchievementValidation($this->all(), 'proses')
            ],

            // NKI adjustment
            'nki_adjustment' => ['nullable', 'numeric', 'min:0', 'max:200'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Foreign keys
            'lini_waktu_id.required' => 'Lini waktu ID harus diisi.',
            'lini_waktu_id.exists' => 'Lini waktu tidak ditemukan.',
            'target_id.required' => 'Target ID harus diisi.',
            'target_id.exists' => 'Target tidak ditemukan.',

            // Realization fields
            'r_revenue.numeric' => 'Revenue realisasi harus berupa angka.',
            'r_revenue.min' => 'Revenue realisasi tidak boleh negatif.',

            // Achievement fields - generic messages
            '*.required' => 'Field :attribute harus diisi.',
            '*.numeric' => 'Field :attribute harus berupa angka.',
            '*.min' => 'Field :attribute tidak boleh negatif.',
            '*.max' => 'Field :attribute maksimal :max%.',

            // Achievement totals
            'ach_result.required' => 'Achievement result harus diisi.',
            'ach_result.max' => 'Achievement result maksimal 2000% (10 field @ 200%).',
            'ach_proses.required' => 'Achievement proses harus diisi.',
            'ach_proses.max' => 'Achievement proses maksimal 800% (4 field @ 200%).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'lini_waktu_id' => 'Lini Waktu ID',
            'target_id' => 'Target ID',
            'r_revenue' => 'Revenue Realisasi',
            'r_scalling' => 'Scalling Realisasi',
            'r_datin' => 'Datin Realisasi',
            'r_hsi' => 'HSI Realisasi',
            'r_wireline' => 'Wireline Realisasi',
            'r_wifi' => 'WiFi Realisasi',
            'r_cyc' => 'CYC Realisasi',
            'r_cr' => 'CR Realisasi',
            'r_profit' => 'Profit Realisasi',
            'r_nps' => 'NPS Realisasi',
            'r_maps' => 'MAPS Realisasi',
            'r_lop' => 'LOP Realisasi',
            'r_capability' => 'Capability Realisasi',
            'r_cc' => 'CC Realisasi',
            'ach_revenue_plan' => 'Achievement Revenue Plan',
            'ach_scaling' => 'Achievement Scaling',
            'ach_sales_datin' => 'Achievement Sales Datin',
            'ach_hsi' => 'Achievement HSI',
            'ach_wireline' => 'Achievement Wireline',
            'ach_wifi' => 'Achievement WiFi',
            'ach_cyc' => 'Achievement CYC',
            'ach_cr' => 'Achievement CR',
            'ach_profit' => 'Achievement Profit',
            'ach_nps' => 'Achievement NPS',
            'ach_maps' => 'Achievement MAPS',
            'ach_lop' => 'Achievement LOP',
            'ach_capability' => 'Achievement Capability',
            'ach_cc' => 'Achievement CC',
            'ach_result' => 'Achievement Result',
            'ach_proses' => 'Achievement Proses',
            'nki_adjustment' => 'NKI Adjustment',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-calculate ach_result and ach_proses if not provided
        if (!$this->has('ach_result')) {
            $achResult = 
                ($this->ach_revenue_plan ?? 0) +
                ($this->ach_scaling ?? 0) +
                ($this->ach_sales_datin ?? 0) +
                ($this->ach_hsi ?? 0) +
                ($this->ach_wireline ?? 0) +
                ($this->ach_wifi ?? 0) +
                ($this->ach_cyc ?? 0) +
                ($this->ach_cr ?? 0) +
                ($this->ach_profit ?? 0) +
                ($this->ach_nps ?? 0);

            $this->merge(['ach_result' => round($achResult, 3)]);
        }

        if (!$this->has('ach_proses')) {
            $achProses = 
                ($this->ach_maps ?? 0) +
                ($this->ach_lop ?? 0) +
                ($this->ach_capability ?? 0) +
                ($this->ach_cc ?? 0);

            $this->merge(['ach_proses' => round($achProses, 3)]);
        }
    }
}
