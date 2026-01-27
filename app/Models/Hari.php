<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hari extends Model
{
    protected $table = 'hari';
    
    protected $fillable = [
        'bulan_id',
        'tanggal',
        'tahun',
        'progress_scaling',
        'sodomoro',
        'adjustment',
    ];

    protected $casts = [
        'progress_scaling' => 'decimal:2',
        'sodomoro' => 'decimal:2',
        'adjustment' => 'decimal:2',
    ];

    /**
     * Relationship: Hari belongs to Bulan
     */
    public function bulan(): BelongsTo
    {
        return $this->belongsTo(Bulan::class, 'bulan_id');
    }
}
