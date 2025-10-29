# Dashboard TWS - Telkom Revenue Analytics

Dashboard analitik revenue untuk monitoring performa perusahaan subsegment Telkom dengan visualisasi data yang interaktif dan real-time.

## 🚀 Features

- **📊 Revenue Analytics**: Visualisasi revenue bulanan, tahunan, dan YTD comparison
- **🎯 Target vs Actual**: Monitoring pencapaian target revenue per bulan
- **🏢 Company Management**: Detail revenue per perusahaan dengan subsegment breakdown
- **📈 Interactive Charts**: Drill-down charts untuk analisis mendalam dengan tooltip detail
- **👨‍💼 Performance AM**: Dashboard khusus untuk AM performance
- **🔍 Responsive Design**: Optimized untuk desktop dan mobile
- **🌓 Dark Mode**: Full dark mode support dengan system preference detection
- **💰 Smart Currency Format**: Automatic M (Miliar) / T (Triliun) formatting untuk large numbers
- **🔒 Two-Factor Authentication**: Enhanced security dengan 2FA support
- **📅 Year Filtering**: Filter dan compare revenue data by year
- **🔄 Revenue Sorting**: Sort monthly revenue by chronological, highest, or lowest

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

### Main Dashboard
- **Summary Cards**: Total Revenue YTD dengan detail tooltip, Active Companies, Current Month, Average per Company
- **Monthly Revenue Trend**: Interactive bar chart dengan target vs actual dan revenue sorting (chronological/highest/lowest)
- **Subsegment Breakdown**: Pie chart revenue by subsegment dengan company details
- **5-Year Trend**: Historical revenue analysis dengan year filter
- **Top Performers**: 1 best dan 1 worst performing company by revenue
- **Currency Display**: Smart formatting - M (Miliar) untuk < 1000B, T (Triliun) untuk >= 1000B
- **Interactive Tooltips**: Hover untuk melihat detail lengkap revenue tanpa singkatan

### Performance AM
- **AM Metrics**: Key performance indicators untuk Account Manager
- **Performance Ranking**: Bar chart ranking AM berdasarkan achievement
- **Account Distribution**: Pie chart distribusi account per subsegment
- **Detailed Table**: Performance metrics dengan sorting dan filtering

### Dark Mode
- **System Preference Detection**: Automatic dark mode berdasarkan sistem
- **Manual Toggle**: Switch between light/dark/system mode
- **Full Component Support**: Semua UI components support dark mode
- **Persistent Setting**: Dark mode preference tersimpan di localStorage

### Authentication
- **Login/Register**: Full authentication dengan dark mode support
- **Two-Factor Authentication**: Enhanced security dengan 2FA
- **Recovery Codes**: Backup access codes untuk 2FA
- **Session Management**: Secure session handling

## 🔧 Development

```bash
# Development mode
composer run dev

# Build for production
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