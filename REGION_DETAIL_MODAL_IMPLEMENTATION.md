# Region Detail Modal Implementation

## Overview
Implementation of drill-down functionality from the Subsegment Regional Table to show detailed company performance per region.

## Feature Description
When users click on a region row in the "Financial Performance & LOP by Subsegment" table, a modal displays:
- Regional summary metrics (revenue, target, achievement, YoY growth, company count)
- Detailed company list with individual performance metrics
- Each company shows: revenue, target, achievement percentage, YoY growth

## Implementation Date
January 2025

## Files Modified/Created

### 1. Frontend Components

#### RegionDetailModal.tsx (NEW)
**Location:** `resources/js/components/modals/RegionDetailModal.tsx`

**Key Features:**
- Full-screen modal with dark mode support
- Loading and error states
- Summary cards section with key metrics
- Companies table with sortable columns
- API integration with axios
- Responsive grid layout

**Props:**
```typescript
interface RegionDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    subsegment: string | null;
    regionCode: string | null;
    regionName: string | null;
    year: number;
}
```

**Data Structure:**
```typescript
interface RegionDetail {
    summary: {
        total_revenue: number;
        total_target: number;
        achievement: number;
        yoy_growth: number;
        company_count: number;
        formatted_total_revenue: string;
        formatted_total_target: string;
    };
    companies: Array<{
        nip_nas: string;
        nama_perusahaan: string;
        witel: string;
        revenue: number;
        target: number;
        achievement: number;
        yoy_growth: number;
        formatted_revenue: string;
        formatted_target: string;
    }>;
}
```

#### SubsegmentRegionalTable.tsx (UPDATED)
**Location:** `resources/js/components/SubsegmentRegionalTable.tsx`

**Changes:**
- Added `onRegionClick` prop to interface
- Added click handler to region table rows
- Added `cursor-pointer` class for visual feedback

**Code Added:**
```typescript
// Interface
onRegionClick?: (subsegment: string, regionCode: string, regionName: string) => void;

// Table row
<tr
    className="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
    onClick={() => onRegionClick?.(subsegment.subsegment, region.region_code, region.region_name)}
>
```

#### Dashboard.tsx (UPDATED)
**Location:** `resources/js/pages/Dashboard.tsx`

**Changes:**
1. Added import for `RegionDetailModal`
2. Added state management for region modal
3. Created `handleRegionClick` handler
4. Passed handler to `SubsegmentRegionalTable` component
5. Rendered `RegionDetailModal` in JSX

**Code Added:**
```typescript
// Import
import RegionDetailModal from '@/components/modals/RegionDetailModal';

// State
const [selectedRegion, setSelectedRegion] = useState<{ 
    subsegment: string; 
    code: string; 
    name: string 
} | null>(null);
const [regionModalOpen, setRegionModalOpen] = useState(false);

// Handler
const handleRegionClick = (subsegment: string, regionCode: string, regionName: string) => {
    setSelectedRegion({ subsegment, code: regionCode, name: regionName });
    setRegionModalOpen(true);
};

// Component props
<SubsegmentRegionalTable 
    data={subsegmentRegionalData} 
    onSubsegmentClick={handleSubsegmentClick}
    onRegionClick={handleRegionClick}
/>

// Modal render
<RegionDetailModal
    isOpen={regionModalOpen}
    onClose={() => setRegionModalOpen(false)}
    subsegment={selectedRegion?.subsegment || null}
    regionCode={selectedRegion?.code || null}
    regionName={selectedRegion?.name || null}
    year={selectedYear}
/>
```

### 2. Backend Implementation

#### DashboardController.php (UPDATED)
**Location:** `app/Http/Controllers/DashboardController.php`

**New Method:** `getRegionDetail(Request $request)`

**Functionality:**
1. Validates request parameters (subsegment, region_code, year)
2. Fetches companies in the specified region for the subsegment
3. Retrieves previous year data for YoY comparison
4. Calculates totals and metrics
5. Returns JSON response with summary and company details

**SQL Logic:**
- Joins: companies → witels → regions → group1-4 → revenues
- Filters: subsegment, region_code, year
- Groups by: company (nip_nas, nama_perusahaan, witel)
- Aggregates: SUM(revenue), SUM(target)

**YoY Growth Calculation:**
```php
$yoyGrowth = $prevRevenue > 0 
    ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
    : ($revenue > 0 ? 100 : 0);
```

**Validation Rules:**
```php
$request->validate([
    'subsegment' => 'required|string',
    'region_code' => 'required|string',
    'year' => 'required|integer|min:2020|max:2030'
]);
```

#### web.php (UPDATED)
**Location:** `routes/web.php`

**New Route:**
```php
Route::get('region-detail', [DashboardController::class, 'getRegionDetail'])
    ->name('api.dashboard.region-detail');
```

**Full Path:** `/api/dashboard/region-detail`

## API Endpoint

### GET /api/dashboard/region-detail

**Parameters:**
- `subsegment` (required): Subsegment name (e.g., "Mikro", "Enterprise")
- `region_code` (required): Region code (e.g., "R1", "R2")
- `year` (required): Target year (integer, 2020-2030)

**Response Structure:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_revenue": 123456789.50,
            "total_target": 150000000.00,
            "achievement": 82.3,
            "yoy_growth": 15.2,
            "company_count": 25,
            "formatted_total_revenue": "Rp 123.456.789",
            "formatted_total_target": "Rp 150.000.000"
        },
        "companies": [
            {
                "nip_nas": "123456",
                "nama_perusahaan": "PT Example Corp",
                "witel": "Jakarta Barat",
                "revenue": 5000000.00,
                "target": 6000000.00,
                "achievement": 83.3,
                "yoy_growth": 12.5,
                "formatted_revenue": "Rp 5.000.000",
                "formatted_target": "Rp 6.000.000"
            }
        ],
        "subsegment": "Mikro",
        "region_code": "R1",
        "year": 2025
    }
}
```

## User Flow

1. User views Dashboard with Subsegment Regional Table
2. User clicks on a region row (e.g., "SUMBAGUT" in "Mikro" subsegment)
3. Modal opens showing:
   - Region name and subsegment in header
   - Summary cards: Revenue, Achievement, YoY Growth, Company Count
   - Company list table with detailed metrics
4. User can:
   - View all companies in that region for the subsegment
   - See individual company performance
   - Compare YoY growth per company
   - Close modal to return to dashboard

## Database Schema

**Tables Used:**
- `companies` - Company master data
- `witels` - Witel mapping (links companies to regions)
- `regions` - Regional hierarchy
- `group1` to `group4` - Payment groups
- `revenues` - Revenue transactions (realisasi and target)

**Key Relationships:**
```
companies (idwitels) → witels (idwitels)
witels (region_id) → regions (id)
companies (nip_nas) → group1 (company_id)
group1-4 → revenues (group4_id)
```

## Technical Notes

### Performance Considerations
- Query uses proper indexes on join columns
- Data aggregated at database level (SUM, COUNT)
- Previous year data fetched separately to avoid complex self-joins
- Results ordered by revenue DESC for most relevant companies first

### Error Handling
- Frontend: Loading states, error messages, null checks
- Backend: Request validation, try-catch for database errors
- Network: Axios error handling with user-friendly messages

### Accessibility
- Modal can be closed with X button or Close button
- Dark mode support throughout
- Responsive layout for different screen sizes
- Clear visual hierarchy with cards and tables

### Code Quality
- TypeScript interfaces for type safety
- Consistent naming conventions
- Reusable UI components from shadcn/ui
- Clean separation of concerns (API, state, presentation)

## Testing Recommendations

### Manual Testing
1. Click various regions across different subsegments
2. Verify data accuracy against database
3. Test with regions having 0 companies
4. Test YoY growth with companies having no previous year data
5. Verify dark mode appearance
6. Test modal close functionality
7. Check responsive behavior on different screen sizes

### Edge Cases
- Region with no companies → Should show empty table
- Companies with zero target → Achievement = 0%
- No previous year data → YoY growth = 100% if current revenue > 0
- Very large numbers → Should format correctly (Rp notation)

## Future Enhancements

### Potential Features
1. Export company list to CSV/Excel
2. Sort companies by different columns
3. Filter companies by witel
4. Show trend chart for top companies
5. Compare with other regions
6. Drill-down to individual company details
7. Add pagination for large company lists

### Performance Optimizations
1. Add caching for frequently accessed region data
2. Implement pagination for regions with many companies
3. Add database indexes on commonly queried columns
4. Consider materialized views for complex aggregations

## Related Documentation
- [Dashboard Main Documentation](./README.md)
- [Revenue Bar Chart Comparison](./COMPARE_FEATURE_DOCUMENTATION.md)
- [YTD Comparison Custom](./CUSTOM_YTD_COMPARISON_GUIDE.md)
- [Subsegment Details Modal](./SUBSEGMENT_DETAILS_MODAL.md) (if exists)

## Changelog

### 2025-01-XX - Initial Implementation
- Created RegionDetailModal component
- Added region click handler to SubsegmentRegionalTable
- Implemented getRegionDetail API endpoint
- Added API route for region details
- Integrated modal into Dashboard page
- Added YoY growth calculation for companies
- Implemented summary metrics display
