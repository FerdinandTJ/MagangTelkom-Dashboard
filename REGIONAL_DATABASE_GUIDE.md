# Regional Database Structure Guide

## 📊 Database Structure

### Tables Created:
1. **regions** - Master data 6 regional (HQ, REG1-REG5)
2. **witels** - WITEL (Wilayah Telkom) data per region (42 WITELs)
3. **company_regions** - Pivot table untuk many-to-many relationship
4. **companies** - Added: `primary_region_id`, `primary_witel_id`
5. **revenues** - Added: `region_id`, `witel_id`

### Hierarchy:
```
Region (6)
└── WITEL (42)
    └── Companies
        └── Revenues
```

## 🔧 Models & Relationships

### Region Model
```php
// Relationships
$region->witels        // All WITELs in region
$region->companies     // Companies with this primary region
$region->revenues      // All revenues in region
```

### Witel Model
```php
// Relationships
$witel->region         // Parent region
$witel->companies      // Companies with this primary WITEL
$witel->revenues       // All revenues in WITEL
```

### Company Model
```php
// Primary location
$company->primaryRegion    // Main region
$company->primaryWitel     // Main WITEL

// Multiple locations (Many-to-Many)
$company->regions          // All regions company operates in
$company->witels           // All WITELs company operates in
$company->companyRegions   // Pivot records with details
```

### Revenue Model
```php
// Location tracking
$revenue->region       // Region for this revenue
$revenue->witel        // WITEL for this revenue
$revenue->company      // Company
```

## 📝 Usage Examples

### 1. Assign Company to Multiple Regions
```php
use App\Models\Company;

$company = Company::find(1);

// Add to HQ (no WITEL)
$company->regions()->attach(1, [
    'witel_id' => null,
    'is_primary' => true,
    'notes' => 'Primary HQ location'
]);

// Add to Region 2 with WITEL Jakarta
$company->regions()->attach(3, [
    'witel_id' => 9,  // WITEL Jakarta
    'is_primary' => false,
    'notes' => 'Secondary location'
]);
```

### 2. Get Companies in a Region
```php
use App\Models\Region;

$region = Region::where('code', 'REG2')->first();

// Primary companies
$primaryCompanies = $region->companies;

// All companies (including secondary locations)
$allCompanies = Company::whereHas('regions', function($query) use ($region) {
    $query->where('regions.id', $region->id);
})->get();
```

### 3. Get Revenue by Region
```php
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

$regionalRevenue = Revenue::select(
        'regions.name',
        'regions.code',
        DB::raw('SUM(revenues.revenue) as total_revenue')
    )
    ->join('regions', 'revenues.region_id', '=', 'regions.id')
    ->where('revenues.tahun', 2025)
    ->groupBy('regions.id', 'regions.name', 'regions.code')
    ->orderBy('total_revenue', 'desc')
    ->get();
```

### 4. Get Revenue by WITEL
```php
$witelRevenue = Revenue::select(
        'witels.name as witel_name',
        'witels.code as witel_code',
        'regions.name as region_name',
        DB::raw('SUM(revenues.revenue) as total_revenue')
    )
    ->join('witels', 'revenues.witel_id', '=', 'witels.id')
    ->join('regions', 'witels.region_id', '=', 'regions.id')
    ->where('revenues.tahun', 2025)
    ->groupBy('witels.id', 'witels.name', 'witels.code', 'regions.name')
    ->orderBy('total_revenue', 'desc')
    ->get();
```

### 5. Get Companies in Multiple Regions (PTN Example)
```php
// PTN companies in HQ and REG2
$ptnCompanies = Company::where('subsegment', 'PTN')
    ->whereHas('regions', function($query) {
        $query->whereIn('regions.code', ['HQ', 'REG2']);
    })
    ->with(['regions', 'witels'])
    ->get();

// Check how many regions each company has
foreach ($ptnCompanies as $company) {
    echo "{$company->nama_perusahaan}: " . $company->regions->count() . " regions\n";
}
```

### 6. Get WITEL Performance
```php
use App\Models\Witel;

$witelPerformance = Witel::with('region')
    ->withCount('companies')
    ->withSum(['revenues' => function($query) {
        $query->where('tahun', 2025);
    }], 'revenue')
    ->orderBy('revenues_sum_revenue', 'desc')
    ->get();
```

### 7. Filter Dashboard by Region/WITEL
```php
use App\Models\Revenue;

// Get monthly revenue for specific region
$monthlyRevenue = Revenue::select(
        'bulan',
        DB::raw('SUM(revenue) as total_revenue')
    )
    ->where('tahun', 2025)
    ->where('region_id', $regionId)
    ->groupBy('bulan')
    ->orderBy('bulan')
    ->get();

// Get subsegment revenue for specific WITEL
$subsegmentRevenue = Revenue::select(
        'companies.subsegment',
        DB::raw('SUM(revenues.revenue) as total_revenue')
    )
    ->join('companies', 'revenues.company_id', '=', 'companies.id')
    ->where('revenues.tahun', 2025)
    ->where('revenues.witel_id', $witelId)
    ->groupBy('companies.subsegment')
    ->get();
```

### 8. Update Company Primary Region
```php
$company = Company::find(1);
$company->primary_region_id = 3;  // Region 2
$company->primary_witel_id = 9;   // WITEL Jakarta
$company->save();
```

### 9. Track Revenue by Location
```php
use App\Models\Revenue;

// When creating revenue, specify location
Revenue::create([
    'company_id' => 1,
    'tahun' => 2025,
    'bulan' => 10,
    'revenue' => 1000000000,
    'region_id' => 3,      // Region 2
    'witel_id' => 9,       // WITEL Jakarta
    'notes' => 'October revenue'
]);
```

## 🎯 Region Codes & IDs

| ID | Code | Name | Description |
|----|------|------|-------------|
| 1 | HQ | Headquarters | Telkom Headquarters |
| 2 | REG1 | Telkom Region 1 | Sumatera |
| 3 | REG2 | Telkom Region 2 | Jakarta, Banten, Jawa Barat |
| 4 | REG3 | Telkom Region 3 | Jawa Tengah & DIY |
| 5 | REG4 | Telkom Region 4 | Jawa Timur |
| 6 | REG5 | Telkom Region 5 | Bali, Nusa Tenggara, Kalimantan, Sulawesi, Maluku, Papua |

## 📋 WITEL Summary

- **Region 1 (Sumatera)**: 8 WITELs
- **Region 2 (Jabodetabek-Jabar)**: 11 WITELs
- **Region 3 (Jateng-DIY)**: 5 WITELs
- **Region 4 (Jawa Timur)**: 5 WITELs
- **Region 5 (Eastern Indonesia)**: 13 WITELs

**Total: 42 WITELs**

## 🚀 Next Steps for Dashboard Visualization

1. Add region/WITEL filter dropdown in dashboard
2. Create regional performance cards
3. Add WITEL comparison charts
4. Implement region-based drill-down
5. Add regional heat map visualization
6. Create WITEL ranking table

## 📝 Notes

- Companies can have ONE primary region/WITEL
- Companies can operate in MULTIPLE regions (via pivot table)
- Each revenue record can be tagged with region/WITEL
- Use `is_primary` flag in `company_regions` to identify main location
- Regions are pre-seeded in migration (6 fixed regions)
- WITELs are seeded via WitelSeeder (42 WITELs)

## 🔍 Database Schema

```sql
-- Regions
CREATE TABLE regions (
    id BIGINT UNSIGNED PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    name VARCHAR(100),
    description TEXT
);

-- WITELs
CREATE TABLE witels (
    id BIGINT UNSIGNED PRIMARY KEY,
    region_id BIGINT UNSIGNED,
    code VARCHAR(20) UNIQUE,
    name VARCHAR(100),
    province VARCHAR(100),
    FOREIGN KEY (region_id) REFERENCES regions(id)
);

-- Companies (updated)
ALTER TABLE companies ADD (
    primary_region_id BIGINT UNSIGNED,
    primary_witel_id BIGINT UNSIGNED,
    FOREIGN KEY (primary_region_id) REFERENCES regions(id),
    FOREIGN KEY (primary_witel_id) REFERENCES witels(id)
);

-- Company Regions (Many-to-Many)
CREATE TABLE company_regions (
    id BIGINT UNSIGNED PRIMARY KEY,
    company_id BIGINT UNSIGNED,
    region_id BIGINT UNSIGNED,
    witel_id BIGINT UNSIGNED,
    is_primary BOOLEAN DEFAULT FALSE,
    notes TEXT,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (region_id) REFERENCES regions(id),
    FOREIGN KEY (witel_id) REFERENCES witels(id)
);

-- Revenues (updated)
ALTER TABLE revenues ADD (
    region_id BIGINT UNSIGNED,
    witel_id BIGINT UNSIGNED,
    FOREIGN KEY (region_id) REFERENCES regions(id),
    FOREIGN KEY (witel_id) REFERENCES witels(id)
);
```
