<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'tahun',
        'bulan',
        'revenue',
        'notes',
        'region_id',
        'witel_id',
    ];

    protected $casts = [
        'revenue' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the region for this revenue
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the WITEL for this revenue
     */
    public function witel(): BelongsTo
    {
        return $this->belongsTo(Witel::class);
    }

    public function getFormattedRevenueAttribute(): string
    {
        return 'Rp ' . number_format($this->revenue, 0, ',', '.');
    }

    public function getBulanNameAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$this->bulan] ?? '';
    }
}
