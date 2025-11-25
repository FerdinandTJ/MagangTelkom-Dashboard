<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\LiniWaktuPercentageValidation;

/**
 * Form Request untuk Store/Update LiniWaktu
 * 
 * Includes automatic validation for percentage constraints
 */
class LiniWaktuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic fields
            'quartal' => ['required', 'string', 'in:Q1,Q2,Q3,Q4'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nik_am' => ['required', 'string', 'exists:account_managers,nik'],
            
            // Main percentages (Result + Process = 100%)
            'percentage_result' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                new LiniWaktuPercentageValidation('result_process')
            ],
            'percentage_proses' => [
                'required',
                'numeric',
                'min:0',
                'max:100'
            ],
            
            // Result sub-percentages (must sum to percentage_result)
            'percentage_revenue' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                new LiniWaktuPercentageValidation('result_breakdown')
            ],
            'percentage_scaling' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_datin' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_hsi' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_wireline' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_wifi' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_cyc' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_cr' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_profit' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_customer' => ['required', 'numeric', 'min:0', 'max:100'],
            
            // Process sub-percentages (must sum to percentage_proses)
            'percentage_maps' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                new LiniWaktuPercentageValidation('process_breakdown')
            ],
            'percentage_lop' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_capability' => ['required', 'numeric', 'min:0', 'max:100'],
            'percentage_cc' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'percentage_result.required' => 'Percentage Result wajib diisi',
            'percentage_result.numeric' => 'Percentage Result harus berupa angka',
            'percentage_result.min' => 'Percentage Result minimal 0%',
            'percentage_result.max' => 'Percentage Result maksimal 100%',
            
            'percentage_proses.required' => 'Percentage Process wajib diisi',
            'percentage_proses.numeric' => 'Percentage Process harus berupa angka',
            'percentage_proses.min' => 'Percentage Process minimal 0%',
            'percentage_proses.max' => 'Percentage Process maksimal 100%',
            
            // Add more custom messages as needed
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'percentage_result' => 'Persentase Result',
            'percentage_proses' => 'Persentase Process',
            'percentage_revenue' => 'Persentase Revenue',
            'percentage_scaling' => 'Persentase Scaling',
            'percentage_datin' => 'Persentase Datin',
            'percentage_hsi' => 'Persentase HSI',
            'percentage_wireline' => 'Persentase Wireline',
            'percentage_wifi' => 'Persentase WiFi',
            'percentage_cyc' => 'Persentase CYC',
            'percentage_cr' => 'Persentase CR',
            'percentage_profit' => 'Persentase Profit',
            'percentage_customer' => 'Persentase Customer',
            'percentage_maps' => 'Persentase MAPS',
            'percentage_lop' => 'Persentase LOP',
            'percentage_capability' => 'Persentase Capability',
            'percentage_cc' => 'Persentase CC',
        ];
    }

    /**
     * Prepare the data for validation.
     * Auto-calculate bulan_awal and bulan_akhir based on quartal
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('quartal') && $this->has('tahun')) {
            $months = \App\Models\LiniWaktu::getQuartalMonths($this->quartal);
            
            $this->merge([
                'bulan_awal' => \Carbon\Carbon::create($this->tahun, $months['start'], 1)->startOfMonth(),
                'bulan_akhir' => \Carbon\Carbon::create($this->tahun, $months['end'], 1)->endOfMonth(),
            ]);
        }
    }
}
