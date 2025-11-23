<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Revenue
 * 
 * Represents monthly revenue data for Group4 products
 * This is the normalized time-series table separated from product master data
 * 
 * @property int $id Primary Key
 * @property int $group4_id FK to group4 (product master)
 * @property int $tahun Year
 * @property int $bulan Month (1-12)
 * @property float $revenue_realisasi Actual revenue amount
 * @property float $revenue_target Target revenue for this period
 * 
 * Relations:
 * - group4: BelongsTo (Many-to-One with Group4 product)
 * 
 * PERUBAHAN STRUKTUR TERBARU (Nov 2025):
 * - Table revenues sekarang untuk group4 time-series data
 * - FK: group4_id (stable product ID)
 * - Fields: revenue_realisasi, revenue_target (bukan total_revenue)
 * - UNIQUE constraint: (group4_id, tahun, bulan) - one record per product per month
 */
class Revenue extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'revenues';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'group4_id',
        'tahun',
        'bulan',
        'revenue_realisasi',
        'revenue_target',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'revenue_realisasi' => 'decimal:2',
        'revenue_target' => 'decimal:2',
        'tahun' => 'integer',
        'bulan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Month names in Indonesian
     */
    const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    /**
     * RELATION: Revenue belongs to Group4 product (Many-to-One)
     * FK references group4.idGroup4 (not 'id')
     */
    public function group4(): BelongsTo
    {
        return $this->belongsTo(Group4::class, 'group4_id', 'idGroup4');
    }

    /**
     * Accessor: Get formatted revenue realisasi
     */
    public function getFormattedRevenueAttribute(): string
    {
        return 'Rp ' . number_format($this->revenue_realisasi, 0, ',', '.');
    }

    /**
     * Accessor: Get formatted target
     */
    public function getFormattedTargetAttribute(): string
    {
        return 'Rp ' . number_format($this->revenue_target, 0, ',', '.');
    }

    /**
     * Accessor: Get month name in Indonesian
     */
    public function getBulanNameAttribute(): string
    {
        return self::MONTH_NAMES[$this->bulan] ?? '';
    }

    /**
     * Accessor: Get period label (e.g., "Januari 2024")
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->bulan_name . ' ' . $this->tahun;
    }

    /**
     * Helper: Calculate achievement percentage (realisasi vs target)
     */
    public function getAchievementPercentageAttribute(): float
    {
        if ($this->revenue_target == 0) {
            return 0;
        }

        return ($this->revenue_realisasi / $this->revenue_target) * 100;
    }

    /**
     * Helper: Calculate variance (difference between revenue and target)
     */
    public function getVarianceAttribute(): float
    {
        return $this->revenue_realisasi - $this->revenue_target;
    }

    /**
     * Helper: Check if target is achieved
     */
    public function isTargetAchieved(): bool
    {
        return $this->revenue_realisasi >= $this->revenue_target;
    }

    /**
     * Scope: Filter by year
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('tahun', $year);
    }

    /**
     * Scope: Filter by month
     */
    public function scopeByMonth($query, int $month)
    {
        return $query->where('bulan', $month);
    }

    /**
     * Scope: Filter by period (year and month)
     */
    public function scopeByPeriod($query, int $year, int $month)
    {
        return $query->where('tahun', $year)->where('bulan', $month);
    }

    /**
     * Scope: Filter by product (group4)
     */
    public function scopeByProduct($query, int $group4_id)
    {
        return $query->where('group4_id', $group4_id);
    }

    /**
     * Scope: Revenues above target
     */
    public function scopeAboveTarget($query)
    {
        return $query->whereRaw('revenue_realisasi >= revenue_target');
    }

    /**
     * Scope: Revenues below target
     */
    public function scopeBelowTarget($query)
    {
        return $query->whereRaw('revenue_realisasi < revenue_target');
    }

    /**
     * Scope: Order by revenue descending
     */
    public function scopeHighestRevenue($query)
    {
        return $query->orderBy('revenue_realisasi', 'desc');
    }
}
