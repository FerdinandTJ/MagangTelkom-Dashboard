<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model TargetAccountM
 * 
 * Represents KPI targets for Account Managers
 * 
 * @property int $id Primary Key
 * @property int|null $account_manager_company_id FK to account_manager_company.id
 * @property float $t_revenue Target Revenue
 * @property float $t_scalling Target Scalling
 * @property float $t_datin Target Data Internet
 * @property float $t_hsi Target HSI
 * @property float $t_wireline Target Wireline
 * @property float $t_wifi Target WiFi
 * @property float $t_cyc Target CYC
 * @property float $t_cr Target CR (Churn Rate)
 * @property float $t_profit Target Profit
 * @property float $t_nps Target NPS
 * @property float $t_maps Target MAPS
 * @property float $t_lop Target LOP
 * @property float $t_capability Target Capability
 * @property float $t_cc Target CC
 * @property float $t_ngtma Target NGTMA
 * @property float $t_sustain Target Sustain
 * 
 * Relations:
 * - accountManagerCompany: BelongsTo (One-to-One with account_manager_company)
 * - liniWaktu: BelongsToMany (Many-to-Many via pivot lini_waktu_target)
 */
class TargetAccountM extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'target_account_m';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'account_manager_company_id',
        't_revenue',
        't_scalling',
        't_datin',
        't_hsi',
        't_wireline',
        't_wifi',
        't_cyc',
        't_cr',
        't_profit',
        't_nps',
        't_maps',
        't_lop',
        't_capability',
        't_cc',
        't_ngtma',
        't_sustain',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        't_revenue' => 'decimal:2',
        't_scalling' => 'decimal:2',
        't_datin' => 'decimal:2',
        't_hsi' => 'decimal:2',
        't_wireline' => 'decimal:2',
        't_wifi' => 'decimal:2',
        't_cyc' => 'decimal:2',
        't_cr' => 'decimal:2',
        't_profit' => 'decimal:2',
        't_nps' => 'decimal:2',
        't_maps' => 'decimal:2',
        't_lop' => 'decimal:2',
        't_capability' => 'decimal:2',
        't_cc' => 'decimal:2',
        't_ngtma' => 'decimal:2',
        't_sustain' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * KPI field labels
     */
    public static function getKPILabels(): array
    {
        return [
            't_revenue' => 'Revenue',
            't_scalling' => 'Scalling',
            't_datin' => 'Data Internet',
            't_hsi' => 'HSI (High Speed Internet)',
            't_wireline' => 'Wireline',
            't_wifi' => 'WiFi',
            't_cyc' => 'CYC (Customer Yield per Customer)',
            't_cr' => 'CR (Churn Rate)',
            't_profit' => 'Profit',
            't_nps' => 'NPS (Net Promoter Score)',
            't_maps' => 'MAPS',
            't_lop' => 'LOP (Length of Payment)',
            't_capability' => 'Capability',
            't_cc' => 'CC (Customer Count)',
            't_ngtma' => 'NGTMA',
            't_sustain' => 'Sustain',
        ];
    }

    /**
     * RELATION: TargetAccountM belongs to AccountManagerCompany (One-to-One)
     * Each target is linked to a specific AM-Company assignment
     */
    public function accountManagerCompany(): BelongsTo
    {
        return $this->belongsTo(AccountManagerCompany::class, 'account_manager_company_id', 'id');
    }

    /**
     * RELATION: TargetAccountM has many LiniWaktu (Many-to-Many)
     * Pivot table: lini_waktu_target (berisi realisasi)
     * Pivot columns: r_revenue, r_scalling, r_datin, dll (14 fields realisasi)
     */
    public function liniWaktu(): BelongsToMany
    {
        return $this->belongsToMany(
            LiniWaktu::class,
            'lini_waktu_target',
            'target_id',
            'lini_waktu_id'
        )->withPivot([
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
        ])->withTimestamps();
    }

    /**
     * Helper: Get realisasi for specific periode
     */
    public function getRealisasiForPeriod(int $lini_waktu_id): ?object
    {
        $pivot = $this->liniWaktu()->where('lini_waktu_id', $lini_waktu_id)->first();
        
        if (!$pivot) {
            return null;
        }

        return (object) [
            'revenue' => $pivot->pivot->r_revenue,
            'scalling' => $pivot->pivot->r_scalling,
            'datin' => $pivot->pivot->r_datin,
            'hsi' => $pivot->pivot->r_hsi,
            'wireline' => $pivot->pivot->r_wireline,
            'wifi' => $pivot->pivot->r_wifi,
            'cyc' => $pivot->pivot->r_cyc,
            'cr' => $pivot->pivot->r_cr,
            'profit' => $pivot->pivot->r_profit,
            'nps' => $pivot->pivot->r_nps,
            'maps' => $pivot->pivot->r_maps,
            'lop' => $pivot->pivot->r_lop,
            'capability' => $pivot->pivot->r_capability,
            'cc' => $pivot->pivot->r_cc,
        ];
    }

    /**
     * Helper: Calculate achievement percentage for specific KPI
     */
    public function calculateAchievement(string $kpi, float $realisasi): float
    {
        $target = $this->{"t_$kpi"} ?? 0;
        
        if ($target == 0) {
            return 0;
        }

        return ($realisasi / $target) * 100;
    }

    /**
     * Helper: Get all targets as array
     */
    public function getTargetsArray(): array
    {
        return [
            'revenue' => $this->t_revenue,
            'scalling' => $this->t_scalling,
            'datin' => $this->t_datin,
            'hsi' => $this->t_hsi,
            'wireline' => $this->t_wireline,
            'wifi' => $this->t_wifi,
            'cyc' => $this->t_cyc,
            'cr' => $this->t_cr,
            'profit' => $this->t_profit,
            'nps' => $this->t_nps,
            'maps' => $this->t_maps,
            'lop' => $this->t_lop,
            'capability' => $this->t_capability,
            'cc' => $this->t_cc,
            'ngtma' => $this->t_ngtma,
            'sustain' => $this->t_sustain,
        ];
    }

    /**
     * Helper: Get total target value (sum of all targets)
     */
    public function getTotalTargetAttribute(): float
    {
        return $this->t_revenue 
             + $this->t_scalling 
             + $this->t_datin 
             + $this->t_ngtma 
             + $this->t_sustain 
             + $this->t_lop;
    }

    /**
     * Scope: Targets with high revenue (above certain amount)
     */
    public function scopeHighRevenue($query, float $minRevenue = 1000000)
    {
        return $query->where('t_revenue', '>=', $minRevenue);
    }
}
