<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Group4 extends Model
{
    protected $table = 'group4';
    protected $primaryKey = 'idGroup4';
    
    protected $fillable = [
        'nama_group4',
        'revenue_realisasi',
        'revenue_target',
        'tahun',
        'bulan',
        'group3_id',
    ];

    protected $casts = [
        'revenue_realisasi' => 'decimal:2',
        'revenue_target' => 'decimal:2',
        'tahun' => 'integer',
        'bulan' => 'integer',
    ];

    /**
     * Get the group3 that owns this group4
     */
    public function group3(): BelongsTo
    {
        return $this->belongsTo(Group3::class, 'group3_id', 'idGroup3');
    }
}
