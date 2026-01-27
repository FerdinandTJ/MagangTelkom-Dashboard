<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lop extends Model
{
    protected $table = 'lop';
    protected $primaryKey = 'ID_LOP';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Disable Laravel timestamps (created_at, updated_at)
    
    protected $fillable = [
        'ID_LOP',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Relationship: LOP belongs to many Bulan
     */
    public function bulans(): BelongsToMany
    {
        return $this->belongsToMany(Bulan::class, 'lop_bulan', 'ID_LOP', 'bulan_id')
            ->withPivot('AM', 'Nama_CC', 'ID_Region', 'Project', 'Scaling', 'Progress')
            ->withTimestamps();
    }
}
