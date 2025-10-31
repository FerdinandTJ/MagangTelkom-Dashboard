<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Witel extends Model
{
    protected $fillable = [
        'region_id',
        'code',
        'name',
        'province',
        'description'
    ];

    /**
     * Get the region that owns this WITEL
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get all companies with this as primary WITEL
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'primary_witel_id');
    }

    /**
     * Get all company-region relationships
     */
    public function companyRegions(): HasMany
    {
        return $this->hasMany(CompanyRegion::class);
    }

    /**
     * Get all revenues in this WITEL
     */
    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }
}
