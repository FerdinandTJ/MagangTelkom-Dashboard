<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bulan extends Model
{
    protected $table = 'bulan';
    
    protected $fillable = [
        'bulan',
        'tahun',
        't_sustain',
        'kebutuhan_scaling',
        'r_scaling',
        'sodomoro',
        'adjustment',
        'target_cm',
        'target_ytd',
        'rev_cm',
        'rev_ytd',
        'ach_cm',
        'ach_ytd',
    ];

    protected $casts = [
        't_sustain' => 'decimal:2',
        'kebutuhan_scaling' => 'decimal:2',
        'r_scaling' => 'decimal:2',
        'sodomoro' => 'decimal:2',
        'adjustment' => 'decimal:2',
        'target_cm' => 'decimal:2',
        'target_ytd' => 'decimal:2',
        'rev_cm' => 'decimal:2',
        'rev_ytd' => 'decimal:2',
        'ach_cm' => 'decimal:2',
        'ach_ytd' => 'decimal:2',
    ];

    /**
     * Relationship: Bulan belongs to many Lop
     */
    public function lops(): BelongsToMany
    {
        return $this->belongsToMany(Lop::class, 'lop_bulan', 'bulan_id', 'ID_LOP')
            ->withPivot('AM', 'Nama_CC', 'ID_Region', 'Project', 'Scaling', 'Progress')
            ->withTimestamps();
    }

    /**
     * Relationship: Bulan has many Hari
     */
    public function haris(): HasMany
    {
        return $this->hasMany(Hari::class, 'bulan_id');
    }
}
