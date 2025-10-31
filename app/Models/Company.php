<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip_nas',
        'nama_perusahaan',
        'subsegment',
        'source_data',
        'status',
        'primary_region_id',
        'primary_witel_id',
    ];

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    /**
     * Get the primary region of the company
     */
    public function primaryRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'primary_region_id');
    }

    /**
     * Get the primary WITEL of the company
     */
    public function primaryWitel(): BelongsTo
    {
        return $this->belongsTo(Witel::class, 'primary_witel_id');
    }

    /**
     * Get all regions this company operates in (Many-to-Many)
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'company_regions')
                    ->withPivot('witel_id', 'is_primary', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get all WITELs this company operates in (Many-to-Many)
     */
    public function witels(): BelongsToMany
    {
        return $this->belongsToMany(Witel::class, 'company_regions')
                    ->withPivot('region_id', 'is_primary', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get all company-region relationships
     */
    public function companyRegions(): HasMany
    {
        return $this->hasMany(CompanyRegion::class);
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
