# Performance AM Page - Update Log

## 📋 Overview
Dokumentasi lengkap untuk update halaman Performance AM dengan 8 perubahan major pada cards, charts, dan table.

---

## 🔄 Currency Formatting Update

**Date**: 2025-11-13  
**Status**: ✅ Completed

### Changes Made
Menyamakan format mata uang (Rupiah) di Performance AM dengan format yang digunakan di Dashboard menggunakan fungsi `formatCurrency()`.

**Backend Updates** (`DashboardController.php`):
1. Menambahkan `formatted_revenue_target` di `amMetrics` (line ~56)
2. Menggunakan `formatCurrency($revenueTarget, 2)` untuk format konsisten
3. Format: Miliar (M) atau Triliun (T) dengan 2 decimal places

**Frontend Updates** (`PerformanceAm.tsx`):
1. Update TypeScript interface untuk include `formatted_revenue_target`
2. Card Revenue Target menggunakan `amMetrics.formatted_revenue_target` 
3. Chart Tooltip menggunakan `formatted_revenue` dari backend
4. Y-axis menggunakan format M/T yang konsisten

**Format Currency Logic**:
```php
private function formatCurrency(float $value, int $decimals = 1): string
{
    if ($value >= 1000000000000) {
        // Triliun (>= 1000 Miliar)
        return 'Rp ' . number_format($value / 1000000000000, $decimals) . 'T';
    } else {
        // Miliar
        return 'Rp ' . number_format($value / 1000000000, $decimals) . 'M';
    }
}
```

**Benefits**:
- ✅ Konsistensi format currency di seluruh aplikasi
- ✅ Format otomatis dari backend (single source of truth)
- ✅ Mudah maintenance (perubahan di satu tempat)
- ✅ Support Triliun dan Miliar dengan auto-detection

---

## ✅ Completed Tasks

### 1. Total Account → Total AM
**Status**: ✅ Completed  
**Changes**:
- Card title: "Total Account" → "Total AM"
- Data source: `account_managers` table (COUNT)
- Method: `getTotalAM()` di `DashboardController.php`
- Logic: Menghitung total Account Manager yang terdaftar
```php
// Query: SELECT COUNT(*) FROM account_managers
return DB::table('account_managers')->count();
```

---

### 2. Active Account → Revenue Target
**Status**: ✅ Completed  
**Changes**:
- Card title: "Active Account" → "Revenue Target"
- Data source: `target_account_m.t_revenue` (SUM filtered by year & quartal)
- Method: `getTotalRevenueTarget($year, $quartal)` di `DashboardController.php`
- Logic: Menjumlahkan t_revenue untuk period yang dipilih
```php
// Query: SUM(t_revenue) WHERE tahun = $year AND quartal = $quartal
return DB::table('target_account_m')
    ->join('lini_waktu_target', 'target_account_m.id', '=', 'lini_waktu_target.target_id')
    ->join('lini_waktu', 'lini_waktu_target.lini_waktu_id', '=', 'lini_waktu.id')
    ->where('lini_waktu.tahun', $year)
    ->where('lini_waktu.quartal', $quartal)
    ->sum('target_account_m.t_revenue') ?? 0;
```

---

### 3. Revenue Target → Year (Dropdown)
**Status**: ✅ Completed  
**Changes**:
- Card title: "Revenue Target" → "Year"
- Component: Dropdown select dengan daftar tahun
- Display: Menampilkan bulan_awal - bulan_akhir untuk tahun dipilih
- Data source: `lini_waktu.tahun` (DISTINCT)
- Method: 
  - `getAvailableYears()` - Get list tahun
  - `getPeriodDetails($year, $quartal)` - Get bulan_awal & bulan_akhir
```php
// Query untuk tahun: SELECT DISTINCT tahun FROM lini_waktu ORDER BY tahun DESC
// Query untuk periode: SELECT bulan_awal, bulan_akhir WHERE tahun & quartal
```

**UI Features**:
- Default: Tahun terbaru otomatis selected
- OnChange: Trigger Inertia router untuk update data
- Display: "Jan - Des 2024" format

---

### 4. Revenue Achieved → Quartal (Dropdown)
**Status**: ✅ Completed  
**Changes**:
- Card title: "Revenue Achieved" → "Quartal"
- Component: Dropdown select dengan Q1-Q4
- Data source: `lini_waktu.quartal` untuk tahun tertentu
- Method: `getAvailableQuartals($year)`
```php
// Query: SELECT DISTINCT quartal FROM lini_waktu WHERE tahun = $year ORDER BY quartal
```

**UI Features**:
- Default: Quartal saat ini otomatis selected (berdasarkan bulan)
- OnChange: Trigger Inertia router untuk update data
- Format: Q1, Q2, Q3, Q4

**Current Quartal Logic**:
```php
private function getCurrentQuartal(): string
{
    $month = now()->month;
    return match(true) {
        $month <= 3 => 'Q1',
        $month <= 6 => 'Q2',
        $month <= 9 => 'Q3',
        default => 'Q4'
    };
}
```

---

### 5. Achievement Rate → Export/Import Data
**Status**: ✅ Completed  
**Changes**:
- Card title: "Achievement Rate" → "Export/Import Data"
- Component: Dua tombol action
  - Export button (Download icon)
  - Import button (Upload icon)
- Functionality: Placeholder untuk future implementation
```tsx
// Export button
<Button variant="outline">
  <Download className="h-4 w-4" />
  Export
</Button>

// Import button
<Button variant="outline">
  <Upload className="h-4 w-4" />
  Import
</Button>
```

---

### 6. AM Performance Ranking → Target Revenue AM (Bar Chart)
**Status**: ✅ Completed + Filtered  
**Changes**:
- Chart type: Bar chart horizontal (Recharts)
- Data source: `target_account_m.t_revenue` per AM
- Method: `getAMRevenueRanking($year, $quartal)` - **NOW FILTERED**
- Display: Semua Account Manager dengan target revenue mereka

**Chart Configuration**:
- Width: 70% (lg:col-span-7 dari grid-cols-10)
- X-axis: Target Revenue (Milyar Rupiah)
- Y-axis: Nama Account Manager
- Color: Red (#ef4444)
- Layout: Horizontal bars

**Data Logic** (UPDATED):
```php
// Query: LEFT JOIN untuk menampilkan semua AM (termasuk yang 0)
// Filter: WHERE lini_waktu.tahun = $year AND lini_waktu.quartal = $quartal
return DB::table('account_managers')
    ->select(
        'account_managers.nama',
        DB::raw('COALESCE(SUM(target_account_m.t_revenue), 0) as t_revenue')
    )
    ->leftJoin('lini_waktu', function($join) use ($year, $quartal) {
        $join->on('account_managers.nik', '=', 'lini_waktu.nik_am')
             ->where('lini_waktu.tahun', '=', $year)
             ->where('lini_waktu.quartal', '=', $quartal);
    })
    ->leftJoin('lini_waktu_target', 'lini_waktu.id', '=', 'lini_waktu_target.lini_waktu_id')
    ->leftJoin('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
    ->groupBy('account_managers.nik', 'account_managers.nama')
    ->orderBy('t_revenue', 'desc')
    ->get();
```

**Why LEFT JOIN?**
- Menampilkan SEMUA Account Manager, termasuk yang tidak punya target (nilai 0)
- Lebih transparan untuk tracking performance

**Filter Behavior**:
- Chart akan UPDATE ketika user mengganti Year atau Quartal
- Data ditampilkan sesuai periode yang dipilih
- Test Results:
  - Q1 2024: Dewi Lestari & Kartika Putri = Rp 90M
  - Q2 2024: Dewi Lestari & Kartika Putri = Rp 100M

---

### 7. Account Distribution by Subsegment → Region Distribution (Pie Chart)
**Status**: ✅ Completed  
**Changes**:
- Chart type: Pie chart dengan custom labels
- Data source: `regions.code` dengan COUNT AM per region
- Method: `getRegionDistribution()`
- Display: Persentase di dalam pie, kode region di luar

**Chart Configuration**:
- Width: 30% (lg:col-span-3 dari grid-cols-10)
- Label Inside: Persentase (contoh: "45.5%")
- Label Outside: Region code (contoh: "REG-1")
- Colors: Array 7 warna (blue, green, yellow, red, purple, pink, orange)

**Data Logic**:
```php
// Query: COUNT AMs per region dengan percentage calculation
return DB::table('account_managers')
    ->select(
        'regions.code as region_name',
        DB::raw('COUNT(account_managers.nik) as total'),
        DB::raw('ROUND((COUNT(account_managers.nik) * 100.0 / 
                 (SELECT COUNT(*) FROM account_managers)), 1) as percentage')
    )
    ->join('witels', 'account_managers.witel_id', '=', 'witels.id')
    ->join('company_region', 'witels.company_region_id', '=', 'company_region.id')
    ->join('regions', 'company_region.region_id', '=', 'regions.id')
    ->groupBy('regions.id', 'regions.code')
    ->orderBy('total', 'desc')
    ->get();
```

**Custom Label Function**:
```tsx
// Label di dalam pie (percentage)
const renderCustomLabel = ({ percent }: any) => {
  return `${(percent * 100).toFixed(1)}%`;
};

// Label di luar pie (region code)
<Label value="region_name" position="outside" />
```

---

### 8. AM Performance Details → List Account Manager (Table)
**Status**: ✅ Completed  
**Changes**:
- Component: Table dengan 6 columns
- Data source: `account_managers` table dengan JOIN ke `witels`
- Method: `getAccountManagerList()`
- Display: Full list semua Account Manager dengan detail

**Table Columns**:
1. **No** - Row number
2. **NIK** - Employee ID
3. **Nama** - Account Manager name
4. **Email** - Contact email
5. **Telp** - Phone number
6. **Witel** - Work location (from `witels.nama_witels`)

**Data Logic**:
```php
// Query: SELECT dengan LEFT JOIN ke witels
return DB::table('account_managers')
    ->select(
        'account_managers.nik',
        'account_managers.nama',
        'account_managers.email',
        'account_managers.telp',
        'witels.nama_witels as witel'
    )
    ->leftJoin('witels', 'account_managers.witel_id', '=', 'witels.id')
    ->orderBy('account_managers.nama')
    ->get();
```

**UI Features**:
- Responsive table dengan scroll horizontal pada mobile
- Alternating row colors (stripe pattern)
- Hover effect pada rows
- Null handling untuk witel (tampilkan "-" jika tidak ada)

---

## 🔧 Bug Fixes & Refinements

### Fix 1: Column Name Error
**Issue**: Query error `Unknown column 'witels.nama_witel'`  
**Root Cause**: Typo di column name  
**Solution**: `nama_witel` → `nama_witels`  
**File**: `DashboardController.php` line ~278  

### Fix 2: Chart Filtering Not Working
**Issue**: Target Revenue AM chart menampilkan SUM dari semua period  
**Root Cause**: Query tidak menggunakan parameter $year dan $quartal  
**Solution**: Tambah WHERE di LEFT JOIN  
```php
->leftJoin('lini_waktu', function($join) use ($year, $quartal) {
    $join->on('account_managers.nik', '=', 'lini_waktu.nik_am')
         ->where('lini_waktu.tahun', '=', $year)
         ->where('lini_waktu.quartal', '=', $quartal);
})
```
**Tested**: ✅ Q1 vs Q2 2024 menunjukkan nilai berbeda

### Refinement 1: Wrong Region Label
**Issue**: Pie chart menggunakan `regions.description` (panjang)  
**Change**: Ganti ke `regions.code` (REG-1, REG-2, dll)  
**Reason**: Lebih ringkas dan sesuai kebutuhan  

### Refinement 2: Chart Layout Proportion
**Issue**: Chart 50:50 tidak optimal untuk readability  
**Change**: Bar chart 70%, Pie chart 30%  
**Implementation**: `lg:grid-cols-10` dengan `lg:col-span-7` dan `lg:col-span-3`  

### Refinement 3: Pie Chart Labels Cluttered
**Issue**: Percentage dan region name keduanya di luar pie  
**Change**: 
- Percentage di DALAM pie slice
- Region code di LUAR pie
**Implementation**: Custom `renderCustomLabel` function + `<Label position="outside" />`

### Refinement 5: Tooltip Region Distribution
**Issue**: Tooltip menampilkan persentase yang redundant (sudah ada di dalam pie)  
**Change**: Tooltip hanya menampilkan jumlah AM tanpa persentase  
**Before**: `5 AM (22.7%)`  
**After**: `5 Account Manager`  
**Reason**: Lebih fokus ke informasi utama (jumlah AM), persentase sudah terlihat di dalam pie  

### Refinement 4: Card Styling Inconsistent
**Issue**: Cards pakai gradient backgrounds, tidak match Dashboard  
**Change**: Ganti ke white background dengan gray border  
**Classes**: `bg-white border border-gray-200 shadow-sm hover:shadow-md`  
**Consistency**: Sekarang match dengan Dashboard `StatCard` component  

---

## 🗂️ File Structure

### Backend Files
```
app/Http/Controllers/
  └── DashboardController.php  [MODIFIED]
      ├── Line ~38-75:   performanceAM() - Main method
      ├── Line ~77-84:   getTotalAM()
      ├── Line ~86-108:  getTotalRevenueTarget($year, $quartal)
      ├── Line ~110-117: getAvailableYears()
      ├── Line ~119-126: getAvailableQuartals($year)
      ├── Line ~128-146: getPeriodDetails($year, $quartal)
      ├── Line ~148-156: getCurrentQuartal()
      ├── Line ~206-233: getAMRevenueRanking($year, $quartal) [FILTERED]
      ├── Line ~235-267: getRegionDistribution()
      └── Line ~269-286: getAccountManagerList()
```

### Frontend Files
```
resources/js/pages/
  └── PerformanceAm.tsx  [MODIFIED]
      ├── Line 51-89:   Component logic & state management
      ├── Line 91-222:  Five metric cards (Total AM, Revenue Target, Year, Quartal, Export/Import)
      ├── Line 224-256: Target Revenue AM chart (Bar chart - 70%)
      ├── Line 258-292: Region Distribution chart (Pie chart - 30%)
      └── Line 294-379: List Account Manager table
```

### Test Files
```
test-am-revenue.php  [NEW]
  └── Test script untuk verifikasi filter year/quartal pada query
```

---

## 📊 Database Schema Reference

### Tables Used
1. **account_managers** - Master data AM
   - `nik` (PK)
   - `nama`, `email`, `telp`
   - `witel_id` (FK to witels)

2. **lini_waktu** - Timeline periods
   - `id` (PK)
   - `nik_am` (FK to account_managers)
   - `tahun`, `quartal`, `bulan_awal`, `bulan_akhir`

3. **lini_waktu_target** - Pivot table
   - `lini_waktu_id` (FK)
   - `target_id` (FK)

4. **target_account_m** - Target data
   - `id` (PK)
   - `t_revenue` (decimal 15,2)

5. **witels** - Work locations
   - `id` (PK)
   - `nama_witels`
   - `company_region_id` (FK)

6. **company_region** - Company regions
   - `id` (PK)
   - `region_id` (FK)

7. **regions** - Regional divisions
   - `id` (PK)
   - `code` (REG-1, REG-2, dll)
   - `description`

---

## 🧪 Testing Results

### Query Filtering Test (Q1 vs Q2 2024)
```
✅ Q1 2024:
- Dewi Lestari: Rp 90.000.000.000
- Kartika Putri: Rp 90.000.000.000
- Hendra Kusuma: Rp 70.000.000.000

✅ Q2 2024:
- Dewi Lestari: Rp 100.000.000.000
- Kartika Putri: Rp 100.000.000.000
- Hendra Kusuma: Rp 80.000.000.000

Result: ✅ Filtering works correctly, values differ per quartal
```

### Frontend Build Test
```
Command: npm run build
Duration: 9.14 seconds
Result: ✅ Success (44 files generated)
Bundle: PerformanceAm-CMUXo7fX.js (32.79 kB, gzipped 10.06 kB)
Errors: 0
Warnings: 0
```

---

## 🎨 UI/UX Design Decisions

### Card Layout
- **5 cards** dalam responsive grid
- Mobile: 1 column (full width)
- Desktop: 3 columns untuk 3 cards pertama, 2 columns untuk 2 cards terakhir
- Styling: Match Dashboard dengan white background + gray border

### Chart Layout
- **2 charts** dalam responsive grid proporsi 70:30
- Mobile: Stack vertically (1 column)
- Desktop: Side by side dengan grid-cols-10
- Bar chart: 7 spans (lebih lebar karena data lebih kompleks)
- Pie chart: 3 spans (cukup untuk visualisasi distribusi)

### Table Layout
- Responsive horizontal scroll
- 6 columns dengan width proporsional
- Stripe pattern untuk readability
- Hover effect untuk interactivity

---

## 🚀 Performance Considerations

### Query Optimization
- LEFT JOIN digunakan untuk include AMs tanpa target (avoid data loss)
- WHERE conditions di JOIN untuk filter early (reduce result set)
- COALESCE untuk handle NULL values (avoid errors)
- Index hints: Pastikan ada index di:
  - `account_managers.nik`
  - `lini_waktu.tahun`, `lini_waktu.quartal`
  - `witels.id`, `regions.id`

### Frontend Optimization
- Inertia.js untuk SPA behavior (no full page reload)
- Recharts lazy loading untuk charts
- Memoization untuk expensive calculations
- Bundle size: 32.79 kB (acceptable for chart-heavy page)

---

## 📝 Code Comments Summary

Semua method di `DashboardController.php` sudah memiliki PHP docblock yang menjelaskan:
- **@return** - Return type dan description
- **Purpose** - Tujuan method
- **Data Source** - Tabel database yang digunakan
- **Logic** - Penjelasan query logic (JOIN, WHERE, GROUP BY)
- **Note** - Catatan khusus (LEFT JOIN, NULL handling, dll)

Example:
```php
/**
 * Get AM revenue ranking filtered by year and quartal
 * 
 * @param int $year Tahun yang dipilih
 * @param string $quartal Quartal yang dipilih (Q1, Q2, Q3, Q4)
 * @return \Illuminate\Support\Collection Collection of AMs dengan t_revenue
 * 
 * Data Source:
 * - account_managers: Master data AM
 * - lini_waktu: Timeline periods (FILTERED by tahun & quartal)
 * - lini_waktu_target: Pivot table
 * - target_account_m: Target data dengan t_revenue
 * 
 * Logic:
 * - LEFT JOIN untuk include semua AM (termasuk yang tidak punya target)
 * - WHERE conditions di JOIN untuk filter by tahun dan quartal
 * - SUM t_revenue per AM untuk period yang dipilih
 * - COALESCE untuk handle NULL (tampilkan 0 instead of NULL)
 * - ORDER BY DESC untuk ranking tertinggi di atas
 */
```

---

## ✅ Completion Checklist

- [x] Task 1: Total Account → Total AM
- [x] Task 2: Active Account → Revenue Target
- [x] Task 3: Revenue Target → Year dropdown
- [x] Task 4: Revenue Achieved → Quartal dropdown
- [x] Task 5: Achievement Rate → Export/Import buttons
- [x] Task 6: AM Performance Ranking → Target Revenue AM chart
- [x] Task 7: Account Distribution → Region Distribution chart
- [x] Task 8: AM Performance Details → List AM table
- [x] Fix: Column name error (nama_witel)
- [x] Fix: Chart filtering by year/quartal
- [x] Refinement: Wrong region label (description → code)
- [x] Refinement: Chart layout proportion (70:30)
- [x] Refinement: Pie chart labels (inside/outside)
- [x] Refinement: Card styling consistency
- [x] Refinement: Tooltip Region Distribution (show AM count only)
- [x] Testing: Query filtering test
- [x] Testing: Frontend build test
- [x] Documentation: Code comments
- [x] Documentation: Update log
- [x] Currency Format: Konsistensi dengan Dashboard formatCurrency()

---

## 🎯 Next Steps (Future Enhancements)

1. **Export Functionality**
   - Implement Excel export untuk AM list
   - Include filters (year, quartal, region)
   - Format: XLSX dengan styling

2. **Import Functionality**
   - Template Excel untuk batch upload
   - Validation rules
   - Error handling & reporting

3. **Advanced Filtering**
   - Filter by region di chart
   - Filter by witel di table
   - Search functionality untuk AM name/NIK

4. **Data Visualization Enhancements**
   - Trend chart untuk revenue over time
   - Comparison chart (actual vs target)
   - Heatmap untuk regional performance

5. **Performance Monitoring**
   - Query performance logging
   - Cache implementation untuk frequent queries
   - Pagination untuk large datasets

---

## 📞 Support & Maintenance

**Last Updated**: 2025-01-XX  
**Laravel Version**: 12.34.0  
**PHP Version**: 8.2+  
**Database**: MySQL 5.7+  

**Key Files**:
- Backend: `app/Http/Controllers/DashboardController.php`
- Frontend: `resources/js/pages/PerformanceAm.tsx`
- Routes: `routes/web.php` (Performance AM route)

**Testing Command**:
```bash
# Test query filtering
php test-am-revenue.php

# Build frontend
npm run build

# Clear cache
php artisan cache:clear
php artisan config:clear
```

---

**Status**: ✅ ALL TASKS COMPLETED  
**Build**: ✅ SUCCESS  
**Tests**: ✅ PASSED
