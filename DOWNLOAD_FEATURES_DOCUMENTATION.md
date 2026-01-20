# Download Features Documentation

## Overview
Terdapat 2 jenis download features untuk data revenue:
1. **Download Per Bulan** - Generate Excel dari database untuk 1 bulan spesifik
2. **Download Per Tahun** - Generate Excel dari database dengan semua 12 bulan dalam satu file

**PENTING**: Kedua fitur ini **tidak** mendownload file asli yang diupload, tetapi **generate ulang dari database**. Ini memastikan data yang didownload adalah data terbaru yang ada di sistem.

## 1. Download Per Bulan (Generated from Database)

### Purpose
Generate Excel file dari database untuk bulan tertentu dengan format grouped (tidak duplikasi company info).

### Route
```
GET /data-import/revenue/download/{year}/{month}
```

### Controller Method
```php
RevenueImportController@downloadFile($year, $month)
```

### How It Works
1. Query database dengan JOIN:
   - revenues → group4 → group3 → group2 → group1 → companies
2. Filter by tahun = $year AND bulan = $month
3. Generate Excel dengan PhpSpreadsheet
4. Format grouped: Jika NIP_NAS, GROUP1, atau GROUP2 sama dengan baris sebelumnya, kolom tersebut dikosongkan
5. Simpan ke temp folder
6. Return download dan hapus file setelah download

### Excel Structure
```
| NIP_NAS | STANDARD_NAME | SOURCE_DATA | GROUP1 | GROUP2 | GROUP3 | GROUP4 | {month} |
|---------|---------------|-------------|--------|--------|--------|--------|---------|
| 123     | PT ABC        | TIBS-NP     | A1     | B1     | C1     | D1     | 500000  |
|         |               |             |        |        | C2     | D2     | 300000  |
|         |               |             |        | B2     | C3     | D3     | 200000  |
| 124     | PT XYZ        | SISKA       | A2     | B3     | C4     | D4     | 100000  |
```

### Grouping Logic
- **NIP_NAS, STANDARD_NAME, SOURCE_DATA**: Tampil hanya jika NIP_NAS berbeda dari baris sebelumnya
- **GROUP1**: Tampil hanya jika NIP_NAS atau GROUP1 berbeda
- **GROUP2**: Tampil hanya jika NIP_NAS, GROUP1, atau GROUP2 berbeda
- **GROUP3, GROUP4**: Selalu tampil (tidak di-group)
- **Revenue**: Selalu tampil

### Storage Location
```
storage/app/temp/Revenue_{year}_{MonthName}_{timestamp}.xlsx
```

### File Naming Convention
```
Revenue_{year}_{MonthName}_{Ymd_His}.xlsx
Example: Revenue_2026_January_20250107_143025.xlsx
```

### Response
- **Success**: Excel file download
- **Error 404**: Tidak ada data revenue untuk bulan dan tahun tersebut
- **Error 500**: Kesalahan server

### Frontend Usage
```tsx
// Button Download di DataImportRevenue.tsx
<Button onClick={() => handleDownload(monthData.month)}>
  <Download className="w-4 h-4" />
  Download
</Button>

// Handler
const handleDownload = (month: number) => {
  window.location.href = `/data-import/revenue/download/${selectedYear}/${month}`;
};
```

## 2. Download Per Tahun (Generated Excel)

### Purpose
Generate Excel file baru dengan data revenue dari semua 12 bulan dalam format pivot dengan grouping.

### Route
```
GET /data-import/revenue/download-year/{year}
```

### Controller Method
```php
RevenueImportController@downloadYear($year)
```

### How It Works
1. Query database dengan JOIN:
   - revenues → group4 → group3 → group2 → group1 → companies
2. Filter by tahun = $year
3. Transform data ke format pivot:
   - Satu row per kombinasi NIP_NAS + GROUP1-4
   - 12 kolom untuk bulan (1-12)
4. Generate Excel menggunakan PhpSpreadsheet dengan format grouped
5. Grouping: Kosongkan NIP_NAS, STANDARD_NAME, SOURCE_DATA, GROUP1, GROUP2 jika sama dengan baris sebelumnya
6. Simpan ke temp folder
7. Return download dan hapus file setelah download

### Excel Structure
```
| NIP_NAS | STANDARD_NAME | SOURCE_DATA | GROUP1 | GROUP2 | GROUP3 | GROUP4 | 1 | 2 | 3 | ... | 12 |
|---------|---------------|-------------|--------|--------|--------|--------|---|---|---|-----|-----|
| 123     | PT ABC        | TIBS-NP     | A1     | B1     | C1     | D1     | 0 | 0 | 500000 | ... | 0 |
|         |               |             |        |        | C2     | D2     | 0 | 100000 | 0 | ... | 0 |
|         |               |             |        | B2     | C3     | D3     | 0 | 0 | 200000 | ... | 0 |
| 124     | PT XYZ        | SISKA       | A2     | B3     | C4     | D4     | 0 | 0 | 0 | ... | 100000 |
```

### Grouping Logic
- **NIP_NAS, STANDARD_NAME, SOURCE_DATA**: Tampil hanya jika NIP_NAS berbeda dari baris sebelumnya
- **GROUP1**: Tampil hanya jika NIP_NAS atau GROUP1 berbeda
- **GROUP2**: Tampil hanya jika NIP_NAS, GROUP1, atau GROUP2 berbeda
- **GROUP3, GROUP4**: Selalu tampil (tidak di-group)
- **Month columns (1-12)**: Selalu tampil

### Temp Storage
```
storage/app/temp/Revenue_{year}_Full_Export_{timestamp}.xlsx
```

### File Naming Convention
```
Revenue_{year}_Full_Export_{Ymd_His}.xlsx
Example: Revenue_2026_Full_Export_20250601_143025.xlsx
```

### Response
- **Success**: Excel file download
- **Error 404**: Tidak ada data revenue untuk tahun tersebut
- **Error 500**: Kesalahan saat generate Excel

### Frontend Usage
```tsx
// Button Download Year di DataImportRevenue.tsx
<Button 
  onClick={() => {
    window.location.href = `/data-import/revenue/download-year/${selectedYear}`;
  }}
>
  <Download className="w-4 h-4" />
  Download {selectedYear}
</Button>
```

## Key Differences

| Feature | Download Per Bulan | Download Per Tahun |
|---------|-------------------|-------------------|
| **Source** | Generated dari database | Generated dari database |
| **Format** | Excel (.xlsx) | Excel (.xlsx) |
| **Content** | 1 bulan saja | Semua 12 bulan |
| **Structure** | 1 kolom revenue untuk bulan tersebut | 12 kolom revenue (pivot format) |
| **Grouping** | Yes (hide duplicate company/group info) | Yes (hide duplicate company/group info) |
| **File Naming** | Revenue_{year}_{MonthName}_{timestamp}.xlsx | Revenue_{year}_Full_Export_{timestamp}.xlsx |
| **Storage** | Temporary di temp/ (auto-delete after send) | Temporary di temp/ (auto-delete after send) |

## Database Dependencies

### revenue_uploads table
```sql
- id
- tahun
- bulan
- original_filename  -- Nama file asli saat upload
- stored_path        -- Path file di storage
- uploaded_by
- row_count
- file_size_kb
- uploaded_at
```

### revenues table
```sql
- id
- tahun
- bulan
- group4_id
- revenue_realisasi
- revenue_target
```

## Error Handling

### Download Per Bulan
1. **Upload not found**: Return 404 dengan message "Data upload tidak ditemukan"
2. **No stored_path**: Return 404 dengan message "File tidak tersedia. Data diimport sebelum fitur penyimpanan file diaktifkan"
3. **File not exists**: Return 404 dengan message "File tidak ditemukan di server. Mungkin sudah dihapus"
4. **General error**: Return 500 dengan exception message

### Download Per Tahun
1. **No data**: Return 404 dengan message "Tidak ada data revenue untuk tahun {year}"
2. **Generate error**: Return 500 dengan message "Terjadi kesalahan: {error_message}"
3. **Log error**: Error logged ke storage/logs/laravel.log dengan context

## Testing

### Test Download Per Bulan
1. Upload file untuk bulan tertentu (misal Januari 2026)
2. Klik button "Download" di uploaded file
3. Verify: File yang terdownload sama dengan file yang diupload
4. Check: Nama file sesuai dengan original_filename

### Test Download Per Tahun
1. Upload beberapa file untuk bulan yang berbeda dalam 1 tahun
2. Klik button "Download 2026" di bagian yearly actions
3. Verify: Excel terdownload dengan nama "Revenue_2026_Full_Export_{timestamp}.xlsx"
4. Open Excel:
   - Headers: NIP_NAS, STANDARD_NAME, SOURCE_DATA, GROUP1-4, 1-12
   - Data: Satu row per kombinasi company-group
   - Revenue: Muncul di kolom bulan yang sesuai (bulan lain = 0)

## Implementation Notes

1. **Month-specific vs Year-wide**:
   - Download per bulan: File asli yang exact sama dengan yang diupload
   - Download per tahun: Data di-query ulang dan di-transform ke format baru

2. **Storage Strategy**:
   - Per bulan: Permanent storage untuk audit trail
   - Per tahun: Temporary storage (auto-delete setelah download)

3. **Performance**:
   - Download per bulan: Fast (langsung ambil file)
   - Download per tahun: Slower (query database, transform, generate Excel)

4. **Data Integrity**:
   - Per bulan: Exact copy of uploaded file
   - Per tahun: Current data from database (bisa berbeda jika ada update manual)

## Related Files

### Backend
- `app/Http/Controllers/RevenueImportController.php` - Lines 331-472 (downloadFile, downloadYear)
- `routes/web.php` - Lines 24, 26 (routes definition)
- `app/Models/RevenueUpload.php` - Model untuk revenue_uploads table

### Frontend
- `resources/js/pages/DataImportRevenue.tsx` - Lines 598-611 (Download Year), 650-685 (Download Per Bulan)
- Both use simple `window.location.href` for download initiation

### Dependencies
- `PhpOffice\PhpSpreadsheet` - For Excel generation in downloadYear()
- `Illuminate\Support\Facades\Storage` - For file retrieval in downloadFile()
- `Illuminate\Support\Facades\DB` - For database query in downloadYear()

## Maintenance Notes

1. **Cleanup Temp Files**: 
   - Temp files auto-deleted setelah download (deleteFileAfterSend)
   - Jika ada file tertinggal, bisa hapus manual di storage/app/temp/

2. **Storage Monitoring**:
   - Monitor penggunaan disk di storage/app/revenue-uploads/
   - Pertimbangkan archive atau cleanup untuk data lama

3. **Performance Optimization**:
   - Jika data tahun sangat besar, pertimbangkan:
     - Pagination
     - Background job dengan queue
     - Caching hasil generate
