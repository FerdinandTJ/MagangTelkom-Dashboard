<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Company
 * 
 * Represents companies/enterprises managed by Account Managers
 * 
 * @property string $nip_nas Primary Key - Nomor Induk Perusahaan
 * @property string $nama_perusahaan Nama lengkap perusahaan
 * @property string $subsegment Sub-segment perusahaan (PTN, PTS, Hospital, dll)
 * @property string $source_data Sumber data (TIBS-NP, SISKA, NGTMA)
 * @property int|null $idwitels FK to witels - Witel tempat company berada
 * 
 * Relations:
 * - witel: BelongsTo (One-to-One dengan Witel)
 * - accountManagers: BelongsToMany (Many-to-Many via pivot account_manager_company)
 * - revenues: HasMany (One-to-Many dengan Revenue)
 * 
 * PERUBAHAN DARI STRUKTUR LAMA:
 * - Primary key berubah dari 'id' ke 'nip_nas'
 * - Hapus: primary_region_id, primary_witel_id
 * - Hapus: regions(), witels(), companyRegions() relations (tidak digunakan lagi)
 * - Tambah: accountManagers() relation
 * - Tambah: idwitels FK dan witel() relation (One-to-One)
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
     * TAMBAH: idwitels (FK to witels)
     */
    protected $fillable = [
        'nip_nas',
        'nama_perusahaan',
        'subsegment',
        'source_data',
        'idwitels',
        'target',
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
     * RELATION: Company belongs to Witel (One-to-One)
     * Satu company berada di satu witel tertentu
     */
    public function witel(): BelongsTo
    {
        return $this->belongsTo(Witel::class, 'idwitels', 'idwitels');
    }

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
        // The old `revenues` table has been removed. Keep a placeholder relation
        // in case other parts reference it in the future. If the model/class
        // doesn't exist this will be null-safe.
        try {
            return $this->hasMany(\App\Models\Revenue::class, 'nip_nas', 'nip_nas');
        } catch (\Throwable $e) {
            // If Revenue model/table no longer exists, return an empty relation-like object
            return $this->hasMany(\App\Models\Group4::class, 'group3_id', 'nip_nas');
        }
    }

    /**
     * RELATION: Company has many Group1 (One-to-Many)
     * Satu company punya multiple group1 revenue breakdown
     */
    public function group1s(): HasMany
    {
        return $this->hasMany(Group1::class, 'company_id', 'nip_nas');
    }

    /**
     * Helper: Get total revenue by year
     * 
     * PERUBAHAN: Field 'revenue' berubah jadi 'total_revenue'
     */
    public function getRevenueByYear(int $year): float
    {
        // Sum realisasi from group4 filtered by year
        return (float) \DB::table('group4')
            ->join('group3', 'group4.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('group1.company_id', $this->nip_nas)
            ->where('group4.tahun', $year)
            ->sum('group4.revenue_realisasi');
    }

    /**
     * Helper: Get revenue by specific month
     * 
     * PERUBAHAN: Field 'revenue' berubah jadi 'total_revenue'
     */
    public function getRevenueByMonth(int $year, int $month): float
    {
        return (float) \DB::table('group4')
            ->join('group3', 'group4.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('group1.company_id', $this->nip_nas)
            ->where('group4.tahun', $year)
            ->where('group4.bulan', $month)
            ->sum('group4.revenue_realisasi');
    }

    /**
     * Helper: Get total revenue all time
     */
    public function getTotalRevenueAttribute(): float
    {
        return (float) \DB::table('group4')
            ->join('group3', 'group4.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('group1.company_id', $this->nip_nas)
            ->sum('group4.revenue_realisasi');
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
