# AM Performance Detail API Documentation

## Overview
API endpoint untuk menampilkan detail performa Account Manager ketika NIK AM atau NAMA AM diklik di tabel Account Manager Performance Details.

## Endpoint
```
GET /api/dashboard/am-performance-detail
```

## Request Parameters
| Parameter | Type   | Required | Description |
|-----------|--------|----------|-------------|
| nik_am    | string | Yes      | NIK Account Manager |
| quarter   | int    | Yes      | Quarter (1-4) |
| year      | int    | Yes      | Year (e.g., 2025) |
| segment   | string | Yes      | Segment (HQ-TWS, POTS, dll) |

## Response Structure

```json
{
    "success": true,
    "data": {
        "am_info": {
            "nik_am": "12345678",
            "nama_am": "John Doe",
            "posisi": "Account Manager",
            "no_gsm": "081234567890",
            "witel_name": "Jakarta Timur",
            "region_name": "Regional 1 Jakarta"
        },
        "current_period": {
            "quarter": 1,
            "year": 2025,
            "period_display": "Q1 2025"
        },
        "summary": {
            "target_proses": 1000,
            "realisasi_proses": 850,
            "target_result": 2000,
            "realisasi_result": 1800
        },
        "historical_data": [
            {
                "period_display": "Q1 2025",
                "quarter": 1,
                "year": 2025,
                "t_revenue": 1000000000,
                "r_revenue": 950000000,
                "ach_revenue_plan": 95.00,
                "t_scaling": 500000000,
                "r_scaling": 480000000,
                "ach_scaling": 96.00,
                "t_sales_datin": 100,
                "r_sales_datin": 95,
                "ach_sales_datin": 95.00,
                "t_hsi": 200,
                "r_hsi": 190,
                "ach_hsi": 95.00,
                "t_wireline": 150,
                "r_wireline": 145,
                "ach_wireline": 96.67,
                "t_wifi": 300,
                "r_wifi": 285,
                "ach_wifi": 95.00,
                "t_cyc": 50,
                "r_cyc": 48,
                "ach_cyc": 96.00,
                "t_cr": 100,
                "r_cr": 95,
                "ach_cr": 95.00,
                "t_profit": 500000000,
                "r_profit": 480000000,
                "ach_profit": 96.00,
                "t_nps": 85,
                "r_nps": 82,
                "ach_nps": 96.47,
                "t_maps": 90,
                "r_maps": 88,
                "ach_maps": 97.78,
                "t_lop": 1000000000,
                "r_lop": 950000000,
                "ach_lop": 95.00,
                "t_capability": 100,
                "r_capability": 95,
                "ach_capability": 95.00,
                "t_cc": 50,
                "r_cc": 48,
                "ach_cc": 96.00,
                "ach_result": 95.50,
                "ach_proses": 96.00,
                "nki_adjustment": 95.75,
                "formatted_t_revenue": "Rp 1.000.000.000",
                "formatted_r_revenue": "Rp 950.000.000",
                "formatted_t_scaling": "Rp 500.000.000",
                "formatted_r_scaling": "Rp 480.000.000",
                "formatted_t_lop": "Rp 1.000.000.000",
                "formatted_r_lop": "Rp 950.000.000"
            },
            {
                "period_display": "Q4 2024",
                "quarter": 4,
                "year": 2024,
                "... (same structure as above)"
            },
            {
                "period_display": "Q3 2024",
                "quarter": 3,
                "year": 2024,
                "... (same structure as above)"
            }
        ],
        "best_period": {
            "period_display": "Q1 2025",
            "nki_adjustment": 95.75
        }
    }
}
```

## Business Logic

### Historical Data
- **Jika filter per quarter**: Tampilkan data quarter saat ini dan 2 quarter sebelumnya (total 3 quarter)
  - Contoh: Jika Q1 2025, maka tampilkan Q1 2025, Q4 2024, Q3 2024
- **Jika filter per year**: Tampilkan data year saat ini dan 2 year sebelumnya (total 3 year)
  - Contoh: Jika 2025, maka tampilkan 2025, 2024, 2023

### Best Period
- Cari periode dengan `nki_adjustment` tertinggi dari `historical_data`
- Return `period_display` dan `nki_adjustment` dari periode tersebut

### Summary Calculation
- `target_proses`: Total target untuk semua parameter proses (MAPS, LOP, Capability, CC)
- `realisasi_proses`: Total realisasi untuk semua parameter proses
- `target_result`: Total target untuk semua parameter result (Revenue, Scaling, Sales Datin, HSI, Wireline, WiFi, CYC, CR, Profit, NPS)
- `realisasi_result`: Total realisasi untuk semua parameter result

### AM Info
Ambil dari tabel `account_managers` join dengan `witels` dan `regions`:
- nik_am
- nama_am  
- posisi
- no_gsm
- witel_name (dari join)
- region_name (dari join)

## Database Tables
- `account_managers` - Data AM
- `account_manager_companies` - Performance data AM per periode
- `companies` - Data perusahaan
- `witels` - Data witel
- `regions` - Data region

## Example Controller Implementation

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\AccountManagerCompany;
use Illuminate\Support\Facades\DB;

class AmPerformanceDetailController extends Controller
{
    public function getAmPerformanceDetail(Request $request)
    {
        $nikAm = $request->input('nik_am');
        $quarter = $request->input('quarter');
        $year = $request->input('year');
        $segment = $request->input('segment');

        // Get AM Info
        $amInfo = AccountManager::with(['witel.region'])
            ->where('nik_am', $nikAm)
            ->first();

        if (!$amInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Account Manager not found'
            ], 404);
        }

        // Get historical data (current + 2 previous quarters)
        $historicalData = $this->getHistoricalData($nikAm, $quarter, $year, $segment);

        // Calculate summary
        $summary = $this->calculateSummary($historicalData[0] ?? null);

        // Find best period
        $bestPeriod = $this->findBestPeriod($historicalData);

        return response()->json([
            'success' => true,
            'data' => [
                'am_info' => [
                    'nik_am' => $amInfo->nik_am,
                    'nama_am' => $amInfo->nama_am,
                    'posisi' => $amInfo->posisi,
                    'no_gsm' => $amInfo->no_gsm,
                    'witel_name' => $amInfo->witel->name ?? '-',
                    'region_name' => $amInfo->witel->region->name ?? '-',
                ],
                'current_period' => [
                    'quarter' => $quarter,
                    'year' => $year,
                    'period_display' => "Q{$quarter} {$year}"
                ],
                'summary' => $summary,
                'historical_data' => $historicalData,
                'best_period' => $bestPeriod
            ]
        ]);
    }

    private function getHistoricalData($nikAm, $currentQuarter, $currentYear, $segment)
    {
        // Logic to get current + 2 previous quarters
        // ... implementation
    }

    private function calculateSummary($currentData)
    {
        if (!$currentData) {
            return [
                'target_proses' => 0,
                'realisasi_proses' => 0,
                'target_result' => 0,
                'realisasi_result' => 0
            ];
        }

        return [
            'target_proses' => $currentData['t_maps'] + $currentData['t_lop'] + 
                              $currentData['t_capability'] + $currentData['t_cc'],
            'realisasi_proses' => $currentData['r_maps'] + $currentData['r_lop'] + 
                                 $currentData['r_capability'] + $currentData['r_cc'],
            'target_result' => $currentData['t_revenue'] + $currentData['t_scaling'] + 
                              $currentData['t_sales_datin'] + $currentData['t_hsi'] + 
                              $currentData['t_wireline'] + $currentData['t_wifi'] + 
                              $currentData['t_cyc'] + $currentData['t_cr'] + 
                              $currentData['t_profit'] + $currentData['t_nps'],
            'realisasi_result' => $currentData['r_revenue'] + $currentData['r_scaling'] + 
                                 $currentData['r_sales_datin'] + $currentData['r_hsi'] + 
                                 $currentData['r_wireline'] + $currentData['r_wifi'] + 
                                 $currentData['r_cyc'] + $currentData['r_cr'] + 
                                 $currentData['r_profit'] + $currentData['r_nps']
        ];
    }

    private function findBestPeriod($historicalData)
    {
        $best = collect($historicalData)->sortByDesc('nki_adjustment')->first();
        
        return [
            'period_display' => $best['period_display'] ?? '-',
            'nki_adjustment' => $best['nki_adjustment'] ?? 0
        ];
    }
}
```

## Route Registration
Add to `routes/web.php` or `routes/api.php`:

```php
Route::get('/api/dashboard/am-performance-detail', [AmPerformanceDetailController::class, 'getAmPerformanceDetail']);
```

## Frontend Integration

### Modal Trigger
Ketika NIK AM atau NAMA AM diklik di tabel Account Manager Performance Details, modal akan terbuka dengan data detail AM tersebut.

### Features
1. **Info Card**: Menampilkan informasi dasar AM (NIK, Posisi, Nama, No GSM, Witel, Region)
2. **Summary Cards**: 4 card untuk Target/Realisasi Parameter Proses dan Result
3. **Historical Table**: Tabel yang menampilkan performa 3 periode terakhir
4. **Best Period**: Highlight periode dengan NKI tertinggi
5. **Performance Trend Chart**: Line chart untuk visualisasi trend (akan diimplementasi dengan chart.js)

### State Management
- Modal state dikelola di parent component (WitelNkiDetailModal)
- Data fetching menggunakan axios ke endpoint `/api/dashboard/am-performance-detail`
- Loading dan error state handling

## Notes
- Chart implementation memerlukan library `chart.js` dan `react-chartjs-2`
- Pastikan format currency untuk nilai Revenue, Scaling, dan LOP
- Period display format: "Q1 2025" atau "2025" tergantung filter
