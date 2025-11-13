<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Model Region
 * 
 * Represents Telkom Regions (HQ TREG2, TREG1-5)
 * 
 * @property int $id Primary Key
 * @property string $code Kode region (HQ TREG2, TREG1, TREG2, TREG3, TREG4, TREG5)
 * @property string $name Nama region
 * @property string $description Deskripsi wilayah cakupan
 * 
 * Relations:
 * - witels: HasMany (One-to-Many dengan Witel)
 * - accountManagers: HasManyThrough (via Witel)
 * 
 * PERUBAHAN DARI STRUKTUR LAMA:
 * - Tambah: code dengan ENUM (standardize)
 * - Tambah: description field
 * - Hapus: companies() relation (tidak ada direct FK lagi)
 * - Hapus: companyRegions() relation (table sudah dihapus)
 * - Hapus: revenues() relation (tidak ada direct FK lagi)
 */
class Region extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'regions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'description'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Region code constants
     */
    const CODE_HQ = 'HQ TREG2';
    const CODE_TREG1 = 'TREG1';
    const CODE_TREG2 = 'TREG2';
    const CODE_TREG3 = 'TREG3';
    const CODE_TREG4 = 'TREG4';
    const CODE_TREG5 = 'TREG5';

    /**
     * Get all available region codes
     */
    public static function getRegionCodes(): array
    {
        return [
            self::CODE_HQ => 'Headquarters TREG2',
            self::CODE_TREG1 => 'Telkom Regional 1 (Sumatera)',
            self::CODE_TREG2 => 'Telkom Regional 2 (Jakarta, Banten, Jabar)',
            self::CODE_TREG3 => 'Telkom Regional 3 (Jateng & DIY)',
            self::CODE_TREG4 => 'Telkom Regional 4 (Jawa Timur)',
            self::CODE_TREG5 => 'Telkom Regional 5 (Bali, NTT, Kaltim, dll)',
        ];
    }

    /**
     * RELATION: Region has many Witels (One-to-Many)
     * Setiap region punya multiple WITEL di bawahnya
     */
    public function witels(): HasMany
    {
        return $this->hasMany(Witel::class, 'region_id', 'id');
    }

    /**
     * RELATION: Region has many Account Managers through Witels
     * Access AM via witel yang berada di region ini
     */
    public function accountManagers(): HasManyThrough
    {
        return $this->hasManyThrough(
            AccountManager::class,
            Witel::class,
            'region_id',  // Foreign key on witels table
            'idwitels',   // Foreign key on account_managers table
            'id',         // Local key on regions table
            'idwitels'    // Local key on witels table
        );
    }

    /**
     * Helper: Get total witels in this region
     */
    public function getTotalWitelsAttribute(): int
    {
        return $this->witels()->count();
    }

    /**
     * Helper: Get total account managers in this region
     */
    public function getTotalAccountManagersAttribute(): int
    {
        return $this->accountManagers()->count();
    }

    /**
     * Scope: Filter by region code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope: Exclude HQ
     */
    public function scopeExcludeHQ($query)
    {
        return $query->where('code', '!=', self::CODE_HQ);
    }

    /**
     * Helper: Check if this is HQ region
     */
    public function isHQ(): bool
    {
        return $this->code === self::CODE_HQ;
    }
}
