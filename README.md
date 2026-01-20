# Dashboard TWS - Telkom Revenue Analytics

Dashboard analitik revenue untuk monitoring performa perusahaan subsegment Telkom dengan visualisasi data yang interaktif, dan role-based access control.

## 🚀 Features

### 📊 Core Analytics
- **Revenue Dashboard**: Visualisasi revenue bulanan, tahunan, dan YTD comparison dengan drill-down capability
- **Target vs Actual**: Real-time monitoring pencapaian target revenue per bulan dengan achievement percentage
- **Company Management**: Detail revenue per perusahaan dengan subsegment breakdown dan regional analysis
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

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x + PHP 8.2
- **Frontend**: React 18 + TypeScript + Inertia.js
- **Database**: MySQL
- **Styling**: Tailwind CSS + Radix UI
- **Charts**: Recharts
- **Build Tool**: Vite
- **Authentication**: Laravel Fortify

## 📋 Requirements

- PHP >= 8.2
- Node.js >= 18
- MySQL >= 8.0
- Composer
- NPM/Yarn

## ⚡ Quick Start

1. **Clone repository**
   ```bash
   git clone https://github.com/FerdinandTJ/MagangTelkom-Dashboard.git
   cd MagangTelkom-Dashboard
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start development server**
   ```bash
   php artisan serve
   npm run dev
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

## 🔧 Development

```bash
# Development mode
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

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/     # API & Page Controllers
│   ├── Models/              # Eloquent Models
│   └── Services/            # Business Logic Services
├── resources/
│   ├── js/
│   │   ├── components/      # React Components
│   │   ├── pages/          # Inertia Pages
│   │   └── types/          # TypeScript Types
│   └── css/                # Tailwind Styles
├── database/
│   ├── migrations/         # Database Migrations
│   └── seeders/           # Sample Data
└── routes/                # API & Web Routes
```

## 🎨 UI Components

- **Charts**: Revenue trends, subsegment breakdown, performance metrics dengan dark mode support
- **Modals**: Drill-down detail views dengan responsive design (Month, Subsegment, Company details)
- **Cards**: Summary metrics dengan trend indicators dan interactive tooltips
- **Tables**: Sortable dan filterable data tables
- **Navigation**: Dark blue sidebar navigation dengan breadcrumbs
- **Filters**: Year selection dan revenue sorting dropdowns
- **Tooltips**: Detail popup untuk currency values dan data points

## 📈 Data Models

- **Companies**: Master data perusahaan dengan subsegment dan source_data
- **Revenue**: Monthly revenue data per company dengan target tracking
- **Users**: Authentication dan user management dengan 2FA support

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

## 📝 License

This project is licensed under the MIT License.

## 👨‍💻 Author

**Ferdinand TJ**
- GitHub: [@FerdinandTJ](https://github.com/FerdinandTJ)

---

<p align="center">Made with ❤️ for TWS Division - Telkom Indonesia</p>