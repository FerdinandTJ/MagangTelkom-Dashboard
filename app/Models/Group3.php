<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group3 extends Model
{
    protected $table = 'group3';
    protected $primaryKey = 'idGroup3';
    
    protected $fillable = [
        'nama_group3',
        'group2_id',
    ];

    /**
     * Get the group2 that owns this group3
     */
    public function group2(): BelongsTo
    {
        return $this->belongsTo(Group2::class, 'group2_id', 'idGroup2');
    }

    /**
     * Get all group4 records for this group3
     */
    public function group4s(): HasMany
    {
        return $this->hasMany(Group4::class, 'group3_id', 'idGroup3');
    }
}
