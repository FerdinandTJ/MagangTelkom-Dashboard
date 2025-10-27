<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

class RevenueAnalyticsService
{
    /**
     * Get yearly revenue data for the last 5 years or specified range
     */
    public function getYearlyRevenue(?int $startYear = null, ?int $endYear = null): array
    {
        $startYear = $startYear ?? date('Y') - 4;
        $endYear = $endYear ?? date('Y');

        return Revenue::select(
                'tahun',
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('COUNT(DISTINCT company_id) as total_companies')
            )
            ->whereBetween('tahun', [$startYear, $endYear])
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'total_revenue' => (float) $item->total_revenue,
                    'total_companies' => $item->total_companies,
                    'formatted_revenue' => 'Rp ' . number_format($item->total_revenue, 0, ',', '.')
                ];
            })
            ->toArray();
    }

    /**
     * Get monthly revenue data for specific year
     */
    public function getMonthlyRevenue(int $year): array
    {
        $monthlyData = Revenue::select(
                'bulan',
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('COUNT(DISTINCT company_id) as total_companies')
            )
            ->where('tahun', $year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Monthly targets (in billions) - you can adjust these values
        $monthlyTargets = [
            1 => 150000000000,   // Jan: 15B
            2 => 155000000000,   // Feb: 15.5B
            3 => 160000000000,   // Mar: 16B
            4 => 165000000000,   // Apr: 16.5B
            5 => 170000000000,   // May: 17B
            6 => 175000000000,   // Jun: 17.5B
            7 => 180000000000,   // Jul: 18B
            8 => 185000000000,   // Aug: 18.5B
            9 => 190000000000,   // Sep: 19B
            10 => 195000000000,  // Oct: 19.5B
            11 => 200000000000,  // Nov: 20B
            12 => 205000000000,  // Dec: 20.5B
        ];

        // Determine last month to display
        $currentMonth = (int) date('n'); // Current month number (1-12)
        $currentYear = (int) date('Y');
        
        // If viewing current year, only show up to current month
        // If viewing past year, show all 12 months
        // If viewing future year, show no months
        if ($year == $currentYear) {
            $lastMonth = $currentMonth;
        } elseif ($year < $currentYear) {
            $lastMonth = 12;
        } else {
            $lastMonth = 0; // Future year, no data to show
        }

        // Fill months with data up to the determined last month
        $result = [];
        for ($month = 1; $month <= $lastMonth; $month++) {
            $data = $monthlyData->get($month);
            $actualRevenue = $data ? (float) $data->total_revenue : 0;
            $targetRevenue = $monthlyTargets[$month];
            
            $result[] = [
                'bulan' => $month,
                'bulan_name' => $this->getMonthName($month),
                'total_revenue' => $actualRevenue,
                'target_revenue' => $targetRevenue,
                'total_companies' => $data ? $data->total_companies : 0,
                'formatted_revenue' => $data ? 'Rp ' . number_format($data->total_revenue, 0, ',', '.') : 'Rp 0',
                'formatted_target' => 'Rp ' . number_format($targetRevenue, 0, ',', '.'),
                'achievement_percentage' => $targetRevenue > 0 ? round(($actualRevenue / $targetRevenue) * 100, 1) : 0
            ];
        }

        return $result;
    }

    /**
     * Get Year-to-Date comparison with previous year
     */
    public function getYtdComparison(int $year): array
    {
        $currentMonth = date('n'); // Current month number
        $previousYear = $year - 1;

        $currentYtd = Revenue::where('tahun', $year)
            ->where('bulan', '<=', $currentMonth)
            ->sum('revenue');

        $previousYtd = Revenue::where('tahun', $previousYear)
            ->where('bulan', '<=', $currentMonth)
            ->sum('revenue');

        $growth = $previousYtd > 0 ? (($currentYtd - $previousYtd) / $previousYtd) * 100 : 0;

        return [
            'current_year' => $year,
            'previous_year' => $previousYear,
            'current_ytd' => (float) $currentYtd,
            'previous_ytd' => (float) $previousYtd,
            'growth_percentage' => round($growth, 2),
            'growth_amount' => (float) ($currentYtd - $previousYtd),
            'formatted_current_ytd' => 'Rp ' . number_format($currentYtd, 0, ',', '.'),
            'formatted_previous_ytd' => 'Rp ' . number_format($previousYtd, 0, ',', '.'),
            'formatted_growth_amount' => 'Rp ' . number_format(abs($currentYtd - $previousYtd), 0, ',', '.'),
            'is_positive_growth' => $growth >= 0
        ];
    }

    /**
     * Get revenue breakdown by subsegment
     */
    public function getSubsegmentRevenue(int $year, ?int $month = null): array
    {
        $query = Revenue::select(
                'companies.subsegment',
                DB::raw('SUM(revenues.revenue) as total_revenue'),
                DB::raw('COUNT(DISTINCT revenues.company_id) as total_companies'),
                DB::raw('AVG(revenues.revenue) as avg_revenue')
            )
            ->join('companies', 'revenues.company_id', '=', 'companies.id')
            ->where('revenues.tahun', $year);

        if ($month) {
            $query->where('revenues.bulan', $month);
        }

        return $query->groupBy('companies.subsegment')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'subsegment' => $item->subsegment,
                    'total_revenue' => (float) $item->total_revenue,
                    'total_companies' => $item->total_companies,
                    'avg_revenue' => (float) $item->avg_revenue,
                    'formatted_total_revenue' => 'Rp ' . number_format($item->total_revenue, 0, ',', '.'),
                    'formatted_avg_revenue' => 'Rp ' . number_format($item->avg_revenue, 0, ',', '.')
                ];
            })
            ->toArray();
    }

    /**
     * Get company details for specific year, month, and subsegment
     */
    public function getCompanyDetails(int $year, ?int $month, string $subsegment): array
    {
        $query = Revenue::select(
                'companies.id',
                'companies.nip_nas',
                'companies.nama_perusahaan',
                'companies.subsegment',
                'companies.source_data',
                DB::raw('SUM(revenues.revenue) as total_revenue'),
                DB::raw('COUNT(revenues.id) as payment_count'),
                DB::raw('AVG(revenues.revenue) as avg_revenue'),
                DB::raw('MAX(revenues.revenue) as max_payment'),
                DB::raw('MIN(revenues.revenue) as min_payment')
            )
            ->join('companies', 'revenues.company_id', '=', 'companies.id')
            ->where('revenues.tahun', $year)
            ->where('companies.subsegment', $subsegment);
            
        if ($month !== null) {
            $query->where('revenues.bulan', $month);
        }
        
        return $query
            ->groupBy('companies.id', 'companies.nip_nas', 'companies.nama_perusahaan', 'companies.subsegment', 'companies.source_data')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nip_nas' => $item->nip_nas,
                    'nama_perusahaan' => $item->nama_perusahaan,
                    'subsegment' => $item->subsegment,
                    'source_data' => $item->source_data,
                    'revenue' => (float) $item->total_revenue,
                    'payment_count' => $item->payment_count,
                    'avg_revenue' => (float) $item->avg_revenue,
                    'max_payment' => (float) $item->max_payment,
                    'min_payment' => (float) $item->min_payment,
                    'formatted_revenue' => 'Rp ' . number_format($item->total_revenue, 0, ',', '.'),
                    'formatted_avg_revenue' => 'Rp ' . number_format($item->avg_revenue, 0, ',', '.'),
                ];
            })
            ->toArray();
    }

    /**
     * Get top performing companies across all subsegments
     */
    public function getTopCompanies(int $year, int $limit = 10): array
    {
        return Company::select(
                'companies.id',
                'companies.nip_nas',
                'companies.nama_perusahaan',
                'companies.subsegment',
                'companies.source_data',
                DB::raw('SUM(revenues.revenue) as total_revenue')
            )
            ->join('revenues', 'companies.id', '=', 'revenues.company_id')
            ->where('revenues.tahun', $year)
            ->groupBy('companies.id', 'companies.nip_nas', 'companies.nama_perusahaan', 'companies.subsegment', 'companies.source_data')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nip_nas' => $item->nip_nas,
                    'nama_perusahaan' => $item->nama_perusahaan,
                    'subsegment' => $item->subsegment,
                    'source_data' => $item->source_data,
                    'total_revenue' => (float) $item->total_revenue,
                    'formatted_total_revenue' => 'Rp ' . number_format($item->total_revenue, 0, ',', '.')
                ];
            })
            ->toArray();
    }

    /**
     * Get revenue trend for specific subsegment over months
     */
    public function getSubsegmentTrend(string $subsegment, int $year): array
    {
        return Revenue::select(
                'revenues.bulan',
                DB::raw('SUM(revenues.revenue) as total_revenue'),
                DB::raw('COUNT(DISTINCT revenues.company_id) as total_companies')
            )
            ->join('companies', 'revenues.company_id', '=', 'companies.id')
            ->where('companies.subsegment', $subsegment)
            ->where('revenues.tahun', $year)
            ->groupBy('revenues.bulan')
            ->orderBy('revenues.bulan')
            ->get()
            ->map(function ($item) {
                return [
                    'bulan' => $item->bulan,
                    'bulan_name' => $this->getMonthName($item->bulan),
                    'total_revenue' => (float) $item->total_revenue,
                    'total_companies' => $item->total_companies,
                    'formatted_revenue' => 'Rp ' . number_format($item->total_revenue, 0, ',', '.')
                ];
            })
            ->toArray();
    }

    /**
     * Get month name in Indonesian
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$month] ?? '';
    }

    /**
     * Get summary statistics for dashboard
     */
    public function getDashboardSummary(int $year): array
    {
        $totalRevenue = Revenue::where('tahun', $year)->sum('revenue');
        $totalCompanies = Company::where('status', 'active')->count();
        $activeSubsegments = Company::where('status', 'active')->distinct('subsegment')->count();
        
        $currentMonth = date('n');
        $currentMonthRevenue = Revenue::where('tahun', $year)
            ->where('bulan', $currentMonth)
            ->sum('revenue');

        return [
            'total_revenue' => (float) $totalRevenue,
            'total_companies' => $totalCompanies,
            'active_subsegments' => $activeSubsegments,
            'current_month_revenue' => (float) $currentMonthRevenue,
            'formatted_total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
            'formatted_current_month_revenue' => 'Rp ' . number_format($currentMonthRevenue, 0, ',', '.'),
            'avg_revenue_per_company' => $totalCompanies > 0 ? (float) ($totalRevenue / $totalCompanies) : 0
        ];
    }
}