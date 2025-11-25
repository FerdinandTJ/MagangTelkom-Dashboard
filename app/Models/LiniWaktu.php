<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Model LiniWaktu
 * 
 * Represents timeline periods (quarterly) for tracking targets and achievements
 * 
 * @property int $id Primary Key
 * @property string $quartal Quarter (Q1, Q2, Q3, Q4)
 * @property Carbon $bulan_awal Start date of quarter
 * @property Carbon $bulan_akhir End date of quarter
 * @property int $tahun Year
 * @property string $nik_am FK to account_managers
 * 
 * Relations:
 * - accountManager: BelongsTo (Many-to-One dengan AccountManager)
 * - targets: BelongsToMany (Many-to-Many dengan TargetAccountM via pivot)
 * 
 * Quartal Mapping:
 * - Q1: Januari - Maret
 * - Q2: April - Juni
 * - Q3: Juli - September
 * - Q4: Oktober - Desember
 */
class LiniWaktu extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'lini_waktu';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quartal',
        'bulan_awal',
        'bulan_akhir',
        'tahun',
        'nik_am',
        'percentage_result',
        'percentage_revenue',
        'percentage_scaling',
        'percentage_datin',
        'percentage_hsi',
        'percentage_wireline',
        'percentage_wifi',
        'percentage_cyc',
        'percentage_cr',
        'percentage_profit',
        'percentage_customer',
        'percentage_proses',
        'percentage_maps',
        'percentage_lop',
        'percentage_capability',
        'percentage_cc',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'bulan_awal' => 'datetime',
        'bulan_akhir' => 'datetime',
        'tahun' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quartal constants
     */
    const QUARTAL_Q1 = 'Q1';
    const QUARTAL_Q2 = 'Q2';
    const QUARTAL_Q3 = 'Q3';
    const QUARTAL_Q4 = 'Q4';

    /**
     * The "booted" method of the model.
     * Add validation on saving to enforce percentage constraints
     */
    protected static function booted(): void
    {
        // Validate before saving (creating or updating)
        static::saving(function (LiniWaktu $liniWaktu) {
            $liniWaktu->validatePercentages();
        });
    }

    /**
     * Validate percentage constraints
     * 
     * @throws ValidationException
     */
    public function validatePercentages(): void
    {
        // Rule 1: percentage_result + percentage_proses = 100%
        $totalResultAndProses = round($this->percentage_result + $this->percentage_proses, 3);
        if ($totalResultAndProses !== 100.0) {
            throw ValidationException::withMessages([
                'percentage_result' => "The sum of percentage_result ({$this->percentage_result}%) and percentage_proses ({$this->percentage_proses}%) must equal 100%. Current: {$totalResultAndProses}%"
            ]);
        }

        // Rule 2: Result sub-percentages must sum to percentage_result
        $resultSubTotal = round(
            $this->percentage_revenue +
            $this->percentage_scaling +
            $this->percentage_datin +
            $this->percentage_hsi +
            $this->percentage_wireline +
            $this->percentage_wifi +
            $this->percentage_cyc +
            $this->percentage_cr +
            $this->percentage_profit +
            $this->percentage_customer,
            3
        );

        if ($resultSubTotal !== round($this->percentage_result, 3)) {
            throw ValidationException::withMessages([
                'percentage_revenue' => "The sum of result sub-percentages ({$resultSubTotal}%) must equal percentage_result ({$this->percentage_result}%)"
            ]);
        }

        // Rule 3: Process sub-percentages must sum to percentage_proses
        $prosesSubTotal = round(
            $this->percentage_maps +
            $this->percentage_lop +
            $this->percentage_capability +
            $this->percentage_cc,
            3
        );

        if ($prosesSubTotal !== round($this->percentage_proses, 3)) {
            throw ValidationException::withMessages([
                'percentage_maps' => "The sum of process sub-percentages ({$prosesSubTotal}%) must equal percentage_proses ({$this->percentage_proses}%)"
            ]);
        }
    }

    /**
     * Get quartal options
     */
    public static function getQuartalOptions(): array
    {
        return [
            self::QUARTAL_Q1 => 'Q1 (Januari - Maret)',
            self::QUARTAL_Q2 => 'Q2 (April - Juni)',
            self::QUARTAL_Q3 => 'Q3 (Juli - September)',
            self::QUARTAL_Q4 => 'Q4 (Oktober - Desember)',
        ];
    }

    /**
     * Helper: Get start and end month for quartal
     */
    public static function getQuartalMonths(string $quartal): array
    {
        return match ($quartal) {
            self::QUARTAL_Q1 => ['start' => 1, 'end' => 3],
            self::QUARTAL_Q2 => ['start' => 4, 'end' => 6],
            self::QUARTAL_Q3 => ['start' => 7, 'end' => 9],
            self::QUARTAL_Q4 => ['start' => 10, 'end' => 12],
            default => ['start' => 1, 'end' => 3],
        };
    }

    /**
     * RELATION: LiniWaktu belongs to AccountManager (Many-to-One)
     * Setiap periode waktu dimiliki oleh satu AM
     */
    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(AccountManager::class, 'nik_am', 'nik');
    }

    /**
     * RELATION: LiniWaktu has many Targets (Many-to-Many)
     * Pivot table: lini_waktu_target (berisi realisasi dan achievement)
     * Pivot model: LiniWaktuTarget (dengan validation constraints)
     */
    public function targets(): BelongsToMany
    {
        return $this->belongsToMany(
            TargetAccountM::class,
            'lini_waktu_target',
            'lini_waktu_id',
            'target_id'
        )->using(LiniWaktuTarget::class)
        ->withPivot([
            // Realization fields
            'r_revenue',
            'r_scalling',
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
        ])->withTimestamps();
    }

    /**
     * Scope: Filter by tahun
     */
    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope: Filter by quartal
     */
    public function scopeByQuartal($query, string $quartal)
    {
        return $query->where('quartal', $quartal);
    }

    /**
     * Scope: Filter by account manager
     */
    public function scopeByAccountManager($query, string $nik_am)
    {
        return $query->where('nik_am', $nik_am);
    }

    /**
     * Scope: Current quarter
     */
    public function scopeCurrentQuarter($query)
    {
        $now = Carbon::now();
        return $query->where('bulan_awal', '<=', $now)
                     ->where('bulan_akhir', '>=', $now);
    }

    /**
     * Helper: Check if period is active (current)
     */
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $now->between($this->bulan_awal, $this->bulan_akhir);
    }

    /**
     * Accessor: Get formatted period name
     */
    public function getPeriodNameAttribute(): string
    {
        return "{$this->quartal} {$this->tahun}";
    }

    /**
     * Accessor: Get quartal label
     */
    public function getQuartalLabelAttribute(): string
    {
        return self::getQuartalOptions()[$this->quartal] ?? $this->quartal;
    }

    /**
     * Helper: Auto-set bulan_awal and bulan_akhir based on quartal and tahun
     */
    public function setDatesFromQuartal(): void
    {
        $months = self::getQuartalMonths($this->quartal);
        
        $this->bulan_awal = Carbon::create($this->tahun, $months['start'], 1)->startOfMonth();
        $this->bulan_akhir = Carbon::create($this->tahun, $months['end'], 1)->endOfMonth();
    }
}
