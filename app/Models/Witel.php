<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Model Witel
 * 
 * Represents WITEL (Wilayah Telekomunikasi) - Regional telecommunication offices
 * 
 * @property int $idwitels Primary Key - Custom ID WITEL
 * @property string $nama_witels Nama WITEL
 * @property int $region_id FK to regions
 * 
 * Relations:
 * - region: BelongsTo (Many-to-One dengan Region)
 * - accountManager: HasOne (One-to-One dengan AccountManager)
 * - companies: HasManyThrough (via AccountManager)
 * 
 * PERUBAHAN DARI STRUKTUR LAMA:
 * - Primary key berubah dari 'id' ke 'idwitels' (custom INT)
 * - Field 'name' berubah jadi 'nama_witels'
 * - Hapus: code, province, description fields
 * - Hapus: companies() direct relation (akses via AccountManager sekarang)
 * - Hapus: companyRegions() relation (table sudah dihapus)
 * - Hapus: revenues() direct relation (akses via companies)
 * - Tambah: accountManager() One-to-One relation
 */
class Witel extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'witels';

    /**
     * The primary key associated with the table.
     * Changed from default 'id' to 'idwitels'
     */
    protected $primaryKey = 'idwitels';

    /**
     * Indicates if the IDs are auto-incrementing.
     * idwitels is custom ID, not auto-increment
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'idwitels',
        'nama_witels',
        'region_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'idwitels' => 'integer',
        'region_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATION: Witel belongs to Region (Many-to-One)
     * Setiap WITEL berada di bawah satu Region
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /**
     * RELATION: Witel has one AccountManager (One-to-One)
     * Setiap WITEL dikelola oleh satu Account Manager
     */
    public function accountManager(): HasOne
    {
        return $this->hasOne(AccountManager::class, 'idwitels', 'idwitels');
    }

    /**
     * RELATION: Witel has many Companies through AccountManager
     * Access companies yang dihandle oleh AM di witel ini
     */
    public function companies(): HasManyThrough
    {
        return $this->hasManyThrough(
            Company::class,
            AccountManager::class,
            'idwitels',      // Foreign key on account_managers table
            'nip_nas',       // Foreign key on companies table (via pivot)
            'idwitels',      // Local key on witels table
            'nik'            // Local key on account_managers table
        );
    }

    /**
     * Helper: Get total account managers in this witel
     */
    public function getTotalAccountManagersAttribute(): int
    {
        return AccountManager::where('idwitels', $this->idwitels)->count();
    }

    /**
     * Helper: Get total companies handled in this witel
     */
    public function getTotalCompaniesAttribute(): int
    {
        if ($this->accountManager) {
            return $this->accountManager->companies()->count();
        }
        return 0;
    }

    /**
     * Scope: Filter by region
     */
    public function scopeByRegion($query, int $region_id)
    {
        return $query->where('region_id', $region_id);
    }

    /**
     * Scope: With account manager data
     */
    public function scopeWithAccountManager($query)
    {
        return $query->with('accountManager');
    }

    /**
     * Helper: Check if witel has assigned account manager
     */
    public function hasAccountManager(): bool
    {
        return $this->accountManager()->exists();
    }
}
