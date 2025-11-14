# Custom YTD Comparison Feature Guide

## Overview
Fitur Custom YTD (Year-to-Date) Comparison memungkinkan pengguna untuk membandingkan revenue YTD antara dua periode yang berbeda secara custom/fleksibel.

## Lokasi Fitur
- **Dashboard Page**: Tombol "Custom YTD Comparison" di dalam Filter Card (di bawah dropdown Year)
- **Button Style**: Gradient red dengan icon chart bar
- **Modal**: YtdComparisonModal yang terbuka saat button diklik

## Cara Penggunaan

### 1. Membuka Modal
- Buka halaman Dashboard
- Pada Filter Card (card pertama di atas), klik tombol **"Custom YTD Comparison"**

### 2. Memilih Periode untuk Dibandingkan
Modal menampilkan dua kolom:

#### Current Period (YTD) - Kiri
- **Year**: Pilih tahun untuk periode pertama (contoh: 2024)
- **Month**: Pilih bulan hingga mana YTD dihitung (contoh: November)
- Contoh: YTD 2024 (Januari - November) = total revenue dari Jan sampai Nov 2024

#### Comparison Period (PYTD) - Kanan
- **Year**: Pilih tahun untuk periode pembanding (contoh: 2023)
- **Month**: Pilih bulan hingga mana YTD dihitung (contoh: November)
- Contoh: YTD 2023 (Januari - November) = total revenue dari Jan sampai Nov 2023

### 3. Melihat Hasil Perbandingan
Setelah klik tombol **"Compare"**, hasil akan menampilkan:

#### Three-Card Summary
1. **Current YTD**: Total revenue periode current yang dipilih (warna biru)
2. **Growth**: Persentase pertumbuhan dengan indikator warna:
   - Hijau (🟢): Pertumbuhan positif
   - Merah (🔴): Pertumbuhan negatif
3. **Previous YTD**: Total revenue periode comparison (warna orange)

#### Detailed Breakdown
- Growth Amount: Selisih nominal antara kedua periode
- Period Comparison: Detail periode yang dibandingkan dengan format bulan Indonesia
- Contoh: "Januari - November 2024 vs Januari - November 2023"

## Technical Implementation

### Backend (Laravel)

#### 1. Service Layer - `RevenueAnalyticsService.php`
```php
public function getCustomYtdComparison(
    int $currentYear, 
    int $currentMonth, 
    int $previousYear, 
    int $previousMonth
): array
```

**Function**: Menghitung YTD dengan menjumlahkan `total_revenue` dari tabel `revenues` untuk:
- Current: `WHERE tahun = $currentYear AND bulan <= $currentMonth`
- Previous: `WHERE tahun = $previousYear AND bulan <= $previousMonth`

**Returns**:
- `current_ytd`: Total revenue current period
- `previous_ytd`: Total revenue comparison period
- `growth_percentage`: Persentase pertumbuhan
- `growth_amount`: Selisih nominal
- `current_ytd_formatted`: Format currency (Rp)
- `previous_ytd_formatted`: Format currency (Rp)
- `current_month_name`: Nama bulan dalam bahasa Indonesia
- `previous_month_name`: Nama bulan dalam bahasa Indonesia

#### 2. Controller - `DashboardController.php`
```php
public function getCustomYtdComparison(Request $request)
```

**Validation**:
- `current_year`: required, integer, min: 2020, max: 2030
- `current_month`: required, integer, min: 1, max: 12
- `previous_year`: required, integer, min: 2020, max: 2030
- `previous_month`: required, integer, min: 1, max: 12

**Route**: `GET /api/dashboard/ytd-comparison-custom`

### Frontend (React + TypeScript)

#### 1. Modal Component - `YtdComparisonModal.tsx`

**Props**:
```typescript
interface YtdComparisonModalProps {
    isOpen: boolean;
    onClose: () => void;
}
```

**State Management**:
- Input states: currentYearInput, currentMonthInput, previousYearInput, previousMonthInput
- Result state: comparisonData (YtdComparisonData | null)
- UI states: loading, error

**Features**:
- Year dropdown dengan range 10 tahun (current year ± 5 years)
- Month dropdown dengan nama bulan Indonesia
- Default values: Current year/month vs Previous year/same month
- Loading indicator saat fetching data
- Error handling dengan pesan yang jelas
- Color coding untuk pertumbuhan (green/red)

#### 2. Dashboard Integration - `Dashboard.tsx`

**Changes**:
1. Import: `import YtdComparisonModal from '@/components/modals/YtdComparisonModal';`
2. State: `const [ytdModalOpen, setYtdModalOpen] = useState(false);`
3. Button di Filter Card: Opens modal dengan `onClick={() => setYtdModalOpen(true)}`
4. Modal render: `<YtdComparisonModal isOpen={ytdModalOpen} onClose={() => setYtdModalOpen(false)} />`

## Use Cases

### Case 1: Membandingkan YTD Tahun Berjalan vs Tahun Lalu (Same Period)
- Current: 2024, November (Jan-Nov 2024)
- Previous: 2023, November (Jan-Nov 2023)
- **Purpose**: Melihat growth year-over-year untuk periode yang sama

### Case 2: Membandingkan YTD Quarter (Q1 vs Q1)
- Current: 2024, Maret (Jan-Mar 2024 / Q1)
- Previous: 2023, Maret (Jan-Mar 2023 / Q1)
- **Purpose**: Membandingkan performa quarter yang sama

### Case 3: Membandingkan YTD H1 vs H2
- Current: 2024, Juni (Jan-Jun 2024 / H1)
- Previous: 2024, Desember (Jan-Dec 2023 / Full Year)
- **Purpose**: Melihat apakah H1 2024 lebih baik dari full year 2023

### Case 4: Custom Arbitrary Comparison
- Current: 2024, Agustus
- Previous: 2023, April
- **Purpose**: Fleksibilitas penuh untuk analisis custom

## Data Source
- **Table**: `revenues`
- **Columns Used**:
  - `tahun`: Year of revenue record
  - `bulan`: Month of revenue record (1-12)
  - `total_revenue`: Actual revenue amount

## Calculations

### YTD Formula
```
YTD = SUM(total_revenue) WHERE tahun = [year] AND bulan <= [month]
```

### Growth Percentage
```
growth_percentage = ((current_ytd - previous_ytd) / previous_ytd) × 100
```

Jika `previous_ytd = 0`, growth_percentage dikembalikan sebagai 0 untuk menghindari division by zero.

### Growth Amount
```
growth_amount = current_ytd - previous_ytd
```

## UI/UX Features

### Visual Indicators
- **Blue accent**: Current period data
- **Orange accent**: Comparison period data
- **Green**: Positive growth (↑)
- **Red**: Negative growth (↓)

### Responsive Design
- Two-column layout pada desktop
- Responsive pada mobile devices
- Dark mode support

### Loading States
- Spinner indicator saat fetching data
- Button disabled saat loading

### Error Handling
- Validation error messages
- API error messages
- Network error handling

## Performance Considerations
- Data fetching menggunakan SUM() aggregation di database level (efficient)
- Tidak ada N+1 query issues
- Results di-cache di frontend state (tidak re-fetch kecuali input berubah)

## Future Enhancements (Optional)
- [ ] Export hasil comparison ke PDF/Excel
- [ ] Save comparison presets untuk quick access
- [ ] Chart visualization untuk hasil comparison
- [ ] Multi-period comparison (compare 3+ periods)
- [ ] Auto-suggest common comparison scenarios

## Testing Checklist
- [ ] Modal opens when button clicked
- [ ] Default values populated correctly
- [ ] Year/month dropdowns work properly
- [ ] Compare button triggers API call
- [ ] Loading indicator shows during fetch
- [ ] Results display correctly with proper formatting
- [ ] Growth percentage calculated correctly
- [ ] Positive/negative growth colors correct
- [ ] Error messages display for invalid inputs
- [ ] Modal closes properly
- [ ] Works in light and dark mode
- [ ] Responsive on mobile devices

---

**Created**: December 2024  
**Branch**: `fitur/update/filter-ytd`  
**Related Files**:
- Backend: `app/Services/RevenueAnalyticsService.php`, `app/Http/Controllers/DashboardController.php`
- Frontend: `resources/js/components/modals/YtdComparisonModal.tsx`, `resources/js/pages/Dashboard.tsx`
- Route: `routes/web.php`
