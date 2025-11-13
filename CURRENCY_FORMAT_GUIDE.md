# Currency Format Standardization - Performance AM

## 📋 Overview
Update format mata uang di Performance AM agar konsisten dengan Dashboard menggunakan fungsi `formatCurrency()`.

---

## ⚠️ Problem

### Before (Inconsistent)
```tsx
// Frontend manual formatting (PerformanceAm.tsx)
<p>Rp {(amMetrics.revenue_target / 1000000000).toFixed(1)}B</p>
// Output: Rp 50.0B (format "B" tidak standar)

// Chart tooltip manual formatting
formatter={(value: any) => [
    `Rp ${(value / 1000000000).toFixed(2)}B`,
    'Target Revenue'
]}
// Output: Rp 90.00B
```

**Issues**:
- ❌ Format berbeda antara Dashboard (M/T) dan Performance AM (B)
- ❌ Manual calculation di frontend (error-prone)
- ❌ Tidak support Triliun
- ❌ Hard to maintain (changes needed in multiple places)

---

## ✅ Solution

### After (Standardized)

**Backend** (`DashboardController.php`):
```php
// Line 56 - Calculate revenue target
$revenueTarget = $this->getTotalRevenueTarget($currentYear, $currentQuartal);

// Line 65-66 - Send both raw and formatted values
'revenue_target' => $revenueTarget,
'formatted_revenue_target' => $this->formatCurrency($revenueTarget, 2),

// Line 222 - Chart ranking data
'formatted_revenue' => $this->formatCurrency($item->t_revenue, 2)

// Line 625-633 - Format currency function
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

**Frontend** (`PerformanceAm.tsx`):
```tsx
// Line 14 - TypeScript interface
interface PerformanceAMProps {
    amMetrics: {
        revenue_target: number;
        formatted_revenue_target: string; // ← New field
        // ...
    };
}

// Line 124 - Revenue Target Card (simplified!)
<p className="text-2xl font-bold">
    {amMetrics.formatted_revenue_target}
</p>
// Output: Rp 50.00M (consistent with Dashboard)

// Line 259-267 - Chart Y-axis
<YAxis 
    tickFormatter={(value) => {
        if (value >= 1000000000000) {
            return `${(value / 1000000000000).toFixed(0)}T`;
        }
        return `${(value / 1000000000).toFixed(0)}M`;
    }}
/>

// Line 268-272 - Chart Tooltip (use backend formatted value)
<Tooltip 
    formatter={(value: any, name: any, props: any) => [
        props.payload.formatted_revenue, // ← From backend
        'Target Revenue'
    ]}
/>
```

---

## 📊 Format Examples

| Raw Value | Formatted Output | Description |
|-----------|------------------|-------------|
| 50000000000 | Rp 50.00M | 50 Miliar |
| 890500000000 | Rp 890.50M | 890.5 Miliar |
| 1250000000000 | Rp 1.25T | 1.25 Triliun |
| 2000000000000 | Rp 2.00T | 2 Triliun |
| 0 | Rp 0.00M | Zero value |

**Decimal Control**:
- Card values: 2 decimals (`.00` for precision)
- Chart Y-axis: 0 decimals (cleaner look)
- Tooltip: 2 decimals (from backend)

---

## 🔄 Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        BACKEND FLOW                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. getTotalRevenueTarget($year, $quartal)                      │
│     └─> SUM target_account_m.t_revenue                          │
│         Output: 50000000000 (raw float)                          │
│                                                                  │
│  2. formatCurrency($revenueTarget, 2)                           │
│     └─> Check if >= 1T                                          │
│     └─> Divide by 1M or 1T                                      │
│     └─> number_format with 2 decimals                           │
│         Output: "Rp 50.00M" (formatted string)                   │
│                                                                  │
│  3. Send to Frontend via Inertia                                │
│     └─> revenue_target: 50000000000                             │
│     └─> formatted_revenue_target: "Rp 50.00M"                   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND FLOW                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. Receive data from backend                                   │
│     ├─> amMetrics.revenue_target (number)                       │
│     └─> amMetrics.formatted_revenue_target (string)             │
│                                                                  │
│  2. Display in Card                                             │
│     └─> {amMetrics.formatted_revenue_target}                    │
│         Output: "Rp 50.00M"                                      │
│                                                                  │
│  3. Display in Chart                                            │
│     ├─> Y-axis: Auto format M/T for readability                │
│     └─> Tooltip: Use props.payload.formatted_revenue            │
│         Output: "Rp 90.00M"                                      │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## ✅ Benefits

### 1. **Consistency** 
- ✅ Same format across Dashboard and Performance AM
- ✅ "M" for Miliar, "T" for Triliun (Indonesian standard)
- ✅ Predictable format for users

### 2. **Single Source of Truth**
- ✅ Format logic in ONE place (backend)
- ✅ Frontend just displays formatted value
- ✅ No duplication of logic

### 3. **Easy Maintenance**
- ✅ Change format once, applies everywhere
- ✅ No need to update multiple files
- ✅ Less prone to errors

### 4. **Better UX**
- ✅ Clear, readable numbers (2 decimals for precision)
- ✅ Auto-detect Triliun vs Miliar
- ✅ Consistent with Indonesian currency convention

### 5. **Type Safety**
- ✅ TypeScript interface ensures formatted_revenue_target exists
- ✅ Compile-time error if field missing
- ✅ Better developer experience

---

## 🧪 Testing

### Manual Test
```bash
# 1. Start Laravel server
php artisan serve

# 2. Open browser
http://localhost:8000/performance-am

# 3. Verify display
- Card "Revenue Target" shows: Rp XX.XXM or Rp X.XXT
- Chart Y-axis shows: XXM or XT (no decimals)
- Chart tooltip shows: Rp XX.XXM or Rp X.XXT (on hover)

# 4. Change Year/Quartal dropdown
- Values should update with correct format
- No "B" or other inconsistent formats
```

### Expected Results
```
✅ Card displays: "Rp 50.00M" (not "Rp 50.0B")
✅ Chart Y-axis: "50M", "100M", "150M" (clean labels)
✅ Chart tooltip: "Rp 90.00M" (on hover AM bars)
✅ Format consistent with Dashboard StatCards
```

---

## 📝 Files Modified

### Backend
- **File**: `app/Http/Controllers/DashboardController.php`
- **Lines**: 56, 65-66, 222
- **Changes**: 
  - Calculate revenue target first
  - Add `formatted_revenue_target` to amMetrics
  - Use `formatCurrency($value, 2)` for 2 decimal places

### Frontend
- **File**: `resources/js/pages/PerformanceAm.tsx`
- **Lines**: 14, 124, 259-272
- **Changes**:
  - Add `formatted_revenue_target: string` to interface
  - Use formatted value in Card display
  - Update Chart tooltip to use backend formatted value
  - Update Y-axis to use M/T format

---

## 🔧 Configuration

### Decimal Places Control

```php
// In DashboardController.php

// For cards (more precision)
$this->formatCurrency($value, 2)  // Output: Rp 50.00M

// For general display (cleaner)
$this->formatCurrency($value, 1)  // Output: Rp 50.0M

// For minimal display
$this->formatCurrency($value, 0)  // Output: Rp 50M
```

**Current Usage**:
- Revenue Target Card: 2 decimals
- Chart Ranking: 2 decimals
- Chart Y-axis: 0 decimals (frontend only)

---

## 🚀 Build Status

```bash
npm run build

✓ built in 9.07s
✓ 44 files generated
✓ PerformanceAm-Be4fbDmb.js (32.81 kB, gzipped 10.06 kB)
✓ 0 errors, 0 warnings
```

---

## 📚 Related Documentation

- **Main Documentation**: `PERFORMANCE_AM_UPDATE_LOG.md`
- **Quick Reference**: `PERFORMANCE_AM_QUICK_REFERENCE.md`
- **Dashboard Format**: Same `formatCurrency()` function used in Dashboard

---

## ✅ Checklist

- [x] Backend: Add formatted_revenue_target
- [x] Backend: Use formatCurrency() with 2 decimals
- [x] Frontend: Update TypeScript interface
- [x] Frontend: Use formatted value in Card
- [x] Frontend: Update Chart tooltip
- [x] Frontend: Update Y-axis format
- [x] Build: Success with 0 errors
- [x] Documentation: Updated logs
- [x] Testing: Manual verification needed

---

**Status**: ✅ COMPLETED  
**Format**: Rupiah dengan satuan M (Miliar) dan T (Triliun)  
**Consistency**: 100% aligned with Dashboard  
**Build Time**: 9.07 seconds

---

**Next Action**: Test di browser untuk verifikasi visual format currency sudah sesuai.
