<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

class RevenueAnalyticsService
{
    /**
     * Format currency value with appropriate suffix (M for Miliar, T for Triliun)
     */
    private function formatCurrency(float $value, int $decimals = 2): string
    {
        if ($value >= 1000000000000) {
            // Triliun (>= 1000 Miliar)
            return 'Rp ' . number_format($value / 1000000000000, $decimals) . 'T';
        } else {
            // Miliar
            return 'Rp ' . number_format($value / 1000000000, $decimals) . 'M';
        }
    }

    /**
     * Get yearly revenue data for the last 5 years or specified range
     */
    public function getYearlyRevenue(?int $startYear = null, ?int $endYear = null): array
    {
        $startYear = $startYear ?? date('Y') - 4;
        $endYear = $endYear ?? date('Y');

        return Revenue::select(
                'tahun',
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('COUNT(DISTINCT nip_nas) as total_companies')
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
        // Get monthly revenue and target from database (SUM of all company targets)
        $monthlyData = Revenue::select(
                'bulan',
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(target) as target_revenue'),
                DB::raw('COUNT(DISTINCT nip_nas) as total_companies')
            )
            ->where('tahun', $year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

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
            $targetRevenue = $data ? (float) $data->target_revenue : 0;
            
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
            ->sum('total_revenue');

        $previousYtd = Revenue::where('tahun', $previousYear)
            ->where('bulan', '<=', $currentMonth)
            ->sum('total_revenue');

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
                DB::raw('SUM(revenues.total_revenue) as total_revenue'),
                DB::raw('COUNT(DISTINCT revenues.nip_nas) as total_companies'),
                DB::raw('AVG(revenues.total_revenue) as avg_revenue')
            )
            ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
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
                'companies.nip_nas',
                'companies.nama_perusahaan',
                'companies.subsegment',
                'companies.source_data',
                DB::raw('SUM(revenues.total_revenue) as total_revenue'),
                DB::raw('COUNT(revenues.id) as payment_count'),
                DB::raw('AVG(revenues.total_revenue) as avg_revenue'),
                DB::raw('MAX(revenues.total_revenue) as max_payment'),
                DB::raw('MIN(revenues.total_revenue) as min_payment')
            )
            ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
            ->where('revenues.tahun', $year)
            ->where('companies.subsegment', $subsegment);
            
        if ($month !== null) {
            $query->where('revenues.bulan', $month);
        }
        
        return $query
            ->groupBy('companies.nip_nas', 'companies.nama_perusahaan', 'companies.subsegment', 'companies.source_data')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                // Get region from company's witel (through foreign key idwitels)
                $regionData = DB::table('companies')
                    ->join('witels', 'companies.idwitels', '=', 'witels.idwitels')
                    ->join('regions', 'witels.region_id', '=', 'regions.id')
                    ->where('companies.nip_nas', $item->nip_nas)
                    ->select(
                        'regions.code as region_code',
                        'regions.name as region_name',
                        'witels.nama_witels as witel_name'
                    )
                    ->first();

                $regions = [];
                if ($regionData) {
                    $regions[] = [
                        'region_code' => $regionData->region_code,
                        'region_name' => $regionData->region_name,
                        'witel_name' => $regionData->witel_name,
                        'is_primary' => true, // Company hanya punya 1 witel, jadi selalu primary
                    ];
                }

                return [
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
                    'regions' => $regions,
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
                'companies.nip_nas',
                'companies.nama_perusahaan',
                'companies.subsegment',
                'companies.source_data',
                DB::raw('SUM(revenues.total_revenue) as total_revenue')
            )
            ->join('revenues', 'companies.nip_nas', '=', 'revenues.nip_nas')
            ->where('revenues.tahun', $year)
            ->groupBy('companies.nip_nas', 'companies.nama_perusahaan', 'companies.subsegment', 'companies.source_data')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
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
                DB::raw('SUM(revenues.total_revenue) as total_revenue'),
                DB::raw('COUNT(DISTINCT revenues.nip_nas) as total_companies')
            )
            ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
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
        $totalRevenue = Revenue::where('tahun', $year)->sum('total_revenue');
        $totalCompanies = Company::count();
        $activeSubsegments = Company::distinct('subsegment')->count('subsegment');
        
        $currentMonth = date('n');
        $currentMonthRevenue = Revenue::where('tahun', $year)
            ->where('bulan', $currentMonth)
            ->sum('total_revenue');

        return [
            'total_revenue' => (float) $totalRevenue,
            'total_companies' => $totalCompanies,
            'active_subsegments' => $activeSubsegments,
            'current_month_revenue' => (float) $currentMonthRevenue,
            'formatted_total_revenue' => $this->formatCurrency($totalRevenue),
            'formatted_current_month_revenue' => $this->formatCurrency($currentMonthRevenue),
            'avg_revenue_per_company' => $totalCompanies > 0 ? (float) ($totalRevenue / $totalCompanies) : 0
        ];
    }

    /**
     * Get subsegment revenue with regional breakdown
     */
    public function getSubsegmentWithRegionalBreakdown(int $year): array
    {
        $subsegments = ['PTN', 'PTS', 'Hospital', 'Airport', 'Media', 'Airlines', 'OLO', 'Professional Service', 'Tourism and MICE'];
        $result = [];

        // Calculate total revenue for share percentage
        $totalRevenue = Revenue::where('tahun', $year)->sum('total_revenue');

        foreach ($subsegments as $subsegment) {
            // Get subsegment totals
            $subsegmentRevenue = Revenue::join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
                ->where('companies.subsegment', $subsegment)
                ->where('revenues.tahun', $year)
                ->sum('revenues.total_revenue');

            $subsegmentCompanies = Revenue::join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
                ->where('companies.subsegment', $subsegment)
                ->where('revenues.tahun', $year)
                ->distinct('companies.nip_nas')
                ->count('companies.nip_nas');

            // Get regional breakdown
            $regionalBreakdown = [];
            $regions = \DB::table('regions')->whereNotNull('code')->orderBy('code')->get();

            foreach ($regions as $region) {
                // Get region revenue for this subsegment through company's witel
                $regionRevenue = Revenue::join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
                    ->join('witels', 'companies.idwitels', '=', 'witels.idwitels')
                    ->where('witels.region_id', $region->id)
                    ->where('companies.subsegment', $subsegment)
                    ->where('revenues.tahun', $year)
                    ->sum('revenues.total_revenue');

                if ($regionRevenue > 0) {
                    // Get top 3 companies in this region for this subsegment
                    $topCompanies = Revenue::select(
                            'companies.nip_nas',
                            'companies.nama_perusahaan',
                            \DB::raw('SUM(revenues.total_revenue) as total_revenue')
                        )
                        ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
                        ->join('witels', 'companies.idwitels', '=', 'witels.idwitels')
                        ->where('witels.region_id', $region->id)
                        ->where('companies.subsegment', $subsegment)
                        ->where('revenues.tahun', $year)
                        ->groupBy('companies.nip_nas', 'companies.nama_perusahaan')
                        ->orderByDesc('total_revenue')
                        ->limit(3)
                        ->get()
                        ->map(function ($company) {
                            return [
                                'nama_perusahaan' => $company->nama_perusahaan,
                                'revenue' => (float) $company->total_revenue,
                                'formatted_revenue' => $this->formatCurrency($company->total_revenue, 1),
                                'achievement' => rand(60, 144), // Mock data - replace with actual calculation
                                'growth_yoy' => rand(-40, 600), // Mock data - replace with actual calculation
                            ];
                        })
                        ->toArray();

                    $regionCompanyCount = Revenue::join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
                        ->join('witels', 'companies.idwitels', '=', 'witels.idwitels')
                        ->where('witels.region_id', $region->id)
                        ->where('companies.subsegment', $subsegment)
                        ->where('revenues.tahun', $year)
                        ->distinct('companies.nip_nas')
                        ->count('companies.nip_nas');

                    $regionalBreakdown[] = [
                        'region_code' => $region->code,
                        'region_name' => $region->description,
                        'revenue' => (float) $regionRevenue,
                        'formatted_revenue' => $this->formatCurrency($regionRevenue, 1),
                        'achievement' => rand(39, 94), // Mock data - replace with actual target calculation
                        'growth_yoy' => rand(-43, 20), // Mock data - replace with actual YoY calculation
                        'company_count' => $regionCompanyCount,
                        'top_companies' => $topCompanies,
                    ];
                }
            }

            if ($subsegmentRevenue > 0) {
                $result[] = [
                    'subsegment' => $subsegment,
                    'total_revenue' => (float) $subsegmentRevenue,
                    'formatted_total_revenue' => $this->formatCurrency($subsegmentRevenue, 0),
                    'total_achievement' => rand(50, 100), // Mock data - replace with actual calculation
                    'total_growth_yoy' => rand(-30, 20), // Mock data - replace with actual YoY calculation
                    'share_percentage' => $totalRevenue > 0 ? round(($subsegmentRevenue / $totalRevenue) * 100) : 0,
                    'total_companies' => $subsegmentCompanies,
                    'regional_breakdown' => $regionalBreakdown,
                ];
            }
        }

        // Sort by revenue descending
        usort($result, function ($a, $b) {
            return $b['total_revenue'] <=> $a['total_revenue'];
        });

        return $result;
    }
}