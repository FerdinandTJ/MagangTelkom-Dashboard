<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip_nas',
        'nama_perusahaan',
        'subsegment',
        'source_data',
        'status',
    ];

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    public function getRevenueByYear(int $year): float
    {
        return $this->revenues()
            ->where('tahun', $year)
            ->sum('revenue');
    }

    public function getRevenueByMonth(int $year, int $month): float
    {
        return $this->revenues()
            ->where('tahun', $year)
            ->where('bulan', $month)
            ->sum('revenue');
    }
}
