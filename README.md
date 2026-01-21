# TWS Dashboard - Revenue & Performance Analytics System

Sistem dashboard analytics untuk monitoring revenue dan performance Account Manager di Telkom Wholesale Service.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** React 19 + TypeScript
- **Bridge:** Inertia.js v2
- **UI Framework:** Tailwind CSS v4 + shadcn/ui
- **Database:** MySQL
- **Authentication:** Laravel Fortify
- **Authorization:** Role-based (Admin/Viewer)

---

## System Architecture

```mermaid
graph TB
    subgraph "Client Layer"
        Browser[Web Browser]
    end
    
    subgraph "Frontend - React + TypeScript"
        Pages[Pages Components]
        Components[UI Components]
        Layouts[Layout Components]
        Routes[Route Helpers]
    end
    
    subgraph "Bridge Layer"
        Inertia[Inertia.js]
    end
    
    subgraph "Backend - Laravel"
        WebRoutes[Web Routes]
        Middleware[Middleware<br/>Auth, Role Check]
        Controllers[Controllers]
        Services[Service Layer]
        Models[Eloquent Models]
    end
    
    subgraph "Data Layer"
        MySQL[(MySQL Database)]
    end
    
    Browser -->|HTTP Request| Inertia
    Inertia -->|Props/Data| Pages
    Pages --> Components
    Pages --> Layouts
    Components --> Routes
    
    Inertia <-->|Server Communication| WebRoutes
    WebRoutes --> Middleware
    Middleware --> Controllers
    Controllers --> Services
    Services --> Models
    Models <-->|Query/Data| MySQL
    
    Controllers -->|Inertia Render| Inertia
```

---

## Database Architecture (ERD)

```mermaid
erDiagram
    USERS {
        bigint id PK
        string name
        string email UK
        string username UK
        string password
        string role "admin, viewer"
        timestamp email_verified_at
        timestamps created_at_updated_at
    }
    
    REGIONS {
        bigint id PK
        string code UK "HQ, TREG1-5"
        string name
        timestamps created_at_updated_at
    }
    
    WITELS {
        bigint idwitels PK
        bigint region_id FK
        string witel
        string lokasi_witel
        timestamps created_at_updated_at
    }
    
    ACCOUNT_MANAGERS {
        string nik PK
        string nama
        string posisi "AM, AM1, AM2, EAM, SAM"
        string no_gsm
        bigint idwitels FK
        timestamps created_at_updated_at
    }
    
    COMPANIES {
        string nip_nas PK
        string nama_perusahaan
        string subsegment "Gold, Silver, Copper"
        string source_data
        timestamps created_at_updated_at
    }
    
    GROUP1 {
        bigint idGroup1 PK
        string nip_nas FK
        string lob_name
        string segment
        timestamps created_at_updated_at
    }
    
    GROUP2 {
        bigint idGroup2 PK
        bigint group1_id FK
        string layanan
        timestamps created_at_updated_at
    }
    
    GROUP3 {
        bigint idGroup3 PK
        bigint group2_id FK
        string produk
        timestamps created_at_updated_at
    }
    
    GROUP4 {
        bigint idGroup4 PK
        bigint group3_id FK
        string sid
        string unit
        timestamps created_at_updated_at
    }
    
    REVENUES {
        bigint id PK
        bigint group4_id FK
        year tahun
        tinyint bulan "1-12"
        decimal revenue_realisasi
        decimal revenue_target
        timestamps created_at_updated_at
        unique group4_id_tahun_bulan
    }
    
    REVENUE_UPLOADS {
        bigint id PK
        int tahun
        int bulan
        bigint uploaded_by FK
        string original_filename
        int row_count
        decimal file_size_kb
        timestamps created_at_updated_at
        index tahun_bulan
    }
    
    LINI_WAKTU {
        bigint id PK
        string nik_am FK
        string quartal "Q1-Q4"
        date bulan_awal
        date bulan_akhir
        int tahun
        decimal percentage_result
        decimal percentage_revenue
        decimal percentage_scaling
        decimal percentage_datin
        decimal percentage_hsi
        decimal percentage_wireline
        decimal percentage_wifi
        decimal percentage_cyc
        decimal percentage_cr
        decimal percentage_profit
        decimal percentage_customer
        decimal percentage_proses
        decimal percentage_maps
        decimal percentage_lop
        decimal percentage_capability
        decimal percentage_cc
        timestamps created_at_updated_at
    }
    
    PERFORMANCE_UPLOADS {
        bigint id PK
        int tahun
        string quartal
        bigint uploaded_by FK
        string original_filename
        int row_count
        decimal file_size_kb
        timestamps created_at_updated_at
    }
    
    TARGET_ACCOUNT_M {
        bigint id PK
        decimal t_revenue "Target Revenue"
        decimal t_scalling
        decimal t_datin
        decimal t_hsi
        decimal t_wireline
        decimal t_wifi
        decimal t_cyc
        decimal t_cr
        decimal t_profit
        decimal t_nps
        decimal t_maps
        decimal t_lop
        decimal t_capability
        decimal t_cc
        timestamps created_at_updated_at
    }
    
    LINI_WAKTU_TARGET {
        bigint id PK
        bigint lini_waktu_id FK
        bigint target_id FK
        decimal r_revenue "Realisasi Revenue"
        decimal r_scalling
        decimal r_datin
        decimal r_hsi
        decimal r_wireline
        decimal r_wifi
        decimal r_cyc
        decimal r_cr
        decimal r_profit
        decimal r_nps
        decimal r_maps
        decimal r_lop
        decimal r_capability
        decimal r_cc
        timestamps created_at_updated_at
    }
    
    ACCOUNT_MANAGER_COMPANY {
        bigint id PK
        string nik_am FK
        string nip_nas FK
        date tanggal_mulai
        date tanggal_selesai
        timestamps created_at_updated_at
    }
    
    USERS ||--o{ REVENUE_UPLOADS : uploads
    USERS ||--o{ PERFORMANCE_UPLOADS : uploads
    
    REGIONS ||--o{ WITELS : contains
    WITELS ||--o{ ACCOUNT_MANAGERS : manages
    
    ACCOUNT_MANAGERS ||--o{ LINI_WAKTU : has_kpi
    ACCOUNT_MANAGERS ||--o{ ACCOUNT_MANAGER_COMPANY : manages
    
    LINI_WAKTU ||--o{ LINI_WAKTU_TARGET : has_targets
    TARGET_ACCOUNT_M ||--o{ LINI_WAKTU_TARGET : tracked_in
    
    LINI_WAKTU_TARGET }o--|| LINI_WAKTU : belongs_to
    LINI_WAKTU_TARGET }o--|| TARGET_ACCOUNT_M : achieves
    
    COMPANIES ||--o{ GROUP1 : has_lob
    COMPANIES ||--o{ ACCOUNT_MANAGER_COMPANY : managed_by
    
    GROUP1 ||--o{ GROUP2 : has_layanan
    GROUP2 ||--o{ GROUP3 : has_produk
    GROUP3 ||--o{ GROUP4 : has_unit
    
    GROUP4 ||--o{ REVENUES : generates
    
    REVENUES }o--|| GROUP4 : belongs_to
    LINI_WAKTU }o--|| ACCOUNT_MANAGERS : belongs_to
    REVENUE_UPLOADS }o--|| USERS : uploaded_by
    PERFORMANCE_UPLOADS }o--|| USERS : uploaded_by
```

---

## Authentication & Authorization Flow

```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant Laravel
    participant Fortify
    participant Middleware
    participant Controller
    
    User->>Browser: Access Application
    Browser->>Laravel: GET /
    Laravel->>Laravel: Check Auth
    
    alt Not Authenticated
        Laravel-->>Browser: Redirect to /login
        Browser->>User: Show Login Form
        User->>Browser: Submit Credentials
        Browser->>Fortify: POST /login
        Fortify->>Fortify: Validate Credentials
        Fortify->>Fortify: Create Session
        Fortify-->>Browser: Redirect to /daily-monitoring
    end
    
    alt Authenticated
        Laravel-->>Browser: Redirect to /daily-monitoring
    end
    
    Browser->>Laravel: GET /daily-monitoring
    Laravel->>Middleware: auth, verified
    Middleware->>Middleware: Check Session
    Middleware->>Controller: DashboardController@dailymonitoring
    Controller-->>Browser: Inertia Render (DailyMonitoring)
    Browser->>User: Display Page
    
    alt Admin Access Required
        User->>Browser: Access /data-import/revenue
        Browser->>Laravel: GET /data-import/revenue
        Laravel->>Middleware: auth, verified, role:admin
        Middleware->>Middleware: Check user.role === 'admin'
        
        alt Is Admin
            Middleware->>Controller: Allow Access
            Controller-->>Browser: Render Page
        else Not Admin
            Middleware-->>Browser: 403 Forbidden
        end
    end
```

---

## Revenue Dashboard - Complete Backend Flow

```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant Route
    participant Middleware
    participant Controller
    participant Service
    participant Model
    participant DB
    
    User->>Browser: Navigate to /dashboard?year=2026&region=ALL
    Browser->>Route: GET /dashboard
    Route->>Middleware: auth, verified
    Middleware->>Controller: DashboardController@index
    
    Controller->>Controller: Extract Request Parameters<br/>(year, region, comparison_year)
    
    par Parallel Data Fetching
        Controller->>Service: getDashboardSummary(year)
        Service->>DB: SELECT SUM(r.revenue_realisasi)<br/>FROM revenues r<br/>JOIN group4 g4 ON r.group4_id = g4.idGroup4<br/>JOIN group3 g3 ON g4.group3_id = g3.idGroup3<br/>JOIN group2 g2 ON g3.group2_id = g2.idGroup2<br/>JOIN group1 g1 ON g2.group1_id = g1.idGroup1<br/>WHERE r.tahun = year
        DB-->>Service: Total Revenue
        Service->>Service: Format Currency (M/T)
        Service-->>Controller: Dashboard Summary
    and
        Controller->>Service: getMonthlyRevenue(year)
        Service->>DB: SELECT r.bulan,<br/>SUM(r.revenue_realisasi) as total_revenue,<br/>SUM(ct.target_revenue) as target_revenue<br/>FROM revenues r<br/>LEFT JOIN group4 g4 ON r.group4_id = g4.idGroup4<br/>LEFT JOIN group3...group1...companies c<br/>LEFT JOIN company_targets ct<br/>WHERE r.tahun = year<br/>GROUP BY r.bulan
        DB-->>Service: Monthly Data
        Service->>Service: Calculate Achievement %<br/>Format Currency
        Service-->>Controller: Monthly Revenue Array [1-12]
    and
        Controller->>Service: getYtdComparison(year)
        Service->>DB: SELECT SUM(revenue_realisasi)<br/>FROM revenues r<br/>JOIN group4...group1 hierarchy<br/>WHERE tahun = year AND bulan <= currentMonth
        DB-->>Service: Current YTD
        Service->>DB: SELECT SUM(revenue_realisasi)<br/>WHERE tahun = year-1 AND bulan <= currentMonth
        DB-->>Service: Previous YTD
        Service->>Service: Calculate Growth %
        Service-->>Controller: YTD Comparison
    and
        Controller->>Service: getSubsegmentRevenue(year)
        Service->>DB: SELECT c.subsegment,<br/>SUM(r.revenue_realisasi) as total<br/>FROM revenues r<br/>JOIN group4...group1...companies c<br/>WHERE r.tahun = year<br/>GROUP BY c.subsegment
        DB-->>Service: Gold/Silver/Copper totals
        Service-->>Controller: Subsegment Revenue
    and
        Controller->>Service: getSubsegmentWithRegionalBreakdown(year)
        Service->>DB: Complex 5-level JOIN:<br/>revenues -> group4 -> group3 -> group2 -> group1<br/>-> companies + witels + regions<br/>GROUP BY subsegment, region
        DB-->>Service: Nested Regional Data
        Service-->>Controller: Regional Breakdown per Subsegment
    and
        Controller->>Service: getTopCompanies(year, limit=5)
        Service->>DB: SELECT c.nama_perusahaan,<br/>SUM(r.revenue_realisasi) as total<br/>FROM revenues r<br/>JOIN group4...companies c<br/>WHERE r.tahun = year<br/>GROUP BY c.nip_nas<br/>ORDER BY total DESC LIMIT 5
        DB-->>Service: Top 5 Companies
        Service-->>Controller: Top Companies Array
    end
    
    Controller->>Controller: Build Inertia Response<br/>with all collected data
    Controller-->>Browser: Inertia::render('Dashboard', [...data])
    Browser->>Browser: React Renders Components
    Browser->>User: Display Dashboard with Charts & Tables
```

---

## Performance AM - Backend Processing Flow

```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant Controller
    participant DB
    participant Service
    
    User->>Browser: Navigate /performance-am?year=2026&quartal=Q1&region=ALL
    Browser->>Controller: GET /performance-am
    
    Controller->>Controller: Extract Parameters<br/>(year, quartal, ytd, region)
    Controller->>Controller: getCurrentQuartal()<br/>if not provided
    
    alt YTD Mode (ytd=1)
        Controller->>Controller: getQuartalList(quartal)<br/>Returns [Q1] if Q1<br/>Returns [Q1, Q2] if Q2<br/>etc.
    else Single Quartal
        Controller->>Controller: quartalList = [quartal]
    end
    
    par Fetch AM Metrics
        Controller->>DB: getTotalAM(region)
        DB->>DB: SELECT COUNT(*) FROM account_managers<br/>LEFT JOIN witels, regions<br/>WHERE region matches
        DB-->>Controller: total_am
    and
        Controller->>DB: getTotalRevenueTarget(year, quartals)
        DB->>DB: SELECT SUM(tam.t_revenue)<br/>FROM target_account_m tam<br/>JOIN lini_waktu_target lwt<br/>JOIN lini_waktu lw<br/>WHERE lw.tahun = year<br/>AND lw.quartal IN (quartals)
        DB-->>Controller: revenue_target
    and
        Controller->>DB: getTotalRevenueActual(year, quartals)
        DB->>DB: SELECT SUM(lwt.r_revenue)<br/>FROM lini_waktu_target lwt<br/>JOIN lini_waktu lw<br/>WHERE lw.tahun = year<br/>AND lw.quartal IN (quartals)
        DB-->>Controller: revenue_actual
    end
    
    par Fetch Performance Data
        Controller->>DB: getAMRevenueRanking()
        DB->>DB: SELECT am.nik, am.nama,<br/>regions.code,<br/>SUM(tam.t_revenue) as t_revenue,<br/>SUM(lwt.r_revenue) as r_revenue<br/>FROM account_managers am<br/>LEFT JOIN witels, regions<br/>LEFT JOIN lini_waktu lw<br/>LEFT JOIN lini_waktu_target lwt<br/>LEFT JOIN target_account_m tam<br/>WHERE lw.tahun = year<br/>AND lw.quartal IN (quartals)<br/>GROUP BY am.nik<br/>ORDER BY t_revenue DESC
        DB-->>Controller: Ranked AM List
    and
        Controller->>DB: getRegionDistribution()
        DB->>DB: SELECT regions.code,<br/>COUNT(am.nik) as am_count<br/>FROM account_managers am<br/>JOIN witels, regions<br/>GROUP BY regions.code
        DB-->>Controller: AM Count per Region
    and
        Controller->>DB: getRegionalPerformance()
        DB->>DB: SELECT regions.code,<br/>SUM(tam.t_revenue),<br/>SUM(lwt.r_revenue),<br/>TOP 3 AM per region<br/>FROM lini_waktu lw<br/>JOIN lini_waktu_target lwt<br/>JOIN target_account_m tam<br/>JOIN account_managers<br/>GROUP BY regions.code
        DB-->>Controller: Regional Stats with Top 3 AM
    and
        Controller->>DB: getBestPerformance()
        DB->>DB: SELECT TOP AMs by achievement %<br/>WHERE achievement >= 100%<br/>ORDER BY achievement DESC<br/>LIMIT 10
        DB-->>Controller: Best Performers
    and
        Controller->>DB: getAccountManagerList()
        DB->>DB: SELECT * FROM account_managers<br/>JOIN witels, regions<br/>ORDER BY nik
        DB-->>Controller: All AM List
    end
    
    Controller->>Controller: Format Currency Values
    Controller->>Controller: Calculate Percentages
    Controller->>Controller: Build Inertia Props
    
    Controller-->>Browser: Inertia::render('PerformanceAm', {<br/>  amMetrics,<br/>  amRevenueRanking,<br/>  regionalPerformance,<br/>  bestPerformance,<br/>  accountManagerList,<br/>  availableYears,<br/>  availableQuartals<br/>})
    
    Browser->>Browser: React Component Renders
    Browser->>User: Display Performance Dashboard
```

---

## Daily Monitoring - Simple Flow

```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant Route
    participant Middleware
    participant Controller
    participant Inertia
    
    User->>Browser: Navigate to /daily-monitoring
    Browser->>Route: GET /daily-monitoring
    Route->>Middleware: Check auth & verified
    Middleware->>Controller: DashboardController@dailymonitoring
    
    Note over Controller: Currently returns<br/>static page with<br/>dummy frontend data
    
    Controller->>Inertia: render('DailyMonitoring')
    Inertia-->>Browser: HTML + React Component
    Browser->>Browser: React renders with<br/>dummy metrics & table data
    Browser->>User: Display Daily Monitoring Page
    
    alt User is Admin
        Browser->>Browser: Show "Update Harian" &<br/>"Upload Data Bulanan" buttons
    else User is Viewer
        Browser->>Browser: Hide admin buttons
    end
```

---

## Data Upload Flow - Revenue Import (Admin Only)

```mermaid
sequenceDiagram
    participant Admin
    participant Browser
    participant Route
    participant Middleware
    participant Controller
    participant Import
    participant Validator
    participant DB
    participant Logger
    
    Admin->>Browser: Access /data-import/revenue
    Browser->>Route: GET /data-import/revenue
    Route->>Middleware: auth, verified, role:admin
    Middleware->>Controller: DashboardController@dataImportRevenue
    
    Controller->>DB: Get upload history & status
    DB-->>Controller: Activity logs per month
    Controller-->>Browser: Render DataImportRevenue page
    
    Admin->>Browser: Select Excel file & upload
    Browser->>Route: POST /data-import/revenue/upload<br/>(file, year, month, description)
    Route->>Middleware: role:admin check
    Middleware->>Controller: RevenueImportController@store
    
    Controller->>Validator: Validate Request
    Validator->>Validator: Check file type (xlsx, xls)<br/>Check year & month<br/>Check file size
    
    alt Validation Failed
        Validator-->>Browser: Return errors (422)
        Browser->>Admin: Show error messages
    end
    
    Controller->>Import: Excel::import(RevenueImport)
    
    Import->>Import: Open Excel file
    Import->>Import: Iterate through rows
    
    loop For each row
        Import->>Validator: Validate row data<br/>(NIP NAS, Company Name, Revenue)
        
        alt Row Valid
            Import->>DB: Find or Create Group4<br/>(Company)
            DB-->>Import: group4_id
            
            Import->>DB: Find Region by lookup
            DB-->>Import: region_id
            
            Import->>DB: Upsert Revenue<br/>(group4_id, region_id, year, month)
            DB-->>Import: Success
        else Row Invalid
            Import->>Import: Collect error<br/>(row number, field, error)
        end
    end
    
    Import-->>Controller: Import Results<br/>(success_count, error_count, errors[])
    
    Controller->>DB: Count imported records
    DB-->>Controller: total_records
    
    Controller->>DB: Calculate total revenue
    DB-->>Controller: total_revenue
    
    Controller->>Logger: Create RevenueUpload record
    Logger->>DB: INSERT INTO revenue_uploads<br/>(tahun, bulan, uploaded_by,<br/>action, filename, file_size,<br/>description, ip_address)
    DB-->>Logger: Upload logged
    
    alt Import Successful
        Controller-->>Browser: Redirect with success message<br/>"Imported X records"
        Browser->>Admin: Show success notification
    else Import with Errors
        Controller-->>Browser: Return with errors array
        Browser->>Admin: Show error details per row
    end
```

---

## Service Layer Architecture

```mermaid
graph TB
    subgraph "Controllers"
        DC[DashboardController]
        RIC[RevenueImportController]
        DIPC[DataImportPerformanceController]
    end
    
    subgraph "Service Layer"
        RAS[RevenueAnalyticsService]
    end
    
    subgraph "Models"
        Revenue[Revenue Model]
        Group4[Group4 Model]
        Group3[Group3 Model]
        Group2[Group2 Model]
        Group1[Group1 Model]
        Company[Company Model]
        Region[Region Model]
        AM[AccountManager Model]
        LW[LiniWaktu Model]
        TAM[TargetAccountM Model]
        LWT[LiniWaktuTarget Pivot]
        Witel[Witel Model]
    end
    
    DC -->|Inject Dependency| RAS
    
    RAS -->|getDashboardSummary| Revenue
    RAS -->|getMonthlyRevenue| Revenue
    RAS -->|getYtdComparison| Revenue
    RAS -->|getSubsegmentRevenue| Revenue
    RAS -->|getSubsegmentWithRegionalBreakdown| Revenue
    RAS -->|getTopCompanies| Revenue
    RAS -->|getYearlyRevenueBySubsegment| Revenue
    
    Revenue -->|belongsTo| Group4
    Group4 -->|belongsTo| Group3
    Group3 -->|belongsTo| Group2
    Group2 -->|belongsTo| Group1
    Group1 -->|belongsTo| Company
    
    DC -->|performanceAM| LW
    DC -->|performanceAM| LWT
    DC -->|performanceAM| TAM
    
    LW -->|belongsTo| AM
    LW -->|belongsToMany via pivot| TAM
    LWT -->|pivot| LW
    LWT -->|pivot| TAM
    
    AM -->|belongsTo| Witel
    Witel -->|belongsTo| Region
    
    RIC -->|Import Excel| Revenue
    RIC -->|Logging| DB[(revenue_uploads)]
    
    DIPC -->|Import Excel| LW
    DIPC -->|Import Excel| TAM
    DIPC -->|Import Excel| LWT
    DIPC -->|Logging| DB2[(performance_uploads)]
```

---

## Route Structure & Middleware Flow

```mermaid
graph LR
    subgraph "Public Routes"
        Root[/ - home] -->|redirect| Login[/login]
    end
    
    subgraph "Authenticated Routes"
        Dashboard[/dashboard<br/>DashboardController@index]
        PerformanceAM[/performance-am<br/>DashboardController@performanceAM]
        DailyMonitoring[/daily-monitoring<br/>DashboardController@dailymonitoring]
    end
    
    subgraph "Admin Only Routes"
        DataImportRev[/data-import/revenue<br/>DashboardController@dataImportRevenue]
        DataImportPerf[/data-import/performance<br/>DataImportPerformanceController@index]
        UploadRev[POST /data-import/revenue/upload]
        UploadPerf[POST /data-import/performance/upload]
        DeleteData[DELETE /data-import/revenue/delete]
    end
    
    subgraph "API Routes"
        MonthlyData[/api/dashboard/monthly-data]
        CompanyDetails[/api/dashboard/company-details]
        SubsegmentDetails[/api/dashboard/subsegment-details]
        YtdComparison[/api/dashboard/ytd-comparison-custom]
    end
    
    subgraph "Middleware Chain"
        AuthMW{auth}
        VerifiedMW{verified}
        RoleMW{role:admin}
    end
    
    Login -->|Fortify Auth| AuthMW
    AuthMW -->|Pass| VerifiedMW
    VerifiedMW -->|Pass| Dashboard
    VerifiedMW -->|Pass| PerformanceAM
    VerifiedMW -->|Pass| DailyMonitoring
    VerifiedMW -->|Pass| MonthlyData
    
    VerifiedMW -->|Pass| RoleMW
    RoleMW -->|Admin| DataImportRev
    RoleMW -->|Admin| DataImportPerf
    RoleMW -->|Admin| UploadRev
    RoleMW -->|Admin| UploadPerf
    
    style RoleMW fill:#ff6b6b
    style AuthMW fill:#4ecdc4
    style VerifiedMW fill:#45b7d1
```

---

## Excel Import Processing Pipeline

```mermaid
flowchart TD
    Start([Admin Uploads Excel File]) --> Validate{Validate File}
    Validate -->|Invalid| Error1[Return Validation Error]
    Validate -->|Valid| OpenFile[Open Excel with PhpSpreadsheet]
    
    OpenFile --> GetSheet[Get Active Sheet]
    GetSheet --> ReadHeader[Read Header Row]
    ReadHeader --> MapColumns[Map Column Names to Indexes]
    
    MapColumns --> LoopStart{For Each Row<br/>Starting Row 2}
    
    LoopStart -->|Has Row| ReadRow[Read Row Data]
    ReadRow --> ValidateRow{Validate Row}
    
    ValidateRow -->|Invalid| LogError[Add to Error Array]
    LogError --> LoopStart
    
    ValidateRow -->|Valid| FindCompany[Find or Create Company<br/>in group4 table]
    FindCompany --> FindRegion[Determine Region<br/>from lookup logic]
    FindRegion --> UpsertRevenue[Upsert Revenue Record<br/>group4_id, region_id<br/>tahun, bulan]
    
    UpsertRevenue --> CountSuccess[Increment Success Counter]
    CountSuccess --> LoopStart
    
    LoopStart -->|No More Rows| CalcTotal[Calculate Total Revenue<br/>& Record Count]
    CalcTotal --> LogUpload[Log Upload Activity<br/>in revenue_uploads table]
    
    LogUpload --> CheckErrors{Has Errors?}
    CheckErrors -->|Yes| ReturnPartial[Return Partial Success<br/>with Error Details]
    CheckErrors -->|No| ReturnSuccess[Return Full Success]
    
    ReturnPartial --> End([Redirect with Message])
    ReturnSuccess --> End
    Error1 --> End
    
    style Start fill:#4ecdc4
    style End fill:#95e1d3
    style CheckErrors fill:#ffa07a
    style UpsertRevenue fill:#f38181
```

---

## State Management & Data Flow (Frontend)

```mermaid
flowchart LR
    subgraph "Laravel Backend"
        Controller[Controller] -->|Inertia::render| InertiaResponse[Inertia Response<br/>JSON Props]
    end
    
    subgraph "Inertia Bridge"
        InertiaResponse -->|HTTP Response| InertiaCore[Inertia Core]
    end
    
    subgraph "React Frontend"
        InertiaCore -->|Props| PageComponent[Page Component]
        PageComponent -->|useState| LocalState[Local State]
        PageComponent -->|usePage| SharedProps[Shared Props<br/>auth, errors]
        
        LocalState --> UIComponents[UI Components]
        SharedProps --> UIComponents
        
        UIComponents -->|User Action| EventHandler[Event Handler]
        EventHandler -->|router.get/post| InertiaCore
    end
    
    InertiaCore -->|New Request| Controller
    
    style InertiaCore fill:#9b59b6
    style PageComponent fill:#3498db
    style LocalState fill:#f39c12
    style SharedProps fill:#e74c3c
```

---

## Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL 8.0+

### Installation Steps

```bash
# Clone repository
git clone <repository-url>
cd dashboard-TWS

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tws_dashboard
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Build frontend assets
npm run build

# For development
npm run dev

# Start server
php artisan serve
```

### Default Credentials
After seeding:
- **Admin:** admin@tws.com / password
- **Viewer:** viewer@tws.com / password

---

## Project Structure

```
dashboard-TWS/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── RevenueImportController.php
│   │   │   └── DataImportPerformanceController.php
│   │   ├── Middleware/
│   │   │   └── CheckRole.php
│   │   └── Requests/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Revenue.php
│   │   ├── Group4.php
│   │   ├── Region.php
│   │   ├── AccountManager.php
│   │   └── LiniWaktu.php
│   ├── Services/
│   │   └── RevenueAnalyticsService.php
│   └── Imports/
│       ├── RevenueImport.php
│       └── PerformanceImport.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── pages/
│   │   │   ├── Dashboard.tsx
│   │   │   ├── PerformanceAm.tsx
│   │   │   ├── DailyMonitoring.tsx
│   │   │   ├── DataImportRevenue.tsx
│   │   │   └── DataImportPerformance.tsx
│   │   ├── components/
│   │   │   ├── ui/ (shadcn components)
│   │   │   ├── charts/
│   │   │   ├── modals/
│   │   │   └── sections/
│   │   ├── layouts/
│   │   ├── routes/
│   │   └── types/
│   └── views/
│       └── app.blade.php
├── routes/
│   ├── web.php
│   └── api.php
└── config/
    ├── fortify.php
    └── inertia.php
```

---

## Key Features

### 1. Revenue Dashboard
- YTD revenue tracking with year-over-year comparison
- Monthly revenue breakdown with target vs actual
- Subsegment analysis (Gold, Silver, Copper)
- Regional performance breakdown
- Top companies ranking
- Interactive charts (Bar, Line, Pie)
- Custom date range comparison

### 2. Performance AM
- Account Manager performance metrics
- Revenue target vs achievement per AM
- Regional distribution analysis
- Best performers ranking
- Quarter-by-quarter tracking
- Year-to-Date (YTD) analysis mode
- Detailed AM information table

### 3. Daily Monitoring
- Daily metrics overview
- Project progress tracking
- SCALING monitoring
- Progress status per project
- Real-time updates (admin only)

### 4. Data Upload (Admin Only)
- Excel import for revenue data
- Excel import for performance data
- Validation & error reporting
- Activity logging
- Download templates
- Data versioning per month/quarter
- Delete & re-upload capability

### 5. Role-Based Access Control
- **Admin:** Full access including data upload
- **Viewer:** Read-only access to dashboards

---

## API Endpoints

### Dashboard Analytics
- `GET /api/dashboard/monthly-data` - Get monthly revenue data
- `GET /api/dashboard/month-details` - Detailed month breakdown
- `GET /api/dashboard/company-details` - Company-level details
- `GET /api/dashboard/subsegment-details` - Subsegment analysis
- `GET /api/dashboard/subsegment-trend` - Trend analysis
- `GET /api/dashboard/ytd-comparison-custom` - Custom YTD comparison
- `GET /api/dashboard/available-periods` - Get available data periods

### Data Management (Admin)
- `POST /data-import/revenue/upload` - Upload revenue Excel
- `POST /api/data-import/performance/upload` - Upload performance Excel
- `GET /data-import/revenue/download-template/{year}` - Download template
- `DELETE /data-import/revenue/delete/{year}/{month}` - Delete month data
- `DELETE /api/data-import/performance/delete/{year}/{quarter}` - Delete quarter data

---

## Development Notes

### Adding New Routes
1. Define route in `routes/web.php`
2. Create/update controller method
3. Route helper will be auto-generated in `resources/js/routes/index.ts`

### Adding New Page
1. Create React component in `resources/js/pages/`
2. Use `AppLayout` or `AppSidebarLayout`
3. Controller renders with `Inertia::render('ComponentName', $data)`
4. TypeScript types in `resources/js/types/`

### Adding New Chart
1. Use Recharts library
2. Create component in `resources/js/components/charts/`
3. Follow existing patterns (Bar, Line, Pie charts)

### Database Changes
1. Create migration: `php artisan make:migration`
2. Run migration: `php artisan migrate`
3. Update seeders if needed

---

## Performance Considerations

- **Database Indexing:** Indexed on frequently queried columns (tahun, bulan, quartal, region_id)
- **Eager Loading:** Used throughout with `::with()` to prevent N+1 queries
- **Query Optimization:** Service layer implements optimized aggregations
- **Caching:** Consider implementing cache for dashboard summary data
- **Pagination:** Implemented for large data tables

---

## Security Features

- **CSRF Protection:** Laravel's built-in CSRF for all forms
- **Authentication:** Laravel Fortify with session management
- **Authorization:** Role-based middleware (`role:admin`)
- **SQL Injection Prevention:** Eloquent ORM with parameter binding
- **XSS Protection:** React's automatic escaping
- **File Upload Validation:** Type, size, and content validation
- **Activity Logging:** All uploads/deletes logged with user & IP

---

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=DashboardTest

# Run with coverage
php artisan test --coverage
```

---

## Contributing

1. Create feature branch
2. Make changes with descriptive commits
3. Write/update tests
4. Submit pull request
5. Code review required

---

## License

Proprietary - Telkom Wholesale Service

---

## Support

For issues or questions, contact the development team.
