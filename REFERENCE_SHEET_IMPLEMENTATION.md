# Reference Sheet Implementation Guide

## Overview
Added "Region_and_Witel" reference sheet to all Excel downloads for revenue import/export functionality. This provides users with a master data reference for valid Region and Witel IDs.

## Implementation Date
January 2026

## Features

### 1. Reference Sheet Structure
- **Sheet Name**: `Region_and_Witel`
- **Position**: First sheet (index 0) in all Excel files
- **Active Sheet**: Revenue/data sheet remains active when user opens file
- **Data Source**: Fresh query from `regions` and `witels` tables on every download

### 2. Sheet Columns
| Column | Header | Description | Width |
|--------|--------|-------------|-------|
| A | Region ID | Unique region identifier | 12 |
| B | Nama Region | Region name | 15 |
| C | Description | Region description | 25 |
| D | ID WITEL | Witel identifier | 12 |
| E | Nama Witel | Witel name | 30 |

### 3. Styling
- **Header Row**:
  - Background: Green (#70AD47)
  - Font: White, Bold, 11pt
  - Alignment: Center horizontal and vertical
  - Borders: Thin black borders on all sides
  - Frozen: Row 1 frozen (header always visible)

- **Data Rows**:
  - Borders: Thin black borders on all cells
  - Merged Cells: Region columns (A, B, C) merged vertically for witels belonging to same region
  - Alignment: Merged region cells have vertical center alignment

### 4. Data Population
```php
// Query
$regions = DB::table('regions')->orderBy('id')->get();
$witels = DB::table('witels')->orderBy('region_id')->orderBy('idwitels')->get();

// Merging Logic:
// - Region columns merge when multiple witels belong to same region
// - Each witel gets its own row
// - Region info repeats but is visually merged
```

Example output:
```
Region ID | Nama Region | Description    | ID WITEL | Nama Witel
----------|-------------|----------------|----------|------------------
1         | TREG 1      | Jakarta Region | WTL-1    | Witel Jakarta Utara
(merged)  | (merged)    | (merged)       | WTL-2    | Witel Jakarta Selatan
2         | TREG 2      | Bandung Region | WTL-3    | Witel Bandung
```

## Files Modified

### 1. `app/Http/Controllers/RevenueImportController.php`

#### Added Helper Method (before line 793)
```php
/**
 * Helper: Add Region and Witel reference sheet to spreadsheet
 * Creates a reference sheet with region and witel data for user guidance
 */
private function addRegionWitelReferenceSheet(Spreadsheet $spreadsheet): void
{
    // Query fresh data
    $regions = DB::table('regions')->orderBy('id')->get();
    $witels = DB::table('witels')->orderBy('region_id')->orderBy('idwitels')->get();
    
    // Create sheet at index 0
    $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Region_and_Witel');
    $spreadsheet->addSheet($sheet, 0);
    
    // Set headers with green styling
    // Write data with region cell merging
    // Apply borders and freeze panes
    // ... (full implementation in controller)
}
```

#### Updated `downloadTemplate($year)` Method
- Calls `$this->addRegionWitelReferenceSheet($spreadsheet)` after creating spreadsheet
- Creates revenue sheet as `new Worksheet` at index 1 (instead of using active sheet)
- All template writing uses `$revenueSheet` instead of `$sheet`
- Sets revenue sheet as active: `$spreadsheet->setActiveSheetIndex(1)`

**Before:**
```php
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Revenue ' . $year);
// ... write to $sheet
```

**After:**
```php
$spreadsheet = new Spreadsheet();

// First, add reference sheet at index 0
$this->addRegionWitelReferenceSheet($spreadsheet);

// Now create revenue template sheet
$revenueSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Revenue ' . $year);
$spreadsheet->addSheet($revenueSheet, 1);
// ... write to $revenueSheet

// Set revenue sheet as active sheet (user will see this when opening file)
$spreadsheet->setActiveSheetIndex(1);
```

#### Updated `downloadFile($year, $month)` Method
- Calls `$this->addRegionWitelReferenceSheet($spreadsheet)` after creating spreadsheet
- Creates data sheet as `new Worksheet` at index 1
- All data writing uses `$dataSheet` instead of `$sheet`
- Sets data sheet as active: `$spreadsheet->setActiveSheetIndex(1)`

**Changes:**
- Similar pattern to downloadTemplate
- Reference sheet added first
- Data sheet created at index 1 with month name
- Active sheet set to data sheet

#### Updated `downloadYear($year)` Method
- Calls `$this->addRegionWitelReferenceSheet($spreadsheet)` after creating spreadsheet
- Creates data sheet as `new Worksheet` at index 1
- All data writing uses `$dataSheet` instead of `$sheet`
- Sets data sheet as active: `$spreadsheet->setActiveSheetIndex(1)`

**Changes:**
- Similar pattern to other download methods
- Reference sheet added first
- Data sheet created at index 1 with year title
- Active sheet set to data sheet

### 2. `app/Imports/RevenueImport.php`

#### Updated `onUnknownSheet($sheetName)` Method
Added explicit handling to skip "region_and_witel" sheet during import:

```php
public function onUnknownSheet($sheetName)
{
    // Skip reference sheet explicitly
    if (strtolower(trim($sheetName)) === 'region_and_witel') {
        Log::debug("Skipping reference sheet: {$sheetName}");
        return;
    }
    
    // Log other unknown sheets
    Log::debug("Skipping unknown sheet: {$sheetName}");
}
```

**Why Needed:**
- Import only processes "Rev YYYY" or "Revenue YYYY" sheets
- Reference sheet should be ignored without causing errors
- Case-insensitive check handles variations in sheet naming

## User Experience

### Template Download
1. User clicks "Download Template" button
2. Excel file opens with 2 sheets:
   - **Region_and_Witel** (reference data)
   - **Revenue 2025** (active sheet with headers only)
3. User sees Revenue sheet by default (active sheet)
4. User can click Region_and_Witel tab to view valid region/witel IDs

### Per-Month Download
1. User downloads data for specific month
2. Excel file opens with 2 sheets:
   - **Region_and_Witel** (reference data)
   - **January 2025** (active sheet with actual data)
3. Downloaded data includes actual revenues and targets
4. Reference sheet provides lookup for WITEL_ID column values

### Per-Year Download
1. User downloads full year data
2. Excel file opens with 2 sheets:
   - **Region_and_Witel** (reference data)
   - **Rev 2025** (active sheet with all 12 months)
3. Downloaded data has interleaved format (1, TARGET_1, 2, TARGET_2, ...)
4. Reference sheet provides lookup for WITEL_ID column values

### Import Process
1. User uploads Excel file with revenue data
2. Import processes only "Rev YYYY" or "Revenue YYYY" sheets
3. **Region_and_Witel sheet is automatically skipped**
4. No errors occur due to presence of reference sheet
5. Import validates WITEL_ID values against database (not reference sheet)

## Testing Checklist

- [x] ✅ No syntax errors in controller
- [x] ✅ No syntax errors in import class
- [ ] ⏳ Download template → Verify 2 sheets, reference first, revenue active
- [ ] ⏳ Download per month → Verify 2 sheets, correct month data
- [ ] ⏳ Download per year → Verify 2 sheets, all 12 months interleaved
- [ ] ⏳ Reference sheet has correct headers and green styling
- [ ] ⏳ Reference sheet shows all regions and witels from database
- [ ] ⏳ Region columns properly merged for multiple witels
- [ ] ⏳ Import file with reference sheet → No errors, sheet skipped
- [ ] ⏳ Verify frozen header row in reference sheet
- [ ] ⏳ Verify borders applied to all cells
- [ ] ⏳ Verify column widths match specification

## Benefits

1. **User Guidance**: Users can quickly reference valid Region and Witel IDs
2. **Data Accuracy**: Reduces errors by providing master data lookup
3. **Self-Documenting**: Excel files include their own reference documentation
4. **Consistency**: All downloads (template, month, year) have same structure
5. **Fresh Data**: Reference data always reflects current database state
6. **User-Friendly**: Active sheet defaults to data entry/viewing sheet
7. **Import-Safe**: Reference sheet automatically skipped during import

## Technical Notes

### PhpSpreadsheet Worksheet Creation
```php
// Create new worksheet at specific index
$sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'SheetName');
$spreadsheet->addSheet($sheet, 0); // Index 0 = first sheet

// Set active sheet by index
$spreadsheet->setActiveSheetIndex(1); // Revenue/data sheet
```

### Cell Merging for Regions
```php
// Merge cells A2:A5 (vertical merge)
$sheet->mergeCells([1, 2, 1, 5]); // Column 1, rows 2-5

// Center align merged cells
$sheet->getStyle([1, 2, 1, 5])->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
```

### Database Query
```php
// Always query fresh data (no caching)
$regions = DB::table('regions')->orderBy('id')->get();
$witels = DB::table('witels')
    ->orderBy('region_id')
    ->orderBy('idwitels')
    ->get();
```

## Related Documentation

- [QUICK_REFERENCE.md](QUICK_REFERENCE.md) - Revenue import format guide
- [AM_REVENUE_MODAL_IMPLEMENTATION.md](AM_REVENUE_MODAL_IMPLEMENTATION.md) - Revenue features
- [REGIONAL_DATABASE_GUIDE.md](REGIONAL_DATABASE_GUIDE.md) - Region/witel database structure
- [DOWNLOAD_FEATURES_DOCUMENTATION.md](DOWNLOAD_FEATURES_DOCUMENTATION.md) - Download functionality

## Maintenance

### Adding New Regions/Witels
1. Add data to `regions` or `witels` table via migration/seeder
2. Reference sheet will automatically include new data on next download
3. No code changes needed

### Styling Changes
1. Modify `addRegionWitelReferenceSheet()` method in RevenueImportController
2. Update `$headerStyle` array for header appearance
3. Update column width calls (`setWidth()`) for sizing

### Sheet Name Changes
1. Modify worksheet creation line: `new Worksheet($spreadsheet, 'NewName')`
2. Update skip logic in RevenueImport.php if name changes
3. Ensure case-insensitive comparison

## Troubleshooting

### Issue: Reference sheet not appearing
- **Check**: Verify `addRegionWitelReferenceSheet()` is called before creating data sheet
- **Check**: Ensure sheet is added at index 0: `addSheet($sheet, 0)`

### Issue: Wrong sheet is active when opening file
- **Check**: Verify `setActiveSheetIndex(1)` is called after all sheets created
- **Check**: Index 0 = reference, index 1 = data/revenue sheet

### Issue: Import fails with "Unknown sheet" error
- **Check**: Verify `onUnknownSheet()` in RevenueImport.php has case-insensitive skip logic
- **Check**: Log messages should show "Skipping reference sheet"

### Issue: Merged cells not displaying correctly
- **Check**: Ensure merge ranges are correct: `[col, startRow, col, endRow]`
- **Check**: Verify vertical alignment is set after merging
- **Check**: Confirm borders are applied to individual cells before merging

### Issue: Old data showing in reference sheet
- **Problem**: Not possible - queries run fresh every time
- **Check**: Verify database tables `regions` and `witels` have correct data
- **Check**: Ensure no caching layer intercepting DB queries

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | January 2026 | Initial implementation of reference sheet feature |

---

**Status**: ✅ Implementation Complete  
**Next Steps**: Testing in development environment
