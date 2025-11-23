<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Group4 Model - Product Master Data (Normalized)
 * 
 * This model represents product definitions only (no time-series revenue data).
 * Revenue data is stored in the separate 'revenues' table.
 * 
 * PERUBAHAN STRUKTUR (Nov 2025):
 * - Primary key: 'id' (bukan 'idGroup4' lagi)
 * - Removed fields: revenue_realisasi, revenue_target, tahun, bulan
 * - Added field: product_code (unique identifier)
 * - Benefit: Stable product ID across all time periods
 */
class Group4 extends Model
{
    protected $table = 'group4';
    
    // Primary key is 'idGroup4' (not changed to 'id' yet - keeping legacy name)
    protected $primaryKey = 'idGroup4';
    
    protected $fillable = [
        'nama_group4',
        'group3_id',
    ];

    protected $casts = [
        'group3_id' => 'integer',
    ];

    /**
     * Get the group3 that owns this group4
     */
    public function group3(): BelongsTo
    {
        return $this->belongsTo(Group3::class, 'group3_id', 'idGroup3');
    }

    /**
     * Get all revenue records for this product
     * One product can have multiple monthly revenue records
     */
    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class, 'group4_id', 'idGroup4');
    }

    /**
     * Get revenue for a specific period
     */
    public function revenueForPeriod(int $year, int $month)
    {
        return $this->revenues()
            ->where('tahun', $year)
            ->where('bulan', $month)
            ->first();
    }

    /**
     * Get total revenue for a year
     */
    public function yearlyRevenue(int $year)
    {
        return $this->revenues()
            ->where('tahun', $year)
            ->sum('revenue_realisasi');
    }

    /**
     * Get YTD revenue (Year-to-Date)
     */
    public function ytdRevenue(int $year, int $month)
    {
        return $this->revenues()
            ->where('tahun', $year)
            ->where('bulan', '<=', $month)
            ->sum('revenue_realisasi');
    }
}
