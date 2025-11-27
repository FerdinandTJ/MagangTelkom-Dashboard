# Compare Feature Implementation - Region NKI Modal

## Overview
Implementasi fitur compare pada modal NKI AM yang memungkinkan perbandingan data antar periode (quarter).

## Changes Made

### Backend Changes

#### 1. RegionNkiController.php
**File:** `app/Http/Controllers/RegionNkiController.php`

**New Features:**
- Added `compare` parameter support in API request
- Implemented `getPreviousPeriod()` method for calculating previous quarter
- Refactored main logic into `getPeriodData()` method for reusability
- New response structure with `current_period` and `previous_period`

**Key Methods:**

```php
// Calculate previous period (Q1 2025 → Q4 2024)
private function getPreviousPeriod($quarter, $year)
{
    if ($quarter == 1) {
        return ['quarter' => 4, 'year' => $year - 1];
    } else {
        return ['quarter' => $quarter - 1, 'year' => $year];
    }
}

// Fetch and calculate data for specific period
private function getPeriodData($regionId, $quarter, $year)
{
    // Returns array with:
    // - summary (target/realisasi revenue, total AM)
    // - segment_stats (Result/Process/NKI data per segment)
    // - parameter_result (10 parameters)
    // - parameter_proses (4 parameters)
}
```

**Response Structure:**
```json
{
  "region": {
    "id": 1,
    "name": "Headquarters TREG2"
  },
  "current_period": {
    "quarter": 3,
    "year": 2025,
    "label": "Q3 2025",
    "data": {
      "summary": {...},
      "segment_stats": [...],
      "parameter_result": {...},
      "parameter_proses": {...}
    }
  },
  "previous_period": {
    "quarter": 2,
    "year": 2025,
    "label": "Q2 2025",
    "data": {...}
  },
  "compare_enabled": true
}
```

### Frontend Changes

#### 2. RegionNkiModal.tsx
**File:** `resources/js/components/RegionNkiModal.tsx`

**New Features:**
- Updated interface to support multi-period data structure
- Added `TrendIndicator` component for numeric comparison
- Added `TrendIndicatorNKI` component for percentage comparison
- Modified table to show multiple period rows with trend arrows
- Updated Parameter tabs (Result & Process) to show period comparison

**Key Components:**

```typescript
// Show value with trend arrow (↑ green if higher, ↓ red if lower)
const TrendIndicator = ({ current, previous }: { current: number; previous: number | null }) => {
    if (previous === null) return <span>{current}</span>;
    
    if (current > previous) {
        return <span className="text-green-600">↑ {current}</span>;
    } else if (current < previous) {
        return <span className="text-red-600">↓ {current}</span>;
    }
    
    return <span>{current}</span>;
};

// Same as above but for NKI percentages
const TrendIndicatorNKI = ({ current, previous }) => {...};
```

**Table Structure Changes:**

**Before:**
```
| Segment  | Result Ach | Result Not Ach | ... |
|----------|------------|----------------|-----|
| HQ-TWS   |     2      |       1        | ... |
```

**After:**
```
| Triwulan | Result Ach | Result Not Ach | ... |
|----------|------------|----------------|-----|
| Q3 2025  |    ↑ 2     |      ↓ 1       | ... | (current - with arrows)
| Q2 2025  |     1      |       2        | ... | (previous - gray text)
```

**Visual Design:**
- **Current Period:** Bold text, colored arrows (green ↑ for higher, red ↓ for lower)
- **Previous Period:** Gray text, lighter background, no arrows
- **Column Header:** Changed from "Segments" to "Triwulan" (Quarter)

## API Usage

### Request
```javascript
GET /api/dashboard/region-nki/{regionId}?quarter=3&year=2025&compare=true
```

**Parameters:**
- `quarter` (required): 1-4
- `year` (required): 2020+
- `compare` (optional): boolean, default=false

### Example Response
```json
{
  "current_period": {
    "label": "Q3 2025",
    "data": {
      "summary": {
        "formatted_target_revenue": "Rp 480.48M",
        "formatted_realisasi_revenue": "Rp 412.34M",
        "total_am": 3
      },
      "segment_stats": [
        {
          "segment": "HQ-TWS",
          "result": {"ach": 2, "not_ach": 1},
          "proses": {"ach": 2, "not_ach": 1},
          "nki": {"above_100": 2, "below_100": 1},
          "highest_nki": 115.50,
          "lowest_nki": 85.20,
          "avg_nki": 98.40,
          "parameters_to_improve": "Revenue, Scaling"
        }
      ]
    }
  },
  "previous_period": {
    "label": "Q2 2025",
    "data": {
      // Same structure as current_period.data
    }
  }
}
```

## Comparison Logic

### Trend Indicators
- **↑ Green Arrow:** Current value > Previous value (improvement)
- **↓ Red Arrow:** Current value < Previous value (decline)
- **No Arrow:** Current value = Previous value OR no previous data

### Applied To:
1. **Summary Table:**
   - Result: Ach / Not Ach
   - Process: Ach / Not Ach
   - NKI: >100% / <100%
   - NKI Statistics: Highest / Lowest / Average

2. **Parameter Tabs:**
   - Aspek Result: All 10 parameters (Ach / Not Ach per parameter)
   - Aspek Process: All 4 parameters (Ach / Not Ach per parameter)

## User Experience

### When Opening Modal:
1. User clicks on HQ TREG2 in pie chart (Q3 2025)
2. Modal opens with compare automatically enabled
3. Shows current period (Q3 2025) with trend arrows
4. Shows previous period (Q2 2025) in gray for reference
5. Arrows indicate if each metric improved (↑ green) or declined (↓ red)

### Benefits:
- Quick visual identification of performance trends
- Easy comparison between consecutive quarters
- Understand which segments/parameters are improving or declining
- Data-driven insights for decision making

## Technical Notes

### Performance Considerations
- Previous period data fetched only when `compare=true`
- Single database query per period
- Data cached at controller level (if implemented)

### Edge Cases Handled
- Q1 comparison goes to previous year's Q4
- Missing previous period data shows current period without comparison
- Null safety checks for all comparisons

### Browser Compatibility
- Arrow symbols (↑ ↓) are Unicode characters
- Supported in all modern browsers
- Fallback to text if symbols not rendered

## Future Enhancements
Possible improvements:
1. Add percentage change calculation (e.g., "+15%")
2. Support comparing multiple periods (Q3 vs Q2 vs Q1)
3. Add trend charts for visual comparison
4. Export comparison data to Excel
5. Add custom period selection (compare any two quarters)

## Testing Checklist
- [x] Backend returns correct previous period (Q1 → Q4 previous year)
- [x] Frontend displays both periods correctly
- [x] Trend arrows show correct direction (up/down)
- [x] Colors applied correctly (green=improvement, red=decline)
- [x] Works when previous period has no data
- [x] Parameter tabs show comparison correctly
- [x] Build completes without errors
- [x] TypeScript types are correct

## Deployment Notes
1. Run `npm run build` to compile assets
2. Clear Laravel cache: `php artisan cache:clear`
3. No database migrations required
4. No .env changes needed

---

**Last Updated:** January 2025
**Author:** Development Team
**Version:** 1.0
