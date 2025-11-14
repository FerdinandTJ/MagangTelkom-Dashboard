<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Pivot Model: AccountManagerCompany
 * 
 * Represents the Many-to-Many relationship between Account Managers and Companies
 * with additional pivot data (proporsi, pembagian, segment)
 * 
 * @property int $id Primary Key
 * @property string $nik_am FK to account_managers.nik
 * @property string $nip_nas FK to companies.nip_nas
 * @property float $proporsi Proporsi pembagian tanggung jawab (0-100%)
 * @property string $pembagian Jenis pembagian: SINGLE or MULTI
 * @property string|null $segment Segment khusus AM di company
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read \App\Models\AccountManager $accountManager
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\TargetAccountM|null $target
 */
class AccountManagerCompany extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'account_manager_company';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nik_am',
        'nip_nas',
        'proporsi',
        'pembagian',
        'segment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'proporsi' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the Account Manager that owns this assignment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(AccountManager::class, 'nik_am', 'nik');
    }

    /**
     * Get the Company that is assigned.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'nip_nas', 'nip_nas');
    }

    /**
     * Get the Target associated with this AM-Company assignment.
     * One-to-One relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function target(): HasOne
    {
        return $this->hasOne(TargetAccountM::class, 'account_manager_company_id', 'id');
    }
}
