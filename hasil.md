# LAPORAN HASIL MAGANG
## SISTEM DASHBOARD ANALYTICS TWS (TELKOM WHOLESALE SERVICE)

---

## 📋 RINGKASAN EKSEKUTIF

Sistem Dashboard Analytics TWS adalah aplikasi web berbasis **Laravel 12** dan **React 19 dengan TypeScript** yang dikembangkan untuk monitoring revenue dan performance Account Manager di Telkom Wholesale Service. Aplikasi ini menggunakan **Inertia.js v2** sebagai bridge layer untuk seamless integration antara backend dan frontend.

### Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** React 19 + TypeScript
- **Bridge:** Inertia.js v2
- **UI Framework:** Tailwind CSS v4 + shadcn/ui
- **Database:** MySQL
- **Authentication:** Laravel Fortify
- **Authorization:** Role-based (Admin/Viewer)
- **Charting:** Recharts

---

## 🎯 FITUR UTAMA APLIKASI

### 1. DASHBOARD REVENUE ANALYTICS

Sistem Dashboard Revenue Analytics adalah fitur utama aplikasi yang dirancang untuk memberikan insight mendalam tentang performa revenue perusahaan-perusahaan klien Telkom Wholesale Service. Dashboard ini mengintegrasikan data dari berbagai level hierarki (Company → Group1-4 → Revenue) dan menyajikannya dalam format yang mudah dipahami dengan visualisasi interaktif.

#### 1.1 Halaman Dashboard Utama
**Lokasi File:** `resources/js/pages/Dashboard.tsx`

Dashboard utama terdiri dari beberapa komponen utama yang bekerja secara terintegrasi:

##### A. Summary Cards (4 Cards Statistik Kunci)

**1. Total Revenue Card**
- Menampilkan total revenue realisasi untuk tahun yang dipilih
- Format currency otomatis (Miliar/Triliun dengan suffix M/T)
- Data diambil dari agregasi table `revenues` dengan filtering by tahun
- Icon: TrendingUp dengan gradient background
- Real-time update saat user mengubah filter tahun

**2. YTD Revenue Card**
- Year-to-Date revenue sampai bulan berjalan
- Menampilkan perbandingan dengan periode yang sama tahun sebelumnya
- Perhitungan: SUM(revenue_realisasi) WHERE bulan <= current_month
- Visual indicator: Badge dengan warna (green untuk growth, red untuk decline)
- Percentage growth calculation otomatis

**3. Achievement Rate Card**
- Persentase pencapaian target keseluruhan
- Formula: (Total Realisasi / Total Target) × 100%
- Color-coded indicator:
  - Green (≥100%): Target tercapai
  - Yellow (80-99%): Mendekati target
  - Red (<80%): Di bawah target
- Icon: Target dengan progress ring

**4. Growth Rate Card**
- Year-over-Year growth rate
- Membandingkan total revenue tahun ini vs tahun lalu
- Formula: ((Current Year - Previous Year) / Previous Year) × 100%
- Trend arrow (up/down) dengan warna sesuai kondisi
- Historical comparison untuk strategic insight

##### B. Filter & Kontrol

**1. Year Selector**
- Dropdown untuk memilih tahun analisis
- Options diambil dari data aktual di database (SELECT DISTINCT tahun)
- Default: Tahun berjalan (2026)
- Mempengaruhi semua chart dan table di halaman

**2. Region Filter**
- Multi-option selector: ALL, HQ TREG2, TREG1, TREG2, TREG3, TREG4, TREG5
- Filter cascading ke semua visualisasi
- Data diambil dari table `regions` join dengan `witels` dan `companies`
- Memungkinkan analisis regional yang spesifik

**3. Comparison Toggle**
- Switch untuk mengaktifkan mode perbandingan tahun
- Menampilkan data current year vs comparison year secara side-by-side
- Berguna untuk analisis trend dan growth pattern

**4. Tab Selector**
- **Tab 1: Chart View**
  - Monthly Revenue Chart (bar chart)
  - Subsegment Distribution (pie chart)
  - Top 5 Companies (card list)
  
- **Tab 2: Regional Performance** (Default View)
  - Regional breakdown table
  - Subsegment per region
  - Interactive drill-down capability

##### C. Visualisasi Data Utama

**1. Monthly Revenue Chart (Bar Chart)**
- **Lokasi:** Tab Chart View
- **Library:** Recharts (ResponsiveContainer + BarChart)
- **Fitur:**
  - Dual bars per month: Target (blue) vs Realisasi (red)
  - X-axis: 12 bulan (Jan-Dec)
  - Y-axis: Revenue dalam Miliar (formatted)
  - Interactive tooltip menampilkan:
    - Nama bulan
    - Revenue realisasi (formatted)
    - Revenue target (formatted)
    - Achievement percentage
    - Selisih (gap) dari target
  
- **Mode Normal:**
  - Menampilkan target dan realisasi untuk tahun dipilih
  - Grid lines untuk kemudahan pembacaan
  - Color scheme konsisten (blue untuk target, red untuk actual)
  
- **Mode Comparison:**
  - Menampilkan 2 sets data: Current Year vs Comparison Year
  - 4 bars per month (current target, current actual, comparison target, comparison actual)
  - Different opacity untuk distinction
  - Tooltip expanded dengan comparison data

- **Data Source:**
  - Query: JOIN revenues → group4 → group3 → group2 → group1 → companies
  - Aggregation: SUM(revenue_realisasi), SUM(revenue_target) GROUP BY bulan
  - Region filtering applied

**2. Subsegment Distribution (Pie Chart)**
- **Lokasi:** Tab Chart View
- **Tujuan:** Menampilkan komposisi revenue berdasarkan subsegment pelanggan
- **Subsegments:**
  - Gold: Pelanggan tier tertinggi
  - Silver: Pelanggan tier menengah
  - Copper: Pelanggan tier dasar
  
- **Fitur:**
  - Interactive segments dengan hover effect
  - Percentage labels di dalam segment
  - Custom colors untuk setiap subsegment
  - Click handler untuk drill-down ke detail subsegment
  - Legend dengan color indicator
  
- **Perhitungan:**
  - Total revenue per subsegment
  - Percentage contribution to total
  - Filtered by year dan region

**3. Top 5 Companies (Card-based Ranking)**
- **Desain:** Card layout dengan ranking visual
- **Informasi per Card:**
  - Rank number dengan badge
  - Company name (nama_perusahaan)
  - NIP NAS (company identifier)
  - Total revenue (formatted currency)
  - Achievement percentage
  - Subsegment badge (Gold/Silver/Copper)
  - Growth indicator (YoY)
  
- **Sorting:** ORDER BY total_revenue DESC LIMIT 5
- **Responsive:** Stack vertically on mobile
- **Interaction:** Click untuk membuka Company Detail Modal

**4. Regional Performance Table** (DEFAULT VIEW)
- **Struktur:** Table dengan nested rows
- **Hierarki Display:**
  - Level 1: Subsegment (Gold, Silver, Copper)
  - Level 2: Region breakdown per subsegment
  
- **Kolom Table:**
  - Subsegment/Region: Nama dengan indentasi untuk hierarchy
  - Revenue: Total revenue realisasi (formatted)
  - Target: Total revenue target (formatted)
  - Achievement: Percentage dengan color badge
  - YoY Growth: Growth rate dengan arrow indicator
  - Companies: Jumlah companies dalam kategori tersebut
  
- **Fitur Interaktif:**
  - Expandable/collapsible rows untuk subsegment
  - Click pada region row → membuka Region Detail Modal
  - Sort by column (revenue, achievement, growth)
  - Color-coded cells:
    - Green: Achievement ≥ 100%
    - Yellow: Achievement 80-99%
    - Red: Achievement < 80%
  
- **Data Aggregation:**
  - Complex JOIN: companies → group1 → revenues + regions + witels
  - GROUP BY subsegment, region
  - SUM untuk revenue dan target
  - COUNT DISTINCT untuk company count

#### 1.2 Drill-Down Flow (3 Level) - Analisis Mendalam

Salah satu kekuatan utama dashboard adalah kemampuan drill-down 3 level yang memungkinkan user mengeksplorasi data dari level makro (regional) hingga level mikro (detail perusahaan individual).

##### **Level 1: Regional Performance Table → Entry Point**

Regional Performance Table adalah default view yang langsung muncul saat dashboard dibuka. Ini memberikan overview cepat tentang distribusi revenue across regions dan subsegments.

**User Journey:**
1. User membuka dashboard
2. Melihat tabel regional performance dengan breakdown per subsegment
3. Mengidentifikasi region dengan performa menarik (high/low achievement)
4. **Click pada row region** → Trigger membuka Level 2

**Data yang Ditampilkan:**
- Aggregated revenue per region dan subsegment
- Comparative metrics (target, achievement, growth)
- Visual indicators untuk quick insight

##### **Level 2: Region Detail Modal → Company List**

**File:** `resources/js/components/modals/RegionDetailModal.tsx`

Modal ini muncul setelah user click region di table Level 1, menampilkan detail semua companies dalam region tersebut untuk subsegment yang dipilih.

**Komponen Modal:**

**A. Modal Header**
- Title: "{Region Name} - {Subsegment}"
- Subtitle: "Tahun {year}"
- Close button (X) di kanan at→ Analisis Komparatif

**File:** `resources/js/components/modals/YtdComparisonModal.tsx`

Modal ini menyediakan analisis perbandingan Year-to-Date (YTD) yang komprehensif antara periode berjalan dengan periode yang sama tahun sebelumnya. Berguna untuk strategic planning dan performance evaluation.

**Trigger:** Click pada YTD Revenue card di dashboard utama

**Struktur Modal:**

**A. Header Section**
- Title: "Year-to-Date Comparison"
- Subtitle: "{Current Year} vs {Previous Year}"
- Period indicator: "January - {Current Month}"

**B. Summary Comparison Cards (Side-by-side)**

**Left Side: Current Year YTD**
- Total revenue sampai bulan berjalan
- Formattrget** - Total revenue target (formatted)
  6. **Achievement** - Percentage dengan color badge
  7. **YoY Growth** - Growth percentage dengan arrow
  
- **Fitur Table:**
  - Sortable columns (click header untuk sort)
  - Hover effect pada rows
  - Responsive design (horizontal scroll di mobile)
  - Max height dengan scroll untuk many companies
  - **Click pada row company** → Trigger membuka Level 3

**D. Backend API**
- **Endpoint:** `/api/dashboard/region-detail`
- **Parameters:**
  - subsegment (required)
  - region_code (required)
  - year (required)
  
- **Query Logic:**
  ```php
  // Simplified query structure
  DB::table('companies')
    ->join('account_manager_company', ...)
    ->join('account_managers', ...)
    ->join('witels', ...)
    ->join('regions', ...)
    ->join('group1', ...)
    ->join('revenues', ...)
    ->where('companies.subsegment', $subsegment)
    ->where('regions.code', $region_code)
    ->where('revenues.tahun', $year)
    ->groupBy('companies.nip_nas')
  ```

##### **Level 3: Company Detail Modal → Detail Analisis**

**File:** `resources/js/components/modals/CompanyDetailModal.tsx`

Modal terakhir ini menampilkan analisis paling detail untuk satu company spesifik, termasuk breakdown hierarki revenue dan historical trend.

**Komponen Modal:**

**A. Modal Header dengan Filter**
- Title: Nama company (dynamic)
- Subtitle: NIP NAS dan Subsegment badge
- **Filter Controls:**
  - **Month Selector:** Dropdown 1-12 (Jan-Dec)
    - Default: Current month
    - Mempengaruhi data revenue yang ditampilkan
  - **Year Selector:** Dropdown years (last 5 years)
    - Default: Current year
    - Independent dari year selector di dashboard utama
  
**B. Summary Section (4 Metric Cards)**
1. **Revenue Realisasi**
   - Total revenue untuk bulan & tahun terpilih
   - Formatted currency dengan icon DollarSign
   
2. **Revenue Target**
   - Target revenue untuk periode yang sama
   - Icon Target dengan blue background
   
3. **Achievement Rate**
   - (Realisasi / Target) × 100%
   - Color-coded badge dan icon TrendingUp
   
4. **YoY Growth**
   - Comparison dengan tahun sebelumnya, bulan yang sama
   - Arrow indicator dan percentage
   - Icon ArrowUpDown

**C. Revenue Breakdown Tree (Hierarchical View)**

Ini adalah fitur unique yang menampilkan breakdown revenue dalam struktur tree 4-level sesuai hierarki database.

**Struktur Hierarki:**
```
Company (Root)
└── Group1 (LOB - Line of Business)
    ├── LOB Name (e.g., "Broadband", "Cloud")
    ├── Segment (e.g., "Enterprise", "Government")
    └── Revenue Sum untuk Group1
    
    └── Group2 (Layanan)
        ├── Layanan Name (e.g., "Astinet", "VPN")
        └── Revenue Sum untuk Group2
        
        └── Group3 (Produk)
            ├── Produk Name (e.g., "Astinet 10Mbps")
            └── Revenue Sum untuk Group3
            
            └── Group4 (Unit/SID)
                ├── SID (Service ID)
                ├── Unit (measurement)
                └── Revenue Actual (leaf node)
```

**Fitur Tree:**
- **Expand/Collapse:** Click pada parent node untuk toggle children
- **Revenue Display:** Formatted currency di setiap level
- **Percentage Contribution:** 
  - % dari parent untuk setiap child
  - Membantu identifikasi kontributor terbesar
- **Visual Hierarchy:**
  - Indentasi untuk menunjukkan level
  - Icons: ChevronRight (collapsed), ChevronDown (expanded)
  - Different font weight per level
- **Color Coding:**
  - Parent nodes: Bold text
  - Leaf nodes: Regular text dengan revenue highlighted
  - Zero revenue: Gray text

**Example Tree Display:**
```
▼ Broadband - Enterprise (Rp 5.2M) [100%]
  ▼ Astinet (Rp 3.5M) [67%]
    ▼ Astinet 10Mbps (Rp 2.0M) [57%]
      ▶ SID-12345 - 1 Unit (Rp 1.0M) [50%]
      ▶ SID-12346 - 1 Unit (Rp 1.0M) [50%]
    ▶ Astinet 20Mbps (Rp 1.5M) [43%]
  ▶ VPN (Rp 1.7M) [33%]
```

**D. Historical Revenue Chart (Yearly Trend)**

Bar chart yang menampilkan total revenue company per tahun untuk melihat trend jangka panjang.

**Fitur Chart:**
- **X-axis:** Years (last 5 years atau years dengan data)
- **Y-axis:** Total Revenue (formatted dalam M/T)
- **Bars:** Blue color dengan hover effect
- **Tooltip:** 
  - Year
  - Total revenue (formatted)
  - Jumlah bulan data (e.g., "12 months" atau "5 months")
  - Growth vs previous year
  
**Data Source:**
- Query: GROUP BY tahun untuk company ini
- Filter: Tidak ada filter bulan (sum all 12 months)
- Sort: ORDER BY tahun ASC

**E. Backend API**
- **Endpoint:** `/api/dashboard/company-detail`
- **Parameters:**
  - company_id (nip_nas, required)
  - year (optional, default current year)
  - month (optional, default current month)
  
- **Response Structure:**
  ```json
  {
    "company": {
      "nip_nas": "123456",
      "nama_perusahaan": "PT ABC",
      "subsegment": "Gold"
    },
    "summary": {
      "revenue": 1500000000,
      "target": 1200000000,
      "achievement": 125.0,
      "yoy_growth": 15.5
    },
    "breakdown": [
      {
        "level": "group1",
        "id": 1,
        "name": "Broadband",
        "revenue": 1500000000,
        "children": [...]
      }
---

### 3
**B. Visualisasi:**

1. **Target Revenue AM Chart (Bar Chart - 70% width):**
   - Horizontal bar chart untuk setiap AM
   - Menampilkan target revenue per AM
   - Filtered by year & quartal yang dipilih
   - Klik pada bar → membuka AM Revenue Detail Modal
   - Color: Red theme (#ef4444)

2. **Region Distribution Chart (Pie Chart - 30% width):**
   - Distribusi jumlah AM per region
   - Label inside: Percentage (e.g., "45.5%")
   - Label outside: Region code (e.g., "TREG-1")
   - Tooltip: Jumlah AM per region
   - Klik pada segment → membuka Region NKI Modal

**C. List Account Manager Table:**
- Tabel dengan 6 kolom:
  - Nama AM
  - Region
  - Witel
  - Target Revenue (formatted)
  - Status (badge)
  - Actions (detail button)
- Filter by year dan quartal
- Responsive design dengan horizontal scroll

#### 2.2 AM Revenue Detail Modal
**File:** `resources/js/components/modals/AMRevenueDetailModal.tsx`

**Dipicu dari:** Klik bar chart di Performance AM page

**Fitur:**
- **Header:** Nama Account Manager dengan icon
- **Summary Cards (3 Cards):**
  - Target Revenue (blue gradient)
  - Total Companies assigned (green gradient)
  - Period (quartal + tahun) (purple gradient)
- **Witel Distribution Pie Chart:**
  - Distribusi companies berdasarkan witel
  - Multi-color palette (8 warna)
  - Percentage labels
- **Companies Detail Table:**
  - Daftar semua companies yang di-assign ke AM
  - Kolom: Company Name, NIP NAS, Witel, Target Revenue, Actions
  - Sticky header dengan scrollable body
  - Dark mode support

#### 2.3 Region NKI Modal dengan Compare Feature
**File:** `resources/js/components/modals/RegionNkiModal.tsx`

**Dipicu dari:** Klik segment pie chart Region Distribution

**Fitur:**
- **Automatic Period Comparison:**
  - Menampilkan data periode saat ini
  - Menampilkan data periode sebelumnya (previous quarter)
  - Visual comparison dengan trend arrows
  
- **Summary Section:**
  - Target revenue dan realisasi revenue
  - Total AM dalam region
  - Perbandingan antar periode

- **Segment Statistics Table:**
  - Kolom: Triwulan, Result (Ach/Not Ach), Proses (Ach/Not Ach), NKI (>100%/<100%)
  - Row 1: Current period dengan trend indicators (↑ green / ↓ red)
  - Row 2: Previous period (gray text, no arrows)
  - NKI Statistics: Highest, Lowest, Average
  - Parameters to improve

- **Parameter Tabs:**
  - **Tab 1: Aspek Result (10 Parameters):**
    - Revenue, Scaling, Datin, HSI, Wireline, Wifi, CYC, CR, Profit, NPS
    - Comparison Ach/Not Ach per parameter
    - Trend arrows untuk setiap parameter
  
  - **Tab 2: Aspek Process (4 Parameters):**
    - MAPS, LOP, Capability, CC
    - Comparison Ach/Not Ach per parameter
    -3Trend arrows untuk setiap parameter

- **Trend Indicators:**
  - ↑ Green arrow: Current value > Previous value (improvement)
  - ↓ Red arrow: Current value < Previous value (decline)
  - No arrow: Same value or no previous data

#### 2.4 AM Performance Detail Modal (Advanced)
**File:** `resources/js/components/modals/AmPerformanceDetailModal.tsx`

**Fitur:**
- Detail lengkap performance individual AM
- Breakdown per parameter (Revenue, Scaling, Datin, HSI, Wireline, Wifi, CYC, CR, Profit, NPS, MAPS, LOP, Capability, CC)
- Target vs Realisasi per parameter
- Achievement percentage calculation
- Visual indicators (progress bars, badges)

---

### 3. DATA IMPORT & MANAGEMENT

#### 3.1 Data Import Revenue
**Lokasi File:** `resources/js/pages/DataImportRevenue.tsx`

**Fitur Upload:**

**A. Upload General (Multi-Year):**
- Upload file Excel untuk multiple tahun sekaligus
- Format file: Excel (.xlsx, .xls) atau CSV (.csv)
- Max file size: 10 MB
- Validasi format dan ukuran file
- Toast notifications untuk success/error
- Menyimpan metadata upload ke `revenue_uploads` table

**B. Upload Per Bulan:**
- Upload file untuk bulan spesifik
- Replace existing data dengan konfirmasi
- Confirm dialog dengan UI yang user-friendly
- Preview data yang akan di-replace

**C. Validasi:**
- Format file validation
- File size checking (max 10MB)
- Company validation (check if company exists)
- Revenue data validation (numeric values)
- Duplicate detection

**Fitur Display:**
- **Monthly Revenue Cards (12 Cards):**
  - Satu card per bulan (Jan - Dec)
  - Menampilkan total revenue bulan tersebut
  - Status badge: "Uploaded" atau "Not Uploaded"
  - Upload info: filename, upload date, row count
  - Animated skeleton loading
  - Download button per bulan

**Fitur Download:**

**1. Download Per Bulan:**
- Generate Excel dari database untuk 1 bulan spesifik
- Format grouped (tidak duplikasi company info)
- Struktur: NIP_NAS, STANDARD_NAME, SOURCE_DATA, GROUP1-4, Revenue
- Grouping logic: Hide duplicate values untuk readability
- File naming: `Revenue_{year}_{MonthName}_{timestamp}.xlsx`

**2. Download Per Tahun:**
- Generate Excel dengan semua 12 bulan
- Format pivot: 12 kolom untuk bulan 1-12
- Satu row per kombinasi NIP_NAS + GROUP1-4
- Grouping logic sama seperti download per bulan
- File naming: `Revenue_{year}_Full_Export_{timestamp}.xlsx`

**Fitur Delete:**
- Delete data per bulan dengan confirm dialog
- Delete data seluruh tahun dengan typing confirmation
- User harus mengetik "HAPUS" untuk konfirmasi delete year
- Cascade delete untuk data terkait
- Toast notification untuk feedback

#### 3.2 Data Import Performance
**Lokasi File:** `resources/js/pages/DataImportPerformance.tsx`

**Import Structure:** 3 Sheets Sequential

**Sheet 1: Region and Witel**
- Import data master region dan witel
- Struktur: Region code, Region name, Witel list

**Sheet 2: TWS {year}**
- Import data target dan realisasi per company
- Mapping ke tables: `target_account_m`, `lini_waktu_target`
- 46+ kolom untuk berbagai metrics (Revenue, Scaling, Datin, HSI, dll)

**Sheet 3: NKI {year}**
- Import Achievement dan NKI Adjustment per AM
- Row 1-2: Persentase threshold untuk Result dan Proses
- Row 3: Header kolom
- Row 4+: Data per Account Manager
- Update: Segment, Target, Realisasi, Achievement, NKI Adjustment

**Validasi:**
- NIK AM harus exists di `account_managers`
- Nama AM harus sesuai dengan NIK
- Witel harus sesuai dengan data AM
- `lini_waktu` harus sudah ada untuk NIK, quartal, tahun
- `lini_waktu_target` (pivot) harus sudah ada

**Update Logic:**
- Update segment ke semua `account_manager_company` untuk NIK tersebut
- Update target ke `target_account_m`
- Update realisasi & achievement ke `lini_waktu_target` (hanya yang terbaru)
- Update persentase threshold ke `lini_waktu`

**Fitur Display:**
- Upload form dengan quarter selector (Q1, Q2, Q3, Q4)
- Upload history table:
  - Tanggal upload
  - Quarter & Year
  - Filename
  - Row count
  - File size
  - Uploader name
  - Download button

**Fitur Download:**
- Download original uploaded file
- File stored di `storage/app/performance_uploads/`

---

### 4. CUSTOM UI COMPONENTS

#### 4.1 Custom Notifications System
**File:** `resources/js/components/ui/notifications.tsx`

**A. Toast Component:**
- Non-blocking notifications
- 4 types: Success, Error, Warning, Info
- Features:
  - Auto-dismiss after 5 seconds
  - Manual close button (X icon)
  - Distinct colors and icons per type
  - Fixed positioning (top-right)
  - Dark mode support
  - Slide-in animation

**B. ConfirmDialog Component:**
- Modal dialog untuk user confirmations
- 3 types: Danger (red), Warning (orange), Info (blue)
- Features:
  - Backdrop blur effect
  - Optional typing confirmation (user must type "HAPUS")
  - Cancel and Confirm buttons
  - Fade-in and zoom-in animations
  - Dark mode support
  - Useful untuk destructive actions

**Penggunaan:**
- Replace semua `alert()`, `window.confirm()`, `window.prompt()`
- Consistent UX across application
- Better accessibility dan user experience

#### 4.2 Reusable Chart Components

**A. RevenueBarChart**
- File: `resources/js/components/charts/RevenueBarChart.tsx`
- Bar chart untuk monthly revenue
- Dual mode: Normal vs Comparison
- Interactive tooltips
- Responsive design

**B. SubsegmentPieChart**
- Pie chart untuk subsegment distribution
- Custom labels (inside & outside)
- Interactive dengan onClick handlers
- Multi-color palette

**C. RegionDistributionChart**
- Pie chart untuk AM distribution per region
- Percentage labels inside
- Region code labels outside
- Tooltip dengan AM count

---

### 5. AUTHENTICATION & AUTHORIZATION

#### 5.1 Authentication Flow
**Provider:** Laravel Fortify

**Features:**
- Login page dengan username/email dan password
- Session-based authentication
- Remember me functionality
- Password reset capability
- Email verification support

**Protected Routes:**
- Semua routes memerlukan authentication (`auth` middleware)
- Redirect ke `/login` jika belum authenticated
- Redirect ke dashboard setelah login

#### 5.2 Role-Based Authorization
**Roles:**
1. **Admin:**
   - Full access ke semua fitur
   - Dapat upload/delete data
   - Dapat access settings
   - Dapat manage users

2. **Viewer:**
   - Read-only access
   - Dapat view dashboards dan reports
   - Tidak dapat upload/delete data
   - Tidak dapat access settings

**Middleware Implementation:**
- `role:admin` middleware untuk admin-only routes
- Guard pada UI buttons/actions based on user role
- Error 403 Forbidden untuk unauthorized access

---

### 6. DATABASE ARCHITECTURE

#### 6.1 Main Tables

**A. Revenue Data:**
- `companies` - Master data perusahaan (PK: nip_nas)
- `group1` - LOB & Segment level
- `group2` - Layanan level
- `group3` - Produk level
- `group4` - SID & Unit level
- `revenues` - Revenue realisasi dan target per bulan
- `revenue_uploads` - Metadata upload revenue

**B. Account Manager & Performance:**
- `account_managers` - Master data AM (PK: nik)
- `account_manager_company` - Pivot: AM ↔ Company assignment
---

### 4ationship: regions → witels → account_managers

**D. Users & Auth:**
- `users` - User accounts dengan role (admin/viewer)
- Laravel standard auth tables

#### 6.2 Key Relationships

**Rev4nue Hierarchy:**
```
companies (1) → (*) group1 → (*) group2 → (*) group3 → (*) group4 → (*) revenues
```

**AM Structure:**
```
regions (1) → (*) witels (1) → (*) account_managers
account_managers (*) ↔ (*) companies (via account_manager_company)
```

**Performance Tracking:**
```
account_managers (1) → (*) lini_waktu (1) → (*) lini_waktu_target (*) ← (1) target_account_m
```
4
#### 6.3 Critical Field Changes (Database Restructure)

**Updated Field Names:**
| OLD | NEW | Context |
|-----|-----|---------|
| `companies.id` | `companies.nip_nas` | Primary Key |
| `witels.id` | `witels.idwitels` | Primary Key |
| `revenues.company_id` | `revenues.nip_nas` | Foreign Key |
| `revenues.revenue` | `revenues.total_revenue` | Field Name |
| `witels.name` | `witels.nama_witels` | Field Name |
| `company_regions` table | `account_manager_company` | Pivot Table |

---

## 🔧 BACKEND IMPLEMENTATION

### Controller Summary

**1. DashboardController.php**
- Main dashboard revenue analytics
- Me5hods: 30+ untuk data fetching dan calculation
- Key methods:
  - `5ndex()` - Main dashboard view
  - `getDashboardSummary()` - Summary statistics
  - `getMonthlyRevenue()` - Monthly breakdown
  - `getYtdComparison()` - YoY comparison
  - `getSubsegmentRevenue()` - Subsegment data
  - `getRegionDetail()` - Region drill-down
  - `getIndividualCompanyDetails()` - Company detail
  - `getAMRevenueRanking()` - AM performance ranking
  - `getAMRevenueDetails()` - AM revenue breakdown

**2. RevenueImportController.php**
- Han
**3. DataImportPerformanceController.php**
- Handle performance file uploads (3 sheets)
- Methods:
  - `index()` - Display upload page
  - `import()` - Process 3-sheet import
  - `download()` - Download uploaded file

**4. RegionNkiController.php**
- Region NKI data dengan compare feature
- Methods:
  - `show()` - Get NKI data deng

**C `getPeriodData()` - Fetch period-specific data

**5. AmPerformanceDetailController.php**
- Detail performance individual AM
- Com5.2 Key Relationships

**Revenue Hierarchy:**
```
companies (1) → (*) group1 → (*) group2 → (*) group3 → (*) group4 → (*) revenues
```

**Regional Structure:**
```
regions (1) → (*) witels
companies (*) ↔ (*) regions (via account_manager_company relationship)
```

#### 5
### Complex Joins
- 5-level JOIN untuk revenue data (revenues → group4 → group3 → group2 → group1 → companies)
- LEFT JOIN untuk performance data (account_managers → lini_waktu → lini_waktu_target → target_account_m)
- Region aggregations dengan witels dan account_managers

### Grouping & Aggregations
- `GROUP BY` untuk monthly, regional, subsegment breakdowns
- `SUM()`, `AVG()`, `COUNT()` untuk statistics
- Subqueries untuk complex calculations (YoY, achievement rates)

### Filtering
- Dynamic WHERE clauses berdasarkan user selections
- Year, month, quarter, region filters
- Conditional joins untuk performance data

---

## 🎨 FRONTEND ARCHITECTURE

### Component Structure

**Pages (Main Routes):**
- `Dashboard.tsx` - Revenue da - Main Revenue Analytics Controller
- **Purpose:** Core controller untuk semua fitur dashboard revenue
- **Total Methods:** 30+ methods untuk data fetching dan calculations
- **Key Methods:**
  - `index()` - Render halaman dashboard utama dengan initial data
  - `getDashboardSummary()` - Calculate 4 summary cards (total, YTD, achievement, growth)
  - `getMonthlyRevenue()` - Monthly breakdown dengan target vs realisasi
  - `getYtdComparison()` - YoY comparison untuk YTD analysis
  - `getSubsegmentRevenue()` - Subsegment distribution (Gold/Silver/Copper)
  - `getSubsegmentWithRegionalBreakdown()` - Regional performance table data
  - `getTopCompanies()` - Top 5 companies ranking
  - `getRegionDetail()` - Level 2 drill-down (companies dalam region)
  - `getIndividualCompanyDetails()` - Level 3 drill-down (company detail)
  - `getRevenueBreakdown()` - Hierarchical tree Group1-4

**2. RevenueImportController.php** - Data Import Management
- **Purpose:** Handle upload, download, dan delete operations untuk revenue data
- **Key Methods:**
  - `index()` - Display upload page dengan monthly cards
  - `upload()` - Process file upload (Excel/CSV) dengan validasi
  - `downloadFile($year, $month)` - Generate Excel dari database untuk 1 bulan
  - `downloadYear($year)` - Generate Excel pivot untuk 12 bulan
  - `deleteMonth($year, $month)` - Delete data bulan dengan confirmation
  - `deleteYear($year)` - Delete data tahun dengan typing confirmation
- **Excel Generation:**
  - PhpSpreadsheet untuk generate files
  - Grouping logic untuk hide duplicate values
  - Format currency otomatis
  - Temporary storage dengan auto-cleanup

**3. RevenueBreakdownController.php** - Hierarchical Breakdown
- **Purpose:** Generate tree structure Group1 → Group2 → Group3 → Group4
- **Features:**
  - Recursive query untuk build hierarchy
  - Revenue aggregation per level
  - Percentage calculation per child to parent
  - Support untuk month/year filtering

**4. RegionRevenueController.php** - Regional Analytics
- **Purpose:** Regional revenue aggregations dan filtering
- **Features:**
  - Group by region dan subsegment
  - Calculate achievement per region
  - YoY comparison per region
  - Support untuk cross-region analysisformance comparison
- Visual trend indicators (arrows, colors)

### 4. Search & Filter
- Real-time search di YTD modal
- Auto-expand matched results
- Text highlighting

### 5. Data Visualization
- Multiple chart types (Bar, Pie, Line)
- Interactive tooltips
- Color-coded indicators
- Responsive charts

### 6. Excel Integration
- Upload Excel files untuk bulk import
- **5-level JOIN untuk revenue data:**
  ```sql
  revenues 
    → group4 (ON revenues.group4_id = group4.idGroup4)
    → group3 (ON group4.group3_id = group3.idGroup3)
    → group2 (ON group3.group2_id = group2.idGroup2)
    → group1 (ON group2.group1_id = group1.idGroup1)
    → companies (ON group1.nip_nas = companies.nip_nas)
  ```
- **Region aggregations:**
  ```sql
  companies
    → account_manager_company (ON companies.nip_nas = amc.nip_nas)
    → account_managers (ON amc.nik_am = am.nik)
    → witels (ON am.idwitels = witels.idwitels)
    → regions (ON witels.region_id = regions.id)
  ```

### 7. User Experience
- Custom notifications (Toast, Confirm)
- Loading states (Skeleton, Spinners)
- Error handling dengan user-friendly (main page)
- `DataImportRevenue.tsx` - Revenue data import & management

**Modal Components (Revenue-focused):**
- `RegionDetailModal.tsx` - Level 2: Region company list
- `CompanyDetailModal.tsx` - Level 3: Company detail dengan filters & tree
- `YtdComparisonModal.tsx` - YTD comparison dengan advanced search

**Chart Components:**
- `RevenueBarChart.tsx` - Monthly revenue bars (dual mode)
- `SubsegmentPieChart.tsx` - Subsegment distribution pie

**UI Components:**
- `notifications.tsx` - Toast dan ConfirmDialog system
- `StatCard.tsx` - Summary statistic cards (4 cards di dashboard)
- `SubsegmentRegionalTable.tsx` - Regional performance table
- `Button.tsx`, `Select.tsx`, `Card.tsx` - shadcn/ui base
- Pagination untuk tables
- Memoization untuk chart data (`useMemo`)
- Efficient SQL queries dengan proper JOINs

---

## 🔐 SECURITY FEATURES

### Authentication
- Laravel Fortify dengan session-based auth
- Password hashing (bcrypt)
- Email verification
- Remember me token

### Authorization
- Role-based middleware (`role:admin`, `role:viewer`)
- Gate checks untuk specific actions
- UI guards berdasarkan user role

### Data Protection
- CSRF token validation
- SQL injection prevention (Eloquent ORM)
- XSS prevention (React default escaping)
- File upload validation (type, size)
- Input sanitization

### Session Management
- Secure session cookies
- Session timeout
- Logout functionality

---

## 📱 RESPONSIVE DESIGN

### Breakpoints
- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: > 1024px

### Adaptive Components
- Collapsible sidebar navigation
- Stack cards on mobile
- Horizontal scroll untuk tables
- Touch-friendly buttons dan interactions
- Responsive charts (ResponsiveContainer)

---

## 🌗 DARK MODE SUPPORT

### Implementation
- Tailwind CSS `dark:` variants
- System preference detection
- Manual toggle (future enhancement)
- Consistent theming across all components

### Color Palette
- Light mode: White backgrounds, gray text
- Dark mode: Gray-900 backgrounds, white text
- Accent colors: Consistent dalam both modes

---

## 📊 REPORTING CAPABILITIES

### Available Reports
1. **Monthly Revenue Report** - Per bulan, per subsegment, per region
2. **YTD Comparison Report** - Current year vs previous year
3. **AM Performance Report** - Target vs realisasi per AM, per parameter
4. **Regional NKI Report** - Performance metrics per region, dengan comparison
5. **Company Performance Report** - Individual company dengan historical trend

### Export Formats
- Excel (.xlsx) - Generated from database
- Visual charts (dapat di-screenshot)
- Tabular data (dapat di-copy)

---

## 🛠️ TROUBLESHOOTING & ERROR HANDLING

### Frontend Error Handling
- Try-catch blocks untuk API calls
- User-friendly error messages via Toast
- Loading states untuk async operations
- Fallback UI untuk failed data loads

### Backend Error Handling
- Validation errors dengan detailed messages
- Database query error catching
- File upload error handling
- 404 responses untuk missing data
- 403 responses untuk unauthorized access
- 500 responses untuk server errors

### Common Issues & Solutions
- **Upload fails:** Check file format, size, and data structure
- **Charts not loading:** Verify year/quartal filter selections
- **Missing data:** Check if data exists untuk selected period
- **Permission denied:** Verify user role (Admin vs Viewer)

---

## 🎓 LESSON LEARNED & BEST PRACTICES

### Development Practices
1. **Component Reusability:** Create reusable UI components untuk consistency
2. **Type Safety:** TypeScript interfaces untuk prevent runtime errors
3. **Documentation:** Comprehensive inline comments dan separate docs
4. **Version Control:** Git commit messages yang descriptive
5. **Code Review:** Peer review sebelum merge
6. **Testing:** Manual testing untuk setiap feature sebelum deployment

### Database Design
1. **Normalization:** Proper table relationships untuk data integrity
2. **Indexing:** Index foreign keys dan frequently queried columns
3. **Naming Convention:** Consistent naming (snake_case untuk database)
4. **Migrations:** Sequential migrations dengan proper rollback support

### UI/UX Design
1. **Consistency:** Uniform styling across all pages
2. **Feedback:** Immediate visual feedback untuk user actions
3. **Accessibility:** Keyboard navigation, proper labels, ARIA attributes
4. **Performance:** Lazy loading, pagination, efficient rendering

### Security Practices
1. **Authentication First:** Check auth pada setiap protected route
2. **Input Validation:** Both frontend dan backend validation
3. **Error Messages:** Generic messages untuk security-sensitive operations
4. **File Uploads:** Strict validation untuk file type dan size

---

## 📚 DOCUMENTATION FILES

Aplikasi ini memiliki dokumentasi komprehensif dalam format Markdown:

1. **README.md** - Overview sistem dan architecture
2. **QUICK_REFERENCE.md** - Query patterns dan common tasks
3. **PERFORMANCE_AM_QUICK_REFERENCE.md** - Performance AM fitur guide
4. **AM_REVENUE_MODAL_IMPLEMENTATION.md** - AM Revenue detail modal docs
5. **REGION_DETAIL_MODAL_IMPLEMENTATION.md** - Region drill-down docs
6. **DRILL_DOWN_FLOW_IMPLEMENTATION.md** - Complete drill-down flow
7. **COMPARE_FEATURE_DOCUMENTATION.md** - Compare feature implementation
8. **DOWNLOAD_FEATURES_DOCUMENTATION.md** - Download fitur guide
9. **CUSTOM_NOTIFICATIONS_IMPLEMENTATION.md** - Custom notification system
10. **SEARCH_FEATURE_IMPLEMENTATION.md** - Search & filter implementation
11. **NKI_SHEET_IMPORT_GUIDE.md** - Performance import structure guide
12. **CALCULATION_FIX_DOCUMENTATION.md** - Bug fixes dan calculation  dengan target vs realisasi
2. **YTD Comparison Report** - Current year vs previous year dengan hierarchical breakdown
3. **Regional Performance Report** - Breakdown per region dan subsegment dengan achievement metrics
4. **Company Performance Report** - Individual company dengan historical trend dan Group1-4 breakdown
5. **Top Companies Report** - Ranking top 5 companies by revenue

## 🎯 KESIMPULAN

Sistem Dashboard Analytics TWS adalah aplikasi enterprise-grade yang dikembangkan dengan best practices modern web development. Aplikasi ini berhasil mengintegrasikan:

✅ **Backend Laravel** yang robust dengan Eloquent ORM  
✅ **Frontend React** yang responsive dan interactive  
✅ **Database MySQL** dengan struktur normalized  
✅ **UI/UX** yang user-friendly dengan dark mode support  
✅ **Role-based authorization** untuk security  
✅ **Excel integration** untuk import/export data  
✅ **Complex data visualization** dengan multiple chart types  
✅ **Multi-level drill-down** untuk detailed analysis  
✅ **Real-time filtering** dan search capabilities  
✅ **Comprehensive error handling** dan user feedback  
✅ **Extensive documentation** untuk maintenance  

Aplikasi ini telah siap untuk production deployment dan dapat menangani volume data yang besar dengan performa yang optimal.

---

## 📞 INFORMASI TEKNIS

**Versi Aplikasi:** 1.0  
**Tanggal Pembuatan:** 2024-2025  
**Tech Stack Version:**
- Laravel: 11.x
- PHP: 8.2+
- React: 19.x
- TypeScript: 5.x
- Node.js: 20.x
- MySQL: 8.x
- Inertia.js: 2.x
- Tailwind CSS: 4.x

**Repository Structure:**
```
dashboard-TWS/
├── app/                    # Laravel backend
│   ├── Http/
│   │   ├── Controllers/   # API controllers
│   │   └── Middleware/    # Auth & role middleware
│   ├── Models/            # Eloquent models
│   ├── Imports/           # Excel import classes
│   └── Services/          # Business logic services
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Data seeders
├── resources/
│   ├── js/
│   │   ├── pages/        # React page components
│   │   ├── components/   # Reusable components
│   │   └── layouts/      # Layout components
│   └── css/              # Tailwind CSS
├── routes/
│   ├── web.php           # Web routes
│   └── api.php           # API routes (if any)
├── public/               # Public assets
└── storage/              # Uploaded files
```

---

**CATATAN:** Laporan ini dibuat berdasarkan dokumentasi lengkap sistem dan tidak mencakup fitur Daily Monitoring sesuai permintaan.

---

*Dokumen ini merupakan bagian dari Laporan Magang dan bersifat konfidensial.*
REGION_DETAIL_MODAL_IMPLEMENTATION.md** - Region drill-down docs
4. **DRILL_DOWN_FLOW_IMPLEMENTATION.md** - Complete 3-level drill-down flow
5. **DOWNLOAD_FEATURES_DOCUMENTATION.md** - Download fitur guide (per bulan & per tahun)
6. **CUSTOM_NOTIFICATIONS_IMPLEMENTATION.md** - Custom notification system
7. **SEARCH_FEATURE_IMPLEMENTATION.md** - Search & filter implementation (YTD Modal)
8. **CALCULATION_FIX_DOCUMENTATION.md** - Bug fixes dan calculation logic
9. **DATABASE_RESTRUCTURE_GUIDE.md** - Database migration guide
10. **COMPARE_FEATURE_DOCUMENTATION.md** - YTD comparison feature
11. Dan dokumentasi teknisRevenue Analytics TWS adalah aplikasi enterprise-grade yang dirancang khusus untuk monitoring dan analisis revenue dari perusahaan-perusahaan klien Telkom Wholesale Service. Aplikasi ini berhasil mengintegrasikan teknologi modern web development dengan kebutuhan bisnis yang kompleks.

### Pencapaian Utama:

✅ **Dashboard Interaktif Komprehensif**
   - 4 summary cards untuk quick insight
   - Multiple visualization types (Bar chart, Pie chart, Table)
   - Real-time filtering by year, region, dan comparison mode
   - Responsive design untuk akses mobile

✅ **3-Level Drill-Down System**
   - Level 1: Regional Performance Table (overview)
   - Level 2: Region Detail Modal (companies list)
   - Level 3: Company Detail Modal (detailed breakdown dengan tree hierarchy)
   - Seamless navigation dengan modal-based approach

✅ **Advanced Data Analysis Features**
   - YTD Comparison dengan search functionality
   - Hierarchical revenue breakdown (Group1 → Group2 → Group3 → Group4)
   - Historical trend analysis dengan yearly charts
   - YoY growth tracking dan achievement monitoring

✅ **Robust Data Management**
   - Excel import dengan comprehensive validation
   - Generate Excel dari database (per bulan & per tahun)
   - Grouping logic untuk readability
   - Delete operations dengan confirmation safeguards

✅ **Technical Excellence**
   - Backend Laravel 11 dengan Eloquent ORM
   - Frontend React 19 + TypeScript untuk type safety
   - Inertia.js v2 untuk seamless SPA experience
   - Complex 5-level SQL joins yang optimal
   - Database indexing untuk performance

✅ **User Experience Focus**
   - Custom Toast notifications (4 types)
   - ConfirmDialog untuk destructive actions
   - Loading states dengan skeleton screens
   - Dark mode support across all components
   - Intuitive navigation dan visual feedback

✅ **Security & Authorization**
   - Laravel Fortify authentication
   - Role-based access control (Admin/Viewer)
   - CSRF protection dan SQL injection prevention
   - File upload validation

### Impact & Value:

Dashboard ini memberikan nilai strategis bagi Telkom Wholesale Service dengan:
- **Visibility:** Real-time insight ke revenue performance
- **Analysis:** Multi-level drill-down untuk root cause analysis
- **Comparison:** YoY dan cross-region comparison untuk benchmarking
- **Efficiency:** Automated reporting dan Excel generation
- **Decision Making:** Data-driven insights untuk strategic planning

Aplikasi telah siap untuk production deployment dan dapat menangani volume data yang besar dengan performa optimal. Architecture yang modular dan documented dengan baik memudahkan untuk future maintenance dan enhancements## 📝 CATATAN PENUTUP

**Fokus Laporan:** Laporan ini secara khusus membahas fitur **Revenue Dashboard Analytics** sebagai sistem utama aplikasi. Pembahasan mencakup:
- Dashboard revenue dengan semua visualisasi dan metrics
- 3-level drill-down system untuk analisis detail
- YTD comparison dengan advanced search
- Data import & export management
- Technical implementation (backend, frontend, database)

**Cakupan:** Laporan ini dibuat berdasarkan dokumentasi lengkap sistem dan source code aktual yang ada di repository dashboard-TWS