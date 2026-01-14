<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueUpload extends Model
{
    protected $fillable = [
        'tahun',
        'bulan',
        'original_filename',
        'stored_path',
        'uploaded_by',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'row_count',
        'file_size_kb',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'uploaded_by' => 'integer',
        'row_count' => 'integer',
        'file_size_kb' => 'decimal:2',
    ];
    
    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
    /**
     * Get action label for display
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'upload' => 'Upload',
            'replace' => 'Replace/Update',
            'delete' => 'Delete',
            default => ucfirst($this->action)
        };
    }
    
    /**
     * Get action badge color
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'upload' => 'green',
            'replace' => 'yellow',
            'delete' => 'red',
            default => 'gray'
        };
    }
}
