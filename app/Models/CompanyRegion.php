<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyRegion extends Model
{
    protected $fillable = [
        'company_id',
        'region_id',
        'witel_id',
        'is_primary',
        'notes'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    /**
     * Get the company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the region
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the WITEL
     */
    public function witel(): BelongsTo
    {
        return $this->belongsTo(Witel::class);
    }
}
