<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model Company
 * 
 * Represents companies/enterprises managed by Account Managers
 * 
 * @property string $nip_nas Primary Key - Nomor Induk Perusahaan
 * @property string $nama_perusahaan Nama lengkap perusahaan
 * @property string $subsegment Sub-segment perusahaan (PTN, PTS, Hospital, dll)
 * @property string $source_data Sumber data (TIBS-NP, SISKA, NGTMA)
 * 
 * Relations:
 * - accountManagers: BelongsToMany (Many-to-Many via pivot account_manager_company)
 * - revenues: HasMany (One-to-Many dengan Revenue)
 * 
 * PERUBAHAN DARI STRUKTUR LAMA:
 * - Primary key berubah dari 'id' ke 'nip_nas'
 * - Hapus: primary_region_id, primary_witel_id
 * - Hapus: regions(), witels(), companyRegions() relations (tidak digunakan lagi)
 * - Tambah: accountManagers() relation
 */
class Company extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'companies';

    /**
     * The primary key associated with the table.
     * Changed from default 'id' to 'nip_nas'
     */
    protected $primaryKey = 'nip_nas';

    /**
     * The "type" of the primary key ID.
     * nip_nas is string, not integer
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * nip_nas is not auto-increment
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     * 
     * PERUBAHAN: Hapus 'status', 'primary_region_id', 'primary_witel_id'
     */
    protected $fillable = [
        'nip_nas',
        'nama_perusahaan',
        'subsegment',
        'source_data',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Common subsegment options
     */
    const SUBSEGMENT_PTN = 'PTN';
    const SUBSEGMENT_PTS = 'PTS';
    const SUBSEGMENT_HOSPITAL = 'Hospital';
    const SUBSEGMENT_AIRPORT = 'Airport';
    const SUBSEGMENT_BANK = 'Bank';
    const SUBSEGMENT_GOVERNMENT = 'Government';

    /**
     * Common source data options
     */
    const SOURCE_TIBS_NP = 'TIBS-NP';
    const SOURCE_SISKA = 'SISKA';
    const SOURCE_NGTMA = 'NGTMA';

    /**
     * RELATION: Company has many AccountManagers (Many-to-Many)
     * Satu company bisa dihandle oleh multiple Account Managers
     * Pivot table: account_manager_company
     * Pivot columns: proporsi, pembagian, segment
     */
    public function accountManagers(): BelongsToMany
    {
        return $this->belongsToMany(
            AccountManager::class,
            'account_manager_company',
            'nip_nas',
            'nik_am',
            'nip_nas',
            'nik'
        )->withPivot('proporsi', 'pembagian', 'segment')
          ->withTimestamps();
    }

    /**
     * RELATION: Company has many Revenues (One-to-Many)
     * Satu company punya multiple revenue records per bulan/tahun
     */
    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class, 'nip_nas', 'nip_nas');
    }

    /**
     * Helper: Get total revenue by year
     * 
     * PERUBAHAN: Field 'revenue' berubah jadi 'total_revenue'
     */
    public function getRevenueByYear(int $year): float
    {
        return $this->revenues()
            ->where('tahun', $year)
            ->sum('total_revenue');
    }

    /**
     * Helper: Get revenue by specific month
     * 
     * PERUBAHAN: Field 'revenue' berubah jadi 'total_revenue'
     */
    public function getRevenueByMonth(int $year, int $month): float
    {
        return $this->revenues()
            ->where('tahun', $year)
            ->where('bulan', $month)
            ->sum('total_revenue');
    }

    /**
     * Helper: Get total revenue all time
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->revenues()->sum('total_revenue');
    }

    /**
     * Helper: Check if company is handled by specific AM
     */
    public function isHandledBy(string $nik_am): bool
    {
        return $this->accountManagers()->where('nik_am', $nik_am)->exists();
    }

    /**
     * Helper: Get primary Account Manager (proporsi terbesar)
     */
    public function getPrimaryAccountManagerAttribute()
    {
        return $this->accountManagers()
            ->orderByPivot('proporsi', 'desc')
            ->first();
    }

    /**
     * Scope: Filter by subsegment
     */
    public function scopeBySubsegment($query, string $subsegment)
    {
        return $query->where('subsegment', $subsegment);
    }

    /**
     * Scope: Filter by source data
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source_data', $source);
    }

    /**
     * Scope: Companies with revenues in specific year
     */
    public function scopeWithRevenueInYear($query, int $year)
    {
        return $query->whereHas('revenues', function ($q) use ($year) {
            $q->where('tahun', $year);
        });
    }
}
