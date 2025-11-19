<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group2 extends Model
{
    protected $table = 'group2';
    protected $primaryKey = 'idGroup2';
    
    protected $fillable = [
        'nama_group2',
        'group1_id',
    ];

    /**
     * Get the group1 that owns this group2
     */
    public function group1(): BelongsTo
    {
        return $this->belongsTo(Group1::class, 'group1_id', 'idGroup1');
    }

    /**
     * Get all group3 records for this group2
     */
    public function group3s(): HasMany
    {
        return $this->hasMany(Group3::class, 'group2_id', 'idGroup2');
    }
}
