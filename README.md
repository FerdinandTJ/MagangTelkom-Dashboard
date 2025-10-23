# Dashboard TWS - Telkom Revenue Analytics

Dashboard analitik revenue untuk monitoring performa perusahaan subsegment Telkom dengan visualisasi data yang interaktif dan real-time.

## 🚀 Features

- **📊 Revenue Analytics**: Visualisasi revenue bulanan, tahunan, dan YTD comparison
- **🎯 Target vs Actual**: Monitoring pencapaian target revenue per bulan
- **🏢 Company Management**: Detail revenue per perusahaan dengan subsegment breakdown
- **📈 Interactive Charts**: Drill-down charts untuk analisis mendalam
- **👨‍💼 Performance AM**: Dashboard khusus untuk AM performance
- **🔍 Responsive Design**: Optimized untuk desktop dan mobile

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
- **Summary Cards**: Total Revenue YTD, Active Companies, Current Month, Average per Company
- **Monthly Revenue Trend**: Interactive bar chart dengan target vs actual
- **Subsegment Breakdown**: Pie chart revenue by subsegment
- **5-Year Trend**: Historical revenue analysis
- **Top Performers**: Ranking perusahaan dengan revenue tertinggi

### Performance AM
- **AM Metrics**: Key performance indicators untuk Account Manager
- **Performance Ranking**: Bar chart ranking AM berdasarkan achievement
- **Account Distribution**: Pie chart distribusi account per subsegment
- **Detailed Table**: Performance metrics dengan sorting dan filtering

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

- **Charts**: Revenue trends, subsegment breakdown, performance metrics
- **Modals**: Drill-down detail views dengan responsive design
- **Cards**: Summary metrics dengan trend indicators
- **Tables**: Sortable dan filterable data tables
- **Navigation**: Sidebar navigation dengan breadcrumbs

## 📈 Data Models

- **Companies**: Master data perusahaan dengan subsegment
- **Revenue**: Monthly revenue data per company
- **Users**: Authentication dan user management

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

<p align="center">Made with ❤️ for TWS Division - Telkom Indonesia,</p>