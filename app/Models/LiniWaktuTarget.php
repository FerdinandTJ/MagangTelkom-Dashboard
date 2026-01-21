<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Validation\ValidationException;

/**
 * Pivot Model: LiniWaktuTarget
 * 
 * Represents the pivot table between LiniWaktu and TargetAccountM
 * with realization (r_*) and achievement (ach_*) data.
 * 
 * VALIDATION CONSTRAINTS:
 * 1. ach_result = sum of 10 result achievement fields
 * 2. ach_proses = sum of 4 process achievement fields
 * 
 * @property int $lini_waktu_id FK to lini_waktu
 * @property int $target_id FK to target_account_m
 * @property float $r_revenue Realization revenue
 * @property float $r_scalling Realization scalling
 * @property float $r_datin Realization data internet
 * @property float $r_hsi Realization HSI
 * @property float $r_wireline Realization wireline
 * @property float $r_wifi Realization WiFi
 * @property float $r_cyc Realization CYC
 * @property float $r_cr Realization CR
 * @property float $r_profit Realization profit
 * @property float $r_nps Realization NPS
 * @property float $r_maps Realization MAPS
 * @property float $r_lop Realization LOP
 * @property float $r_capability Realization capability
 * @property float $r_cc Realization CC
 * @property float $ach_revenue_plan Achievement revenue plan (%)
 * @property float $ach_scaling Achievement scaling (%)
 * @property float $ach_sales_datin Achievement sales datin (%)
 * @property float $ach_hsi Achievement HSI (%)
 * @property float $ach_wireline Achievement wireline (%)
 * @property float $ach_wifi Achievement WiFi (%)
 * @property float $ach_cyc Achievement CYC (%)
 * @property float $ach_cr Achievement CR (%)
 * @property float $ach_profit Achievement profit (%)
 * @property float $ach_nps Achievement NPS (%)
 * @property float $ach_maps Achievement MAPS (%)
 * @property float $ach_lop Achievement LOP (%)
 * @property float $ach_capability Achievement capability (%)
 * @property float $ach_cc Achievement CC (%)
 * @property float $ach_result Achievement result total (%)
 * @property float $ach_proses Achievement process total (%)
 * @property float $nki_adjustment NKI adjustment factor (%)
 */
class LiniWaktuTarget extends Pivot
{
    /**
     * The table associated with the model.
     */
    protected $table = 'lini_waktu_target';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;
    
    /**
     * Flag to skip validation (used during imports)
     * 
     * @var bool
     */
    public $skipValidation = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'lini_waktu_id',
        'target_id',
        // Realization fields
        'r_revenue',
        'r_sustain',
        'r_scalling',
        'r_ngtma',
        'r_datin',
        'r_hsi',
        'r_wireline',
        'r_wifi',
        'r_cyc',
        'r_cr',
        'r_profit',
        'r_nps',
        'r_maps',
        'r_lop',
        'r_capability',
        'r_cc',
        // Achievement fields - Result
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
        // Achievement fields - Process
        'ach_maps',
        'ach_lop',
        'ach_capability',
        'ach_cc',
        // Achievement totals
        'ach_result',
        'ach_proses',
        'nki_adjustment',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'lini_waktu_id' => 'integer',
        'target_id' => 'integer',
        'r_revenue' => 'decimal:2',
        'r_scalling' => 'decimal:2',
        'r_datin' => 'decimal:2',
        'r_hsi' => 'decimal:2',
        'r_wireline' => 'decimal:2',
        'r_wifi' => 'decimal:2',
        'r_cyc' => 'decimal:2',
        'r_cr' => 'decimal:2',
        'r_profit' => 'decimal:2',
        'r_nps' => 'decimal:2',
        'r_maps' => 'decimal:2',
        'r_lop' => 'decimal:2',
        'r_capability' => 'decimal:2',
        'r_cc' => 'decimal:2',
        'ach_revenue_plan' => 'decimal:3',
        'ach_scaling' => 'decimal:3',
        'ach_sales_datin' => 'decimal:3',
        'ach_hsi' => 'decimal:3',
        'ach_wireline' => 'decimal:3',
        'ach_wifi' => 'decimal:3',
        'ach_cyc' => 'decimal:3',
        'ach_cr' => 'decimal:3',
        'ach_profit' => 'decimal:3',
        'ach_nps' => 'decimal:3',
        'ach_maps' => 'decimal:3',
        'ach_lop' => 'decimal:3',
        'ach_capability' => 'decimal:3',
        'ach_cc' => 'decimal:3',
        'ach_result' => 'decimal:3',
        'ach_proses' => 'decimal:3',
        'nki_adjustment' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * Add validation on saving to enforce achievement constraints
     */
    protected static function booted(): void
    {
        // Validate before saving (creating or updating)
        static::saving(function (LiniWaktuTarget $pivot) {
            // Skip validation if flag is set (e.g., during imports)
            if (!$pivot->skipValidation) {
                $pivot->validateAchievements();
            }
        });
    }

    /**
     * Validate achievement constraints
     * 
     * CONSTRAINT 1: ach_result = sum of result achievement fields
     * CONSTRAINT 2: ach_proses = sum of process achievement fields
     * 
     * @throws ValidationException
     */
    public function validateAchievements(): void
    {
        // Result achievement fields
        $resultFields = [
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

        // Process achievement fields
        $prosesFields = [
            'ach_maps',
            'ach_lop',
            'ach_capability',
            'ach_cc',
        ];

        // CONSTRAINT 1: Validate ach_result
        $resultSum = 0;
        foreach ($resultFields as $field) {
            $resultSum += (float) $this->$field;
        }
        $resultSum = round($resultSum, 3);
        $achResult = round((float) $this->ach_result, 3);

        if ($resultSum !== $achResult) {
            throw ValidationException::withMessages([
                'ach_result' => "Total dari achievement result ({$resultSum}%) harus sama dengan ach_result ({$achResult}%). Field yang dijumlahkan: " . implode(', ', $resultFields)
            ]);
        }

        // CONSTRAINT 2: Validate ach_proses
        $prosesSum = 0;
        foreach ($prosesFields as $field) {
            $prosesSum += (float) $this->$field;
        }
        $prosesSum = round($prosesSum, 3);
        $achProses = round((float) $this->ach_proses, 3);

        if ($prosesSum !== $achProses) {
            throw ValidationException::withMessages([
                'ach_proses' => "Total dari achievement proses ({$prosesSum}%) harus sama dengan ach_proses ({$achProses}%). Field yang dijumlahkan: " . implode(', ', $prosesFields)
            ]);
        }
    }

    /**
     * Relation: LiniWaktu
     */
    public function liniWaktu()
    {
        return $this->belongsTo(LiniWaktu::class, 'lini_waktu_id');
    }

    /**
     * Relation: TargetAccountM
     */
    public function target()
    {
        return $this->belongsTo(TargetAccountM::class, 'target_id');
    }

    /**
     * Helper: Calculate ach_result from component fields
     */
    public function calculateAchResult(): float
    {
        return round(
            $this->ach_revenue_plan +
            $this->ach_scaling +
            $this->ach_sales_datin +
            $this->ach_hsi +
            $this->ach_wireline +
            $this->ach_wifi +
            $this->ach_cyc +
            $this->ach_cr +
            $this->ach_profit +
            $this->ach_nps,
            3
        );
    }

    /**
     * Helper: Calculate ach_proses from component fields
     */
    public function calculateAchProses(): float
    {
        return round(
            $this->ach_maps +
            $this->ach_lop +
            $this->ach_capability +
            $this->ach_cc,
            3
        );
    }

    /**
     * Helper: Auto-calculate and set ach_result and ach_proses
     */
    public function autoCalculateAchievements(): void
    {
        $this->ach_result = $this->calculateAchResult();
        $this->ach_proses = $this->calculateAchProses();
    }

    /**
     * Helper: Get all achievement fields as array
     */
    public function getAchievementsArray(): array
    {
        return [
            'result' => [
                'ach_revenue_plan' => $this->ach_revenue_plan,
                'ach_scaling' => $this->ach_scaling,
                'ach_sales_datin' => $this->ach_sales_datin,
                'ach_hsi' => $this->ach_hsi,
                'ach_wireline' => $this->ach_wireline,
                'ach_wifi' => $this->ach_wifi,
                'ach_cyc' => $this->ach_cyc,
                'ach_cr' => $this->ach_cr,
                'ach_profit' => $this->ach_profit,
                'ach_nps' => $this->ach_nps,
                'total' => $this->ach_result,
            ],
            'proses' => [
                'ach_maps' => $this->ach_maps,
                'ach_lop' => $this->ach_lop,
                'ach_capability' => $this->ach_capability,
                'ach_cc' => $this->ach_cc,
                'total' => $this->ach_proses,
            ],
        ];
    }
}
