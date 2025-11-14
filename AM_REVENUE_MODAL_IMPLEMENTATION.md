# AM Revenue Detail Modal Implementation

## Overview
Implementasi modal detail untuk menampilkan breakdown revenue Account Manager ketika user mengklik bar di chart "Target Revenue AM" pada halaman Performance AM.

## Tanggal Implementasi
14 Januari 2025

## Fitur yang Diimplementasikan

### 1. **Backend API Endpoint**
**File**: `app/Http/Controllers/DashboardController.php`

**Method Baru**: `getAMRevenueDetails()`
- **Lines**: 663-795
- **Endpoint**: `GET /api/dashboard/am-revenue-details`
- **Parameters**:
  - `am_nik` (required, string): NIK dari Account Manager
  - `year` (required, integer): Tahun periode
  - `quartal` (required, string): Quartal (Q1, Q2, Q3, Q4)

**Response Format**:
```json
{
    "success": true,
    "data": {
        "am_name": "Nama Account Manager",
        "total_target_revenue": 1234567890,
        "formatted_total_revenue": "1.23 M",
        "total_companies": 5,
        "period_display": "2024 - Q1",
        "witel_distribution": [
            {
                "witel_name": "WITEL JAKARTA BARAT",
                "company_count": 3,
                "percentage": 60.0
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

**Query Logic**:
1. Mencari Account Manager berdasarkan NIK
2. Mencari lini_waktu yang sesuai dengan AM, tahun, dan quartal
3. Join melalui lini_waktu_target ke target_account_m
4. Join ke account_manager_company untuk mendapatkan company assignment
5. Join ke companies dan witels untuk detail company
6. Group companies berdasarkan witel untuk pie chart distribution
7. Calculate total target revenue dan persentase distribusi

**Perubahan pada Method Existing**: `getAMRevenueRanking()`
- **Line 226**: Menambahkan field `'nik' => $item->nik` pada return array
- Diperlukan agar bar chart memiliki data NIK untuk onClick handler

### 2. **Frontend Modal Component**
**File**: `resources/js/components/modals/AMRevenueDetailModal.tsx`

**Komponen Baru**: 338 baris
**Dependencies**:
- Dialog component dari `@/components/ui/dialog`
- Recharts (PieChart, Pie, Cell, Tooltip, Legend)
- Axios untuk API call
- Lucide React icons (User, Target, Building2, Calendar, Eye, X)

**Struktur Modal**:

#### Header Section
- Icon User dengan gradient background
- Judul dinamis: nama Account Manager
- Close button (X)

#### Summary Cards (3 Cards)
1. **Target Revenue** (Blue gradient)
   - Icon: Target
   - Menampilkan formatted total target revenue
   
2. **Total Companies** (Green gradient)
   - Icon: Building2
   - Menampilkan jumlah companies yang di-assign
   
3. **Period** (Purple gradient)
   - Icon: Calendar
   - Menampilkan tahun dan quartal

#### Pie Chart Section
- **Judul**: "Company Distribution by Witel"
- **Data**: Distribusi companies berdasarkan witel
- **Colors**: Array 8 warna (#8b5cf6, #ef4444, #f97316, #eab308, #22c55e, #3b82f6, #6366f1, #ec4899)
- **Label**: Witel name dengan persentase

#### Table Section
- **Judul**: "Companies Detail"
- **Columns**:
  1. Company (nama perusahaan)
  2. NIP NAS (6 digit ID)
  3. Witel (lokasi witel)
  4. Target Revenue (formatted currency)
  5. Action (Detail button - placeholder untuk future enhancement)
- **Features**:
  - Sticky header
  - Scrollable body (max-height: 320px)
  - Hover effect pada rows
  - Dark mode support

#### State Management
- `isOpen`: Boolean untuk control visibility modal
- `data`: AMRevenueDetailData | null untuk menyimpan data dari API
- `isLoading`: Boolean untuk loading state
- `error`: String | null untuk error handling

#### API Integration
**Function**: `fetchAMDetails()`
- Dipanggil dalam `useEffect` ketika modal dibuka dan amNik tersedia
- Endpoint: `/api/dashboard/am-revenue-details`
- Query params: `am_nik`, `year`, `quartal`
- Error handling dengan try-catch dan console.error

### 3. **Frontend Integration - PerformanceAm Page**
**File**: `resources/js/pages/PerformanceAm.tsx`

**Perubahan**:

#### Import Statement (Line 9)
```tsx
import AMRevenueDetailModal from '@/components/modals/AMRevenueDetailModal';
```

#### Interface Update (Line 20-27)
Menambahkan field `nik` pada `amRevenueRanking` array:
```tsx
amRevenueRanking: Array<{
    nik: string;  // ADDED
    am_name: string;
    region_code: string;
    t_revenue: number;
    formatted_revenue: string;
}>;
```

#### State Management (Line 63-64)
Menambahkan 2 state baru:
```tsx
const [isModalOpen, setIsModalOpen] = useState(false);
const [selectedAMNik, setSelectedAMNik] = useState<string | null>(null);
```

#### Handler Function (Line 105-110)
```tsx
const handleBarClick = (data: any) => {
    if (data && data.nik) {
        setSelectedAMNik(data.nik);
        setIsModalOpen(true);
    }
};
```

#### Bar Chart Update (Line 272-275)
Menambahkan onClick handler dan cursor style:
```tsx
<Bar 
    dataKey="t_revenue" 
    fill="#dc2626" 
    radius={[4, 4, 0, 0]} 
    onClick={handleBarClick}  // ADDED
    cursor="pointer"           // ADDED
/>
```

#### Modal Component (Line 390-396)
Menambahkan modal component sebelum closing AppSidebarLayout:
```tsx
<AMRevenueDetailModal
    isOpen={isModalOpen}
    onClose={() => setIsModalOpen(false)}
    amNik={selectedAMNik}
    year={selectedYear}
    quartal={selectedQuartal}
/>
```

### 4. **Route Registration**
**File**: `routes/web.php`

**Perubahan** (Line 26):
Menambahkan route baru dalam `Route::prefix('api/dashboard')->group()`:
```php
Route::get('am-revenue-details', [DashboardController::class, 'getAMRevenueDetails'])
    ->name('api.dashboard.am-revenue-details');
```

## Alur Kerja (Flow)

1. **User Action**: User membuka halaman Performance AM (`/performance-am`)
2. **Data Display**: Bar chart "Target Revenue AM" menampilkan target revenue per AM
3. **Click Event**: User mengklik salah satu bar di chart
4. **Handler Triggered**: `handleBarClick()` dipanggil dengan data bar yang diklik
5. **State Update**: 
   - `selectedAMNik` diset dengan NIK dari data bar
   - `isModalOpen` diset menjadi `true`
6. **Modal Render**: Modal component di-render
7. **API Call**: `useEffect` di modal trigger `fetchAMDetails()`
8. **Backend Query**: 
   - Validasi parameters (am_nik, year, quartal)
   - Query database dengan complex joins (6 tables)
   - Group dan calculate data
   - Format currency values
9. **Response**: Backend return JSON dengan structure yang telah didefinisikan
10. **Data Display**: Modal menampilkan:
    - Summary cards dengan metrics
    - Pie chart distribusi companies by witel
    - Table detail semua companies
11. **Close Modal**: User klik tombol X atau area luar modal, `isModalOpen` diset `false`

## Database Schema yang Terlibat

### Tables Used
1. **account_managers**: Data Account Manager (nik, nama)
2. **lini_waktu**: Timeline records (tahun, quartal, nik_am)
3. **lini_waktu_target**: Pivot table (lini_waktu_id, target_id)
4. **target_account_m**: Target revenue data (t_revenue, account_manager_company_id)
5. **account_manager_company**: AM-Company assignments (nik_am, nip_nas)
6. **companies**: Company details (nama_perusahaan, nip_nas, idwitels)
7. **witels**: Witel information (idwitels, nama_witel)

### Relasi yang Digunakan
- `account_managers.nik` → `lini_waktu.nik_am` (One-to-Many)
- `lini_waktu.id` → `lini_waktu_target.lini_waktu_id` (One-to-One)
- `lini_waktu_target.target_id` → `target_account_m.id` (One-to-One)
- `target_account_m.account_manager_company_id` → `account_manager_company.id` (Many-to-One)
- `account_manager_company.nip_nas` → `companies.nip_nas` (One-to-One)
- `companies.idwitels` → `witels.idwitels` (Many-to-One)

## Testing Checklist

### Backend Testing
- [ ] Test API endpoint dengan valid parameters
- [ ] Test dengan invalid AM NIK (harus return error/empty)
- [ ] Test dengan periode yang tidak ada data (harus return empty arrays)
- [ ] Test format currency dengan berbagai nilai (millions, billions, trillions)
- [ ] Verify query efficiency dengan EXPLAIN
- [ ] Test dengan AM yang tidak punya company assignments

### Frontend Testing
- [ ] Test klik bar chart di Performance AM page
- [ ] Verify modal terbuka dengan data yang benar
- [ ] Test loading state saat fetch data
- [ ] Test error handling jika API gagal
- [ ] Test close modal (button X dan backdrop click)
- [ ] Test responsiveness modal di berbagai screen sizes
- [ ] Verify pie chart render dengan benar
- [ ] Verify table scrollable dengan banyak data
- [ ] Test dark mode compatibility
- [ ] Test dengan berbagai tahun dan quartal selection

### Integration Testing
- [ ] Test flow lengkap: click bar → modal open → data display → close modal
- [ ] Test multiple clicks pada berbagai bars (verify data berubah sesuai)
- [ ] Test filter region pada chart (verify bars berubah, click masih works)
- [ ] Test change year/quartal (verify bar data update, modal data update)
- [ ] Performance testing dengan banyak AMs (scroll horizontal chart)

## Known Limitations

1. **Detail Button**: Button "Detail" di table companies masih placeholder, belum ada functionality
2. **No Cache**: API call dilakukan setiap kali modal dibuka, tidak ada caching mechanism
3. **Loading State**: Loading spinner minimal, bisa ditambahkan skeleton loading
4. **Error Display**: Error hanya di-log ke console, tidak ada user-friendly error message
5. **AM with No Companies**: AM tanpa company assignments akan show modal dengan 0 total revenue dan empty table

## Future Enhancements

### 1. **Company Detail Drill-down**
- Implement onClick handler untuk Detail button di table
- Buka `CompanyDetailModal` untuk menampilkan full revenue history company tersebut
- Create nested modal atau close AM modal dulu sebelum open company modal

### 2. **Data Caching**
- Implement React Query atau SWR untuk caching API responses
- Reduce unnecessary API calls untuk same AM + period combination
- Add cache invalidation strategy

### 3. **Enhanced Loading State**
- Replace simple spinner dengan skeleton loading
- Show skeleton untuk cards, pie chart placeholder, table rows
- Improve UX dengan progressive loading

### 4. **Error Handling Enhancement**
- Add user-friendly error toast notifications
- Implement retry mechanism untuk failed API calls
- Add fallback UI untuk error states

### 5. **Export Functionality**
- Add Export button di modal footer
- Allow user to export table data to Excel/CSV
- Include summary metrics dan charts dalam export

### 6. **Comparison Feature**
- Allow user to select multiple AMs untuk comparison
- Side-by-side comparison modal
- Compare metrics, distributions, dan company counts

### 7. **Time Series View**
- Add tab/toggle untuk melihat historical data AM
- Line chart showing target revenue trend across quarters
- Year-over-year comparison

## Build & Deployment

### Development
```bash
npm run dev
```

### Production Build
```bash
npm run build
```

### Laravel Artisan Commands
```bash
# Check routes registered
php artisan route:list --path=am-revenue

# Start development server
php artisan serve

# Clear caches if needed
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Files Modified/Created Summary

### Created Files (1)
1. `resources/js/components/modals/AMRevenueDetailModal.tsx` - 338 lines

### Modified Files (3)
1. `app/Http/Controllers/DashboardController.php`
   - Added `getAMRevenueDetails()` method (133 lines)
   - Modified `getAMRevenueRanking()` to include `nik` field (1 line)

2. `resources/js/pages/PerformanceAm.tsx`
   - Added import for AMRevenueDetailModal (1 line)
   - Updated interface to include `nik` in amRevenueRanking (1 line)
   - Added state management (2 lines)
   - Added click handler function (6 lines)
   - Added onClick and cursor props to Bar component (2 lines)
   - Added modal component at bottom (7 lines)

3. `routes/web.php`
   - Added API route for am-revenue-details (1 line)

### Total Lines Changed
- Created: 338 lines
- Modified: ~153 lines
- **Total Impact**: ~491 lines of code

## Screenshots Placeholders
(Add screenshots ketika testing selesai)

1. Performance AM page dengan bar chart
2. Modal opened dengan sample data
3. Pie chart distribution view
4. Table scrolling with many companies
5. Dark mode modal view
6. Loading state
7. Error state (if implemented)

## Conclusion

Implementasi modal AM Revenue Detail telah berhasil dilakukan dengan complete functionality. Modal menampilkan breakdown detail revenue Account Manager yang dipilih, termasuk summary metrics, distribusi companies by witel dalam pie chart, dan table lengkap dengan detail semua companies yang di-assign.

Feature ini meningkatkan user experience pada halaman Performance AM dengan memberikan drill-down capability untuk melihat detail behind the numbers di bar chart. Implementasi mengikuti pattern yang sama dengan Dashboard page (CompanyDetailModal) untuk konsistensi UI/UX.

Next steps adalah melakukan comprehensive testing dan implement future enhancements sesuai prioritas bisnis.
