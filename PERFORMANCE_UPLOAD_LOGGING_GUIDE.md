# Performance Upload Logging System - Complete Guide

## Overview
Sistem logging komprehensif untuk mencatat semua aktivitas upload, update, dan delete data Performance AM. Sistem ini menampilkan nama file original yang diupload user, bukan nama hardcoded.

## Database Structure

### Table: `performance_upload_logs`

```sql
CREATE TABLE performance_upload_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tahun YEAR NOT NULL,
    quartal ENUM('Q1', 'Q2', 'Q3', 'Q4') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    stored_path VARCHAR(255) NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    row_count INT NOT NULL,
    file_size DECIMAL(10,2) NOT NULL COMMENT 'File size in KB',
    status ENUM('Upload', 'Update', 'Delete') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_year_quarter (tahun, quartal),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### Columns Explanation

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT | Primary key, auto increment |
| `tahun` | YEAR | Year of the data (e.g., 2026) |
| `quartal` | ENUM | Quarter (Q1, Q2, Q3, Q4) |
| `file_name` | VARCHAR(255) | Original filename from user upload |
| `stored_path` | VARCHAR(255) | Path where file is stored (nullable for future use) |
| `uploaded_by` | BIGINT | Foreign key to users table |
| `row_count` | INT | Number of rows imported |
| `file_size` | DECIMAL(10,2) | File size in kilobytes |
| `status` | ENUM | Action type: Upload, Update, or Delete |
| `created_at` | TIMESTAMP | When the action occurred |

## Model Usage

### PerformanceUploadLog Model

Location: `app/Models/PerformanceUploadLog.php`

#### Constants

```php
const STATUS_UPLOAD = 'Upload';
const STATUS_UPDATE = 'Update';
const STATUS_DELETE = 'Delete';

const QUARTAL_Q1 = 'Q1';
const QUARTAL_Q2 = 'Q2';
const QUARTAL_Q3 = 'Q3';
const QUARTAL_Q4 = 'Q4';
```

#### Helper Methods

##### 1. Log Upload Activity

```php
PerformanceUploadLog::logUpload(
    int $tahun,
    int $quarter,       // 1-4, akan dikonversi ke Q1-Q4
    string $fileName,
    int $rowCount,
    float $fileSizeKB,
    int $userId
): PerformanceUploadLog
```

**Example:**
```php
$log = PerformanceUploadLog::logUpload(
    2026,
    1,
    'performance_data_jan_2026.xlsx',
    150,
    256.75,
    auth()->id()
);
```

##### 2. Log Update Activity

```php
PerformanceUploadLog::logUpdate(
    int $tahun,
    int $quarter,
    string $fileName,
    int $rowCount,
    float $fileSizeKB,
    int $userId
): PerformanceUploadLog
```

##### 3. Log Delete Activity

```php
PerformanceUploadLog::logDelete(
    int $tahun,
    int $quarter,
    string $description,
    int $rowCount,
    int $userId
): PerformanceUploadLog
```

**Example:**
```php
$log = PerformanceUploadLog::logDelete(
    2026,
    1,
    'Deleted Q1 2026 data',
    150,
    auth()->id()
);
```

##### 4. Get Logs for Specific Period

```php
$logs = PerformanceUploadLog::getLogsForPeriod(
    int $tahun,
    int|null $quarter = null  // Optional, omit for entire year
): Collection
```

**Examples:**
```php
// Get all logs for Q1 2026
$q1Logs = PerformanceUploadLog::getLogsForPeriod(2026, 1);

// Get all logs for entire year 2026
$yearLogs = PerformanceUploadLog::getLogsForPeriod(2026);
```

#### Relationships

```php
// Get the user who performed the action
$log->uploader; // Returns User model

// Example
$userName = $log->uploader->name;
$userEmail = $log->uploader->email;
```

## Controller Integration

### DataImportPerformanceController Updates

#### 1. Import Use Statement

```php
use App\Models\PerformanceUploadLog;
```

#### 2. Upload Method - Log Upload Activity

```php
public function upload(Request $request)
{
    // ... validation ...
    
    try {
        DB::beginTransaction();
        
        $file = $request->file('file');
        $originalFileName = $file->getClientOriginalName();
        $fileSizeKB = $file->getSize() / 1024;
        
        $import = new PerformanceAMImport(
            $request->input('quarter'),
            $request->input('year')
        );
        
        Excel::import($import, $file);
        
        // Log the upload activity
        PerformanceUploadLog::logUpload(
            $request->input('year'),
            $request->input('quarter'),
            $originalFileName,
            $import->getRowCount(),
            $fileSizeKB,
            Auth::id()
        );
        
        DB::commit();
        
        // ... return response ...
    }
}
```

#### 3. Delete Method - Log Delete Activity

```php
public function delete(Request $request, $year, $quarter = null)
{
    try {
        DB::beginTransaction();
        
        if ($quarter) {
            // ... get row count before deleting ...
            
            // Delete data
            // ...
            
            // Log the delete activity
            PerformanceUploadLog::logDelete(
                $year,
                $quarter,
                "Deleted Q{$quarter} {$year} data",
                $rowCount,
                Auth::id()
            );
        } else {
            // Delete entire year
            // Log for each quarter
            for ($q = 1; $q <= 4; $q++) {
                if ($hasQuarterData) {
                    PerformanceUploadLog::logDelete(
                        $year,
                        $q,
                        "Deleted entire year {$year} data",
                        $rowCount,
                        Auth::id()
                    );
                }
            }
        }
        
        DB::commit();
    }
}
```

#### 4. Index Method - Display Original Filename

```php
public function index(Request $request)
{
    // ... quarters loop ...
    
    if ($hasData) {
        // Get most recent upload log for this quarter
        $uploadLog = PerformanceUploadLog::where('tahun', $selectedYear)
            ->where('quartal', 'Q' . $q)
            ->where('status', PerformanceUploadLog::STATUS_UPLOAD)
            ->orderBy('created_at', 'desc')
            ->first();
        
        $quarterData['uploadInfo'] = [
            'fileName' => $uploadLog ? $uploadLog->file_name : 'N/A',
            'uploadDate' => $uploadLog ? $uploadLog->created_at->format('d M Y, H:i') : 'N/A',
            'uploadedBy' => $uploadLog && $uploadLog->uploader ? $uploadLog->uploader->name : 'Admin',
            'fileSize' => $uploadLog ? number_format($uploadLog->file_size, 2) . ' KB' : 'N/A',
            'rowCount' => $uploadLog ? $uploadLog->row_count : 0,
            // ... other fields ...
        ];
        
        // Get activity logs for this quarter
        $logs = PerformanceUploadLog::where('tahun', $selectedYear)
            ->where('quartal', 'Q' . $q)
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $quarterData['activityLogs'] = $logs->map(function ($log) {
            return [
                'action' => strtolower($log->status),
                'fileName' => $log->file_name,
                'user' => $log->uploader ? $log->uploader->name : 'Admin',
                'timestamp' => $log->created_at->format('d M Y, H:i'),
            ];
        })->toArray();
    }
}
```

## Frontend Display

### DataImportPerformance.tsx

The frontend automatically displays:

1. **Original Filename** in quarter cards
2. **Upload Info** with real file metadata
3. **Activity Logs** showing complete history

```typescript
interface UploadInfo {
  fileName: string;          // Original filename from user
  uploadDate: string;        // Format: "13 Jan 2026, 14:30"
  uploadedBy: string;        // User's name
  fileSize: string;          // Format: "256.75 KB"
  rowCount: number;          // Number of rows imported
  amCount: number;           // Number of unique AMs
  totalTarget: string;       // Format: "Rp 1,234.56M"
  totalRealisasi: string;    // Format: "Rp 987.65M"
  regionCount: number;       // Number of regions
}

interface ActivityLog {
  action: 'upload' | 'update' | 'delete';
  fileName: string;
  user: string;
  timestamp: string;
}
```

## Benefits

### 1. Audit Trail
- Complete history of all uploads, updates, and deletes
- Track who uploaded what and when
- Compliance and accountability

### 2. Original Filename Display
- Users see the actual filename they uploaded
- No more confusing hardcoded names
- Better file identification

### 3. File Metadata Tracking
- File size in KB
- Row count for verification
- Upload timestamp

### 4. User Attribution
- Know who uploaded each file
- Track user activity
- User relationship via Foreign Key

### 5. Query Performance
- Indexed columns for fast queries
- Efficient period-based lookups
- Optimized for common query patterns

## Query Examples

### Get All Uploads for Q1 2026

```php
$uploads = PerformanceUploadLog::where('tahun', 2026)
    ->where('quartal', 'Q1')
    ->where('status', PerformanceUploadLog::STATUS_UPLOAD)
    ->with('uploader')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Get Most Recent Upload

```php
$latestUpload = PerformanceUploadLog::where('tahun', 2026)
    ->where('quartal', 'Q1')
    ->where('status', PerformanceUploadLog::STATUS_UPLOAD)
    ->orderBy('created_at', 'desc')
    ->first();

$fileName = $latestUpload->file_name;
$userName = $latestUpload->uploader->name;
```

### Get All Activities for a User

```php
$userActivities = PerformanceUploadLog::where('uploaded_by', $userId)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Get Delete History

```php
$deletions = PerformanceUploadLog::where('status', PerformanceUploadLog::STATUS_DELETE)
    ->where('tahun', 2026)
    ->with('uploader')
    ->get();
```

### Get Statistics

```php
// Count uploads per user
$uploadStats = PerformanceUploadLog::select('uploaded_by', DB::raw('COUNT(*) as upload_count'))
    ->where('status', PerformanceUploadLog::STATUS_UPLOAD)
    ->groupBy('uploaded_by')
    ->with('uploader')
    ->get();

// Total file size uploaded
$totalSize = PerformanceUploadLog::where('status', PerformanceUploadLog::STATUS_UPLOAD)
    ->sum('file_size');

// Average rows per upload
$avgRows = PerformanceUploadLog::where('status', PerformanceUploadLog::STATUS_UPLOAD)
    ->avg('row_count');
```

## Migration Details

### File Location
`database/migrations/2026_01_13_000001_create_performance_upload_logs_table.php`

### Run Migration

```bash
php artisan migrate
```

### Rollback Migration

```bash
php artisan migrate:rollback
```

### Check Migration Status

```bash
php artisan migrate:status
```

## Testing

### Test Upload Logging

```php
// Upload a file through DataImportPerformance
// Check if log is created
$log = PerformanceUploadLog::where('tahun', 2026)
    ->where('quartal', 'Q1')
    ->latest()
    ->first();

assert($log->file_name === 'your_uploaded_file.xlsx');
assert($log->status === 'Upload');
assert($log->uploaded_by === auth()->id());
```

### Test Delete Logging

```php
// Delete a quarter
// Check if delete log is created
$deleteLog = PerformanceUploadLog::where('tahun', 2026)
    ->where('quartal', 'Q1')
    ->where('status', 'Delete')
    ->latest()
    ->first();

assert($deleteLog !== null);
assert(strpos($deleteLog->file_name, 'Deleted') !== false);
```

## Future Enhancements

### 1. File Storage
Currently `stored_path` is nullable. In future, you can:
- Store original files in storage/app/performance_uploads/
- Set `stored_path` to the file location
- Allow users to download original files

```php
$path = $file->store('performance_uploads', 'local');

PerformanceUploadLog::logUpload(
    $year,
    $quarter,
    $originalFileName,
    $rowCount,
    $fileSizeKB,
    auth()->id(),
    $path  // Add this parameter
);
```

### 2. File Versioning
- Keep multiple versions of uploads
- Allow rollback to previous version
- Compare different uploads

### 3. Advanced Analytics
- Dashboard showing upload trends
- User activity reports
- File size trends over time

### 4. Notifications
- Email notifications on upload
- Slack notifications for deletes
- Alert on large file uploads

## Troubleshooting

### Issue: Original filename not showing

**Solution:** Check if migration ran successfully:
```bash
php artisan migrate:status
```

### Issue: Foreign key constraint error

**Solution:** Ensure user exists before logging:
```php
if (!Auth::check()) {
    throw new \Exception('User must be authenticated to upload');
}
```

### Issue: Activity logs not appearing

**Solution:** Check relationship is loaded:
```php
$logs = PerformanceUploadLog::with('uploader')->get();
```

## Summary

✅ **Completed:**
- Created `performance_upload_logs` table
- Created `PerformanceUploadLog` model with helper methods
- Integrated logging into upload process
- Integrated logging into delete process
- Updated index to display original filenames
- Updated frontend to show activity logs

✅ **Benefits:**
- Original filename display instead of hardcoded names
- Complete audit trail of all activities
- User attribution for accountability
- File metadata tracking
- Efficient querying with proper indexes

✅ **Ready to Use:**
- Migration executed successfully
- All controller methods updated
- Frontend displaying correct information
- System fully integrated and operational
