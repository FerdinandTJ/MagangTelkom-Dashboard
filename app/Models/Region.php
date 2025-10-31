<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description'
    ];

    /**
     * Get all WITELs in this region
     */
    public function witels(): HasMany
    {
        return $this->hasMany(Witel::class);
    }

    /**
     * Get all companies with this as primary region
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'primary_region_id');
    }

    /**
     * Get all company-region relationships
     */
    public function companyRegions(): HasMany
    {
        return $this->hasMany(CompanyRegion::class);
    }

    /**
     * Get all revenues in this region
     */
    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }
}
