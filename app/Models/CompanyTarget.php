<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyTarget extends Model
{
    protected $fillable = [
        'nip_nas',
        'tahun',
        'bulan',
        'target_revenue',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'target_revenue' => 'decimal:2',
    ];

    /**
     * Get the company that owns the target
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'nip_nas', 'nip_nas');
    }
}
