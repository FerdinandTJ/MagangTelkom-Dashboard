<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Revenue
 * 
 * Represents monthly revenue data for companies
 * 
 * @property int $id Primary Key
 * @property string $nip_nas FK to companies
 * @property int $tahun Year
 * @property int $bulan Month (1-12)
 * @property float $total_revenue Total revenue amount
 * @property string|null $note Notes
 * @property float $target Target revenue for this period
 * 
 * Relations:
 * - company: BelongsTo (Many-to-One dengan Company)
 * 
 * PERUBAHAN DARI STRUKTUR LAMA:
 * - FK berubah dari company_id (INT) ke nip_nas (VARCHAR)
 * - Field 'revenue' berubah jadi 'total_revenue'
 * - Tambah field: 'note', 'target'
 * - Hapus: region_id, witel_id (tidak ada direct FK lagi)
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
     * 
     * PERUBAHAN: Ganti company_id dengan nip_nas, revenue jadi total_revenue
     * Hapus: region_id, witel_id
     * Tambah: note, target
     */
    protected $fillable = [
        'nip_nas',
        'tahun',
        'bulan',
        'total_revenue',
        'note',
        'target',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_revenue' => 'decimal:6',
        'target' => 'decimal:2',
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
     * RELATION: Revenue belongs to Company (Many-to-One)
     * FK menggunakan nip_nas (bukan company_id lagi)
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'nip_nas', 'nip_nas');
    }

    /**
     * Accessor: Get formatted revenue
     */
    public function getFormattedRevenueAttribute(): string
    {
        return 'Rp ' . number_format($this->total_revenue, 0, ',', '.');
    }

    /**
     * Accessor: Get formatted target
     */
    public function getFormattedTargetAttribute(): string
    {
        return 'Rp ' . number_format($this->target, 0, ',', '.');
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
        if ($this->target == 0) {
            return 0;
        }

        return ($this->total_revenue / $this->target) * 100;
    }

    /**
     * Helper: Calculate variance (difference between revenue and target)
     */
    public function getVarianceAttribute(): float
    {
        return $this->total_revenue - $this->target;
    }

    /**
     * Helper: Check if target is achieved
     */
    public function isTargetAchieved(): bool
    {
        return $this->total_revenue >= $this->target;
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
     * Scope: Filter by company
     */
    public function scopeByCompany($query, string $nip_nas)
    {
        return $query->where('nip_nas', $nip_nas);
    }

    /**
     * Scope: Revenues above target
     */
    public function scopeAboveTarget($query)
    {
        return $query->whereRaw('total_revenue >= target');
    }

    /**
     * Scope: Revenues below target
     */
    public function scopeBelowTarget($query)
    {
        return $query->whereRaw('total_revenue < target');
    }

    /**
     * Scope: Order by revenue descending
     */
    public function scopeHighestRevenue($query)
    {
        return $query->orderBy('total_revenue', 'desc');
    }
}
