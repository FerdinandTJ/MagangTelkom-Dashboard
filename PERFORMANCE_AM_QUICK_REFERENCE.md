# Performance AM - Quick Reference

## 🎯 8 Major Changes Overview

| No | Before | After | Method | Key Data |
|----|--------|-------|--------|----------|
| 1 | Total Account | **Total AM** | `getTotalAM()` | COUNT(account_managers) |
| 2 | Active Account | **Revenue Target** | `getTotalRevenueTarget()` | SUM(t_revenue) filtered |
| 3 | Revenue Target | **Year Dropdown** | `getAvailableYears()` | SELECT DISTINCT tahun |
| 4 | Revenue Achieved | **Quartal Dropdown** | `getAvailableQuartals()` | SELECT DISTINCT quartal |
| 5 | Achievement Rate | **Export/Import Buttons** | N/A | UI only |
| 6 | AM Performance Ranking | **Target Revenue AM Chart** | `getAMRevenueRanking()` | Bar chart (70% width) |
| 7 | Account Distribution | **Region Distribution Chart** | `getRegionDistribution()` | Pie chart (30% width) |
| 8 | AM Performance Details | **List Account Manager Table** | `getAccountManagerList()` | 6 columns table |

---

## 🔧 Critical Fix: Chart Filtering

### Problem
Chart menampilkan **SUM dari semua period**, tidak mengikuti filter Year/Quartal yang dipilih user.

### Solution
Tambahkan WHERE conditions di LEFT JOIN:

```php
->leftJoin('lini_waktu', function($join) use ($year, $quartal) {
    $join->on('account_managers.nik', '=', 'lini_waktu.nik_am')
         ->where('lini_waktu.tahun', '=', $year)      // ← Filter by year
         ->where('lini_waktu.quartal', '=', $quartal); // ← Filter by quartal
})
```

### Test Results
```
Q1 2024: Dewi Lestari = Rp 90M
Q2 2024: Dewi Lestari = Rp 100M
✅ Values differ correctly per period
```

---

## 📊 Chart Configuration

### Target Revenue AM (Bar Chart)
- **Width**: 70% (lg:col-span-7)
- **Type**: Horizontal bars
- **Data**: All AMs with t_revenue (including 0)
- **Filter**: By year & quartal
- **Color**: Red (#ef4444)
- **Query**: LEFT JOIN (shows all AMs even without targets)

### Region Distribution (Pie Chart)
- **Width**: 30% (lg:col-span-3)
- **Type**: Pie with custom labels
- **Data**: AM count per region with percentage
- **Label Inside**: Percentage (e.g., "45.5%")
- **Label Outside**: Region code (e.g., "REG-1")
- **Tooltip**: AM count only (e.g., "5 Account Manager")
- **Query**: COUNT with percentage calculation

---

## 🎨 UI Styling

### Cards (Match Dashboard)
```tsx
className="bg-white border border-gray-200 shadow-sm hover:shadow-md transition-shadow"
```
**Changed from**: Gradient backgrounds  
**Why**: Consistency with Dashboard StatCard design

### Icon Background
```tsx
className="p-3 bg-red-500 rounded-lg"
```
**Color**: Red for all 5 cards  
**Icons**: Users2, DollarSign, Calendar, Filter, Database

---

## 🗂️ Methods Reference

### DashboardController.php

| Method | Line | Purpose | Returns |
|--------|------|---------|---------|
| `performanceAM()` | ~38-75 | Main controller method | Inertia response |
| `getTotalAM()` | ~77-84 | Count total AMs | int |
| `getTotalRevenueTarget()` | ~86-108 | Sum t_revenue for period | float |
| `getAvailableYears()` | ~110-117 | Get distinct years | Collection |
| `getAvailableQuartals()` | ~119-126 | Get quartals for year | Collection |
| `getPeriodDetails()` | ~128-146 | Get month range | object |
| `getCurrentQuartal()` | ~148-156 | Calculate current Q | string |
| `getAMRevenueRanking()` | ~206-233 | **AM revenue (filtered)** | Collection |
| `getRegionDistribution()` | ~235-267 | Count AMs per region | Collection |
| `getAccountManagerList()` | ~269-286 | Full AM list with witel | Collection |

---

## 🧪 Testing Commands

```bash
# Test query filtering (Q1 vs Q2)
php test-am-revenue.php

# Build frontend
npm run build

# Clear cache
php artisan cache:clear && php artisan config:clear

# Check database tables
php artisan db:table account_managers
php artisan db:table lini_waktu
php artisan db:table target_account_m
```

---

## 🚨 Common Issues & Fixes

### Issue 1: Column Not Found
**Error**: `Unknown column 'witels.nama_witel'`  
**Fix**: Change to `nama_witels` (with 's')

### Issue 2: Chart Not Filtering
**Symptom**: Same values for all year/quartal  
**Fix**: Add WHERE in LEFT JOIN (see Critical Fix above)

### Issue 3: NULL Values in Chart
**Symptom**: Some AMs show null instead of 0  
**Fix**: Use `COALESCE(SUM(...), 0)` in SELECT

### Issue 4: Pie Chart Labels Overlap
**Symptom**: Text cluttered outside pie  
**Fix**: Percentage inside, code outside with custom renderLabel

### Issue 5: Tooltip Shows Redundant Percentage
**Symptom**: Tooltip shows percentage that's already visible inside pie  
**Fix**: Show only AM count without percentage  
**Example**: `5 Account Manager` (was: `5 AM (22.7%)`)

---

## 📁 Key Files

```
app/Http/Controllers/
  └── DashboardController.php     ← Backend logic (10 methods)

resources/js/pages/
  └── PerformanceAm.tsx           ← Frontend UI (379 lines)

test-am-revenue.php               ← Test script for filtering

PERFORMANCE_AM_UPDATE_LOG.md      ← Full documentation
PERFORMANCE_AM_QUICK_REFERENCE.md ← This file
```

---

## 🎯 Status

✅ **ALL 8 TASKS COMPLETED**  
✅ **CHART FILTERING FIXED**  
✅ **CURRENCY FORMAT STANDARDIZED**  
✅ **BUILD SUCCESS** (9.07s, 0 errors)  
✅ **TESTS PASSED** (Q1 vs Q2 different values)

---

## � Currency Format

Semua nilai Rupiah menggunakan fungsi `formatCurrency()` yang konsisten dengan Dashboard:

```php
// DashboardController.php line 625
private function formatCurrency(float $value, int $decimals = 1): string
{
    if ($value >= 1000000000000) {
        return 'Rp ' . number_format($value / 1000000000000, $decimals) . 'T';
    } else {
        return 'Rp ' . number_format($value / 1000000000, $decimals) . 'M';
    }
}
```

**Implementasi**:
- ✅ Revenue Target Card: `formatted_revenue_target` (2 decimals)
- ✅ Chart Ranking: `formatted_revenue` per AM (2 decimals)
- ✅ Chart Tooltip: Menggunakan formatted value dari backend
- ✅ Chart Y-axis: Auto M/T format

**Format Examples**:
- Rp 50.00M (50 Miliar)
- Rp 1.25T (1.25 Triliun)
- Rp 890.50M (890.5 Miliar)

---

## �📞 Quick Links

- Full Documentation: `PERFORMANCE_AM_UPDATE_LOG.md`
- Test Script: `test-am-revenue.php`
- Controller: `app/Http/Controllers/DashboardController.php`
- Frontend: `resources/js/pages/PerformanceAm.tsx`

**Last Updated**: 2025-01-XX  
**Laravel**: 12.34.0 | **PHP**: 8.2+ | **DB**: MySQL 5.7+
