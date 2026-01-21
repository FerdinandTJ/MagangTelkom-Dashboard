# LAPORAN HASIL MAGANG
## SISTEM PERFORMANCE AM (ACCOUNT MANAGER) - TWS DASHBOARD

---

## 📋 RINGKASAN EKSEKUTIF

Sistem Performance AM adalah modul khusus dalam Dashboard Analytics TWS yang dirancang untuk monitoring dan evaluasi performa Account Manager di Telkom Wholesale Service. Sistem ini mengintegrasikan multiple metrics (Revenue, Scaling, Datin, HSI, Wireline, Wifi, CYC, CR, Profit, NPS, MAPS, LOP, Capability, CC) dalam satu dashboard komprehensif dengan visualisasi interaktif dan drill-down capabilities.

### Tech Stack
- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** React 19 + TypeScript
- **Bridge:** Inertia.js v2
- **UI Framework:** Tailwind CSS v4 + shadcn/ui
- **Database:** MySQL
- **Authentication:** Laravel Fortify
- **Charting:** Recharts

---

## 🎯 FITUR UTAMA APLIKASI

### 1. PERFORMANCE AM DASHBOARD

Halaman Performance AM adalah dashboard khusus yang memberikan overview lengkap tentang performa seluruh Account Manager dalam periode tertentu (per quartal). Dashboard ini dirancang untuk memudahkan management dalam mengevaluasi pencapaian target dan mengidentifikasi area yang perlu improvement.

#### 1.1 Halaman Utama Performance AM
**Lokasi File:** `resources/js/pages/PerformanceAm.tsx`

Dashboard Performance AM terdiri dari beberapa komponen utama yang saling terintegrasi:

##### A. Summary Cards (5 Cards Informatif)

**1. Total AM Card**
- **Tujuan:** Menampilkan jumlah total Account Manager yang aktif
- **Data Source:** COUNT dari table `account_managers`
- **Visual:** Icon Users2 dengan red gradient background
- **Update:** Real-time berdasarkan data di database
- **Significance:** Memberikan gambaran scale operasional tim AM

**2. Revenue Target Card**
- **Tujuan:** Menampilkan total target revenue untuk periode terpilih
- **Data Source:** SUM dari `target_account_m.t_revenue` via `lini_waktu_target`
- **Filter Applied:** Year dan Quartal yang dipilih user
- **Format:** Currency dengan suffix M/T (Miliar/Triliun)
- **Icon:** DollarSign dengan blue gradient
- **Calculation Logic:**
  ```php
  // Simplified query
  DB::table('lini_waktu')
    ->join('lini_waktu_target', ...)
    ->join('target_account_m', ...)
    ->where('lini_waktu.tahun', $year)
    ->where('lini_waktu.quartal', $quartal)
    ->sum('target_account_m.t_revenue')
  ```

**3. Year Dropdown Card**
- **Tujuan:** Filter selector untuk memilih tahun analisis
- **Options:** SELECT DISTINCT tahun FROM lini_waktu ORDER BY tahun DESC
- **Default:** Current year (2026)
- **Cascading Effect:** Mempengaruhi semua visualisasi di halaman
- **Icon:** Calendar dengan green gradient

**4. Quartal Dropdown Card**
- **Tujuan:** Filter selector untuk memilih quartal (Q1, Q2, Q3, Q4)
- **Options:** SELECT DISTINCT quartal FROM lini_waktu WHERE tahun = selected_year
- **Default:** Current quartal (calculated based on current month)
- **Calculation Logic:**
  - Q1: January - March
  - Q2: April - June
  - Q3: July - September
  - Q4: October - December
- **Icon:** Filter dengan purple gradient

**5. Export/Import Actions Card**
- **Tujuan:** Quick access untuk data management operations
- **Buttons:**
  - Export: Download performance data (future feature)
  - Import: Navigate ke Data Import Performance page
- **Icon:** Database dengan orange gradient
- **Role Guard:** Only visible untuk Admin users

##### B. Visualisasi Utama

**1. Target Revenue AM Chart (Bar Chart - 70% width)**

**Lokasi:** Grid column span 7 dari 10 (70% width)

**Tujuan:** 
Menampilkan ranking Account Manager berdasarkan target revenue dalam format horizontal bar chart untuk memudahkan perbandingan antar AM.

**Features:**
- **Chart Type:** Horizontal Bar Chart (Recharts)
- **Data Display:**
  - Y-axis: Nama Account Manager
  - X-axis: Target Revenue (formatted dalam Miliar)
  - Bars: Single bar per AM dengan red color (#ef4444)
  
- **Sorting:** ORDER BY t_revenue DESC (highest to lowest)
- **Data Inclusion:** Menampilkan SEMUA AM, termasuk yang t_revenue = 0
  - Menggunakan LEFT JOIN untuk ensure all AMs shown
  - AM tanpa target tetap muncul dengan bar kosong
  
- **Interactive Features:**
  - Click pada bar → Trigger AM Revenue Detail Modal
  - Hover effect dengan tooltip
  - Tooltip content:
    - Nama AM
    - Region code
    - Target revenue (formatted)

- **Filtering:**
  - Filtered by selected year AND quartal
  - **CRITICAL FIX:** WHERE conditions dalam LEFT JOIN
    ```php
    ->leftJoin('lini_waktu', function($join) use ($year, $quartal) {
        $join->on('account_managers.nik', '=', 'lini_waktu.nik_am')
             ->where('lini_waktu.tahun', '=', $year)
             ->where('lini_waktu.quartal', '=', $quartal);
    })
    ```
  - Ini memastikan chart hanya menampilkan data untuk periode spesifik, bukan SUM dari semua periode

- **Backend Query:**
  ```php
  // DashboardController@getAMRevenueRanking()
  SELECT 
    am.nik,
    am.nama as am_name,
    r.code as region_code,
    COALESCE(SUM(tam.t_revenue), 0) as t_revenue
  FROM account_managers am
  LEFT JOIN witels w ON am.idwitels = w.idwitels
  LEFT JOIN regions r ON w.region_id = r.id
  LEFT JOIN lini_waktu lw ON am.nik = lw.nik_am 
    AND lw.tahun = ? AND lw.quartal = ?
  LEFT JOIN lini_waktu_target lwt ON lw.id = lwt.lini_waktu_id
  LEFT JOIN target_account_m tam ON lwt.target_id = tam.id
  GROUP BY am.nik
  ORDER BY t_revenue DESC
  ```

**2. Region Distribution Chart (Pie Chart - 30% width)**

**Lokasi:** Grid column span 3 dari 10 (30% width)

**Tujuan:**
Menampilkan distribusi jumlah Account Manager per region untuk memberikan gambaran spread geografis tim AM.

**Features:**
- **Chart Type:** Pie Chart dengan custom labels
- **Data Display:**
  - Segments: Satu per region (HQ TREG2, TREG1-5)
  - Value: COUNT jumlah AM per region
  
- **Label Configuration:**
  - **Inside Label:** Percentage (e.g., "45.5%")
    - Formula: (AM count per region / Total AM) × 100%
    - Position: Center of segment
    - Color: White untuk visibility
  
  - **Outside Label:** Region code (e.g., "TREG-1")
    - Position: Outside segment dengan connector line
    - Color: Match segment color
  
- **Tooltip Content:**
  - Region name
  - AM count (e.g., "5 Account Manager")
  - Percentage
  
- **Color Palette:**
  - Multi-color scheme (8 colors untuk support sampai 8 regions)
  - Colors: Blue, Green, Yellow, Orange, Red, Purple, Pink, Cyan
  
- **Interactive Features:**
  - Click pada segment → Trigger Region NKI Modal
  - Hover effect dengan scale animation
  - Legend display dengan region names

- **Backend Query:**
  ```php
  // DashboardController@getRegionDistribution()
  SELECT 
    r.id,
    r.code as region_code,
    r.description as region_name,
    COUNT(am.nik) as am_count,
    ROUND((COUNT(am.nik) * 100.0 / total.count), 1) as percentage
  FROM regions r
  LEFT JOIN witels w ON r.id = w.region_id
  LEFT JOIN account_managers am ON w.idwitels = am.idwitels
  CROSS JOIN (SELECT COUNT(*) as count FROM account_managers) as total
  GROUP BY r.id
  ORDER BY am_count DESC
  ```

##### C. List Account Manager Table

**Tujuan:**
Menyediakan view tabular detail untuk semua Account Manager dengan informasi lengkap dan actions.

**Structure:**
- **Table Layout:** Full-width table dengan 6 kolom
- **Scrollable:** Horizontal scroll untuk mobile devices
- **Sticky Header:** Header tetap visible saat scroll

**Kolom Table:**

1. **Nama AM** (Column 1)
   - Full name dari `account_managers.nama`
   - Bold text untuk emphasis
   - Left-aligned

2. **Region** (Column 2)
   - Region code (e.g., "HQ TREG2", "TREG1")
   - Badge format dengan color coding
   - Center-aligned

3. **Witel** (Column 3)
   - Nama witel dari `witels.witel`
   - Text format
   - Left-aligned

4. **Target Revenue** (Column 4)
   - Formatted currency (e.g., "Rp 500 M")
   - Right-aligned untuk number readability
   - Gray text jika 0

5. **Status** (Column 5)
   - Badge indicator:
     - "Active" - Green badge (has target)
     - "Inactive" - Gray badge (no target)
   - Center-aligned

6. **Actions** (Column 6)
   - Detail button: Click → AM Performance Detail Modal
   - Icon: Eye (view)
   - Button styling: Small, outline, hover effect

**Features:**
- **Filtering:** Sama seperti charts (by year & quartal)
- **Sorting:** Default by nama (A-Z), can be extended
- **Pagination:** If AM count > 50, implement pagination
- **Search:** Future feature untuk quick find AM
- **Row Hover:** Highlight row on hover untuk better UX

**Backend Query:**
```php
// DashboardController@getAccountManagerList()
SELECT 
  am.nik,
  am.nama,
  w.witel as witel_name,
  r.code as region_code,
  COALESCE(SUM(tam.t_revenue), 0) as target_revenue,
  CASE 
    WHEN SUM(tam.t_revenue) > 0 THEN 'Active'
    ELSE 'Inactive'
  END as status
FROM account_managers am
LEFT JOIN witels w ON am.idwitels = w.idwitels
LEFT JOIN regions r ON w.region_id = r.id
LEFT JOIN lini_waktu lw ON am.nik = lw.nik_am 
  AND lw.tahun = ? AND lw.quartal = ?
LEFT JOIN lini_waktu_target lwt ON lw.id = lwt.lini_waktu_id
LEFT JOIN target_account_m tam ON lwt.target_id = tam.id
GROUP BY am.nik
ORDER BY am.nama ASC
```

---

#### 1.2 AM Revenue Detail Modal

**File:** `resources/js/components/modals/AMRevenueDetailModal.tsx`

**Trigger:** User click pada bar di "Target Revenue AM Chart"

**Tujuan:**
Modal ini menampilkan breakdown detail revenue untuk satu Account Manager spesifik, termasuk distribusi companies yang di-handle dan breakdown per witel.

**Props Interface:**
```typescript
interface AMRevenueDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  amNik: string | null;  // NIK Account Manager
  year: number;
  quartal: string;       // Q1, Q2, Q3, Q4
}
```

**Struktur Modal:**

##### A. Modal Header
- **Title:** Nama Account Manager (dynamic berdasarkan NIK)
- **Icon:** User icon dengan gradient blue-purple background
- **Close Button:** X icon di kanan atas (Lucide React X icon)
- **Styling:** 
  - Dark mode support
  - Border bottom untuk separation
  - Padding untuk spacing

##### B. Summary Section (3 Cards)

**Card 1: Target Revenue**
- **Display:** Total target revenue AM untuk periode ini
- **Format:** Currency dengan Rp prefix dan M/T suffix
- **Calculation:** SUM dari semua target_account_m.t_revenue untuk AM ini
- **Icon:** Target icon (Lucide React)
- **Background:** Blue gradient (from-blue-500 to-blue-600)
- **Text Color:** White untuk contrast

**Card 2: Total Companies**
- **Display:** Jumlah companies yang di-assign ke AM
- **Format:** Number dengan suffix "Companies"
- **Calculation:** COUNT DISTINCT companies via account_manager_company
- **Icon:** Building2 icon
- **Background:** Green gradient (from-green-500 to-green-600)
- **Purpose:** Show workload AM (banyak company = high workload)

**Card 3: Period**
- **Display:** "{Year} - {Quartal}" (e.g., "2024 - Q1")
- **Format:** Text dengan dash separator
- **Icon:** Calendar icon
- **Background:** Purple gradient (from-purple-500 to-purple-600)
- **Purpose:** Context reminder untuk user

##### C. Witel Distribution Pie Chart

**Tujuan:**
Menampilkan distribusi companies berdasarkan witel untuk melihat geographic coverage AM.

**Chart Configuration:**
- **Type:** Pie Chart (Recharts)
- **Data:** Array of { witel_name, company_count, percentage }
- **Colors:** 8-color palette untuk support multiple witels
  - Colors: #8b5cf6, #ef4444, #f97316, #eab308, #22c55e, #3b82f6, #6366f1, #ec4899
  
**Labels:**
- **Inside:** Percentage (e.g., "33.3%")
  - Position: Center of segment
  - Font size: 14px
  - Font weight: Bold
  - Color: White
  
- **Outside:** Witel name
  - Position: Outside dengan connector line
  - Font size: 12px
  - Color: Match segment color

**Legend:**
- Position: Bottom
- Format: Witel name dengan color indicator
- Layout: Horizontal wrap

**Tooltip:**
- Witel name
- Company count
- Percentage
- Dark mode support

**Backend Calculation:**
```php
// Group companies by witel
$witelDistribution = DB::table('account_manager_company as amc')
  ->join('companies as c', 'amc.nip_nas', '=', 'c.nip_nas')
  ->join('account_managers as am', 'amc.nik_am', '=', 'am.nik')
  ->join('witels as w', 'am.idwitels', '=', 'w.idwitels')
  ->where('amc.nik_am', $amNik)
  ->select(
    'w.witel as witel_name',
    DB::raw('COUNT(DISTINCT c.nip_nas) as company_count')
  )
  ->groupBy('w.idwitels')
  ->get()
  ->map(function($item) use ($totalCompanies) {
    return [
      'witel_name' => $item->witel_name,
      'company_count' => $item->company_count,
      'percentage' => round(($item->company_count / $totalCompanies) * 100, 1)
    ];
  });
```

##### D. Companies Detail Table

**Purpose:**
Daftar lengkap semua companies yang di-handle oleh AM dengan detail revenue target masing-masing.

**Table Structure:**
- **Scrollable:** Max height dengan vertical scroll
- **Sticky Header:** Header tetap saat scroll
- **Zebra Striping:** Alternate row colors untuk readability

**Columns:**

1. **Company** (35% width)
   - Nama perusahaan lengkap
   - Bold text
   - Left-aligned
   - Truncate dengan ellipsis jika terlalu panjang

2. **NIP NAS** (15% width)
   - 6-digit company identifier
   - Monospace font untuk alignment
   - Center-aligned

3. **Witel** (20% width)
   - Nama witel lokasi company
   - Regular text
   - Left-aligned

4. **Target Revenue** (20% width)
   - Formatted currency
   - Right-aligned
   - Bold untuk emphasis

5. **Action** (10% width)
   - "Detail" button (placeholder for future feature)
   - Click → Navigate to Company Detail
   - Icon: Eye
   - Small button size

**Row Styling:**
- **Hover Effect:** Light gray background on hover
- **Dark Mode:** Dark gray hover untuk dark theme
- **Border:** Subtle border between rows
- **Padding:** Adequate padding untuk touch targets

**Empty State:**
- Display jika AM tidak punya companies
- Message: "Tidak ada company yang di-assign"
- Icon: Package X (Lucide React)
- Center aligned

##### E. Backend API Endpoint

**Route:** `GET /api/dashboard/am-revenue-details`

**Parameters:**
- `am_nik` (required, string): NIK Account Manager
- `year` (required, integer): Tahun periode
- `quartal` (required, string): Q1/Q2/Q3/Q4

**Response Format:**
```json
{
  "success": true,
  "data": {
    "am_name": "John Doe",
    "total_target_revenue": 1234567890,
    "formatted_total_revenue": "1.23 T",
    "total_companies": 15,
    "period_display": "2024 - Q1",
    "witel_distribution": [
      {
        "witel_name": "WITEL JAKARTA BARAT",
        "company_count": 8,
        "percentage": 53.3
      },
      {
        "witel_name": "WITEL JAKARTA SELATAN",
        "company_count": 7,
        "percentage": 46.7
      }
    ],
    "companies": [
      {
        "company_name": "PT TELKOM INDONESIA",
        "nip_nas": "123456",
        "witel_name": "WITEL JAKARTA BARAT",
        "target_revenue": 500000000,
        "formatted_target_revenue": "500 M"
      }
    ]
  }
}
```

**Query Logic:**
```php
// 1. Get AM basic info
$am = DB::table('account_managers')->where('nik', $amNik)->first();

// 2. Get lini_waktu for period
$liniWaktu = DB::table('lini_waktu')
  ->where('nik_am', $amNik)
  ->where('tahun', $year)
  ->where('quartal', $quartal)
  ->first();

// 3. Get companies with targets via pivot tables
$companies = DB::table('account_manager_company as amc')
  ->join('companies as c', 'amc.nip_nas', '=', 'c.nip_nas')
  ->join('lini_waktu_target as lwt', function($join) use ($liniWaktu) {
    $join->on('amc.id', '=', 'lwt.account_manager_company_id')
         ->where('lwt.lini_waktu_id', '=', $liniWaktu->id);
  })
  ->join('target_account_m as tam', 'lwt.target_id', '=', 'tam.id')
  ->where('amc.nik_am', $amNik)
  ->select('c.*', 'tam.t_revenue')
  ->get();

// 4. Calculate totals and distributions
```

---

#### 1.3 Region NKI Modal dengan Compare Feature

**File:** `resources/js/components/modals/RegionNkiModal.tsx`

**Trigger:** User click pada pie segment di "Region Distribution Chart"

**Tujuan:**
Modal ini menampilkan detail performance metrics untuk semua Account Manager dalam satu region, dengan fitur automatic comparison ke periode sebelumnya untuk trend analysis.

**Props Interface:**
```typescript
interface RegionNkiModalProps {
  isOpen: boolean;
  onClose: () => void;
  regionId: number | null;
  quarter: number;        // 1, 2, 3, 4
  year: number;
  compare?: boolean;      // Default true untuk auto-compare
}
```

**Struktur Modal:**

##### A. Modal Header dengan Period Info
- **Title:** "{Region Name} - Performance Metrics"
  - Example: "HQ TREG2 - Performance Metrics"
- **Subtitle:** "Comparison: Q{current} {year} vs Q{previous} {year}"
  - Example: "Comparison: Q3 2025 vs Q2 2025"
- **Period Calculation:**
  - Q1 → Q4 previous year
  - Q2 → Q1 same year
  - Q3 → Q2 same year
  - Q4 → Q3 same year

##### B. Summary Section (3 Cards dengan Comparison)

**Card Layout:** Side-by-side untuk current vs previous

**Card 1: Revenue Summary**
- **Left Column:** Current Period
  - Target Revenue (formatted)
  - Realisasi Revenue (formatted)
  - Color: Blue theme
  
- **Right Column:** Previous Period
  - Target Revenue (gray text)
  - Realisasi Revenue (gray text)
  - Color: Gray theme (dimmed)

- **Indicator:** Arrow dan percentage difference
  - ↑ Green: Improvement
  - ↓ Red: Decline
  - → Gray: No change

**Card 2: Total AM**
- **Current:** Jumlah AM aktif periode ini
- **Previous:** Jumlah AM periode sebelumnya
- **Comparison:** Delta (e.g., "+2 AM" atau "-1 AM")
- **Icon:** Users2

**Card 3: Period Selector**
- Display current period range
- Month range based on quartal:
  - Q1: Jan - Mar
  - Q2: Apr - Jun
  - Q3: Jul - Sep
  - Q4: Oct - Dec

##### C. Segment Statistics Table dengan Trend

**Purpose:**
Menampilkan breakdown performance per segment (HQ-TWS, POTS, dll) dalam region tersebut dengan comparison data.

**Table Structure:**

**Headers:**
| Triwulan | Result Ach | Result Not Ach | Proses Ach | Proses Not Ach | NKI >100% | NKI <100% | Highest NKI | Lowest NKI | Avg NKI |
|----------|------------|----------------|------------|----------------|-----------|-----------|-------------|------------|---------|

**Row 1: Current Period** (Bold dengan trend arrows)
- Triwulan: Q{number} {year}
- Result Ach: Count dengan ↑↓ arrow
- Result Not Ach: Count dengan ↑↓ arrow
- Proses Ach: Count dengan ↑↓ arrow
- Proses Not Ach: Count dengan ↑↓ arrow
- NKI >100%: Count dengan ↑↓ arrow
- NKI <100%: Count dengan ↑↓ arrow
- Highest NKI: Percentage dengan ↑↓
- Lowest NKI: Percentage dengan ↑↓
- Avg NKI: Percentage dengan ↑↓

**Row 2: Previous Period** (Gray text, no arrows)
- Same columns tapi dimmed untuk reference
- No trend indicators

**Trend Arrows Logic:**
```typescript
// TrendIndicator Component
const TrendIndicator = ({ current, previous }: { current: number; previous: number | null }) => {
  if (previous === null) return <span>{current}</span>;
  
  if (current > previous) {
    return (
      <span className="text-green-600 font-semibold">
        ↑ {current}
      </span>
    );
  } else if (current < previous) {
    return (
      <span className="text-red-600 font-semibold">
        ↓ {current}
      </span>
    );
  }
  
  return <span>{current}</span>;
};
```

**Visual Styling:**
- Current row: White background, bold text
- Previous row: Light gray background, regular text
- Borders: Subtle borders untuk separation
- Colors:
  - Green arrows: Improvement (↑)
  - Red arrows: Decline (↓)
  - Black/Gray: No change atau no previous data

##### D. Parameter Tabs (Aspek Result & Aspek Proses)

**Tab Navigation:**
- Tab 1: **Aspek Result** (10 Parameters)
- Tab 2: **Aspek Proses** (4 Parameters)
- Active tab: Blue underline
- Inactive tab: Gray text

**Tab 1: Aspek Result (10 Parameters)**

Table showing achievement status per parameter dengan comparison:

**Parameters:**
1. Revenue
2. Scaling
3. Datin
4. HSI
5. Wireline
6. Wifi
7. CYC
8. CR
9. Profit
10. NPS

**Table Structure:**
| Triwulan | Revenue Ach | Revenue Not Ach | Scaling Ach | Scaling Not Ach | ... | NPS Ach | NPS Not Ach |
|----------|-------------|-----------------|-------------|-----------------|-----|---------|-------------|
| Q3 2025  | ↑ 8        | ↓ 2            | ↑ 7        | ↓ 3            | ... | ↑ 6    | ↓ 4        |
| Q2 2025  | 6          | 4              | 5          | 5              | ... | 5      | 5          |

**Achievement Logic:**
- Ach: AM yang achieve target untuk parameter tersebut
- Not Ach: AM yang tidak achieve target
- Calculation: Compare realisasi vs target untuk setiap AM
  - If (r_parameter / t_parameter) >= percentage_threshold → Ach
  - Else → Not Ach

**Tab 2: Aspek Proses (4 Parameters)**

Same structure tapi dengan 4 parameters saja:

**Parameters:**
1. MAPS
2. LOP (Length of Process)
3. Capability
4. CC (Customer Care)

**Table Structure:**
| Triwulan | MAPS Ach | MAPS Not Ach | LOP Ach | LOP Not Ach | Capability Ach | Capability Not Ach | CC Ach | CC Not Ach |
|----------|----------|--------------|---------|-------------|----------------|-------------------|--------|------------|
| Q3 2025  | ↑ 9     | ↓ 1         | ↑ 8    | ↓ 2        | ↑ 7           | ↓ 3              | ↑ 6   | ↓ 4       |
| Q2 2025  | 7       | 3           | 6      | 4          | 5             | 5                | 5     | 5         |

##### E. Backend API dengan Compare Logic

**Endpoint:** `GET /api/dashboard/region-nki/{regionId}`

**Parameters:**
- `quarter` (required): 1-4
- `year` (required): 2020+
- `compare` (optional, default true): Enable comparison

**Response Structure:**
```json
{
  "region": {
    "id": 1,
    "name": "HQ TREG2"
  },
  "current_period": {
    "quarter": 3,
    "year": 2025,
    "label": "Q3 2025",
    "data": {
      "summary": {
        "formatted_target_revenue": "Rp 480.48M",
        "formatted_realisasi_revenue": "Rp 412.34M",
        "total_am": 10
      },
      "segment_stats": [
        {
          "segment": "HQ-TWS",
          "result": { "ach": 8, "not_ach": 2 },
          "proses": { "ach": 7, "not_ach": 3 },
          "nki": { "above_100": 6, "below_100": 4 },
          "highest_nki": 125.5,
          "lowest_nki": 85.2,
          "avg_nki": 102.3
        }
      ],
      "parameter_result": {
        "revenue": { "ach": 8, "not_ach": 2 },
        "scaling": { "ach": 7, "not_ach": 3 },
        "datin": { "ach": 9, "not_ach": 1 },
        "hsi": { "ach": 6, "not_ach": 4 },
        "wireline": { "ach": 8, "not_ach": 2 },
        "wifi": { "ach": 7, "not_ach": 3 },
        "cyc": { "ach": 9, "not_ach": 1 },
        "cr": { "ach": 8, "not_ach": 2 },
        "profit": { "ach": 7, "not_ach": 3 },
        "nps": { "ach": 6, "not_ach": 4 }
      },
      "parameter_proses": {
        "maps": { "ach": 9, "not_ach": 1 },
        "lop": { "ach": 8, "not_ach": 2 },
        "capability": { "ach": 7, "not_ach": 3 },
        "cc": { "ach": 6, "not_ach": 4 }
      }
    }
  },
  "previous_period": {
    "quarter": 2,
    "year": 2025,
    "label": "Q2 2025",
    "data": {
      // Same structure as current_period
    }
  },
  "compare_enabled": true
}
```

**Backend Method Structure:**
```php
// RegionNkiController.php

public function show(Request $request, $regionId)
{
  $year = $request->input('year');
  $quarter = $request->input('quarter');
  $compare = $request->input('compare', true);
  
  // Get current period data
  $currentData = $this->getPeriodData($regionId, $quarter, $year);
  
  $response = [
    'region' => $region,
    'current_period' => [
      'quarter' => $quarter,
      'year' => $year,
      'label' => "Q{$quarter} {$year}",
      'data' => $currentData
    ]
  ];
  
  // Get previous period data if compare enabled
  if ($compare) {
    $previous = $this->getPreviousPeriod($quarter, $year);
    $previousData = $this->getPeriodData($regionId, $previous['quarter'], $previous['year']);
    
    $response['previous_period'] = [
      'quarter' => $previous['quarter'],
      'year' => $previous['year'],
      'label' => "Q{$previous['quarter']} {$previous['year']}",
      'data' => $previousData
    ];
    $response['compare_enabled'] = true;
  }
  
  return response()->json($response);
}

private function getPreviousPeriod($quarter, $year)
{
  if ($quarter == 1) {
    return ['quarter' => 4, 'year' => $year - 1];
  } else {
    return ['quarter' => $quarter - 1, 'year' => $year];
  }
}

private function getPeriodData($regionId, $quarter, $year)
{
  // Complex query untuk calculate:
  // 1. Revenue summary (target, realisasi)
  // 2. Total AM dalam region
  // 3. Segment statistics (Result/Proses/NKI per segment)
  // 4. Parameter breakdown (Ach/Not Ach per 14 parameters)
}
```

---

#### 1.4 AM Performance Detail Modal (Advanced Analysis)

**File:** `resources/js/components/modals/AmPerformanceDetailModal.tsx`

**Trigger:** User click tombol "Detail" di List Account Manager Table

**Tujuan:**
Modal paling detail yang menampilkan complete breakdown performance satu Account Manager individual dengan visualisasi untuk setiap parameter (14 parameters total).

**Props Interface:**
```typescript
interface AmPerformanceDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  amNik: string | null;
  amName: string;
  quarter: number;
  year: number;
  segment: string;        // HQ-TWS, POTS, dll
}
```

**Struktur Modal:**

##### A. Modal Header dengan AM Info
- **Title:** Nama AM (large, bold)
- **Subtitle:** NIK - Segment - Region - Witel
- **Period Badge:** Q{number} {year}
- **Close Button:** X icon

##### B. Overall Performance Summary (Top Section)

**Achievement Overview Cards (3 Cards):**

**Card 1: Result Achievement**
- **Display:** "ACHIEVE" atau "NOT ACHIEVE"
- **Color:**
  - Green: ACHIEVE
  - Red: NOT ACHIEVE
- **Calculation:** 
  - SUM(ach_revenue + ach_scaling + ... + ach_nps) / 10 >= percentage_result
- **Percentage Display:** Show actual percentage achieved
- **Icon:** CheckCircle (achieve) atau XCircle (not achieve)

**Card 2: Proses Achievement**
- **Display:** "ACHIEVE" atau "NOT ACHIEVE"
- **Calculation:**
  - SUM(ach_maps + ach_lop + ach_capability + ach_cc) / 4 >= percentage_proses
- **Color coding sama dengan Card 1

**Card 3: NKI (Nilai Kinerja Individu)**
- **Display:** NKI percentage (e.g., "105.5%")
- **Status Badge:**
  - ">100%" - Green badge
  - "<100%" - Red badge
- **Calculation:** 
  - AVG(nki_adjustment) untuk AM ini
- **Purpose:** Overall performance score

##### C. Parameter Breakdown Sections

**Section dibagi menjadi 2 tabs:**

**Tab 1: Aspek Result (10 Parameters)**
**Tab 2: Aspek Proses (4 Parameters)**

**Untuk SETIAP Parameter, tampilkan card dengan:**

**Card Structure per Parameter:**

**Header:**
- Parameter name (e.g., "Revenue", "Scaling", "MAPS")
- Achievement badge: "Ach" (green) atau "Not Ach" (red)

**3 Metrics per Card:**

1. **Target (t_xxx)**
   - Label: "Target"
   - Value: Numeric value
   - Format: 
     - Untuk percentage (CYC, CR, Profit, MAPS): Display sebagai percentage (e.g., "75%")
     - Untuk count (Datin, HSI): Display sebagai number
     - Untuk currency (Revenue): Display dengan format Rp
   - **CRITICAL FIX:** Gunakan `.first()` bukan `.sum()` untuk percentage parameters
     ```php
     // ❌ WRONG (akan multiply nilai)
     $pivotData->sum('t_cyc') // 0.75 + 0.75 = 1.5 → Display: 150%
     
     // ✅ CORRECT (ambil nilai pertama)
     $firstRecord->t_cyc // 0.75 → Display: 75%
     ```

2. **Realisasi (r_xxx)**
   - Label: "Realisasi"
   - Value: Numeric value dari lini_waktu_target
   - Format sama dengan target

3. **Achievement (ach_xxx)**
   - Label: "Achievement"
   - Value: Numeric value (biasanya 0-1 range untuk percentage)
   - Format: Display sebagai decimal
   - Color coding:
     - >= threshold: Green
     - < threshold: Red

**Visual Progress Bar:**
- Show progress dari realisasi terhadap target
- Width: (Realisasi / Target) × 100%
- Color: Green jika achieve, Red jika not achieve
- Tooltip: Show exact percentage

**Example Card Layout:**
```
┌─────────────────────────────────────────┐
│ Revenue                    ✓ Ach        │
├─────────────────────────────────────────┤
│ Target: Rp 500 M                        │
│ Realisasi: Rp 525 M                     │
│ Achievement: 1.05                        │
│ ━━━━━━━━━━━━━━━━━━━━━━━━ 105%          │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ CYC                        ✗ Not Ach    │
├─────────────────────────────────────────┤
│ Target: 75%                             │
│ Realisasi: 0.68 (68%)                   │
│ Achievement: 0.91                        │
│ ━━━━━━━━━━━━━━━━━        91%           │
└─────────────────────────────────────────┘
```

##### D. Parameters Detail

**Aspek Result (10 Parameters):**

1. **Revenue (t_revenue, r_revenue, ach_revenue_plan)**
   - Type: Currency (Rupiah)
   - Target example: Rp 500 M
   - Display format: formatCurrency()

2. **Scaling (t_scalling, r_scalling, ach_scaling)**
   - Type: Numeric
   - Target example: 50 (units)
   - Display format: number

3. **Datin (t_datin, r_datin, ach_sales_datin)**
   - Type: Count
   - Target example: 10 (new customers)
   - Display format: number

4. **HSI (t_hsi, r_hsi, ach_hsi)**
   - Type: Count
   - Target example: 15 (HSI units)
   - Display format: number

5. **Wireline (t_wireline, r_wireline, ach_wireline)**
   - Type: Count
   - Target example: 20
   - Display format: number

6. **Wifi (t_wifi, r_wifi, ach_wifi)**
   - Type: Count
   - Target example: 25
   - Display format: number

7. **CYC - Complete Your Connection (t_cyc, r_cyc, ach_cyc)**
   - Type: Percentage (0-1 range)
   - Target example: 0.75 → Display: 75%
   - Display format: × 100 + "%"

8. **CR - Churn Rate (t_cr, r_cr, ach_cr)**
   - Type: Percentage (0-1 range)
   - Target example: 0.05 → Display: 5%
   - Display format: × 100 + "%"

9. **Profit (t_profit, r_profit, ach_profit)**
   - Type: Percentage (0-1 range)
   - Target example: 0.15 → Display: 15%
   - Display format: × 100 + "%"

10. **NPS - Net Promoter Score (t_nps, r_nps, ach_nps)**
    - Type: Score (-100 to +100)
    - Target example: 50
    - Display format: number

**Aspek Proses (4 Parameters):**

1. **MAPS - Market Activation & Product Sales (t_maps, r_maps, ach_maps)**
   - Type: Percentage (0-1 range)
   - Target example: 0.80 → Display: 80%
   - Display format: × 100 + "%"

2. **LOP - Length of Process (t_lop, r_lop, ach_lop)**
   - Type: Days (numeric)
   - Target example: 14.5 (days)
   - Display format: number dengan 2 decimal + " days"

3. **Capability (t_capability, r_capability, ach_capability)**
   - Type: Score (0-100)
   - Target example: 75
   - Display format: number

4. **CC - Customer Care (t_cc, r_cc, ach_cc)**
   - Type: Score (0-100)
   - Target example: 80
   - Display format: number

##### E. Backend API Endpoint

**Route:** `GET /api/dashboard/am-performance-detail`

**Parameters:**
- `am_nik` (required)
- `quarter` (required)
- `year` (required)
- `segment` (required)

**Response Structure:**
```json
{
  "success": true,
  "data": {
    "am_info": {
      "nik": "810057",
      "nama": "Vino Pradana",
      "segment": "HQ-TWS",
      "region": "HQ TREG2",
      "witel": "WITEL JAKARTA BARAT"
    },
    "period": {
      "quarter": 1,
      "year": 2026,
      "label": "Q1 2026"
    },
    "achievement": {
      "result": {
        "status": "ACHIEVE",
        "percentage": 0.85,
        "threshold": 0.75
      },
      "proses": {
        "status": "NOT ACHIEVE",
        "percentage": 0.65,
        "threshold": 0.70
      },
      "nki": {
        "value": 105.5,
        "status": ">100%"
      }
    },
    "parameters": {
      "result": [
        {
          "name": "Revenue",
          "target": 500000000,
          "realisasi": 525000000,
          "achievement": 1.05,
          "status": "Ach",
          "formatted": {
            "target": "Rp 500 M",
            "realisasi": "Rp 525 M"
          }
        },
        {
          "name": "CYC",
          "target": 0.75,
          "realisasi": 0.68,
          "achievement": 0.91,
          "status": "Not Ach",
          "formatted": {
            "target": "75%",
            "realisasi": "68%"
          }
        }
        // ... 8 more parameters
      ],
      "proses": [
        {
          "name": "MAPS",
          "target": 0.80,
          "realisasi": 0.72,
          "achievement": 0.90,
          "status": "Not Ach",
          "formatted": {
            "target": "80%",
            "realisasi": "72%"
          }
        }
        // ... 3 more parameters
      ]
    }
  }
}
```

**Backend Query Logic:**
```php
// AmPerformanceDetailController.php

public function show(Request $request)
{
  $nikAm = $request->input('am_nik');
  $quarter = $request->input('quarter');
  $year = $request->input('year');
  $segment = $request->input('segment');
  
  // 1. Get lini_waktu
  $liniWaktu = DB::table('lini_waktu')
    ->where('nik_am', $nikAm)
    ->where('quartal', "Q{$quarter}")
    ->where('tahun', $year)
    ->first();
  
  // 2. Get account_manager_company IDs untuk segment ini
  $amCompanyIds = DB::table('account_manager_company')
    ->where('nik_am', $nikAm)
    ->where('segment', $segment)
    ->pluck('id')->toArray();
  
  // 3. Get target_account_m IDs
  $targetIds = DB::table('target_account_m')
    ->whereIn('account_manager_company_id', $amCompanyIds)
    ->pluck('id')->toArray();
  
  // 4. Get pivot data (lini_waktu_target)
  $pivotData = DB::table('lini_waktu_target as lwt')
    ->join('target_account_m as t', 'lwt.target_id', '=', 't.id')
    ->where('lwt.lini_waktu_id', $liniWaktu->id)
    ->whereIn('lwt.target_id', $targetIds)
    ->select('lwt.*', 't.*')
    ->get();
  
  // 5. Get first record untuk target values (CRITICAL FIX)
  $firstRecord = $pivotData->first();
  
  // 6. Build response dengan proper formatting
  $parameters = [
    'result' => [
      [
        'name' => 'Revenue',
        'target' => $firstRecord->t_revenue,
        'realisasi' => $pivotData->sum('r_revenue'),
        'achievement' => $pivotData->sum('ach_revenue_plan'),
        'status' => ($pivotData->sum('ach_revenue_plan') >= $liniWaktu->percentage_revenue) ? 'Ach' : 'Not Ach'
      ],
      [
        'name' => 'CYC',
        'target' => $firstRecord->t_cyc, // ✅ Use first(), not sum()
        'realisasi' => $pivotData->avg('r_cyc'),
        'achievement' => $pivotData->avg('ach_cyc'),
        'status' => ($pivotData->avg('ach_cyc') >= $liniWaktu->percentage_cyc) ? 'Ach' : 'Not Ach',
        'formatted' => [
          'target' => ($firstRecord->t_cyc * 100) . '%',
          'realisasi' => ($pivotData->avg('r_cyc') * 100) . '%'
        ]
      ]
      // ... other parameters
    ],
    'proses' => [
      // Similar structure untuk 4 proses parameters
    ]
  ];
  
  return response()->json(['success' => true, 'data' => $data]);
}
```

---

### 2. DATA IMPORT PERFORMANCE

Sistem import data performance adalah mekanisme untuk upload dan manage data performance Account Manager dari file Excel dengan struktur 3 sheets sequential.

#### 2.1 Halaman Data Import Performance

**Lokasi File:** `resources/js/pages/DataImportPerformance.tsx`

**Tujuan:**
Menyediakan interface untuk admin melakukan import data performance dari Excel template yang sudah standardized.

**Struktur Halaman:**

##### A. Upload Section

**Upload Form:**
- **Quarter Selector:**
  - Dropdown dengan options: Q1, Q2, Q3, Q4
  - Required field
  - Default: Current quarter
  
- **File Input:**
  - Accept: .xlsx, .xls only
  - Max size: 50 MB (lebih besar dari revenue karena lebih banyak kolom)
  - Drag & drop support
  - File preview dengan nama dan size
  
- **Upload Button:**
  - Primary button dengan loading state
  - Disabled jika no file atau no quarter selected
  - Icon: Upload cloud
  - Text: "Upload Performance Data"

**Validations:**
- File format check (Excel only)
- File size check (max 50MB)
- Quarter selection check
- Sheet structure validation (3 sheets required)

##### B. Excel Template Structure

**Sheet 1: Region and Witel**

Purpose: Master data untuk region dan witel mapping

**Columns:**
- Column A: Region Code (e.g., "TREG1", "HQ TREG2")
- Column B: Region Name (e.g., "Regional 1")
- Column C: Witel Name (e.g., "WITEL JAKARTA BARAT")

**Processing:**
- Validate region codes exists
- Validate witel names
- Update or insert witels with region mapping

**Sheet 2: TWS {year}**

Purpose: Target dan Realisasi per Company untuk Account Manager

**Structure:**
- **Row 1:** Header
- **Row 2+:** Data per company assignment

**Key Columns (46+ columns total):**
- Column A: Quartal (Q1/Q2/Q3/Q4)
- Column B: NIK AM
- Column C: Nama AM
- Column D: Segment (HQ-TWS, POTS, dll)
- Column E: NIP NAS (Company ID)
- Column F: Company Name
- Columns G-AX: Target dan Realisasi untuk 14 parameters
  - Each parameter has 3 columns: Target (t_xxx), Realisasi (r_xxx), Achievement (ach_xxx)

**Parameters Covered:**
1. Revenue (3 cols: t_revenue, r_revenue, ach_revenue_plan)
2. Scaling (3 cols: t_scalling, r_scalling, ach_scaling)
3. Datin (3 cols: t_datin, r_datin, ach_sales_datin)
4. HSI (3 cols: t_hsi, r_hsi, ach_hsi)
5. Wireline (3 cols: t_wireline, r_wireline, ach_wireline)
6. Wifi (3 cols: t_wifi, r_wifi, ach_wifi)
7. CYC (3 cols: t_cyc, r_cyc, ach_cyc)
8. CR (3 cols: t_cr, r_cr, ach_cr)
9. Profit (3 cols: t_profit, r_profit, ach_profit)
10. NPS (3 cols: t_nps, r_nps, ach_nps)
11. MAPS (3 cols: t_maps, r_maps, ach_maps)
12. LOP (3 cols: t_lop, r_lop, ach_lop)
13. Capability (3 cols: t_capability, r_capability, ach_capability)
14. CC (3 cols: t_cc, r_cc, ach_cc)

**Processing Logic:**
```php
foreach ($row as $index => $rowData) {
  // 1. Validate NIK AM exists
  $am = AccountManager::where('nik', $rowData[1])->first();
  if (!$am) throw ValidationException("NIK not found");
  
  // 2. Get or create account_manager_company
  $amc = AccountManagerCompany::firstOrCreate([
    'nik_am' => $rowData[1],
    'nip_nas' => $rowData[4]
  ]);
  
  // 3. Update segment
  $amc->update(['segment' => $rowData[3]]);
  
  // 4. Get or create lini_waktu
  $liniWaktu = LiniWaktu::firstOrCreate([
    'nik_am' => $rowData[1],
    'quartal' => $rowData[0],
    'tahun' => $year
  ]);
  
  // 5. Create or update target_account_m
  $target = TargetAccountM::updateOrCreate(
    ['account_manager_company_id' => $amc->id],
    [
      't_revenue' => $rowData[6],
      't_scalling' => $rowData[9],
      't_datin' => $rowData[12],
      // ... all 14 target parameters
    ]
  );
  
  // 6. Create or update lini_waktu_target (pivot)
  LiniWaktuTarget::updateOrCreate(
    [
      'lini_waktu_id' => $liniWaktu->id,
      'target_id' => $target->id
    ],
    [
      'r_revenue' => $rowData[7],
      'ach_revenue_plan' => $rowData[8],
      'r_scalling' => $rowData[10],
      'ach_scaling' => $rowData[11],
      // ... all 14 realisasi & achievement parameters
    ]
  );
}
```

**Sheet 3: NKI {year}**

Purpose: Achievement Result/Proses dan NKI Adjustment per AM

**Structure:**
- **Row 1:** Persentase threshold untuk Result
- **Row 2:** Persentase threshold untuk 10 Result parameters + 4 Proses parameters
- **Row 3:** Header kolom
- **Row 4+:** Data per Account Manager

**Row 1 (Thresholds):**
- Column G: percentage_result (e.g., 0.75)
- Column AK: percentage_proses (e.g., 0.60)

**Row 2 (Parameter Thresholds):**
- Columns G, J, M, P, S, V, Y, AB, AE, AH: Thresholds untuk 10 Result parameters
- Columns AK, AN, AQ, AT: Thresholds untuk 4 Proses parameters

**Row 4+ (AM Data):**
- Column A: Quartal
- Column B: NIK AM (KEY COLUMN)
- Column C: Nama AM
- Column D: Segment
- Column E: Witel
- Columns F-AU: Target, Realisasi, Achievement untuk semua parameters
- **Column AV: ach_result** ⭐ (Achievement Result total)
- **Column AW: ach_proses** ⭐ (Achievement Proses total)
- **Column AX: nki_adjustment** ⭐ (NKI final value)

**CRITICAL LOGIC untuk Column AV-AX:**

Achievement Result (Column AV):
- Nilai ini menentukan apakah AM ACHIEVE untuk Result
- Sistem akan UPDATE HANYA 1 lini_waktu_target (yang paling baru) per NIK AM
- Jika AM punya multiple companies (multiple lini_waktu_target), hanya 1 yang dapat nilai ini
- **Example:**
  ```
  NIK 810057 (Vino) punya 9 companies:
  - lini_waktu_target #1 (oldest): ach_result = 0
  - lini_waktu_target #2: ach_result = 0
  - ...
  - lini_waktu_target #9 (newest): ach_result = 0.75 ← UPDATED dari Excel
  
  Total ach_result = SUM(0 + 0 + ... + 0.75) = 0.75
  Threshold = 0.75
  0.75 >= 0.75 → ACHIEVE ✅
  ```

Achievement Proses (Column AW):
- Similar logic dengan ach_result
- Determines ACHIEVE status untuk Proses

NKI Adjustment (Column AX):
- Final NKI value setelah adjustment
- Used untuk determine >100% atau <100%
- Calculated: AVG(nki_adjustment) untuk AM

**Processing Logic:**
```php
// Process Row 1-2: Update thresholds
$percentageResult = $sheet->getCell('G1')->getValue();
$percentageProses = $sheet->getCell('AK1')->getValue();

LiniWaktu::where('quartal', $quartal)
  ->where('tahun', $year)
  ->update([
    'percentage_result' => $percentageResult,
    'percentage_proses' => $percentageProses,
    'percentage_revenue' => $sheet->getCell('G2')->getValue(),
    'percentage_scaling' => $sheet->getCell('J2')->getValue(),
    // ... update all 14 parameter thresholds
  ]);

// Process Row 4+: Update AM data
foreach ($rows as $row) {
  $nikAm = $row[1]; // Column B
  
  // 1. Update segment untuk SEMUA account_manager_company
  AccountManagerCompany::where('nik_am', $nikAm)
    ->update(['segment' => $row[3]]);
  
  // 2. Get lini_waktu untuk NIK ini
  $liniWaktu = LiniWaktu::where('nik_am', $nikAm)
    ->where('quartal', $quartal)
    ->where('tahun', $year)
    ->first();
  
  // 3. Get LATEST lini_waktu_target untuk NIK ini
  $latestLwt = LiniWaktuTarget::where('lini_waktu_id', $liniWaktu->id)
    ->orderBy('created_at', 'desc')
    ->first();
  
  // 4. Update target values (target_account_m)
  TargetAccountM::where('id', $latestLwt->target_id)
    ->update([
      't_datin' => $row[11], // Column L
      't_hsi' => $row[14],   // Column O
      't_cyc' => $row[23],   // Column X
      't_cr' => $row[26],    // Column AA
      't_profit' => $row[29], // Column AD
      't_maps' => $row[35],  // Column AJ
      't_lop' => $row[38],   // Column AM
      // ... other target parameters
    ]);
  
  // 5. Update ONLY the latest lini_waktu_target
  $latestLwt->update([
    'r_datin' => $row[12],         // Column M
    'ach_sales_datin' => $row[13], // Column N
    'r_hsi' => $row[15],           // Column P
    'ach_hsi' => $row[16],         // Column Q
    // ... all realisasi & achievement columns
    'ach_result' => $row[47],      // Column AV ⭐
    'ach_proses' => $row[48],      // Column AW ⭐
    'nki_adjustment' => $row[49]   // Column AX ⭐
  ]);
}
```

##### C. Upload History Table

**Purpose:**
Menampilkan riwayat upload performance data untuk tracking dan audit.

**Table Columns:**
1. **Tanggal Upload**
   - Timestamp dengan format: DD MMM YYYY, HH:mm
   - Example: "15 Jan 2025, 14:30"
   
2. **Quarter & Year**
   - Display: "Q{number} {year}"
   - Example: "Q1 2026"
   
3. **Filename**
   - Original filename dari file Excel
   - Truncate jika terlalu panjang dengan ellipsis
   
4. **Row Count**
   - Jumlah AM data yang berhasil diimport
   - Display: "{count} AM records"
   
5. **File Size**
   - Size dalam KB atau MB
   - Format: "{size} MB"
   
6. **Uploaded By**
   - Nama user yang melakukan upload
   - From `users.name`
   
7. **Actions**
   - Download button: Download original file
   - Delete button: Delete upload record (admin only)

**Features:**
- Pagination (10 records per page)
- Sort by upload date (newest first)
- Filter by quarter/year
- Search by filename

##### D. Backend Controller

**Controller:** `DataImportPerformanceController.php`

**Key Methods:**

```php
class DataImportPerformanceController extends Controller
{
  // Display upload page dengan history
  public function index()
  {
    $uploads = PerformanceUpload::with('uploader')
      ->orderBy('created_at', 'desc')
      ->paginate(10);
    
    return Inertia::render('DataImportPerformance', [
      'uploads' => $uploads,
      'quarters' => ['Q1', 'Q2', 'Q3', 'Q4']
    ]);
  }
  
  // Process import dari 3 sheets
  public function import(Request $request)
  {
    $request->validate([
      'file' => 'required|mimes:xlsx,xls|max:51200', // 50MB
      'quarter' => 'required|in:Q1,Q2,Q3,Q4'
    ]);
    
    DB::beginTransaction();
    try {
      $file = $request->file('file');
      $quarter = $request->input('quarter');
      $year = date('Y');
      
      // Load Excel file
      $spreadsheet = IOFactory::load($file->path());
      
      // Process Sheet 1: Region and Witel
      $this->processRegionWitelSheet($spreadsheet->getSheet(0));
      
      // Process Sheet 2: TWS {year}
      $this->processTWSSheet($spreadsheet->getSheet(1), $quarter, $year);
      
      // Process Sheet 3: NKI {year}
      $this->processNKISheet($spreadsheet->getSheet(2), $quarter, $year);
      
      // Store file
      $storedPath = $file->store('performance_uploads', 'local');
      
      // Save upload record
      PerformanceUpload::create([
        'tahun' => $year,
        'quartal' => $quarter,
        'uploaded_by' => auth()->id(),
        'original_filename' => $file->getClientOriginalName(),
        'stored_path' => $storedPath,
        'row_count' => $rowCount,
        'file_size_kb' => $file->getSize() / 1024
      ]);
      
      DB::commit();
      return response()->json(['success' => true, 'message' => 'Import successful']);
      
    } catch (Exception $e) {
      DB::rollBack();
      return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
    }
  }
  
  // Download uploaded file
  public function download($uploadId)
  {
    $upload = PerformanceUpload::findOrFail($uploadId);
    
    if (!Storage::exists($upload->stored_path)) {
      abort(404, 'File not found');
    }
    
    return Storage::download($upload->stored_path, $upload->original_filename);
  }
  
  // Delete upload record
  public function destroy($uploadId)
  {
    $upload = PerformanceUpload::findOrFail($uploadId);
    
    // Delete file dari storage
    if (Storage::exists($upload->stored_path)) {
      Storage::delete($upload->stored_path);
    }
    
    // Delete record
    $upload->delete();
    
    return response()->json(['success' => true]);
  }
}
```

---

### 3. DATABASE ARCHITECTURE

#### 3.1 Performance-Related Tables

**A. Account Manager Core:**

**1. account_managers**
```sql
CREATE TABLE account_managers (
  nik VARCHAR(20) PRIMARY KEY,
  nama VARCHAR(255) NOT NULL,
  posisi VARCHAR(50),     -- AM, AM1, AM2, EAM, SAM
  no_gsm VARCHAR(20),
  idwitels BIGINT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (idwitels) REFERENCES witels(idwitels)
);
```

**2. account_manager_company** (Pivot)
```sql
CREATE TABLE account_manager_company (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  nik_am VARCHAR(20) NOT NULL,
  nip_nas VARCHAR(6) NOT NULL,
  segment VARCHAR(50),              -- HQ-TWS, POTS, dll
  proporsi DECIMAL(5,2),            -- e.g., 50.00 untuk 50%
  pembagian ENUM('SINGLE', 'MULTI'),
  tanggal_mulai DATE,
  tanggal_selesai DATE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (nik_am) REFERENCES account_managers(nik),
  FOREIGN KEY (nip_nas) REFERENCES companies(nip_nas)
);
```

**B. Performance Period & Targets:**

**3. lini_waktu**
```sql
CREATE TABLE lini_waktu (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  nik_am VARCHAR(20) NOT NULL,
  quartal ENUM('Q1', 'Q2', 'Q3', 'Q4'),
  bulan_awal DATE,
  bulan_akhir DATE,
  tahun INT,
  
  -- Percentage thresholds untuk achievement
  percentage_result DECIMAL(5,4),      -- e.g., 0.7500 untuk 75%
  percentage_revenue DECIMAL(5,4),
  percentage_scaling DECIMAL(5,4),
  percentage_datin DECIMAL(5,4),
  percentage_hsi DECIMAL(5,4),
  percentage_wireline DECIMAL(5,4),
  percentage_wifi DECIMAL(5,4),
  percentage_cyc DECIMAL(5,4),
  percentage_cr DECIMAL(5,4),
  percentage_profit DECIMAL(5,4),
  percentage_customer DECIMAL(5,4),
  percentage_proses DECIMAL(5,4),
  percentage_maps DECIMAL(5,4),
  percentage_lop DECIMAL(5,4),
  percentage_capability DECIMAL(5,4),
  percentage_cc DECIMAL(5,4),
  
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (nik_am) REFERENCES account_managers(nik),
  UNIQUE KEY unique_period (nik_am, quartal, tahun)
);
```

**4. target_account_m**
```sql
CREATE TABLE target_account_m (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  account_manager_company_id BIGINT NOT NULL,
  
  -- Target values untuk 14 parameters
  t_revenue DECIMAL(15,2),
  t_scalling DECIMAL(10,2),
  t_datin INT,
  t_hsi INT,
  t_wireline INT,
  t_wifi INT,
  t_cyc DECIMAL(5,4),       -- 0-1 range untuk percentage
  t_cr DECIMAL(5,4),
  t_profit DECIMAL(5,4),
  t_nps DECIMAL(6,2),       -- -100 to +100
  t_maps DECIMAL(5,4),
  t_lop DECIMAL(6,2),       -- Days
  t_capability DECIMAL(5,2),
  t_cc DECIMAL(5,2),
  
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (account_manager_company_id) REFERENCES account_manager_company(id)
);
```

**5. lini_waktu_target** (Pivot Table)
```sql
CREATE TABLE lini_waktu_target (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  lini_waktu_id BIGINT NOT NULL,
  target_id BIGINT NOT NULL,
  
  -- Realisasi values untuk 14 parameters
  r_revenue DECIMAL(15,2),
  r_scalling DECIMAL(10,2),
  r_datin INT,
  r_hsi INT,
  r_wireline INT,
  r_wifi INT,
  r_cyc DECIMAL(5,4),
  r_cr DECIMAL(5,4),
  r_profit DECIMAL(5,4),
  r_nps DECIMAL(6,2),
  r_maps DECIMAL(5,4),
  r_lop DECIMAL(6,2),
  r_capability DECIMAL(5,2),
  r_cc DECIMAL(5,2),
  
  -- Achievement values untuk 14 parameters
  ach_revenue_plan DECIMAL(5,4),
  ach_scaling DECIMAL(5,4),
  ach_sales_datin DECIMAL(5,4),
  ach_hsi DECIMAL(5,4),
  ach_wireline DECIMAL(5,4),
  ach_wifi DECIMAL(5,4),
  ach_cyc DECIMAL(5,4),
  ach_cr DECIMAL(5,4),
  ach_profit DECIMAL(5,4),
  ach_nps DECIMAL(5,4),
  ach_maps DECIMAL(5,4),
  ach_lop DECIMAL(5,4),
  ach_capability DECIMAL(5,4),
  ach_cc DECIMAL(5,4),
  
  -- Overall achievement
  ach_result DECIMAL(5,4),    -- Total Result achievement
  ach_proses DECIMAL(5,4),    -- Total Proses achievement
  nki_adjustment DECIMAL(6,2), -- Final NKI value
  
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (lini_waktu_id) REFERENCES lini_waktu(id),
  FOREIGN KEY (target_id) REFERENCES target_account_m(id),
  UNIQUE KEY unique_assignment (lini_waktu_id, target_id)
);
```

**C. Upload Metadata:**

**6. performance_uploads**
```sql
CREATE TABLE performance_uploads (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tahun INT NOT NULL,
  quartal VARCHAR(2) NOT NULL,  -- Q1, Q2, Q3, Q4
  uploaded_by BIGINT NOT NULL,
  original_filename VARCHAR(255),
  stored_path VARCHAR(500),
  row_count INT,                -- Jumlah AM records imported
  file_size_kb DECIMAL(10,2),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(id),
  INDEX idx_period (tahun, quartal)
);
```

#### 3.2 Key Relationships

**Relationship Flow:**
```
account_managers (AM)
    ↓ (1:Many)
lini_waktu (Performance Period)
    ↓ (1:Many)
lini_waktu_target (Pivot)
    ↓ (Many:1)
target_account_m (Target Values)
    ↓ (Many:1)
account_manager_company (Pivot)
    ↓ (Many:1)
companies (Company Assignment)
```

**Example Data Flow:**
```
1. AM "Vino" (nik: 810057) handles 9 companies
2. For Q1 2026, create 1 lini_waktu record
3. For each company, create:
   - 1 account_manager_company record (if not exists)
   - 1 target_account_m record
   - 1 lini_waktu_target record
4. Total: 1 lini_waktu + 9 target_account_m + 9 lini_waktu_target
```

---

## 🔧 BACKEND IMPLEMENTATION

### Controller Summary

**1. DashboardController.php** (Performance AM Methods)
- `performanceAM()` - Main Performance AM page render
- `getTotalAM()` - Count total Account Managers
- `getTotalRevenueTarget()` - Sum target revenue untuk period
- `getAvailableYears()` - Get distinct years dari lini_waktu
- `getAvailableQuartals()` - Get quartals for selected year
- `getCurrentQuartal()` - Calculate current Q based on month
- `getAMRevenueRanking()` - Bar chart data dengan LEFT JOIN filtering ⭐
- `getRegionDistribution()` - Pie chart AM count per region
- `getAccountManagerList()` - Table data all AMs dengan filtering
- `getAMRevenueDetails()` - Modal data: companies list per AM

**2. RegionNkiController.php** - Region Performance dengan Compare
- `show()` - Main method with compare feature
- `getPreviousPeriod()` - Calculate previous quarter
  ```php
  if ($quarter == 1) {
    return ['quarter' => 4, 'year' => $year - 1];
  } else {
    return ['quarter' => $quarter - 1, 'year' => $year];
  }
  ```
- `getPeriodData()` - Fetch and calculate data untuk specific period
  - Revenue summary (target, realisasi)
  - Total AM dalam region
  - Segment statistics (Result/Proses/NKI)
  - Parameter breakdown (Ach/Not Ach per 14 parameters)

**3. AmPerformanceDetailController.php** - Individual AM Detail
- `show()` - Complete breakdown per AM
- **CRITICAL FIX:** Use `first()` untuk percentage parameters, not `sum()`
  ```php
  // ❌ WRONG
  $pivotData->sum('t_cyc')  // Will multiply: 0.75 + 0.75 = 1.5
  
  // ✅ CORRECT
  $firstRecord = $pivotData->first();
  $firstRecord->t_cyc  // Will get: 0.75
  ```
- Calculate achievement status per parameter
- Format display values (currency, percentage, numbers)

**4. DataImportPerformanceController.php** - Excel Import
- `index()` - Display upload page dengan history
- `import()` - Process 3-sheet Excel import
  - `processRegionWitelSheet()` - Sheet 1 processing
  - `processTWSSheet()` - Sheet 2 processing (main data)
  - `processNKISheet()` - Sheet 3 processing (achievements)
- `download()` - Download original uploaded file
- `destroy()` - Delete upload record dan file

**5. LiniWaktuController.php** - Performance Period Management
- Manage lini_waktu records
- Update percentage thresholds
- Calculate period ranges (bulan_awal, bulan_akhir)

---

## 📊 QUERY OPTIMIZATION

### Complex Joins untuk Performance Data

**1. AM Revenue Ranking Query:**
```sql
SELECT 
  am.nik,
  am.nama as am_name,
  r.code as region_code,
  COALESCE(SUM(tam.t_revenue), 0) as t_revenue
FROM account_managers am
LEFT JOIN witels w ON am.idwitels = w.idwitels
LEFT JOIN regions r ON w.region_id = r.id
LEFT JOIN lini_waktu lw ON am.nik = lw.nik_am 
  AND lw.tahun = ? 
  AND lw.quartal = ?  -- ⭐ CRITICAL: Filter in JOIN
LEFT JOIN lini_waktu_target lwt ON lw.id = lwt.lini_waktu_id
LEFT JOIN target_account_m tam ON lwt.target_id = tam.id
GROUP BY am.nik
ORDER BY t_revenue DESC
```

**2. Region NKI Query dengan Comparison:**
```sql
-- Current Period
SELECT 
  am.nik,
  am.nama,
  SUM(lwt.ach_result) as total_ach_result,
  SUM(lwt.ach_proses) as total_ach_proses,
  AVG(lwt.nki_adjustment) as avg_nki
FROM account_managers am
JOIN witels w ON am.idwitels = w.idwitels
JOIN regions r ON w.region_id = r.id
JOIN lini_waktu lw ON am.nik = lw.nik_am
JOIN lini_waktu_target lwt ON lw.id = lwt.lini_waktu_id
WHERE r.id = ?
  AND lw.tahun = ?
  AND lw.quartal = ?
GROUP BY am.nik

-- Previous Period (same query, different period)
```

**3. AM Performance Detail Query:**
```sql
SELECT 
  lwt.*,
  tam.*
FROM lini_waktu_target lwt
JOIN target_account_m tam ON lwt.target_id = tam.id
JOIN account_manager_company amc ON tam.account_manager_company_id = amc.id
WHERE lwt.lini_waktu_id = ?
  AND amc.nik_am = ?
  AND amc.segment = ?
ORDER BY lwt.created_at DESC
```

### Aggregations & Calculations

**Achievement Calculation:**
```php
// Result Achievement
$totalAchResult = $pivotData->sum('ach_revenue_plan') + 
                  $pivotData->sum('ach_scaling') + 
                  $pivotData->sum('ach_sales_datin') +
                  // ... sum all 10 Result parameters
                  
$resultPercentage = $totalAchResult / 10;
$isResultAchieve = $resultPercentage >= $liniWaktu->percentage_result;

// Proses Achievement
$totalAchProses = $pivotData->sum('ach_maps') +
                  $pivotData->sum('ach_lop') +
                  $pivotData->sum('ach_capability') +
                  $pivotData->sum('ach_cc');
                  
$prosesPercentage = $totalAchProses / 4;
$isProsesAchieve = $prosesPercentage >= $liniWaktu->percentage_proses;

// NKI
$avgNki = $pivotData->avg('nki_adjustment');
$nkiStatus = $avgNki >= 100 ? '>100%' : '<100%';
```

### Indexing Strategy

**Indexes untuk Performance:**
```sql
-- account_managers
CREATE INDEX idx_am_witel ON account_managers(idwitels);

-- lini_waktu
CREATE INDEX idx_lw_period ON lini_waktu(nik_am, tahun, quartal);

-- lini_waktu_target
CREATE INDEX idx_lwt_lw ON lini_waktu_target(lini_waktu_id);
CREATE INDEX idx_lwt_target ON lini_waktu_target(target_id);

-- target_account_m
CREATE INDEX idx_tam_amc ON target_account_m(account_manager_company_id);

-- account_manager_company
CREATE INDEX idx_amc_am ON account_manager_company(nik_am);
CREATE INDEX idx_amc_company ON account_manager_company(nip_nas);
CREATE INDEX idx_amc_segment ON account_manager_company(segment);
```

---

## 🎨 FRONTEND ARCHITECTURE

### Component Structure

**Pages:**
- `PerformanceAm.tsx` - Main Performance AM dashboard
- `DataImportPerformance.tsx` - Performance data import page

**Modal Components:**
- `AMRevenueDetailModal.tsx` - AM revenue breakdown dengan witel distribution
- `RegionNkiModal.tsx` - Region performance dengan period comparison
- `AmPerformanceDetailModal.tsx` - Complete AM detail dengan 14 parameters

**Chart Components:**
- Bar Chart untuk Target Revenue AM (horizontal)
- Pie Chart untuk Region Distribution
- Pie Chart untuk Witel Distribution (dalam AM Revenue modal)
- Progress bars untuk achievement per parameter

**UI Components:**
- Custom notifications (Toast, ConfirmDialog)
- StatCard untuk summary metrics
- Parameter cards dengan trend indicators
- Tables dengan sorting dan filtering

### State Management

**Performance AM Page State:**
```typescript
const [selectedYear, setSelectedYear] = useState(currentYear);
const [selectedQuartal, setSelectedQuartal] = useState(currentQuartal);
const [amModalOpen, setAmModalOpen] = useState(false);
const [selectedAmNik, setSelectedAmNik] = useState(null);
const [regionNkiModalOpen, setRegionNkiModalOpen] = useState(false);
const [selectedRegionId, setSelectedRegionId] = useState(null);
```

**Modal Interactions:**
```typescript
// Click bar chart → AM Revenue Detail Modal
const handleBarClick = (data: any) => {
  setSelectedAmNik(data.nik);
  setAmModalOpen(true);
};

// Click pie segment → Region NKI Modal
const handlePieClick = (data: any) => {
  setSelectedRegionId(data.id);
  setRegionNkiModalOpen(true);
};

// Click detail button → AM Performance Detail Modal
const handleDetailClick = (nik: string, name: string) => {
  setSelectedAmNik(nik);
  setSelectedAmName(name);
  setDetailModalOpen(true);
};
```

---

## 🚀 KEY FEATURES HIGHLIGHTS

### 1. Period-based Filtering
- Year selector dengan distinct years dari data
- Quartal selector (Q1-Q4) dengan cascading options
- Automatic calculation current quartal
- Filter applied to ALL visualizations consistently

### 2. Interactive Visualizations
- Clickable bar chart untuk drill-down ke AM detail
- Clickable pie chart untuk drill-down ke region NKI
- Hover tooltips dengan formatted data
- Responsive charts dengan mobile support

### 3. Automatic Period Comparison
- Compare current quarter vs previous quarter automatically
- Trend arrows (↑↓) untuk quick visual insight
- Color coding (green/red) untuk improvement/decline
- Side-by-side data display untuk easy comparison

### 4. Multi-parameter Tracking
- 14 parameters tracked per Account Manager
- 10 Result parameters (Revenue, Scaling, Datin, HSI, Wireline, Wifi, CYC, CR, Profit, NPS)
- 4 Proses parameters (MAPS, LOP, Capability, CC)
- Individual achievement tracking per parameter

### 5. Excel Import System
- 3-sheet sequential processing
- Comprehensive validation
- Transaction-based (rollback on error)
- File storage untuk audit trail

### 6. Achievement Logic
- Threshold-based achievement determination
- Percentage-based thresholds per parameter
- Overall Result & Proses achievement
- NKI calculation dengan adjustment

### 7. Critical Bug Fix
- **Problem:** Percentage parameters (CYC, CR, Profit, MAPS) displayed incorrectly when AM has multiple companies
- **Cause:** Using `sum()` instead of `first()` for target values
- **Solution:** Use `first()` record untuk get single target value, not sum
- **Impact:** Correct percentage display (75% instead of 150% for 2 companies)

---

## 📈 PERFORMANCE METRICS

### Data Volume Handling
- Puluhan Account Managers dengan multiple company assignments
- Ratusan records di lini_waktu_target per period
- Complex joins across 5-6 tables
- Efficient aggregations dengan proper indexing

### Optimization Techniques
- LEFT JOIN dengan WHERE conditions untuk filtering
- Database indexing pada foreign keys dan period fields
- Query result caching untuk repeated requests
- Lazy loading untuk large datasets
- Memoization untuk chart data calculations

---

## 🔐 SECURITY FEATURES

### Role-Based Access
- Admin: Full access (view, upload, delete)
- Viewer: Read-only access (view dashboards only)
- Upload/Delete operations require Admin role

### Data Validation
- NIK AM validation (must exist in account_managers)
- Company validation (NIP NAS must exist)
- Period validation (quartal, year format)
- File type validation (.xlsx, .xls only)
- File size validation (max 50MB)

### Transaction Safety
- Database transactions untuk import operations
- Rollback on any validation error
- File cleanup on failed uploads
- Audit trail via performance_uploads table

---

## 📚 DOCUMENTATION FILES

Aplikasi ini memiliki dokumentasi komprehensif:

1. **PERFORMANCE_AM_QUICK_REFERENCE.md** - Quick reference untuk Performance AM fitur
2. **AM_REVENUE_MODAL_IMPLEMENTATION.md** - AM Revenue Detail Modal docs
3. **COMPARE_FEATURE_DOCUMENTATION.md** - Region NKI compare feature
4. **NKI_SHEET_IMPORT_GUIDE.md** - Complete guide untuk Sheet 3 (NKI) import
5. **CALCULATION_FIX_DOCUMENTATION.md** - Bug fixes dan calculation logic
6. **PERFORMANCE_UPLOAD_LOGGING_GUIDE.md** - Upload logging mechanism
7. **TROUBLESHOOTING_AM_MODAL.md** - Common issues dan solutions

---

## 🎯 KESIMPULAN

Sistem Performance AM adalah modul komprehensif untuk monitoring dan evaluasi performa Account Manager di Telkom Wholesale Service. Aplikasi ini berhasil mengintegrasikan:

### Pencapaian Utama:

✅ **Comprehensive Performance Dashboard**
   - 5 summary cards untuk quick metrics
   - 2 interactive charts (Bar & Pie) untuk visual analysis
   - Complete AM list table dengan filtering
   - Real-time filtering by year dan quartal

✅ **Multi-level Drill-Down**
   - Level 1: Performance AM Dashboard (overview all AMs)
   - Level 2: AM Revenue Detail Modal (companies per AM)
   - Level 3: Region NKI Modal (performance per region dengan comparison)
   - Level 4: AM Performance Detail Modal (complete 14 parameters)

✅ **Advanced Comparison Features**
   - Automatic period comparison (current vs previous quarter)
   - Trend indicators (↑↓ arrows dengan color coding)
   - Side-by-side data display
   - Historical performance tracking

✅ **Multi-parameter Performance Tracking**
   - 14 parameters tracked per AM (10 Result + 4 Proses)
   - Individual achievement status per parameter
   - Overall Result & Proses achievement
   - NKI calculation dengan adjustment
   - Threshold-based achievement determination

✅ **Robust Excel Import System**
   - 3-sheet sequential processing
   - Comprehensive validation (NIK, Company, Period)
   - Transaction-based untuk data integrity
   - Upload history dengan file storage
   - Download original files untuk reference

✅ **Critical Bug Fixes**
   - Fixed percentage parameters display (CYC, CR, Profit, MAPS)
   - Changed from `sum()` to `first()` untuk target values
   - Resolved multiple-company calculation issues
   - Ensured correct percentage display

✅ **Technical Excellence**
   - Complex 5-6 table joins dengan proper indexing
   - Efficient aggregations dan calculations
   - LEFT JOIN dengan WHERE conditions untuk accurate filtering
   - Transaction safety untuk data operations
   - Role-based access control

### Impact & Value:

Dashboard Performance AM memberikan nilai strategis dengan:
- **Visibility:** Real-time monitoring performa seluruh AM
- **Accountability:** Individual tracking dengan detailed metrics
- **Comparison:** Period-to-period comparison untuk trend analysis
- **Efficiency:** Automated data import dan calculations
- **Decision Making:** Data-driven insights untuk performance improvement

Sistem telah siap production dengan architecture yang robust, well-documented, dan optimized untuk performance.

---

## 📞 INFORMASI TEKNIS

**Versi Aplikasi:** 1.0  
**Tech Stack:**
- Laravel: 11.x
- React: 19.x
- TypeScript: 5.x
- MySQL: 8.x
- Recharts: 2.x

**Database Tables (Performance):**
- `account_managers`
- `account_manager_company`
- `lini_waktu`
- `target_account_m`
- `lini_waktu_target`
- `performance_uploads`

---

## 📝 CATATAN PENUTUP

**Fokus Laporan:** Laporan ini secara khusus membahas fitur **Performance AM (Account Manager)** sebagai sistem monitoring dan evaluasi performa Account Manager. Pembahasan mencakup:
- Performance AM Dashboard dengan semua visualisasi
- Multi-level modal drill-down system
- Period comparison features
- 14-parameter performance tracking
- Excel import system (3 sheets)
- Achievement calculations dan NKI logic
- Technical implementation details

**Cakupan:** Laporan ini dibuat berdasarkan dokumentasi lengkap dan source code aktual di repository dashboard-TWS, fokus pada modul Performance AM tanpa Daily Monitoring dan Revenue Dashboard.

---

*Dokumen ini merupakan bagian dari Laporan Magang dan bersifat konfidensial.*
