<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LopBulan extends Model
{
    protected $table = 'lop_bulan';
    
    protected $fillable = [
        'ID_LOP',
        'bulan_id',
        'AM',
        'Nama_CC',
        'ID_Region',
        'Project',
        'Scaling',
        'Progress',
    ];

    protected $casts = [
        'Scaling' => 'decimal:2',
    ];

    /**
     * Relationship: LopBulan belongs to Lop
     */
    public function lop(): BelongsTo
    {
        return $this->belongsTo(Lop::class, 'ID_LOP', 'ID_LOP');
    }

    /**
     * Relationship: LopBulan belongs to Bulan
     */
    public function bulan(): BelongsTo
    {
        return $this->belongsTo(Bulan::class, 'bulan_id');
    }

    /**
     * Relationship: LopBulan belongs to Region
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'ID_Region');
    }
}
