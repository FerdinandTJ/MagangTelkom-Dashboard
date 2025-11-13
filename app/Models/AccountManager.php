<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model AccountManager
 * 
 * Represents Account Managers who manage companies and have KPI targets
 * 
 * @property string $nik Primary Key - NIK Account Manager
 * @property string $nama Nama lengkap Account Manager
 * @property string $posisi Jabatan (AM, AM1, AM1PRO, AM2, AM2PRO, AM3, EAM, SAM)
 * @property string|null $no_gsm Nomor telepon
 * @property int|null $idwitels FK to witels
 * 
 * Relations:
 * - witel: BelongsTo (One-to-One dengan Witel)
 * - companies: BelongsToMany (Many-to-Many via pivot account_manager_company)
 * - liniWaktu: HasMany (One-to-Many dengan LiniWaktu)
 */
class AccountManager extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'account_managers';

    /**
     * The primary key associated with the table.
     * Changed from default 'id' to 'nik'
     */
    protected $primaryKey = 'nik';

    /**
     * The "type" of the primary key ID.
     * NIK is string, not integer
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * NIK is not auto-increment
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nik',
        'nama',
        'posisi',
        'no_gsm',
        'idwitels',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Posisi/Jabatan constants
     */
    const POSISI_AM = 'AM';
    const POSISI_AM1 = 'AM1';
    const POSISI_AM1PRO = 'AM1PRO';
    const POSISI_AM2 = 'AM2';
    const POSISI_AM2PRO = 'AM2PRO';
    const POSISI_AM3 = 'AM3';
    const POSISI_EAM = 'EAM';
    const POSISI_SAM = 'SAM';

    /**
     * Get all available posisi options
     */
    public static function getPosisiOptions(): array
    {
        return [
            self::POSISI_AM => 'Account Manager',
            self::POSISI_AM1 => 'Account Manager 1',
            self::POSISI_AM1PRO => 'Account Manager 1 Professional',
            self::POSISI_AM2 => 'Account Manager 2',
            self::POSISI_AM2PRO => 'Account Manager 2 Professional',
            self::POSISI_AM3 => 'Account Manager 3',
            self::POSISI_EAM => 'Enterprise Account Manager',
            self::POSISI_SAM => 'Senior Account Manager',
        ];
    }

    /**
     * RELATION: AccountManager belongs to Witel (One-to-One)
     * Setiap AM ditugaskan di satu WITEL
     */
    public function witel(): BelongsTo
    {
        return $this->belongsTo(Witel::class, 'idwitels', 'idwitels');
    }

    /**
     * RELATION: AccountManager has many Companies (Many-to-Many)
     * Satu AM bisa handle multiple companies
     * Pivot table: account_manager_company
     * Pivot columns: proporsi, pembagian, segment
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(
            Company::class,
            'account_manager_company',
            'nik_am',
            'nip_nas',
            'nik',
            'nip_nas'
        )->withPivot('proporsi', 'pembagian', 'segment')
          ->withTimestamps();
    }

    /**
     * RELATION: AccountManager has many LiniWaktu (One-to-Many)
     * Satu AM punya multiple periode waktu untuk tracking target
     */
    public function liniWaktu(): HasMany
    {
        return $this->hasMany(LiniWaktu::class, 'nik_am', 'nik');
    }

    /**
     * Scope: Filter by posisi
     */
    public function scopeByPosisi($query, string $posisi)
    {
        return $query->where('posisi', $posisi);
    }

    /**
     * Scope: Filter by witel
     */
    public function scopeByWitel($query, int $idwitels)
    {
        return $query->where('idwitels', $idwitels);
    }

    /**
     * Accessor: Get formatted posisi name
     */
    public function getPosisiNameAttribute(): string
    {
        return self::getPosisiOptions()[$this->posisi] ?? $this->posisi;
    }

    /**
     * Helper: Check if AM handles a specific company
     */
    public function handlesCompany(string $nip_nas): bool
    {
        return $this->companies()->where('nip_nas', $nip_nas)->exists();
    }

    /**
     * Helper: Get total companies handled by this AM
     */
    public function getTotalCompaniesAttribute(): int
    {
        return $this->companies()->count();
    }
}
