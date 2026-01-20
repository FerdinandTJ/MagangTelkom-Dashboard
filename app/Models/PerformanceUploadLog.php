<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: PerformanceUploadLog
 * 
 * Model untuk table performance_upload_logs
 * Menyimpan log aktivitas upload, update, dan delete file Performance AM
 * 
 * @property int $id
 * @property int $tahun
 * @property string $quartal
 * @property string $file_name
 * @property string|null $stored_path
 * @property int $uploaded_by
 * @property int $row_count
 * @property float $file_size
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * 
 * @property-read \App\Models\User $user
 */
class PerformanceUploadLog extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'performance_upload_logs';

    /**
     * Indicates if the model should be timestamped.
     * Hanya created_at, tidak ada updated_at
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tahun',
        'quartal',
        'file_name',
        'stored_path',
        'uploaded_by',
        'row_count',
        'file_size',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tahun' => 'integer',
        'row_count' => 'integer',
        'file_size' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_UPLOAD = 'Upload';
    const STATUS_UPDATE = 'Update';
    const STATUS_DELETE = 'Delete';

    /**
     * Quartal constants
     */
    const QUARTAL_Q1 = 'Q1';
    const QUARTAL_Q2 = 'Q2';
    const QUARTAL_Q3 = 'Q3';
    const QUARTAL_Q4 = 'Q4';

    /**
     * Relasi ke User yang melakukan upload
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Helper method untuk create log upload
     */
    public static function logUpload(
        int $tahun,
        string $quartal,
        string $fileName,
        int $uploadedBy,
        int $rowCount,
        float $fileSize,
        ?string $storedPath = null
    ): self {
        return self::create([
            'tahun' => $tahun,
            'quartal' => $quartal,
            'file_name' => $fileName,
            'stored_path' => $storedPath,
            'uploaded_by' => $uploadedBy,
            'row_count' => $rowCount,
            'file_size' => $fileSize,
            'status' => self::STATUS_UPLOAD,
        ]);
    }

    /**
     * Helper method untuk create log update
     */
    public static function logUpdate(
        int $tahun,
        string $quartal,
        string $fileName,
        int $uploadedBy,
        int $rowCount,
        float $fileSize,
        ?string $storedPath = null
    ): self {
        return self::create([
            'tahun' => $tahun,
            'quartal' => $quartal,
            'file_name' => $fileName,
            'stored_path' => $storedPath,
            'uploaded_by' => $uploadedBy,
            'row_count' => $rowCount,
            'file_size' => $fileSize,
            'status' => self::STATUS_UPDATE,
        ]);
    }

    /**
     * Helper method untuk create log delete
     */
    public static function logDelete(
        int $tahun,
        string $quartal,
        int $uploadedBy,
        string $fileName = 'Data deleted'
    ): self {
        return self::create([
            'tahun' => $tahun,
            'quartal' => $quartal,
            'file_name' => $fileName,
            'uploaded_by' => $uploadedBy,
            'row_count' => 0,
            'file_size' => 0,
            'status' => self::STATUS_DELETE,
        ]);
    }

    /**
     * Get logs untuk specific tahun dan quartal
     */
    public static function getLogsForPeriod(int $tahun, string $quartal)
    {
        return self::where('tahun', $tahun)
            ->where('quartal', $quartal)
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
