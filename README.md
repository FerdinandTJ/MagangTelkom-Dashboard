# Dashboard TWS - Telkom Revenue Analytics

## 📋 Daftar Isi
- [Pendahuluan](#-pendahuluan)
- [Latar Belakang](#-latar-belakang)
- [Tujuan Aplikasi](#-tujuan-aplikasi)
- [Fitur Utama](#-fitur-utama)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Database Design](#-database-design)
- [Implementasi Fitur](#-implementasi-fitur)
- [User Interface](#-user-interface)
- [Security Implementation](#-security-implementation)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Panduan Penggunaan](#-panduan-penggunaan)
- [Kesimpulan](#-kesimpulan)

---

## 📖 Pendahuluan

Dashboard TWS (Telkom Wilayah Sumatera) adalah aplikasi web berbasis dashboard analitik yang dirancang untuk memonitor dan menganalisis revenue perusahaan subsegment Telkom serta mengevaluasi performance Account Manager. Aplikasi ini dibangun menggunakan teknologi modern dengan pendekatan full-stack development, menggabungkan Laravel sebagai backend framework dan React dengan TypeScript untuk frontend, dihubungkan melalui Inertia.js untuk seamless single-page application experience.

### Informasi Proyek
- **Nama Aplikasi**: Dashboard TWS - Revenue Analytics & AM Performance
- **Versi**: 1.0.0
- **Tanggal Pengembangan**: Oktober 2025 - Januari 2026
- **Platform**: Web Application (Desktop & Mobile Responsive)
- **Organisasi**: PT Telkom Indonesia - Divisi TWS (Telkom Wilayah Sumatera)

---

## 🎯 Latar Belakang

### Permasalahan yang Dihadapi

Divisi TWS (Telkom Wilayah Sumatera) mengelola ratusan perusahaan subsegment dengan revenue yang mencapai triliunan rupiah per tahun. Sebelum aplikasi ini dikembangkan, terdapat beberapa permasalahan dalam pengelolaan dan monitoring revenue:

1. **Data Tersebar**: Revenue data tersimpan dalam berbagai file Excel yang terpisah, menyulitkan analisis komprehensif
2. **Proses Manual**: Perhitungan achievement, ranking, dan analisis trend dilakukan secara manual menggunakan spreadsheet
3. **Tidak Real-time**: Laporan revenue dan performance AM membutuhkan waktu lama untuk dikompilasi
4. **Kesulitan Visualisasi**: Data dalam format tabel sulit dipahami untuk decision making cepat
5. **Akses Terbatas**: Tidak ada sistem yang memungkinkan stakeholder untuk melihat data kapan saja
6. **Lack of Audit Trail**: Tidak ada tracking siapa yang mengubah atau menghapus data
7. **No Role Management**: Semua user memiliki akses yang sama, tidak ada pembatasan berdasarkan role

### Kebutuhan Solusi

Berdasarkan analisis kebutuhan, diperlukan sistem yang dapat:
- Menyimpan dan mengelola data revenue secara terpusat
- Menyediakan visualisasi data yang interaktif dan mudah dipahami
- Melakukan perhitungan otomatis untuk metrics dan KPI
- Memberikan akses real-time kepada stakeholder
- Menyediakan audit trail untuk accountability
- Mengimplementasikan role-based access control untuk keamanan
- Support untuk import data dari Excel yang sudah ada
- Responsive untuk akses dari berbagai device

---

## 🎯 Tujuan Aplikasi

### Tujuan Umum
Membangun aplikasi dashboard analitik yang dapat membantu management Telkom TWS dalam monitoring revenue dan evaluasi performance Account Manager secara real-time, efisien, dan akurat.

### Tujuan Khusus

1. **Revenue Management**
   - Menyediakan platform terpusat untuk data revenue semua perusahaan subsegment
   - Memfasilitasi upload dan update data revenue secara batch
   - Menyimpan historical data untuk analisis trend jangka panjang

2. **Performance Monitoring**
   - Menampilkan metrics performance Account Manager secara real-time
   - Menyediakan ranking dan comparison antar AM
   - Tracking achievement terhadap target yang ditetapkan

3. **Data Visualization**
   - Menyajikan data dalam bentuk charts yang interaktif dan mudah dipahami
   - Menyediakan drill-down capability untuk analisis detail
   - Support multiple view modes (monthly, yearly, comparison)

4. **Access Control**
   - Implementasi role-based access (Admin dan Viewer)
   - Protect sensitive operations (upload, delete) untuk admin only
   - Audit trail untuk compliance dan accountability

5. **User Experience**
   - Interface yang intuitive dan mudah digunakan
   - Responsive design untuk akses dari desktop dan mobile
   - Dark mode untuk kenyamanan pengguna

---

---
**Komponen Utama:**
- **Summary Cards**: 4 card yang menampilkan metrics kunci
- **Monthly Revenue Chart**: Bar chart dengan target line
- **Subsegment Pie Chart**: Distribusi revenue per subsegment
- **5-Year Trend**: Historical analysis dengan line chart
- **Top Performers**: Best dan worst performing companies

**Fitur Interaktif:**
- Year filter untuk membandingkan data antar tahun
- Revenue sorting (chronological, highest, lowest)
- Drill-down ke detail per month, subsegment, atau company
**Komponen Utama:**
- **AM Summary Cards**: Total revenue managed, companies handled, achievement
- **Performance Ranking Chart**: Bar chart ranking semua AM
- **Distribution Pie Chart**: Distribusi companies per AM
- **Detailed Performance Table**: Complete metrics dengan sorting

**Metrics yang Ditampilkan:**
- ✅ View all dashboards (Revenue & Performance AM)
- ✅ Upload revenue data via Excel import
- ✅ Replace existing data dengan konfirmasi
- ✅ Delete data (per month atau per year)
- ✅ Download Excel templates
- ✅ Download uploaded files
- ✅ View complete activity logs
- ✅ Access all features without restriction

**Viewer Role:**
- ✅ View all dashboards (Revenue & Performance AM)
- ✅ View company details dengan drill-down
- ✅ Access all charts dan visualizations
- ✅ Export data untuk reporting
- ❌ Cannot upload data
- ❌ Cannot delete data
- ❌ Cannot access Data Upload menu (hidden dari sidebar)

**Implementasi Keamanan:**
- Middleware `CheckRole` untuk validasi role pada setiap request
- Frontend conditional rendering berdasarkan user role
- Backend route protection dengan `middleware(['role:admin'])`
- Custom 403 error page untuk unauthorized access
**Upload Process:**
1. User selects Excel file (drag & drop atau browse)
2. System validates file format dan size (max 10MB)
3. Backend reads Excel dan validates:
   - Company NIP-NAS exists di master data
   - Year dan month dalam range valid
**Cara Akses:**
- Click company name dari dashboard
- Click pie chart segment (subsegment drill-down)
- Click monthly bar chart (month drill-down → company list)

**Information Displayed:**NAS harus ada di master companies table
- **Year Validation**: Year harus dalam range 2020-2030
- **Month Validation**: Month harus 1-12
- **Amount Validation**: Revenue dan target harus numeric dan positive
- **Group Validation**: Product groups harus exist di Group1-4 tables
- **Duplicate Check**: Check existing data untuk prevent accidental overwrite

**Upload Modes:**
- **Quick Upload**: Upload data untuk 1 bulan atau multiple months dalam 1 file
- **Replace Mode**: 
  - Checkbox untuk enable replace mode
  - Konfirmasi dialog sebelum replace
  - Old data dihapus, new data di-insert
  - Activity log mencatat "REPLACE" action

**Activity Logs:**
- **Upload Log**: Filename, tahun, bulan, row count, file size, timestamp, user
- **Replace Log**: Same as upload dengan indication "REPLACE" action
- **Delete Log**: Tahun, bulan, deleted records count, timestamp, user
- **Audit Information**: IP address, user agent, device information

**Template & Downloads:**
- **Template Download**: Excel template dengan:
  - Correct column headers
  - Sample data untuk reference
  - Instructions sheet
  - Data validation rules
- **File Download**: 
  - Download specific month's uploaded file
  - Download all files untuk specific year (zip)
  - Original filename preserved

### 5. 🗑️ Data Deletion (Admin Only)lysis
- Regional performance analysis

### 3. 🔐 Role-Based Access Control

**Admin Role:**
- Achievement percentage vs target
- Ranking companies berdasarkan revenue

### 2. 👨‍💼 Performance AM Dashboard revenue per perusahaan dengan subsegment breakdown dan regional analysis
- **Interactive Charts**: Drill-down charts untuk analisis mendalam dengan tooltip detail dan hover effects
- **Performance AM**: Dashboard khusus untuk Account Manager performance tracking

### 🔐 Role-Based Access Control
- **Admin Role**: Full access - dashboard viewing, data upload, data management, template download
- **Viewer Role**: Read-only access - dashboard viewing, performance monitoring (no upload/delete permissions)
- **Automatic Role Assignment**: New users automatically assigned as 'viewer' for security
- **Protected Routes**: Middleware-based protection untuk data upload features
- **Custom 403 Page**: User-friendly unauthorized access page dengan navigation

### 📁 Data Import & Management
- **Excel Import**: Upload revenue data dengan validasi otomatis (tahun, bulan, company matching)
- **Quick Upload**: Upload single month atau multiple months sekaligus
- **Replace Mode**: Replace existing data dengan konfirmasi untuk safety
- **Activity Logs**: Complete audit trail untuk semua upload, replace, dan delete operations
- **Template Download**: Download Excel template dengan struktur yang benar untuk upload
- **File Download**: Download uploaded Excel files (monthly atau yearly)

### 🗑️ Data Deletion
- **Monthly Delete**: Hapus data per bulan dengan konfirmasi
- **Yearly Delete**: Hapus data untuk seluruh tahun sekaligus
- **Cascade Delete**: Otomatis hapus revenue dan target data terkait
- **Delete Logs**: Audit trail untuk semua operasi delete dengan timestamp dan user info

### 💼 Company Detail Modal
- **Full Year View**: Checkbox untuk toggle antara single month atau full year data
- **Period Filter**: Dynamic filtering dengan year dan month selector
- **Month Range Info**: Display range bulan yang ada data (e.g., "Januari - Desember, 12 bulan")
- **Revenue Breakdown**: Hierarchical product/service breakdown dengan pie chart
- **Account Manager Info**: Display AM yang handle company dengan proporsi
- **Regional Data**: Show region dan witel information untuk setiap company

### 📈 Advanced Visualizations
- **Responsive Design**: Fully optimized untuk desktop, tablet, dan mobile
- **Dark Mode**: Complete dark mode support dengan system preference detection
- **Smart Currency Format**: Automatic M (Miliar) / T (Triliun) formatting untuk large numbers
- **Interactive Tooltips**: Detailed hover information tanpa page reload
- **Drill-down Modals**: Multi-level data exploration (Month → Subsegment → Company → Product)

### 🔒 Security Features
- **Two-Factor Authentication**: Enhanced security dengan 2FA support
- **Recovery Codes**: Backup access codes untuk 2FA
- **Session Management**: Secure session handling dengan encryption
- **CSRF Protection**: Built-in CSRF token validation untuk semua forms
- **Role-based Middleware**: Route protection berdasarkan user role

### 🎯 Performance Features
- **Year Filtering**: Filter dan compare revenue data by year (multi-year support)
- **Revenue Sorting**: Sort monthly revenue by chronological, highest, or lowest
- **Lazy Loading**: Efficient data loading untuk large datasets
- **Caching**: Optimized query performance dengan strategic caching
- **Pagination**: Smart pagination untuk large data tables

## 🛠️ Teknologi yang Digunakan

### Backend Technology Stack
- **Framework**: Laravel 11.x (PHP Framework)
  - Alasan: Robust MVC framework dengan ecosystem lengkap, secure by default, excellent documentation
- **PHP**: Version 8.2+
  - Alasan: Modern PHP dengan type safety, JIT compilation, improved performance
- **Database**: MySQL 8.0+
  - Alasan: Reliable, scalable, excellent untuk relational data dengan complex queries
- **ORM**: Eloquent ORM
  - Alasan: Intuitive API, automatic relationship handling, query builder yang powerful
- **Authentication**: Laravel Fortify
  - Alasan: Built-in authentication dengan 2FA support, secure password hashing
- **Server-Side Rendering**: Inertia.js
  - Alasan: Modern monolith - React SPA dengan Laravel routing, no need untuk separate API

### Frontend Technology Stack
- **Framework**: React 18.x
  - Alasan: Component-based architecture, large ecosystem, excellent for interactive UI
- **Language**: TypeScript 5.x
  - Alasan: Type safety, better IDE support, catch errors at compile time
- **UI Library**: Shadcn/ui + Radix UI
  - Alasan: Accessible components, customizable, modern design patterns
- **Styling**: Tailwind CSS 3.x
  - Alasan: Utility-first, rapid development, consistent design system
- **Charts**: Recharts
  - Alasan: React-native chart library, declarative, highly customizable
- **Icons**: Lucide React
  - Alasan: Beautiful, consistent icons, tree-shakeable, actively maintained
- **Form Handling**: React Hook Form
  - Alasan: Performant, easy validation, minimal re-renders
- **State Management**: Built-in React Hooks + Inertia shared data
  - Alasan: Simplified state, no need untuk Redux/Zustand untuk this use case

### Build Tools & Development
- **Build Tool**: Vite
  - Alasan: Lightning fast HMR, optimized production builds, modern ES modules
- **Package Manager**: NPM / Yarn
- **Code Quality**: ESLint + Prettier
  - Alasan: Consistent code style, catch common errors
- **Version Control**: Git + GitHub
  - Alasan: Industry standard, excellent collaboration features

### Additional Libraries
- **Excel Handling**: Maatwebsite/Laravel-Excel (PhpSpreadsheet)
  - Alasan: Powerful Excel import/export untuk Laravel, supports XLSX, CSV, etc.
- **Date Handling**: Carbon (PHP) + date-fns (JavaScript)
  - Alasan: Rich API untuk date manipulation dan formatting
- **HTTP Client**: Axios (with Inertia)
  - Alasan: Promise-based, interceptors untuk global config, automatic JSON transformation
- **Utilities**: clsx, tailwind-merge
  - Alasan: Conditional className handling, merge Tailwind classes intelligently

## 📋 System Requirements

### Development Environment
- **PHP**: >= 8.2 with extensions:
  - BCMath (decimal calculation)
  - Ctype (character type checking)
  - Fileinfo (file type detection)
  - JSON (JSON handling)
  - Mbstring (multibyte string)
  - OpenSSL (encryption)
  - PDO (database connection)
  - Tokenizer (parsing)
  - XML (XML processing)
- **Node.js**: >= 18.0.0 (for frontend build)
- **MySQL**: >= 8.0 atau MariaDB >= 10.3
- **Composer**: Latest version (PHP dependency manager)
- **NPM/Yarn**: Latest version (JavaScript package manager)
- **Git**: For version control

### Production Environment
- **Web Server**: Nginx atau Apache dengan mod_rewrite
- **PHP-FPM**: For optimal PHP performance
- **SSL Certificate**: For HTTPS (recommended)
- **Storage**: Minimum 1GB untuk application + database
- **Memory**: Minimum 512MB RAM (1GB+ recommended)
- **CPU**: 1 core minimum (2+ cores recommended for production)

---

## 🏗️ Arsitektur Sistem

### High-Level Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                          USER BROWSER                             │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐│
│  │   HTML     │  │    CSS     │  │     JS     │  │   Assets   ││
│  │  (Blade)   │  │ (Tailwind) │  │  (React)   │  │  (Images)  ││
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘│
└─────────────────────────────┬────────────────────────────────────┘
                              │ HTTP/HTTPS
                              ▼
┌──────────────────────────────────────────────────────────────────┐
│                        WEB SERVER LAYER                           │
│                     (Nginx / Apache + PHP-FPM)                    │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │                   Laravel Application                       │ │
│  │  ┌──────────────────────────────────────────────────────┐ │ │
│  │  │              MIDDLEWARE LAYER                         │ │ │
│  │  │  • Authentication (Fortify)                          │ │ │
│  │  │  • Authorization (CheckRole)                         │ │ │
│  │  │  • CSRF Protection                                   │ │ │
│  │  │  • Session Management                                │ │ │
│  │  │  • Inertia Middleware (Share Data)                  │ │ │
│  │  │  • Dark Mode Handler                                │ │ │
│  │  └──────────────────────────────────────────────────────┘ │ │
│  │                          │                                  │ │
│  │                          ▼                                  │ │
│  │  ┌──────────────────────────────────────────────────────┐ │ │
│  │  │                 ROUTING LAYER                        │ │ │
│  │  │  • web.php (Main Routes)                            │ │ │
│  │  │  • auth.php (Authentication Routes)                 │ │ │
│  │  │  • Route Middleware (auth, verified, role)          │ │ │
│  │  └──────────────────────────────────────────────────────┘ │ │
│  │                          │                                  │ │
│  │                          ▼                                  │ │
│  │  ┌──────────────────────────────────────────────────────┐ │ │
│  │  │              CONTROLLER LAYER                        │ │ │
│  │  │  • DashboardController                              │ │ │
│  │  │  • RevenueImportController                          │ │ │
│  │  │  • RevenueBreakdownController                       │ │ │
│  │  │  • Validate Input                                   │ │ │
│  │  │  • Return Inertia Response                          │ │ │
│  │  └──────────────────────────────────────────────────────┘ │ │
│  │                          │                                  │ │
│  │                          ▼                                  │ │
│  │  ┌──────────────────────────────────────────────────────┐ │ │
│  │  │              SERVICE LAYER (Business Logic)          │ │ │
│  │  │  • RevenueImportService                             │ │ │
│  │  │    - Excel Parsing                                  │ │ │
│  │  │    - Data Validation                                │ │ │
│  │  │    - Data Transformation                            │ │ │
│  │  │  • (Future: RevenueAnalyticsService)                │ │ │
│  │  └──────────────────────────────────────────────────────┘ │ │
│  │                          │                                  │ │
│  │                          ▼                                  │ │
│  │  ┌──────────────────────────────────────────────────────┐ │ │
│  │  │                 MODEL LAYER (Eloquent ORM)           │ │ │
│  │  │  • User Model (Authentication, Role)                │ │ │
│  │  │  • Company Model (Master Data)                      │ │ │
│  │  │  • Revenue Model (Transactions)                     │ │ │
│  │  │  • RevenueUpload Model (Audit Trail)                │ │ │
│  │  │  • AccountManager, Groups, Regions (Supporting)     │ │ │
│  │  │  • Define Relationships                             │ │ │
│  │  │  • Query Scopes                                     │ │ │
│  │  │  • Accessors & Mutators                             │ │ │
│  │  └──────────────────────────────────────────────────────┘ │ │
│  └───────────────────────────┬──────────────────────────────────┘ │
└────────────────────────────┬─┴──────────────────────────────────┘
                             │
                             ▼
┌──────────────────────────────────────────────────────────────────┐
│                       DATABASE LAYER                              │
│                         MySQL 8.0+                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │   Tables    │  │   Indexes   │  │  Relations  │             │
│  │  • users    │  │  • Primary  │  │  • Foreign  │             │
│  │  • revenues │  │  • Unique   │  │    Keys     │             │
│  │  • companies│  │  • Composite│  │  • Cascade  │             │
│  │  • uploads  │  │  • Fulltext │  │    Delete   │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                      FILE STORAGE LAYER                           │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  storage/app/public/revenue-uploads/                     │   │
│  │  • Excel files organized by year/month                   │   │
│  │  • Symlinked to public/storage for access               │   │
│  │  • Original filenames preserved                          │   │
│  └──────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────┘
```

### Request Flow Diagram

**Typical Page Request (GET):**
```
User Browser
    │
    │ 1. HTTP GET /dashboard
    ▼
Nginx/Apache
    │
    │ 2. Forward to PHP-FPM
    ▼
Laravel Router (web.php)
    │
    │ 3. Apply middleware: auth, verified
    ▼
Middleware Stack
    │ • Authenticate user
    │ • Check CSRF token
    │ • Share Inertia data (user, role, flash messages)
    ▼
DashboardController@index
    │
    │ 4. Fetch data from database
    ▼
Eloquent Models
    │ • Revenue::with('company')->whereYear(...)
    │ • Company::whereHas('revenues')...
    │ • Execute optimized queries with eager loading
    ▼
Database (MySQL)
    │
    │ 5. Return results
    ▼
Controller
    │
    │ 6. Format data, apply business logic
    │ 7. Return Inertia::render('Dashboard', [...])
    ▼
Inertia.js Server
    │
    │ 8. Generate JSON response with:
    │    • Component name: 'Dashboard'
    │    • Props: revenue data, companies, etc.
    │    • Shared data: auth user, flash messages
    ▼
User Browser
    │
    │ 9. React hydrates component
    │ 10. Recharts render visualizations
    │ 11. Page interactive and ready
    ▼
User sees Dashboard with charts
```

**Data Upload Request (POST):**
```
User Browser
    │ User selects Excel file
    │ Drag & drop or browse
    ▼
React Component (DataImportRevenue.tsx)
    │
    │ 1. FormData with file
    │ 2. Inertia.post('/data-import/revenue/upload', formData)
    ▼
Laravel Router
    │
    │ 3. Apply middleware: auth, verified, role:admin
    ▼
Middleware: CheckRole
    │
    │ 4. Verify user.role === 'admin'
    │ 5. Abort 403 if not admin
    ▼
RevenueImportController@upload
    │
    │ 6. Validate request:
    │    • File required, mimes:xlsx,xls,csv, max:10MB
    │    • Replace mode (boolean)
    ▼
Controller
    │
    │ 7. Call RevenueImportService::import($file, $replace)
    ▼
RevenueImportService
    │
    │ 8. Read Excel with PhpSpreadsheet
    │ 9. Loop through rows, validate each:
    │    • Company NIP-NAS exists?
    │    • Year, month valid?
    │    • Revenue, target numeric?
    │    • Groups exist in master data?
    │ 10. If replace mode:
    │     • Delete existing data for (company, year, month)
    │ 11. Insert new revenue records (batch insert)
    │ 12. Create RevenueUpload log record
    │ 13. Store Excel file to storage
    ▼
Database
    │
    │ 14. Execute transactions:
    │     • BEGIN
    │     • DELETE (if replace)
    │     • INSERT revenues (batch)
    │     • INSERT revenue_upload log
    │     • COMMIT
    ▼
File Storage
    │
    │ 15. Save Excel to:
    │     storage/app/public/revenue-uploads/{year}/{filename}
    ▼
Controller
    │
    │ 16. Return success response with Inertia:
    │     • Flash message: "Upload successful"
    │     • Redirect back to upload page
    ▼
React Component
    │
    │ 17. Show success toast notification
    │ 18. Refresh activity logs table
    │ 19. Clear file input
    ▼
User sees success message
```

### Frontend Architecture (React + Inertia)

```
resources/js/
│
├── app.tsx                    # Entry point, setup Inertia
│   │
│   ├── Setup Axios defaults (CSRF token)
│   ├── Configure Inertia app
│   └── Render root component
│
├── pages/                     # Top-level page components
│   │
│   ├── dashboard.tsx          # Revenue Dashboard page
│   │   │
│   │   ├── State: selectedYear, sortOrder
│   │   ├── Fetch: Revenue data via Inertia props
│   │   ├── Components:
│   │   │   ├── SummaryCards (revenue, companies, etc)
│   │   │   ├── MonthlyRevenueChart (Bar + Line)
│   │   │   ├── SubsegmentPieChart
│   │   │   ├── FiveYearTrendChart
│   │   │   └── TopPerformersCards
│   │   └── Handlers:
│   │       ├── onYearChange → Filter data
│   │       ├── onSortChange → Re-sort months
│   │       └── onChartClick → Open drill-down modal
│   │
│   ├── performance-am.tsx     # AM Performance page
│   │   └── Similar structure dengan dashboard
│   │
│   └── DataImportRevenue.tsx  # Upload page (admin only)
│       │
│       ├── Tabs: Upload, Delete Month, Delete Year, Activity Logs
│       ├── State: file, replaceMode, selectedYear, selectedMonth
│       ├── Upload Handler:
│       │   ├── Validate file (client-side)
│       │   ├── Inertia.post() dengan FormData
│       │   └── Handle response (success/error)
│       └── Delete Handlers:
│           ├── Confirmation dialog
│           └── Inertia.delete() request
│
├── components/                # Reusable components
│   │
│   ├── modals/                # Modal components
│   │   │
│   │   ├── CompanyDetailModal.tsx
│   │   │   │
│   │   │   ├── Props: company, isOpen, onClose
│   │   │   ├── State: selectedYear, selectedMonth, fullYear
│   │   │   ├── API Call: fetchRevenueBreakdown(company, year, month)
│   │   │   ├── Components:
│   │   │   │   ├── Period Filter (Year, Month, Full Year checkbox)
│   │   │   │   ├── Summary Cards
│   │   │   │   ├── Company Info
│   │   │   │   ├── Revenue Breakdown Tree (recursive)
│   │   │   │   └── Charts (Monthly Trend, Pie Chart)
│   │   │   └── Handlers:
│   │   │       ├── onFullYearToggle → Hide/show month selector
│   │   │       ├── onYearChange → Fetch new data
│   │   │       └── onMonthChange → Fetch new data
│   │   │
│   │   ├── MonthDetailModal.tsx
│   │   └── SubsegmentModal.tsx
│   │
│   ├── ui/                    # Shadcn/ui components
│   │   ├── button.tsx         # Button variants
│   │   ├── card.tsx           # Card container
│   │   ├── dialog.tsx         # Modal dialog
│   │   ├── dropdown.tsx       # Dropdown menu
│   │   ├── input.tsx          # Form input
│   │   ├── table.tsx          # Data table
│   │   └── ... (30+ components)
│   │
│   ├── app-sidebar.tsx        # Application sidebar
│   │   │
│   │   ├── Props: user (from Inertia shared data)
│   │   ├── Conditional rendering:
│   │   │   └── if (user.role === 'admin') show Data Upload menu
│   │   └── Active state detection from current URL
│   │
│   └── nav-user.tsx           # User dropdown menu
│       └── Display role badge, logout button
│
├── types/                     # TypeScript type definitions
│   │
│   ├── index.d.ts             # Global types
│   │   │
│   │   ├── interface User { id, name, email, role }
│   │   ├── interface Company { id, nip_nas, nama, subsegment }
│   │   ├── interface Revenue { id, company_id, tahun, bulan, ... }
│   │   ├── interface PageProps { auth, flash, errors }
│   │   └── interface ChartData { name, value, ... }
│   │
│   └── auth.ts                # Auth-specific types
│
└── lib/
    └── utils.ts               # Utility functions
        │
        ├── formatCurrency(value)   # Rp X.XM / X.XT
        ├── cn(...classes)          # Merge Tailwind classes
        └── getMonthName(month)     # 1 → "Januari"
```

### Backend Architecture (Laravel)

```
app/
│
├── Http/
│   │
│   ├── Controllers/
│   │   │
│   │   ├── DashboardController.php
│   │   │   │
│   │   │   ├── index()
│   │   │   │   ├── Fetch revenue summary
│   │   │   │   ├── Fetch companies count
│   │   │   │   ├── Fetch monthly revenue dengan target
│   │   │   │   ├── Fetch subsegment breakdown
│   │   │   │   ├── Fetch 5-year trend
│   │   │   │   └── return Inertia::render('Dashboard', [...])
│   │   │   │
│   │   │   └── performanceAm()
│   │   │       └── Similar untuk Performance AM page
│   │   │
│   │   ├── RevenueImportController.php
│   │   │   │
│   │   │   ├── index()
│   │   │   │   ├── Fetch activity logs
│   │   │   │   └── return Inertia::render('DataImportRevenue', [...])
│   │   │   │
│   │   │   ├── upload(Request $request)
│   │   │   │   ├── Validate file
│   │   │   │   ├── Call RevenueImportService::import()
│   │   │   │   └── return back()->with('success', ...)
│   │   │   │
│   │   │   ├── deleteMonth(Request $request)
│   │   │   │   ├── Validate year, month
│   │   │   │   ├── Delete revenues untuk (year, month)
│   │   │   │   ├── Log deletion
│   │   │   │   └── return back()->with('success', ...)
│   │   │   │
│   │   │   ├── deleteYear(Request $request)
│   │   │   │   └── Loop months, call deleteMonth()
│   │   │   │
│   │   │   ├── downloadTemplate()
│   │   │   │   └── Generate Excel template, return download
│   │   │   │
│   │   │   └── downloadFile($id)
│   │   │       └── Fetch RevenueUpload, return stored file
│   │   │
│   │   └── RevenueBreakdownController.php
│   │       │
│   │       └── getHierarchy(Company $company, $year, $month)
│   │           │
│   │           ├── Fetch revenues dengan filters
│   │           ├── Group by Group1 → Group2 → Group3 → Group4
│   │           ├── If no month: show per-month breakdown
│   │           ├── Calculate totals untuk each level
│   │           └── return response()->json([...])
│   │
│   └── Middleware/
│       │
│       ├── CheckRole.php
│       │   │
│       │   └── handle($request, Closure $next, ...$roles)
│       │       │
│       │       ├── if (!auth()->check()) redirect login
│       │       ├── if (!in_array(auth()->user()->role, $roles))
│       │       │   abort(403, 'Unauthorized')
│       │       └── return $next($request)
│       │
│       ├── HandleInertiaRequests.php
│       │   │
│       │   └── share(Request $request)
│       │       │
│       │       └── return array_merge(parent::share($request), [
│       │           'auth' => [
│       │               'user' => $request->user() ? [
│       │                   'id' => ...,
│       │                   'name' => ...,
│       │                   'email' => ...,
│       │                   'role' => ...,    # ← Shared to frontend
│       │               ] : null,
│       │           ],
│       │           'flash' => [
│       │               'success' => session('success'),
│       │               'error' => session('error'),
│       │           ],
│       │       ])
│       │
│       └── HandleAppearance.php
│           └── share($request)
│               └── 'appearance' => request()->cookie('appearance', 'system')
│
├── Models/
│   │
│   ├── User.php
│   │   │
│   │   ├── $fillable = ['name', 'email', 'password', 'role']
│   │   ├── Relationships:
│   │   │   └── hasMany(RevenueUpload::class, 'uploaded_by_id')
│   │   └── Methods:
│   │       ├── isAdmin() { return $this->role === 'admin'; }
│   │       ├── isViewer() { return $this->role === 'viewer'; }
│   │       └── canAccessDataUpload() { return $this->isAdmin(); }
│   │
│   ├── Company.php
│   │   │
│   │   ├── Relationships:
│   │   │   ├── hasMany(Revenue::class)
│   │   │   ├── belongsToMany(AccountManager::class)
│   │   │   └── belongsToMany(Region::class)
│   │   └── Scopes:
│   │       └── scopeWithRevenue($query, $year)
│   │
│   ├── Revenue.php
│   │   │
│   │   ├── Relationships:
│   │   │   ├── belongsTo(Company::class)
│   │   │   ├── belongsTo(Group1::class)
│   │   │   ├── belongsTo(Group2::class)
│   │   │   ├── belongsTo(Group3::class)
│   │   │   └── belongsTo(Group4::class)
│   │   ├── Accessors:
│   │   │   └── getAchievementPercentageAttribute()
│   │   │       └── return ($this->jumlah_revenue / $this->target) * 100
│   │   └── Scopes:
│   │       ├── scopeYear($query, $year)
│   │       ├── scopeMonth($query, $month)
│   │       └── scopeWithProduct($query)
│   │
│   └── RevenueUpload.php
│       │
│       ├── $fillable = ['uploaded_by_id', 'filename', 'tahun', 'bulan', ...]
│       ├── Relationships:
│       │   └── belongsTo(User::class, 'uploaded_by_id')
│       └── Scopes:
│           └── scopeLatest($query)
│
└── Services/
    │
    └── RevenueImportService.php
        │
        ├── import($file, $replaceMode = false)
        │   │
        │   ├── Load Excel dengan Maatwebsite/Laravel-Excel
        │   ├── $rows = Excel::toArray(new RevenueImport, $file)[0]
        │   ├── Validate structure (required columns)
        │   ├── Loop through rows:
        │   │   ├── Validate each field
        │   │   ├── Lookup Company by NIP-NAS
        │   │   ├── Lookup Groups
        │   │   ├── Build Revenue model
        │   │   └── Add to $revenuesData array
        │   ├── if ($replaceMode):
        │   │   ├── $affectedPeriods = collect($revenuesData)
        │   │   │       ->map(fn($r) => [$r['tahun'], $r['bulan']])
        │   │   │       ->unique()
        │   │   ├── foreach ($affectedPeriods as [$year, $month]):
        │   │   │   └── Revenue::where('tahun', $year)
        │   │   │           ->where('bulan', $month)
        │   │   │           ->delete()
        │   ├── Revenue::insert($revenuesData)  # Batch insert
        │   ├── Store file:
        │   │   └── $path = $file->storeAs(
        │   │           'revenue-uploads/' . $year,
        │   │           $originalName,
        │   │           'public'
        │   │       )
        │   ├── Create RevenueUpload log:
        │   │   └── RevenueUpload::create([
        │   │           'uploaded_by_id' => auth()->id(),
        │   │           'filename' => $originalName,
        │   │           'tahun' => $year,
        │   │           'bulan' => $month,
        │   │           'action' => $replaceMode ? 'REPLACE' : 'UPLOAD',
        │   │           'row_count' => count($revenuesData),
        │   │           'file_size' => $file->getSize(),
        │   │           'ip_address' => request()->ip(),
        │   │           'user_agent' => request()->userAgent(),
        │   │       ])
        │   └── return ['success' => true, 'rowsProcessed' => ...]
        │
        └── validate(array $row)
            │
            ├── Check Company exists by NIP-NAS
            ├── Validate year (2020-2030)
            ├── Validate month (1-12)
            ├── Validate revenue & target (numeric, >= 0)
            ├── Check Groups exist
            └── return true|false
```

### Data Flow Patterns

**Pattern 1: Server-Side Rendering dengan Inertia**
```
1. User navigates → Laravel route
2. Controller fetches data → Eloquent models
3. Controller returns Inertia::render('Page', $data)
4. Inertia generates JSON response:
   {
     component: 'Page',
     props: { ...data },
     url: '/dashboard',
     version: 'hash123'
   }
5. Browser receives JSON
6. React hydrates component dengan props
7. Page rendered, interactive
```

**Pattern 2: AJAX Navigation (Inertia Links)**
```
1. User clicks <Link href="/performance-am">
2. Inertia intercepts click (preventDefault)
3. Inertia makes AJAX request:
   GET /performance-am
   Headers: X-Inertia: true
4. Laravel detects Inertia request
5. Returns JSON (not full HTML)
6. React replaces current page component
7. URL updated via pushState
8. No full page reload!
```

**Pattern 3: Form Submission dengan Inertia**
```
1. User fills form, clicks submit
2. Inertia.post('/data-import/revenue/upload', formData)
3. AJAX POST dengan CSRF token
4. Laravel validates, processes
5. Returns redirect()->back()->with('success', ...)
6. Inertia receives redirect response
7. Makes new GET request to redirect URL
8. Page re-rendered dengan flash message
9. React shows toast notification
```

### Security Layers

```
┌──────────────────────────────────────┐
│     1. NETWORK LAYER                 │
│  • HTTPS/SSL encryption              │
│  • Firewall rules                    │
│  • DDoS protection                   │
└────────────┬─────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│     2. WEB SERVER LAYER              │
│  • Rate limiting                     │
│  • Request size limits               │
│  • Allowed HTTP methods              │
└────────────┬─────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│     3. APPLICATION LAYER (Laravel)   │
│  ┌────────────────────────────────┐ │
│  │   Authentication               │ │
│  │ • Laravel Fortify              │ │
│  │ • Session-based auth           │ │
│  │ • 2FA support                  │ │
│  │ • Password hashing (bcrypt)    │ │
│  └────────────────────────────────┘ │
│  ┌────────────────────────────────┐ │
│  │   Authorization                │ │
│  │ • CheckRole middleware         │ │
│  │ • Route protection             │ │
│  │ • Conditional UI rendering     │ │
│  └────────────────────────────────┘ │
│  ┌────────────────────────────────┐ │
│  │   Input Validation             │ │
│  │ • Form Request validation      │ │
│  │ • Type checking                │ │
│  │ • Sanitization                 │ │
│  └────────────────────────────────┘ │
│  ┌────────────────────────────────┐ │
│  │   CSRF Protection              │ │
│  │ • Token generation             │ │
│  │ • Token validation             │ │
│  │ • SameSite cookies             │ │
│  └────────────────────────────────┘ │
└────────────┬─────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│     4. DATABASE LAYER                │
│  • Prepared statements (SQL inject) │
│  • Foreign key constraints           │
│  • Unique constraints                │
│  • User permissions (least privilege)│
└──────────────────────────────────────┘
```

### Performance Optimization Strategies

**1. Database Optimizations:**
- Composite indexes: `(company_id, tahun, bulan)`
- Eager loading: `with('company', 'groups')` → Prevent N+1
- Query result caching: Cache summary stats (1 hour TTL)
- Pagination: Large datasets paginated (50 items/page)

**2. Frontend Optimizations:**
- Code splitting: Lazy load modals and charts
- Asset optimization: Vite builds minified bundles
- Image optimization: WebP format, lazy loading
- Memoization: `useMemo` untuk expensive calculations

**3. Caching Strategy:**
- **Browser Cache**: Static assets (1 year)
- **Laravel Cache**: Database queries (1 hour)
- **Opcode Cache**: PHP opcache enabled
- **CDN**: (Future) Static assets served from CDN

**4. Bundle Size Optimization:**
- Tree shaking: Remove unused code
- Code splitting: Separate vendor bundles
- Lazy loading: Load components on demand
- Compression: Gzip/Brotli compression

---

## ⚡ Installation & Setup

### Step 1: Clone Repository
```bash
# Clone the repository
git clone https://github.com/FerdinandTJ/MagangTelkom-Dashboard.git
cd MagangTelkom-Dashboard

# Or if you already have the project
cd /path/to/dashboard-TWS
```

### Step 2: Install Backend Dependencies
```bash
# Install PHP dependencies via Composer
composer install

# Catatan: Proses ini akan download semua Laravel packages yang dibutuhkan
# Durasi: ~2-5 menit tergantung koneksi internet
```

### Step 3: Install Frontend Dependencies
```bash
# Install JavaScript dependencies via NPM
npm install

# Atau menggunakan Yarn
yarn install

# Catatan: Akan install React, TypeScript, Tailwind, dan semua dependencies
# Durasi: ~3-7 menit tergantung koneksi internet
```

### Step 4: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key (untuk encryption)
php artisan key:generate

# Edit .env file dengan text editor untuk configure database
nano .env  # atau vim, code, etc.
```

**Configure Database Connection di .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dashboard_tws
DB_USERNAME=root
DB_PASSWORD=your_password

# Untuk XAMPP default:
DB_USERNAME=root
DB_PASSWORD=
```

**Optional Configuration:**
```env
# Application Settings
APP_NAME="Dashboard TWS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=file

# Mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
```

### Step 5: Create Database
```bash
# Masuk ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE dashboard_tws CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Atau via phpMyAdmin jika menggunakan XAMPP
# Akses: http://localhost/phpmyadmin
# Create database dengan nama 'dashboard_tws'
```

### Step 6: Run Migrations
```bash
# Run all database migrations
php artisan migrate

# Catatan: Ini akan membuat semua tables yang dibutuhkan
# Tables: users, companies, revenues, revenue_uploads, account_managers, 
#         groups1-4, regions, witels, etc.
```

### Step 7: Seed Database (Optional tapi Recommended)
```bash
# Seed dengan sample data
php artisan db:seed

# Catatan: Ini akan membuat:
# - Admin user (admin@tws.com / password)
# - Viewer user (viewer@tws.com / password)
# - Sample companies
# - Sample revenue data
# - Account managers
# - Regional data

# Atau seed specific seeder
php artisan db:seed --class=DatabaseSeeder
```

### Step 8: Storage Link
```bash
# Create symbolic link untuk storage
php artisan storage:link

# Catatan: Diperlukan untuk file uploads (Excel files)
# Creates link dari public/storage ke storage/app/public
```

### Step 9: Build Frontend Assets

**Development Mode (dengan Hot Module Replacement):**
```bash
# Terminal 1: Laravel development server
php artisan serve
# Server akan running di http://localhost:8000

# Terminal 2: Vite development server (buka terminal baru)
npm run dev
# Vite akan running di http://localhost:5173
# Tapi access aplikasi tetap via http://localhost:8000
```

**Production Build:**
```bash
# Build optimized production assets
npm run build

# Start Laravel server
php artisan serve
```

### Step 10: Access Application
```
URL: http://localhost:8000
Admin: admin@tws.com / password
Viewer: viewer@tws.com / password
```

### Troubleshooting Common Issues

**Issue: Composer install failed**
```bash
# Clear composer cache
composer clear-cache
composer install --no-cache
```

**Issue: NPM install failed**
```bash
# Clear npm cache
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

**Issue: Migration failed - Access denied**
```bash
# Check MySQL is running (XAMPP)
# Check DB credentials di .env file
# Test connection:
php artisan tinker
DB::connection()->getPdo();
```

**Issue: Permission denied - storage/logs**
```bash
# Fix permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows: Run as Administrator
# Atau disable permission check sementara
```

**Issue: Vite not serving assets**
```bash
# Check if node_modules exists
npm install

# Kill port 5173 if occupied
lsof -ti:5173 | xargs kill -9  # Mac/Linux
netstat -ano | findstr :5173   # Windows, then kill PID

# Restart Vite
npm run dev
```

**Issue: 500 Error after deployment**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📊 Dashboard Features

### 🏠 Main Dashboard (Revenue Dashboard)
- **Summary Cards**: 
  - Total Revenue YTD dengan detail tooltip
  - Active Companies count dengan growth indicator
  - Current Month revenue dengan MoM comparison
  - Average Revenue per Company
- **Monthly Revenue Trend**: 
  - Interactive bar chart dengan target vs actual
  - Achievement percentage visualization
  - Revenue sorting (chronological/highest/lowest)
  - Year filter untuk historical comparison
- **Subsegment Breakdown**: 
  - Pie chart revenue distribution by subsegment
  - Click untuk drill-down ke company details
  - Percentage dan value display
- **5-Year Trend**: 
  - Historical revenue analysis dengan line chart
  - Year-over-year comparison
  - Trend indicators
- **Top Performers**: 
  - Best performing company by revenue
  - Worst performing company untuk improvement focus
  - Achievement metrics
- **Currency Display**: 
  - Smart formatting - M (Miliar) untuk < 1000B
  - T (Triliun) untuk >= 1000B
  - Full value on hover
- **Interactive Tooltips**: 
  - Detailed information without page reload
  - Revenue breakdown details
  - Target vs actual comparison

### 👨‍💼 Performance AM Dashboard
- **AM Metrics**: 
  - Key performance indicators untuk Account Manager
  - Total revenue managed
  - Number of companies handled
  - Achievement percentage
- **Performance Ranking**: 
  - Bar chart ranking AM berdasarkan achievement
  - Sortable dan filterable
  - Color-coded performance levels
- **Account Distribution**: 
  - Pie chart distribusi account per AM
  - Subsegment breakdown
  - Company count per AM
- **Detailed Table**: 
  - Complete 
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php       # Main dashboard & data import pages
│   │   │   ├── RevenueImportController.php   # Upload, delete, download operations
│   │   │   └── RevenueBreakdownController.php # Hierarchical breakdown API
│   │   └── Middleware/
│   │       ├── CheckRole.php                 # Role-based access control
│   │       ├── HandleInertiaRequests.php     # Share data to frontend
│   │       └── HandleAppearance.php          # Dark mode management
│   ├── Models/
│   │   ├── User.php                          # User dengan role (admin/viewer)
│   │   ├── Company.php                       # Master company data
│   │   ├── Revenue.php                       # Revenue transactions
│   │   ├── RevenueUpload.php                 # Upload history & audit logs
│   │   ├── AccountManager.php                # AM master data
│   │   └── Group1/2/3/4.php                 # Product hierarchy
│   └── Services/
│       └── RevenueImportService.php          # Business logic untuk import
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── modals/
│   │   │   │   ├── CompanyDetailModal.tsx   # Company detail dengan full year
│   │   │   │   ├── MonthDetailModal.tsx      # Month drill-down
│   │   │   │   └── SubsegmentModal.tsx       # Subsegment drill-down
│   │   │   ├── ui/                           # Reusable UI components
│   │   │   ├── app-sidebar.tsx               # Navigation dengan role filter
│   │   │   └── nav-user.tsx                  # User menu dengan role display
│   │   ├── pages/
│   │   │   ├── dashboard.tsx                 # Main revenue dashboard
│   │   │   ├── performance-am.tsx            # AM performance dashboard
│   │   │   ├── DataImportRevenue.tsx         # Data upload page (admin only)
│   │   │   └── errors/
│   │   │       └── 403.tsx                   # Custom unauthorized page
│   │   └── types/
│   │       ├── index.d.ts                    # Global TypeScript types
│   │       └── auth.ts                       # Auth related types
│   └── css/
│       └── app.css                           # Tailwind + custom styles
├── database/
│   ├── migrations/
│   │   ├── *_create_users_table.php
│   │   ├── *_add_role_to_users_table.php    # Role column migration
│   │   ├── *_create_companies_table.php
│   │   ├── *_create_revenues_table.php
│   │   ├── *_create_revenue_uploads_table.php # Audit logs table
│   │   └── *_create_group1-4_tables.php     # Product hierarchy
│   └── seeders/
│       ├── DatabaseSeeder.php                # Main seeder dengan admin/viewer users
│       ├── DummyDataSeeder.php               # Sample revenue data
│       └── AccountManagerSeeder.php          # AM master data
├── routes/
│   ├── web.php                               # Main routes dengan role middleware
│   └── auth.php                              # Authentication routes
└── bootstrap/
    └── app.php                               # App config dengan middleware & exception handleran atau multiple months
  - **Replace Mode**: Checkbox untuk replace existing data (dengan konfirmasi)
- **Validation**:
  - Company NIP-NAS validation terhadap master data
  - Tahun dan bulan validation
  - Revenue dan target amount validation
  - Product/service group validation
- **Activity Logs**:
  - Complete audit trail dengan timestamp
  - User information (uploaded_by)
  - Action types: Upload, Replace, Delete
  - File metadata (filename, size, row count)
  - IP address dan user agent tracking
- **Template & Downloads**:
  - Download Excel template untuk upload
  - Download existing uploaded files (per month)
  - Download all files untuk specific year
  - Original filename preservation

### 🗑️ Data Management (Admin Only)
- **Delete Operations**:
  - **Delete Month**: Hapus data untuk specific bulan dengan konfirmasi
  - **Delete Year**: Hapus semua data untuk specific tahun (batch delete)
  - Cascade delete revenue dan target data
  - Safety confirmations untuk prevent accidental deletion
- **Audit Trail**:
  - All delete operations logged dengan detail
  - User tracking untuk accountability
  - Timestamp precision untuk compliance
  - Preserved history tidak bisa dihapus (immutable logs)

### 💼 Company Detail Modal
- **Period Filtering**:
  - **Full Year Checkbox**: Toggle untuk view full year vs single month
  - **Year Selector**: Dropdown dengan available years
  - **Month Selector**: Dropdown dengan available months (hidden saat full year)
  - **Info Badge**: Display "Menampilkan data agregat: (Januari - Desember, 12 bulan)"
- **Summary Cards**:
  - Total Revenue dengan period context
  - Reporting Period dengan month range
  - Average Monthly Revenue
  - Best performing month
- **Company Information**:
  - NIP-NAS dengan copy functionality
  - Subsegment classification
  - Regional assignment (Region → Witel)
  - Primary region indicator
- **Account Managers**:
  - List of AMs handling company
  - Proporsi dan pembagian type
  - Contact information
- **Revenue Breakdown**:
  - **Hierarchical Tree**: Group1 → Group2 → Group3 → Group4 (Product)
  - **Per Month Display**: Saat full year, show "Product Name (Januari 2026)", "Product Name (Februari 2026)", etc.
  - **Pie Chart**: Visual distribution per category
  - **Interactive**: Click untuk expand/collapse categories
  - **Total Calculation**: Auto-sum dari semua child items
- **Charts**:
  - Monthly trend chart (12 months view)
  - Product distribution pie chart
  - Target vs actual comparison

### 🌓 Dark Mode
- **System Preference Detection**: Automatic dark mode berdasarkan OS setting
- **Manual Toggle**: Switch between light/dark/system mode via settings
- **Full Component Support**: All UI components fully support dark mode
- **Persistent Setting**: Dark mode preference stored in localStorage
- **Smooth Transitions**: Animated transitions between modes
- **Optimized Colors**: Carefully selected colors untuk readability di semua kondisi

### 🔐 Authentication & Security
- **Login/Register**: Full authentication system dengan validation
- **Two-Factor Authentication**: 
  - QR code setup dengan authenticator apps
  - Recovery codes untuk backup access
  - Session management dengan 2FA verification
- **Role Assignment**:
  - New users automatically get 'viewer' role
  - Admin can be set via database atau seeder
  - Role persists across sessions
- **Session Security**:
  - Encrypted session data
  - CSRF protection pada all forms
  - Secure cookie handling
  - Auto-logout on inactivity

## 🔧 Development Workflow

### Development Mode
```bash
# Terminal 1: Start Laravel development server
php artisan serve
# Server will run at http://localhost:8000

# Terminal 2: Start Vite development server (open new terminal)
npm run dev
# Vite will run at http://localhost:5173 with HMR
# But access application through http://localhost:8000

# Hot Module Replacement (HMR) akan otomatis reload saat edit file
```

### Code Organization Best Practices

**Controllers:**
- Thin controllers - business logic di services
- Return Inertia responses untuk pages
- Return JSON untuk AJAX requests
```php
// Good
return Inertia::render('Dashboard', [
    'revenues' => RevenueService::getSummary($year)
]);

// Bad - business logic di controller
return Inertia::render('Dashboard', [
    'revenues' => Revenue::with('company')
        ->whereYear('tahun', $year)
        ->get()
        ->groupBy('bulan')
        ->map(function($items) {
            return $items->sum('jumlah_revenue');
        })
]);
```

**React Components:**
- Functional components dengan hooks
- Separate UI components dari business logic
- Use TypeScript untuk type safety
```typescript
// Good - typed props
interface CompanyDetailModalProps {
    company: Company;
    isOpen: boolean;
    onClose: () => void;
}

export function CompanyDetailModal({ 
    company, 
    isOpen, 
    onClose 
}: CompanyDetailModalProps) {
    // component logic
}
```

**Services:**
- Encapsulate complex business logic
- Reusable across controllers
- Easy to test

**Models:**
- Define relationships clearly
- Use accessors untuk computed properties
- Use scopes untuk reusable queries

### Git Workflow
```bash
# Create feature branch
git checkout -b feature/new-feature-name

# Make changes and commit
git add .
git commit -m "feat: add new feature description"

# Push to remote
git push origin feature/new-feature-name

# Create Pull Request di GitHub
# After review & approval, merge to main
```

### Code Style & Standards
```bash
# PHP - follow PSR-12
composer run phpcs

# JavaScript/TypeScript - ESLint
npm run lint

# Auto-fix
npm run lint:fix

# Format code dengan Prettier
npm run format
```
### Core Models
- **Users**: Authentication dan user management
  - Fields: id, name, username, email, password, role (admin/viewer)
  - Relationships: hasMany RevenueUploads
  - Methods: isAdmin(), isViewer(), canAccessDataUpload()

- **Role-Based Access Control
**Admin Role:**
- ✅ View all dashboards (Revenue & Performance AM)
- ✅ Upload revenue data (Quick Upload & Replace)
- ✅ Download templates dan uploaded files
- ✅ Delete revenue data (monthly atau yearly)
- ✅ View complete activity logs
- ✅ Access all features without restriction

**Viewer Role:**
- ✅ View all dashboards (Revenue & Performance AM)
- ✅ View company details dengan drill-down
- ✅ Access all charts dan visualizations
- ✅ Export data untuk reporting
- ❌ Cannot upload data
- ❌ Cannot delete data
- ❌ Cannot access Data Upload menu

**Security Implementation:**
### Production Build
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci --only=production

# Build frontend assets
npm run build
### Charts & Visualizations
- **Bar Charts**: Monthly revenue trends dengan target lines (Recharts)
- **Pie Charts**: Subsegment distribution dengan interactive legends
- **Line Charts**: Historical 5-year trends dengan area fills
- **Composed Charts**: Combined bar+line untuk complex metrics
- **Tooltips**: Custom styled dengan detailed breakdowns
- **Legends**: Interactive dengan click-to-filter functionality
- **Responsive**: Auto-resize based on container dengan proper aspect ratios
- **Dark Mode**: Optimized colors untuk both light dan dark themes

### Modals & Dialogs
- **Company Detail Modal**: 
  - Large modal dengan tabs navigation
  - Scrollable content dengan sticky header
  - Full year toggle dengan dynamic filtering
  - Revenue breakdown tree dengan expand/collapse
- **Month Detail Modal**: Drill-down dari monthly chart
- **Subsegment Modal**: Drill-down dari pie chart
- **Confirmation Dialogs**: Delete confirmations dengan warning messages
- **Loading States**: Skeleton loaders untuk better UX
- **Error States**: User-friendly error messages dengan retry options

### Cards & Summary Components
- **Stat Cards**: 
  - Large number display dengan trend indicators
  - Icon dengan colored background
  - Hover effects untuk additional info
  - Tooltip dengan detailed metrics
- **Company Cards**: 
  - Company info dengan logo placeholder
  - Quick stats display
  - Action buttons (view details, etc.)
- **Performance Cards**: 
  - AM metrics dengan achievement badges
  - Color-coded performance levels
  - Progress bars untuk visual indication

### Tables & Lists
- **Data Tables**: 
  - Sortable columns dengan sort indicators
  - Filterable dengan search input
  - Paginated untuk large datasets
  - Row selection dengan bulk actions
  - Responsive pada mobile (card view)
- **Activity Logs Table**:
  - Chronological display dengan timestamps
  - Action type badges (Upload/Replace/Delete)
  - � Testing

### Manual Testing Checklist

**Authentication & Authorization:**
- [ ] Login dengan admin credentials
- [ ] Login dengan viewer credentials
- [ ] Two-factor authentication setup dan verification
- [ ] Logout functionality
- [ ] Session expiration handling
- [ ] Password reset flow

**Admin Features:**
- [ ] Upload revenue data (single month)
- [ ] Upload revenue data (multiple months)
- [ ] Replace existing data dengan confirmation
- [ ] Download Excel template
- [ ] Download uploaded file (monthly)
- [ ] Download all files (yearly)
- [ ] Delete month data dengan confirmation
- [ ] Delete year data dengan confirmation
- [ ] View activity logs dengan complete audit trail

**Viewer Restrictions:**
- [ ] Cannot see "Data Upload" menu
- [ ] 403 error when accessing `/data-import/revenue` directly
- [ ] Can view revenue dashboard
- [ ] Can view performance AM dashboard
- [ ] Can open company detail modal
- [ ] Can interact dengan all charts

**Dashboard Features:**
- [ ] Revenue summary cards display correctly
- [ ] Monthly revenue chart dengan bar dan line
- [ ] Subsegment pie chart dengan drill-down
- [ ] 5-year trend line chart
- [ ] Top performers cards
- [ ] Year filter changes data correctly
- [ ] Revenue sorting (chronological, highest, lowest)
- [ ] Currency formatting (M untuk Miliar, T untuk Triliun)
- [ ] Hover tooltips show full values

**Company Detail Modal:**
- [ ] Open dari dashboard click
- [ ] Company info displayed correctly
- [ ] Account manager info shown
- [ ] Regional data displayed
- [ ] Year selector works
- [ ] Month selector works (when unchecked)
- [ ] Full year checkbox toggles month selector
- [ ] Info badge shows month range saat full year
- [ ] Revenue breakdown tree renders
- [ ] Product per month display (e.g., "Product (Januari 2026)")
- [ ] Pie chart updates based on filter
- [ ] Monthly trend chart displays
- [ ] Close modal functionality

**Dark Mode:**
- [ ] Auto-detect system preference
- [ ] Manual toggle light/dark/system
- [ ] All components render correctly di dark mode
- [ ] Charts colors optimized untuk dark mode
- [ ] Setting persists across sessions

**Performance:**
- [ ] Page load under 3 seconds
- [ ] Chart rendering smooth (no lag)
- [ ] Modal opening smooth
- [ ] Large dataset handling (1000+ records)
- [ ] File upload progress indicator
- [ ] No memory leaks dengan repeated interactions

**Responsive Design:**
- [ ] Desktop (1920x1080) layout correct
- [ ] Laptop (1366x768) layout correct
- [ ] Tablet (768x1024) layout correct
- [ ] Mobile (375x667) layout correct
- [ ] Charts resize properly
- [ ] Tables switch to card view on mobile
- [ ] Modals adapt to screen size

### Automated Testing (Future Implementation)
```bash
# Unit tests
php artisan test --testsuite=Unit

# Feature tests
php artisan test --testsuite=Feature

# Browser tests with Dusk
php artisan dusk

# Code coverage
php artisan test --coverage
```on dengan avatars
  - File metadata display
  - Expandable rows untuk details

### Navigation Components
- **Sidebar**: 
  - Dark blue theme dengan hover effects
  - Collapsible dengan icon-only mode
  - Active state indication
  - Role-based menu filtering
  - Smooth transitions
- **Breadcrumbs**: 
  - Current page location display
  - Clickable navigation trail
  - Responsive truncation
- **User Menu**: 
  - Profile dropdown dengan role badge
  - Quick settings access
  - Logout dengan confirmation
- **Mobile Menu**: 
  - Hamburger toggle
  - Slide-in drawer
  - Full navigation access

### Forms & Inputs
- **File Upload**: 
  - Drag & drop area dengan hover state
  - File browser fallback
  - Progress bar during upload
  - File validation messages
  - Preview untuk uploaded files
- **Dropdowns**: 
  - Year selector dengan available years
  - Month selector dengan conditional display
  - Subsegment filter
  - Sort options
- **Checkboxes**: 
  - Full Year toggle
  - Replace mode confirmation
  - Multi-select untuk bulk actions
- **Buttons**: 
  - Primary, secondary, outline, ghost variants
  - Loading states dengan spinners
  - Disabled states dengan tooltips
  - Icon buttons dengan labels

### Filters & Controls
- **Year Filter**: Dropdown dengan available years dari data
- **Month Filter**: Dropdown dengan month names (conditional)
- **Sort Control**: Dropdown dengan sorting options
- **Search Input**: Debounced search dengan clear button
- **Date Range Picker**: Range selection untuk reporting
- **Export Button**: Download data dalam various formats

### Feedback Components
- **Toasts**: 
  - Success, error, warning, info variants
  - Auto-dismiss dengan configurable duration
  - Action buttons (undo, retry)
  - Position configurable (top-right default)
- **Loading Indicators**: 
  - Skeleton loaders untuk cards
  - Spinner untuk async operations
  - Progress bars untuk uploads
  - Shimmer effects untuk tables
- **Empty States**: 
  - Friendly illustrations
  - Call-to-action buttons
  - Helpful guidance text
- **Error Pages**: 
  - 403 Unauthorized dengan navigation
  - 404 Not Found dengan search
  - 500 Server Error dengan retry
  - Consistent branding

### Accessibility Features
- **Keyboard Navigation**: Full keyboard support dengan focus indicators
- **ARIA Labels**: Proper labels untuk screen readers
- **Color Contrast**: WCAG AA compliance untuk all text
- **Focus Management**: Logical tab order dan focus trapping
- **Alternative Text**: Descriptive alt text untuk image
php artisan migrate --force

# Set proper permissions
chmod -R 755 storage bootstrap/cache
```

### Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Security
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

### Server Requirements
- PHP >= 8.2 with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- MySQL >= 8.0 or MariaDB >= 10.3
- Nginx or Apache with mod_rewrite
- Composer
- Node.js >= 18 (untuk build only, tidak untuk production runtime)

### Web Server Configuration (Nginx)
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/dashboard-tws/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Performance Optimization
```bash
# Enable OPcache in php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Queue workers untuk background jobs
php artisan queue:work --daemon

# Schedule task untuk periodic jobs
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1ame as upload dengan indication of replacement
- **Delete Actions**: What was deleted (month/year), number of records affected
- **User Tracking**: Who performed action (user_id, name)
- **Technical Details**: IP address, user agent, timestamp

**Benefits:**
- Complete audit trail untuk compliance
- Troubleshooting upload issues
- Monitor who's uploading/deleting data
- Track data changes over time
- Rollback reference jika needed

**Implementation:**
- Immutable logs (tidak bisa dihapus atau diubah)
- Automatic logging pada setiap operation
- Efficient indexing untuk fast queries
- Display chronologically dengan latest first

### Smart Currency Formatting
**Display Logic:**
- Nilai < 1,000 Miliar: "Rp X.XXM" (e.g., Rp 500.50M)
- Nilai >= 1,000 Miliar: "Rp X.XXT" (e.g., Rp 1.5T)
- Hover tooltip: Full value "Rp 1,234,567,890,123"

**Implementation:**
- Frontend formatting dengan locale-aware number formatting
- Consistent across all charts dan tables
- Responsive tooltip positioning
- Color-coded untuk visual clarity

### Revenue Filtering & Sorting
**Year Filter:**
- Multi-year support (2020-2030)
- Dynamic year list based on available data
- URL parameter persistence untuk sharing
- Smooth transitions between years

**Revenue Sorting:**
- **Chronological**: Default - Januari sampai Desember
- **Highest First**: Useful untuk identifying peak months
- **Lowest First**: Focus pada improvement areas
- Maintains year filter state saat sorting

### Full Year View (Company Detail Modal)
**User Experience:**
- Checkbox "Full Year" untuk toggle view mode
- Month selector otomatis hidden saat full year active
- Info badge menampilkan data range (e.g., "Januari - Desember, 12 bulan")
- Period card shows year dengan month range detail

**Data Handling:**
- Backend aggregates data dari semua bulan
- Revenue breakdown shows per-month detail:
  - "Product Name (Januari 2026)"
  - "Product Name (Februari 2026)"
  - etc.
- Charts automatically adjust untuk multi-month data
- Summary metrics calculated across all months

**API Optimization:**
- Single API call dengan conditional month parameter
- Backend filters: year only (no month) = full year data
- Efficient query dengan proper indexing
- Cached untuk performance

### Dark Mode Implementation
**Features:**
- System preference auto-detection via `prefers-color-scheme`
- Manual override: Light / Dark / System modes
- Persistent storage di localStorage
- Smooth CSS transitions (200ms)

**Component Support:**
- All UI components (buttons, cards, modals, charts)
- Custom scrollbar styling
- Chart colors optimized untuk both modes
- Text contrast ratios meet WCAG standards

**Implementation:**
- Tailwind dark: variant untuk all components
- CSS variables untuk theme colors
- Context provider untuk global state
- Middleware injection untuk SSR consistency
- **Group2**: Sub category (e.g., FMC, BBE)
- **Group3**: Service type (e.g., INTERNET, VOICE)
- **Group4**: Product detail (e.g., INDIHOME, ASTINET)
- Relationships: Group1 hasMany Group2 hasMany Group3 hasMany Group4

### Supporting Models
- **AccountManagers**: AM master data dengan witel assignment
- **Regions**: Regional classification (7 regions)
- **Witels**: Witel master data dengan regional mapping
- **CompanyTargets**: Yearly company targets
npm run build

# Run tests
php artisan test

# Generate routes
php artisan wayfinder:generate
```

## 📁 Struktur Proyek

```
dashboard-TWS/
├── app/                                  # Laravel Application Core
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php       # Main dashboard & data import pages
│   │   │   ├── RevenueImportController.php   # Upload, delete, download operations
│   │   │   └── RevenueBreakdownController.php # Hierarchical breakdown API
│   │   └── Middleware/
│   │       ├── CheckRole.php                 # Role-based access control
│   │       ├── HandleInertiaRequests.php     # Share data to frontend
│   │       └── HandleAppearance.php          # Dark mode management
│   ├── Models/
│   │   ├── User.php                          # User dengan role (admin/viewer)
│   │   ├── Company.php                       # Master company data
│   │   ├── Revenue.php                       # Revenue transactions
│   │   ├── RevenueUpload.php                 # Upload history & audit logs
│   │   ├── AccountManager.php                # AM master data
│   │   └── Group1/2/3/4.php                 # Product hierarchy
│   ├── Services/
│   │   └── RevenueImportService.php          # Business logic untuk import
│   └── Rules/                                # Custom validation rules
│
├── resources/                            # Frontend Resources
│   ├── js/
│   │   ├── components/
│   │   │   ├── modals/
│   │   │   │   ├── CompanyDetailModal.tsx   # Company detail dengan full year
│   │   │   │   ├── MonthDetailModal.tsx      # Month drill-down
│   │   │   │   └── SubsegmentModal.tsx       # Subsegment drill-down
│   │   │   ├── ui/                           # Reusable UI components (Shadcn)
│   │   │   │   ├── button.tsx
│   │   │   │   ├── card.tsx
│   │   │   │   ├── dialog.tsx
│   │   │   │   └── ... (30+ components)
│   │   │   ├── app-sidebar.tsx               # Navigation dengan role filter
│   │   │   └── nav-user.tsx                  # User menu dengan role display
│   │   ├── pages/
│   │   │   ├── dashboard.tsx                 # Main revenue dashboard
│   │   │   ├── performance-am.tsx            # AM performance dashboard
│   │   │   ├── DataImportRevenue.tsx         # Data upload page (admin only)
│   │   │   └── errors/
│   │   │       ├── 403.tsx                   # Custom unauthorized page
│   │   │       └── 404.tsx                   # Not found page
│   │   ├── types/
│   │   │   ├── index.d.ts                    # Global TypeScript types
│   │   │   └── auth.ts                       # Auth related types
│   │   ├── lib/
│   │   │   └── utils.ts                      # Utility functions
│   │   └── app.tsx                           # Main React entry point
│   └── css/
│       └── app.css                           # Tailwind + custom styles
│
├── database/                             # Database Structure
│   ├── migrations/                       # Schema definitions
│   │   ├── 2024_01_01_000000_create_users_table.php
│   │   ├── 2026_01_15_091332_add_role_to_users_table.php
│   │   ├── 2024_01_02_000000_create_companies_table.php
│   │   ├── 2024_01_03_000000_create_revenues_table.php
│   │   ├── 2024_01_04_000000_create_revenue_uploads_table.php
│   │   ├── 2024_01_05_000000_create_account_managers_table.php
│   │   ├── 2024_01_06_000000_create_groups_tables.php
│   │   └── ... (20+ migrations)
│   ├── seeders/                          # Sample data
│   │   ├── DatabaseSeeder.php                # Main seeder
│   │   ├── CompanySeeder.php                 # Company master data
│   │   ├── DummyDataSeeder.php               # Sample revenue data
│   │   ├── AccountManagerSeeder.php          # AM master data
│   │   └── RegionalSeeder.php                # Regional & witel data
│   └── factories/                        # Model factories for testing
│
├── routes/                               # Application Routes
│   ├── web.php                               # Main routes dengan role middleware
│   ├── auth.php                              # Authentication routes
│   └── console.php                           # Artisan commands
│
├── public/                               # Public Web Root
│   ├── index.php                             # Application entry point
│   ├── storage/                              # Symlink to storage/app/public
│   └── build/                                # Compiled frontend assets (Vite)
│       ├── assets/
│       │   ├── app-[hash].js                 # Main JavaScript bundle
│       │   └── app-[hash].css                # Main CSS bundle
│       └── manifest.json                     # Asset manifest
│
├── storage/                              # Application Storage
│   ├── app/
│   │   ├── public/                           # Publicly accessible files
│   │   │   └── revenue-uploads/              # Uploaded Excel files
│   │   └── private/                          # Private files
│   ├── framework/                        # Framework cache & sessions
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/                            # Compiled Blade views
│   └── logs/                             # Application logs
│       └── laravel.log
│
├── bootstrap/                            # Bootstrap Configuration
│   ├── app.php                               # App config dengan middleware
│   ├── providers.php                         # Service providers
│   └── cache/                                # Bootstrap cache
│
├── config/                               # Configuration Files
│   ├── app.php                               # Application config
│   ├── database.php                          # Database connections
│   ├── fortify.php                           # Authentication config
│   ├── inertia.php                           # Inertia.js config
│   └── ... (15+ config files)
│
├── tests/                                # Automated Tests
│   ├── Feature/                          # Feature tests
│   ├── Unit/                             # Unit tests
│   └── TestCase.php                      # Base test class
│
├── vendor/                               # PHP Dependencies (Composer)
│   └── ... (100+ packages)
│
├── node_modules/                         # JavaScript Dependencies (NPM)
│   └── ... (500+ packages)
│
├── .env                                  # Environment variables (not in repo)
├── .env.example                          # Example environment file
├── .gitignore                            # Git ignore rules
├── artisan                               # Artisan CLI
├── composer.json                         # PHP dependencies definition
├── composer.lock                         # PHP dependencies lock file
├── package.json                          # JavaScript dependencies definition
├── package-lock.json                     # JavaScript dependencies lock file
├── phpunit.xml                           # PHPUnit configuration
├── vite.config.ts                        # Vite build configuration
├── tsconfig.json                         # TypeScript configuration
├── tailwind.config.js                    # Tailwind CSS configuration
├── eslint.config.js                      # ESLint configuration
└── README.md                             # This file
```

### Key Directories Explained

**app/Http/Controllers**: Menangani HTTP requests dan mengembalikan responses
- `DashboardController`: Render dashboard pages dan data fetching
- `RevenueImportController`: Handle upload, delete, download Excel files
- `RevenueBreakdownController`: API untuk hierarchical product breakdown

**app/Models**: Eloquent models yang merepresentasikan database tables
- Setiap model punya relationships, scopes, dan business logic
- Menggunakan fillable/guarded untuk mass assignment protection

**app/Services**: Business logic yang complex, dipisahkan dari controllers
- `RevenueImportService`: Excel parsing, validation, data transformation

**resources/js/components**: React components yang reusable
- `ui/`: Shadcn components (button, card, dialog, dll)
- `modals/`: Complex modal components dengan state management
- Layout components (sidebar, navigation, user menu)

**resources/js/pages**: Inertia pages (top-level components)
- Setiap file merepresentasikan 1 route/page
- Menerima props dari Laravel controller

**database/migrations**: Database schema evolution
- Timestamped files untuk version control
- Up/down methods untuk rollback capability

**database/seeders**: Populate database dengan initial/test data
- Development: Sample data untuk testing
- Production: Master data yang required (regions, witels)

**storage/app/public**: File yang di-upload user
- Excel files disimpan di `revenue-uploads/`
- Accessible via `/storage` URL (symlink)

**public/**: Publicly accessible files
- Entry point (`index.php`)
- Compiled assets dari Vite build
- Static files (images, fonts, dll)

**config/**: Centralized configuration
- Semua config bisa di-override via environment variables (.env)
- Cached untuk performance di production

## 🎨 UI Components

- **Charts**: Revenue trends, subsegment breakdown, performance metrics dengan dark mode support
- **Modals**: Drill-down detail views dengan responsive design (Month, Subsegment, Company details)
- **Cards**: Summary metrics dengan trend indicators dan interactive tooltips
- **Tables**: Sortable dan filterable data tables
- **Navigation**: Dark blue sidebar navigation dengan breadcrumbs
- **Filters**: Year selection dan revenue sorting dropdowns
- **Tooltips**: Detail popup untuk currency values dan data points

## �️ Database Design

### Entity Relationship Diagram (ERD)

```
┌─────────────────┐          ┌──────────────────┐
│     USERS       │          │    COMPANIES     │
├─────────────────┤          ├──────────────────┤
│ id (PK)         │          │ id (PK)          │
│ name            │          │ nip_nas          │
│ email (unique)  │          │ nama_perusahaan  │
│ password        │          │ subsegment       │
│ role (enum)     │───┐      │ source_data      │
│ created_at      │   │      │ created_at       │
│ updated_at      │   │      │ updated_at       │
└─────────────────┘   │      └──────────────────┘
                      │               │
                      │               │ 1:N
                      │               ▼
                      │      ┌──────────────────┐
                      │      │    REVENUES      │
                      │      ├──────────────────┤
                      │      │ id (PK)          │
                      │      │ company_id (FK)  │
                      │      │ tahun            │
                      │      │ bulan            │
                      │      │ jumlah_revenue   │
                      │      │ target           │
                      │      │ achievement_%    │
                      │      │ group1_id (FK)   │
                      │      │ group2_id (FK)   │
                      │      │ group3_id (FK)   │
                      │      │ group4_id (FK)   │
                      │      │ created_at       │
                      │      │ updated_at       │
                      │      └──────────────────┘
                      │               │
                      │               │ 1:N
                      │               ▼
                      │      ┌──────────────────────┐
                      └─────▶│  REVENUE_UPLOADS     │
                             ├──────────────────────┤
                             │ id (PK)              │
                             │ uploaded_by_id (FK)  │
                             │ filename             │
                             │ tahun                │
                             │ bulan                │
                             │ action (enum)        │
                             │ row_count            │
                             │ file_size            │
                             │ ip_address           │
                             │ user_agent           │
                             │ created_at           │
                             └──────────────────────┘

┌─────────────────────┐      ┌──────────────────┐
│ ACCOUNT_MANAGERS    │      │     REGIONS      │
├─────────────────────┤      ├──────────────────┤
│ id (PK)             │      │ id (PK)          │
│ nama_am             │      │ nama_region      │
│ witel_id (FK)       │──┐   │ kode_region      │
│ created_at          │  │   │ created_at       │
│ updated_at          │  │   │ updated_at       │
└─────────────────────┘  │   └──────────────────┘
                         │            │ 1:N
                         │            ▼
                         │   ┌──────────────────┐
                         └──▶│     WITELS       │
                             ├──────────────────┤
                             │ id (PK)          │
                             │ nama_witel       │
                             │ kode_witel       │
                             │ region_id (FK)   │
                             │ created_at       │
                             │ updated_at       │
                             └──────────────────┘

Product Hierarchy (Denormalized for Performance):
┌──────────┐    1:N    ┌──────────┐    1:N    ┌──────────┐    1:N    ┌──────────┐
│  GROUP1  │────────▶  │  GROUP2  │────────▶  │  GROUP3  │────────▶  │  GROUP4  │
├──────────┤           ├──────────┤           ├──────────┤           ├──────────┤
│ id (PK)  │           │ id (PK)  │           │ id (PK)  │           │ id (PK)  │
│ nama     │           │ nama     │           │ nama     │           │ nama     │
└──────────┘           │ group1_id│           │ group2_id│           │ group3_id│
                       └──────────┘           └──────────┘           └──────────┘
```

### Table Descriptions

#### **users** - User Management & Authentication
Menyimpan data user yang bisa login ke sistem dengan role-based access.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key, auto increment |
| name | VARCHAR(255) | Nama lengkap user |
| email | VARCHAR(255) | Email unique untuk login |
| email_verified_at | TIMESTAMP | Kapan email verified (nullable) |
| password | VARCHAR(255) | Hashed password (bcrypt) |
| role | ENUM('admin','viewer') | User role, default 'viewer' |
| remember_token | VARCHAR(100) | Token untuk "remember me" |
| created_at | TIMESTAMP | Kapan user dibuat |
| updated_at | TIMESTAMP | Kapan terakhir update |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)
- INDEX (role) - untuk quick role filtering

**Relationships:**
- hasMany RevenueUploads (uploaded_by_id)

---

#### **companies** - Master Data Perusahaan
Master data semua perusahaan subsegment yang revenue-nya ditrack.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key, auto increment |
| nip_nas | VARCHAR(50) | Unique identifier perusahaan |
| nama_perusahaan | VARCHAR(255) | Nama perusahaan |
| subsegment | VARCHAR(100) | Kategori subsegment (MSP, EDU, etc) |
| source_data | VARCHAR(100) | Source data tracking |
| created_at | TIMESTAMP | Kapan company dibuat |
| updated_at | TIMESTAMP | Kapan terakhir update |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (nip_nas) - untuk prevent duplicate companies
- INDEX (subsegment) - untuk filtering by subsegment

**Relationships:**
- hasMany Revenues
- belongsToMany AccountManagers (through pivot table)
- belongsToMany Regions (through pivot table)

---

#### **revenues** - Transactional Revenue Data
Data revenue per company per bulan dengan product breakdown.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key, auto increment |
| company_id | BIGINT UNSIGNED | Foreign key ke companies |
| tahun | INT | Tahun revenue (2020-2030) |
| bulan | TINYINT | Bulan (1-12) |
| jumlah_revenue | DECIMAL(20,2) | Total revenue dalam Rupiah |
| target | DECIMAL(20,2) | Target revenue bulan ini |
| achievement_percentage | DECIMAL(5,2) | Persentase achievement (calculated) |
| group1_id | BIGINT UNSIGNED | FK to group1 (Product Line) |
| group2_id | BIGINT UNSIGNED | FK to group2 (Category) |
| group3_id | BIGINT UNSIGNED | FK to group3 (Service Type) |
| group4_id | BIGINT UNSIGNED | FK to group4 (Product Detail) |
| created_at | TIMESTAMP | Kapan data dibuat |
| updated_at | TIMESTAMP | Kapan terakhir update |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
- INDEX (tahun, bulan) - untuk filtering by period (composite index)
- INDEX (company_id, tahun, bulan) - untuk quick company revenue lookup
- FOREIGN KEY (group1_id, group2_id, group3_id, group4_id) - product hierarchy

**Relationships:**
- belongsTo Company
- belongsTo Group1, Group2, Group3, Group4 (product hierarchy)

**Business Rules:**
- UNIQUE constraint pada (company_id, tahun, bulan, group4_id) - prevent duplicate
- achievement_percentage = (jumlah_revenue / target) * 100
- Cascade delete: hapus revenue saat company dihapus

---

#### **revenue_uploads** - Audit Trail & Activity Logs
Complete audit trail untuk semua upload, replace, dan delete operations.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key, auto increment |
| uploaded_by_id | BIGINT UNSIGNED | FK to users - siapa yang action |
| filename | VARCHAR(255) | Original filename (nullable untuk delete) |
| tahun | INT | Year yang affected |
| bulan | TINYINT | Month yang affected (nullable untuk year delete) |
| action | ENUM('UPLOAD','REPLACE','DELETE') | Tipe action |
| row_count | INT | Jumlah rows processed |
| file_size | BIGINT | File size in bytes (nullable) |
| ip_address | VARCHAR(45) | User IP address |
| user_agent | TEXT | Browser user agent |
| created_at | TIMESTAMP | Kapan action dilakukan |
| updated_at | TIMESTAMP | Tidak digunakan (immutable logs) |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (uploaded_by_id) REFERENCES users(id)
- INDEX (action) - untuk filter by action type
- INDEX (created_at) - untuk chronological sorting
- INDEX (tahun, bulan) - untuk tracking specific period

**Relationships:**
- belongsTo User (uploaded_by)

**Business Rules:**
- Immutable records - tidak pernah di-update atau delete
- Chronological sorting (latest first)
- Comprehensive tracking untuk compliance

---

#### **account_managers** - Account Manager Master Data
Data Account Manager yang handle companies.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key, auto increment |
| nama_am | VARCHAR(255) | Nama Account Manager |
| witel_id | BIGINT UNSIGNED | FK to witels |
| created_at | TIMESTAMP | Kapan AM dibuat |
| updated_at | TIMESTAMP | Kapan terakhir update |

**Relationships:**
- belongsTo Witel
- belongsToMany Companies (through pivot)

---

#### **groups1-4** - Product Hierarchy (4 Levels)
Hierarchical product structure untuk revenue breakdown.

**GROUP1 (Product Line)**
```
Examples: PRODUK & JASA, LAYANAN DIGITAL, etc.
```

**GROUP2 (Category)**
```
Examples: FMC, BBE, SMART PLATFORM, etc.
Parent: group1_id
```

**GROUP3 (Service Type)**
```
Examples: INTERNET, VOICE, VIDEO, etc.
Parent: group2_id
```

**GROUP4 (Product Detail)**
```
Examples: INDIHOME, ASTINET, METRO-E, etc.
Parent: group3_id
```

Each table structure:
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| nama | VARCHAR(255) | Product/category name |
| parent_id | BIGINT UNSIGNED | FK to parent level (nullable for group1) |

---

### Database Optimization Strategies

**Indexing:**
- Composite indexes pada frequently queried combinations (company_id + tahun + bulan)
- Foreign key indexes untuk join performance
- Covering indexes untuk reduce table lookups

**Denormalization:**
- Product hierarchy ids stored directly di revenues table
- Achievement percentage calculated dan stored (tidak real-time calculate)

**Partitioning (Future):**
- Partition revenues table by tahun untuk better performance
- Archive old data (> 5 years) ke separate table

**Caching:**
- Dashboard summary metrics cached (1 hour)
- Product hierarchy cached (24 hours - rarely changes)
- Company list cached (6 hours)

**Query Optimization:**
- Use eager loading untuk prevent N+1 queries
- Pagination untuk large result sets
- Select only needed columns (tidak select *)

### Data Integrity Rules

1. **Referential Integrity**: All foreign keys dengan CASCADE on delete untuk maintain consistency
2. **Data Validation**: Application-level validation sebelum insert (Laravel Rules)
3. **Unique Constraints**: Prevent duplicate data (company nip_nas, user email)
4. **Soft Deletes**: Not used - hard delete dengan cascade untuk simplicity
5. **Audit Trail**: Immutable logs di revenue_uploads table

---

## 📱 Panduan Penggunaan Aplikasi

### Untuk Admin

#### 1. Login sebagai Admin
```
URL: http://localhost:8000/login
Email: admin@tws.com
Password: password
```

#### 2. Dashboard Overview
Setelah login, Anda akan diarahkan ke **Revenue Dashboard** yang menampilkan:
- **Summary Cards**: Total revenue YTD, active companies, current month revenue, average revenue
- **Monthly Revenue Chart**: Bar chart dengan target line
- **Subsegment Distribution**: Pie chart per subsegment
- **5-Year Trend**: Historical line chart
- **Top Performers**: Best & worst performing companies

**Interactive Features:**
- **Year Filter**: Select tahun untuk compare data antar tahun
- **Revenue Sorting**: Chronological, Highest, Lowest untuk monthly chart
- **Click to Drill-down**:
  - Click company name → Company Detail Modal
  - Click pie chart segment → Subsegment companies list
  - Click monthly bar → Month detail dengan company list

#### 3. Upload Revenue Data

**Step 1: Akses Menu Data Upload**
```
Sidebar → Data Upload → Revenue
```

**Step 2: Download Template (First Time)**
- Click tombol "Download Template"
- Open Excel file, lihat struktur columns
- Columns required:
  ```
  NIP-NAS | COMPANY NAME | YEAR | MONTH | REVENUE | TARGET | 
  GROUP1 | GROUP2 | GROUP3 | GROUP4
  ```

**Step 3: Prepare Data di Excel**
- Copy data ke template
- Pastikan format correct:
  - NIP-NAS: Must exist di master companies
  - YEAR: 2020-2030
  - MONTH: 1-12
  - REVENUE & TARGET: Numeric (no commas or currency symbols)
  - GROUPS: Must match master data

**Step 4: Upload File**
- Drag & drop file atau click "Browse"
- Wait for validation (green checkmark jika success)
- Check "Preview" table untuk verify data

**Step 5: Konfirmasi Upload**
- Review data summary (rows, months, total revenue)
- Check "Replace Mode" jika want to replace existing data
- Click "Upload" button
- Wait for processing (progress bar)
- Success notification akan muncul

**Upload Modes:**
- **Normal Upload**: Add data untuk bulan baru (error jika data exists)
- **Replace Mode**: Replace existing data dengan konfirmasi
  - Checkbox "Replace existing data"
  - Confirmation dialog muncul
  - Old data dihapus, new data inserted

**Activity Log:**
- Automatic logging setiap upload
- View di tab "Activity Logs"
- Details: filename, date, user, rows, action (UPLOAD/REPLACE)

#### 4. Delete Revenue Data

**Delete Per Month:**
```
Menu: Data Upload → Revenue → Tab "Delete Month"
1. Select Year dropdown
2. Select Month dropdown
3. Preview: Shows how many records will be deleted
4. Click "Delete Month"
5. Confirmation dialog appears
6. Type "DELETE" untuk confirm
7. Data deleted, activity logged
```

**Delete Per Year:**
```
Menu: Data Upload → Revenue → Tab "Delete Year"
1. Select Year dropdown
2. Preview: Shows all months dengan total records
3. Click "Delete Year"
4. Confirmation dialog appears
5. Type "DELETE YEAR" untuk confirm
6. All year data deleted, activity logged for each month
```

**Safety Features:**
- Confirmation dialog untuk prevent accidents
- Preview sebelum delete
- Activity log tetap preserved (immutable)
- Cannot be undone - be careful!

#### 5. Download Files

**Download Template:**
```
Tab "Upload" → Button "Download Template"
- Excel template dengan correct structure
- Sample data included
- Instructions sheet
```

**Download Uploaded Files:**
```
Tab "Activity Logs" → Click filename
- Download original Excel yang di-upload
- Useful untuk reference atau rollback
```

**Download All Files (Year):**
```
Tab "Activity Logs" → Select year → "Download All"
- Zip file containing all Excel dari year tersebut
- Organized by month
```

#### 6. View Performance AM

```
Sidebar → Performance AM
```

**Components:**
- **AM Summary Cards**: Total revenue, companies, achievement
- **Performance Ranking**: Bar chart ranking AM by achievement
- **Distribution Pie**: Distribusi companies per AM
- **Detailed Table**: Complete metrics dengan sorting
  - Columns: AM Name, Total Revenue, Companies Count, Achievement %, Ranking
  - Sortable by clicking column headers
  - Search AM by name

**Drill-down:**
- Click AM name → Detail modal dengan:
  - List companies handled
  - Revenue per company
  - Monthly trend
  - Regional distribution

### Untuk Viewer

#### 1. Login sebagai Viewer
```
URL: http://localhost:8000/login
Email: viewer@tws.com
Password: password
```

#### 2. Akses Dashboard
- Same access dengan admin untuk viewing
- **Revenue Dashboard**: Full access to all charts dan metrics
- **Performance AM**: Full access to rankings dan details
- **Company Detail Modal**: Can open dan explore all data

#### 3. Restrictions
- ❌ Cannot see "Data Upload" menu di sidebar
- ❌ Cannot access `/data-import/*` routes
  - Direct URL access akan redirect ke 403 error page
- ❌ Cannot upload files
- ❌ Cannot delete data
- ✅ Can view, filter, sort, explore all dashboards
- ✅ Can drill-down ke company details
- ✅ Can export data untuk reporting (future feature)

#### 4. Company Detail Modal

**How to Open:**
1. Click company name dari dashboard
2. Click pie chart segment (subsegment drill-down, then click company)
3. Click monthly bar chart → Month detail → Click company row

**Modal Features:**
- **Period Filter**:
  - Year Selector: Choose year
  - Month Selector: Choose month (or uncheck for full year)
  - Full Year Checkbox: Toggle untuk view all months vs single month
  
- **Summary Cards**:
  - Total Revenue (period-specific)
  - Reporting Period dengan month range
  - Average Monthly Revenue
  - Best Performing Month
  
- **Company Info**:
  - NIP-NAS dengan copy button
  - Subsegment
  - Region & Witel
  - Account Managers list
  
- **Revenue Breakdown**:
  - Hierarchical tree: Group1 → Group2 → Group3 → Group4
  - Pie chart visualization
  - Per-month display saat full year selected
  - Example: "INDIHOME (Januari 2026)", "INDIHOME (Februari 2026)"
  
- **Charts**:
  - Monthly trend (line chart)
  - Product distribution (pie chart)
  - Target vs Actual comparison

**Full Year Mode:**
```
Checkbox: [✓] Full Year
Year: 2026
Info: "Menampilkan data agregat: Januari - Desember (12 bulan)"

Revenue Breakdown Tree:
└── PRODUK & JASA (Rp 15.2T)
    ├── FMC
    │   ├── INTERNET
    │   │   ├── INDIHOME (Januari 2026) - Rp 500M
    │   │   ├── INDIHOME (Februari 2026) - Rp 520M
    │   │   └── ... (10 more months)
```

### Common User Flows

#### User Flow 1: Monthly Performance Review
```
1. Login → Dashboard
2. Year Filter → Select current year
3. View Monthly Chart → Identify trends
4. Click specific month bar → See companies that month
5. Click company → Detail modal
6. Review revenue breakdown by product
7. Compare dengan target
8. Identify improvement areas
```

#### User Flow 2: Compare Year-over-Year
```
1. Dashboard → Year Filter → 2024
2. Note metrics (total revenue, achievement, etc)
3. Year Filter → 2025
4. Compare metrics dengan 2024
5. 5-Year Trend Chart → View historical comparison
6. Identify growth/decline patterns
```

#### User Flow 3: Account Manager Performance Review
```
1. Sidebar → Performance AM
2. View Ranking Chart → Identify top/bottom performers
3. Click AM name → Detail modal
4. Review companies handled
5. Analyze revenue distribution
6. Check monthly trend
7. Identify coaching/training needs
```

#### User Flow 4: Upload Monthly Data (Admin)
```
1. Receive Excel data from source
2. Sidebar → Data Upload → Revenue
3. Download Template (if first time)
4. Copy data ke template format
5. Validate data (check NIP-NAS, dates, amounts)
6. Upload file via drag & drop
7. Review preview table
8. Confirm upload
9. Wait for processing
10. Verify success notification
11. Check Activity Logs untuk audit
12. Return to Dashboard → Verify data appears
```

#### User Flow 5: Data Correction (Admin)
```
1. Identify wrong data di dashboard/reports
2. Prepare corrected Excel file
3. Sidebar → Data Upload → Revenue
4. Upload file
5. Check "Replace existing data"
6. Confirm replacement
7. Old data deleted, new data inserted
8. Activity log records "REPLACE" action
9. Verify correction di dashboard
```

### Tips & Best Practices

**Untuk Admin:**
- ✅ Always download template untuk ensure correct format
- ✅ Validate data di Excel sebelum upload (use Excel formulas)
- ✅ Test dengan small dataset first (1-2 months)
- ✅ Review activity logs regularly untuk audit
- ✅ Backup Excel files before replacing data
- ❌ Don't upload data tanpa konfirmasi source
- ❌ Don't delete data without proper approval
- ❌ Don't share admin credentials

**Untuk Viewer:**
- ✅ Use filters untuk focus analysis
- ✅ Drill-down untuk detailed insights
- ✅ Compare periods untuk identify trends
- ✅ Export/screenshot data untuk reports
- ❌ Don't try to access admin URLs directly
- ❌ Don't share login credentials

**Performance Tips:**
- 🚀 Use Year Filter untuk reduce data load
- 🚀 Close modals after viewing (free memory)
- 🚀 Refresh browser jika charts tidak load properly
- 🚀 Use Chrome/Edge untuk best performance (Recharts optimization)

---

## 💡 Key Features Explained

### Smart Currency Formatting
- Nilai < 1,000 Miliar: ditampilkan sebagai "Rp X.XXM"
- Nilai >= 1,000 Miliar: ditampilkan sebagai "Rp X.XXT"
- Hover pada nilai untuk melihat angka lengkap (e.g., "Rp 1,234,567,890,123")

### Revenue Filtering
- **Chronological**: Urutan bulan Januari - Desember
- **Highest First**: Urutan dari revenue tertinggi ke terendah
- **Lowest First**: Urutan dari revenue terendah ke tertinggi
- **Year Filter**: Filter data berdasarkan tahun (2024-2028)

### Dark Mode
- Otomatis detect system preference
- Manual override via settings
- Smooth transitions antar mode
- Optimized untuk readability di semua kondisi

## 🚀 Deployment

```bash
# Production build
npm run build

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache routes dan config
php artisan route:cache
php artisan config:cache
```

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 🎓 Kesimpulan & Pembelajaran

### Pencapaian Proyek

Dashboard TWS berhasil dikembangkan sebagai solusi comprehensive untuk monitoring revenue perusahaan subsegment Telkom dan evaluasi performance Account Manager. Aplikasi ini menggantikan proses manual yang sebelumnya memakan waktu dengan sistem otomatis yang real-time, aman, dan mudah digunakan.

**Key Achievements:**
1. **Centralized Data Management**: Semua revenue data tersimpan terpusat di database dengan struktur yang terorganisir
2. **Real-time Analytics**: Stakeholder dapat melihat metrics dan performance kapan saja tanpa waiting time
3. **Interactive Visualizations**: Charts yang interaktif membuat data lebih mudah dipahami untuk decision making
4. **Role-based Security**: Implementasi admin/viewer roles memastikan data security dan access control
5. **Complete Audit Trail**: Setiap perubahan data ter-track untuk compliance dan accountability
6. **User-friendly Interface**: Interface yang intuitive mengurangi learning curve untuk pengguna

### Dampak Bisnis

**Efisiensi Operasional:**
- **Time Savings**: Reduce report generation time dari hours ke minutes
- **Error Reduction**: Automated validation reduce human error di data entry
- **Accessibility**: 24/7 access untuk stakeholders dari mana saja

**Decision Making:**
- **Faster Insights**: Real-time data enable quick response terhadap trends
- **Better Visibility**: Multi-level drill-down provide deep insights
- **Data-driven**: Metrics dan visualizations support evidence-based decisions

**Compliance & Governance:**
- **Audit Trail**: Complete history untuk regulatory compliance
- **Access Control**: Role-based permissions protect sensitive data
- **Data Integrity**: Validation rules ensure data quality

### Pembelajaran Teknis

#### 1. Full-Stack Development dengan Laravel + React
**Konsep yang Dipelajari:**
- **Inertia.js**: Seamless integration antara Laravel backend dan React frontend tanpa need untuk separate API
- **SSR (Server-Side Rendering)**: Initial page load dirender di server untuk better SEO dan performance
- **TypeScript**: Type safety mengurangi bugs dan improve code maintainability

**Challenges:**
- Learning curve untuk sync antara Laravel routes dan Inertia pages
- Managing shared data antara server dan client
- TypeScript type definitions untuk complex nested objects

**Solutions:**
- Comprehensive type definitions di `types/index.d.ts`
- Centralized data sharing via `HandleInertiaRequests` middleware
- Consistent naming conventions antara backend dan frontend

#### 2. Database Design & Optimization
**Konsep yang Dipelajari:**
- **Relational Database Design**: Proper normalization (3NF) untuk minimize redundancy
- **Indexing Strategy**: Composite indexes untuk optimize complex queries
- **Foreign Key Constraints**: Maintain referential integrity dengan cascade deletes

**Challenges:**
- Balancing normalization dengan query performance
- Product hierarchy (4 levels) causing complex joins
- Large dataset performance (thousands of revenue records)

**Solutions:**
- Denormalization untuk frequently accessed data (product IDs di revenues table)
- Strategic indexing pada frequently queried columns
- Eager loading untuk prevent N+1 query problem
- Query optimization dengan proper `select`, `where`, dan `join` clauses

#### 3. Data Visualization dengan Recharts
**Konsep yang Dipelajari:**
- **Responsive Charts**: Auto-resize based on container width
- **Interactive Features**: Tooltips, click events, drill-downs
- **Color Schemes**: Accessibility-compliant colors untuk light/dark mode
- **Chart Types**: Bar, Line, Pie, Composed charts untuk different data representations

**Challenges:**
- Performance dengan large datasets (100+ data points)
- Responsive behavior pada mobile devices
- Custom tooltip styling dan positioning
- Dark mode color schemes

**Solutions:**
- Data aggregation untuk reduce chart data points
- CSS-based responsive design dengan proper aspect ratios
- Custom tooltip components dengan conditional rendering
- CSS variables untuk theme-aware colors

#### 4. File Handling & Excel Integration
**Konsep yang Dipelajari:**
- **PhpSpreadsheet**: Reading dan writing Excel files di PHP
- **Maatwebsite/Laravel-Excel**: Laravel wrapper untuk PhpSpreadsheet
- **File Uploads**: Handling multipart/form-data dengan validation
- **Storage Management**: Organizing uploaded files dengan Laravel Storage

**Challenges:**
- Memory limits untuk large Excel files (> 5MB)
- Reading Excel with inconsistent formatting
- Preserving original filename untuk audit trail
- Download Excel templates dengan pre-filled data

**Solutions:**
- Chunk reading untuk large files
- Strict validation rules dan error messages
- Storing original filename di database
- Template generation with sample data dan instructions

#### 5. Security Implementation
**Konsep yang Dipelajari:**
- **Authentication**: Laravel Fortify dengan 2FA support
- **Authorization**: Role-based access control (RBAC) dengan middleware
- **CSRF Protection**: Token validation untuk all state-changing operations
- **Input Validation**: Server-side validation untuk all user inputs

**Challenges:**
- Protecting routes dari unauthorized access
- Frontend conditional rendering based on role
- CSRF token management dengan Inertia
- Session security dan cookie handling

**Solutions:**
- Custom `CheckRole` middleware untuk route protection
- Shared auth data via Inertia untuk frontend
- Axios interceptors untuk automatic CSRF token inclusion
- Environment-based session configuration

#### 6. UI/UX Design Principles
**Konsep yang Dipelajari:**
- **Component-based Design**: Reusable UI components dengan Shadcn/ui
- **Responsive Design**: Mobile-first approach dengan Tailwind CSS
- **Accessibility**: WCAG-compliant colors, keyboard navigation, ARIA labels
- **Dark Mode**: System preference detection dan manual override

**Challenges:**
- Consistent design language across all pages
- Mobile responsiveness untuk complex charts
- Dark mode color schemes yang readable
- Loading states dan error handling

**Solutions:**
- Design system dengan Tailwind config
- Responsive chart libraries (Recharts)
- CSS variables untuk theme colors
- Skeleton loaders dan error boundaries

### Soft Skills yang Dikembangkan

**1. Problem Solving:**
- Menganalisis requirements yang complex
- Breaking down large problems menjadi smaller tasks
- Researching solutions untuk unfamiliar technologies
- Debugging dengan systematic approach

**2. Time Management:**
- Planning dan prioritizing features
- Setting realistic deadlines
- Balancing perfectionism dengan pragmatism
- Managing technical debt

**3. Communication:**
- Documenting code dan architecture decisions
- Writing clear commit messages
- Creating user documentation
- Explaining technical concepts ke non-technical stakeholders

**4. Self-Learning:**
- Reading official documentation (Laravel, React, TypeScript)
- Following tutorials dan online courses
- Experimenting dengan new technologies
- Learning from errors dan mistakes

### Kendala & Solusi

**Kendala 1: Performance dengan Large Dataset**
- **Problem**: Dashboard loading slow saat display 1000+ companies
- **Root Cause**: Loading all data at once, inefficient queries
- **Solution**: 
  - Pagination untuk tables
  - Lazy loading untuk modals
  - Query optimization dengan eager loading
  - Caching frequently accessed data

**Kendala 2: CSRF Token Mismatch After Login**
- **Problem**: First upload after login fails dengan CSRF error
- **Root Cause**: Session not refreshed properly after authentication
- **Solution**:
  - Axios interceptor untuk auto-include CSRF token
  - Session regeneration after login
  - Environment configuration untuk proper cookie handling

**Kendala 3: Complex Product Hierarchy Queries**
- **Problem**: 4-level product hierarchy causing slow queries
- **Root Cause**: Multiple joins untuk get full product path
- **Solution**:
  - Denormalization: Store all group IDs di revenues table
  - Eager loading dengan `with()` untuk reduce queries
  - Strategic indexing pada foreign keys

**Kendala 4: Excel Upload Validation**
- **Problem**: Users upload Excel dengan wrong format
- **Root Cause**: No clear template, inconsistent data format
- **Solution**:
  - Provide downloadable template dengan sample data
  - Server-side validation dengan clear error messages
  - Preview table before confirm upload
  - Instructions sheet di template Excel

### Rekomendasi Pengembangan Lebih Lanjut

**Short Term (1-3 bulan):**
1. **Export Features**: 
   - Export dashboard data ke Excel/PDF
   - Custom report generation dengan selected metrics
   - Scheduled email reports

2. **Advanced Filters**:
   - Multi-select filters (regions, subsegments)
   - Date range picker untuk custom periods
   - Saved filter presets

3. **Notifications**:
   - Email notifications untuk upload success/failure
   - Achievement alerts (below target)
   - Monthly summary reports

**Medium Term (3-6 bulan):**
1. **Predictive Analytics**:
   - Revenue forecasting dengan historical data
   - Trend analysis dengan machine learning
   - Anomaly detection

2. **Mobile App**:
   - Native mobile app (React Native)
   - Push notifications
   - Offline mode dengan sync

3. **API Development**:
   - RESTful API untuk third-party integrations
   - API documentation dengan Swagger
   - Rate limiting dan authentication

**Long Term (6-12 bulan):**
1. **Business Intelligence**:
   - Advanced analytics dengan Power BI integration
   - Custom dashboards untuk different roles
   - Real-time streaming data

2. **Automation**:
   - Automatic data import dari source systems
   - Scheduled data refresh
   - Auto-generated insights

3. **Scalability**:
   - Microservices architecture untuk better scaling
   - Redis caching untuk improved performance
   - Load balancing untuk high availability

### Refleksi Pribadi

Pengembangan Dashboard TWS merupakan pengalaman pembelajaran yang sangat berharga dalam full-stack web development. Proyek ini tidak hanya meningkatkan technical skills, tapi juga soft skills seperti problem solving, time management, dan communication.

**Key Takeaways:**
- **Think Before Code**: Planning dan design yang matang mengurangi rework
- **Documentation Matters**: Good documentation save time untuk future maintenance
- **User First**: Selalu prioritize user experience di setiap design decision
- **Iterate Fast**: Better to have working MVP than perfect vaporware
- **Learn Continuously**: Technology terus berkembang, harus terus belajar

Aplikasi ini telah membuktikan value-nya dengan successfully menggantikan proses manual dan memberikan insights yang valuable untuk decision making. Kedepannya, aplikasi ini dapat terus dikembangkan dengan fitur-fitur advanced untuk memberikan value yang lebih besar kepada organisasi.

---

## 🤝 Contributing

Contributions are welcome! Jika Anda ingin berkontribusi ke proyek ini:

1. Fork repository ini
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'feat: Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

**Contribution Guidelines:**
- Follow existing code style (PSR-12 untuk PHP, ESLint untuk TypeScript)
- Write clear commit messages (use conventional commits)
- Add tests untuk new features
- Update documentation jika needed
- Be respectful dan constructive di code reviews

---

## 📝 License

This project is licensed under the MIT License.

## 👨‍💻 Author

**Ferdinand TJ**
- GitHub: [@FerdinandTJ](https://github.com/FerdinandTJ)

---

<p align="center">Made with ❤️ for TWS Division - Telkom Indonesia</p>