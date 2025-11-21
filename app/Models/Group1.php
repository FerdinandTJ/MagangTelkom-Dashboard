<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group1 extends Model
{
    protected $table = 'group1';
    protected $primaryKey = 'idGroup1';
    
    protected $fillable = [
        'nama_group1',
        'company_id',
    ];

    /**
     * Get the company that owns this group1
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'nip_nas');
    }

    /**
     * Get all group2 records for this group1
     */
    public function group2s(): HasMany
    {
        return $this->hasMany(Group2::class, 'group1_id', 'idGroup1');
    }
}
