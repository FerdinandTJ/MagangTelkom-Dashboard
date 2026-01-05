<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueUpload extends Model
{
    protected $fillable = [
        'tahun',
        'bulan',
        'original_filename',
        'uploaded_by',
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
}
