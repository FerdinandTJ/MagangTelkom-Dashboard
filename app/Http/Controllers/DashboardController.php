<?php

namespace App\Http\Controllers;

use App\Services\RevenueAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $analyticsService;

    public function __construct(RevenueAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display main dashboard page
     */
    public function index(Request $request)
    {
        $currentYear = $request->input('year', date('Y'));
        
        return Inertia::render('Dashboard', [
            'dashboardSummary' => $this->analyticsService->getDashboardSummary($currentYear),
            'yearlyRevenue' => $this->analyticsService->getYearlyRevenue(),
            'monthlyRevenue' => $this->analyticsService->getMonthlyRevenue($currentYear),
            'ytdComparison' => $this->analyticsService->getYtdComparison($currentYear),
            'subsegmentRevenue' => $this->analyticsService->getSubsegmentRevenue($currentYear),
            'subsegmentRegionalData' => $this->analyticsService->getSubsegmentWithRegionalBreakdown($currentYear),
            'topCompanies' => $this->analyticsService->getTopCompanies($currentYear, 5),
            'currentYear' => (int)$currentYear,
        ]);
    }

    /**
     * Display Performance AM page
     */
    public function performanceAM()
    {
        $currentYear = date('Y');
        
        return Inertia::render('PerformanceAm', [
            'amMetrics' => [
                'total_accounts' => 150,
                'active_accounts' => 135,
                'revenue_target' => 50000000000, // 50B
                'revenue_achieved' => 38000000000, // 38B
                'achievement_rate' => 76.0,
            ],
            'amPerformance' => $this->getAMPerformanceData(),
            'accountDistribution' => $this->getAccountDistributionData(),
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Get AM performance placeholder data
     */
    private function getAMPerformanceData()
    {
        return [
            ['name' => 'Ahmad Santoso', 'accounts' => 25, 'revenue' => 8500000000, 'achievement' => 85.0],
            ['name' => 'Sari Dewi', 'accounts' => 22, 'revenue' => 7800000000, 'achievement' => 78.0],
            ['name' => 'Budi Pratama', 'accounts' => 28, 'revenue' => 9200000000, 'achievement' => 92.0],
            ['name' => 'Maya Indira', 'accounts' => 20, 'revenue' => 6800000000, 'achievement' => 68.0],
            ['name' => 'Rizki Fauzi', 'accounts' => 24, 'revenue' => 8100000000, 'achievement' => 81.0],
        ];
    }

    /**
     * Get account distribution placeholder data
     */
    private function getAccountDistributionData()
    {
        return [
            ['subsegment' => 'Airport', 'count' => 35, 'percentage' => 23.3],
            ['subsegment' => 'Hospital', 'count' => 42, 'percentage' => 28.0],
            ['subsegment' => 'PTN', 'count' => 28, 'percentage' => 18.7],
            ['subsegment' => 'PTS', 'count' => 25, 'percentage' => 16.7],
            ['subsegment' => 'Media', 'count' => 20, 'percentage' => 13.3],
        ];
    }

    /**
     * Get monthly revenue data for specific year
     */
    public function getMonthlyData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        return response()->json([
            'success' => true,
            'data' => [
                'monthly_revenue' => $this->analyticsService->getMonthlyRevenue($year),
                'ytd_comparison' => $this->analyticsService->getYtdComparison($year),
                'year' => $year
            ]
        ]);
    }

    /**
     * Get subsegment details for specific month
     */
    public function getMonthDetails(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12'
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'subsegments' => $this->analyticsService->getSubsegmentRevenue($year, $month),
                'month_name' => $monthNames[$month],
                'month' => $month,
                'year' => $year
            ]
        ]);
    }

    /**
     * Get company details for specific subsegment and month
     */
    public function getCompanyDetails(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'subsegment' => 'required|string|in:Airport,Hospital,PTN,PTS,Media'
        ]);

        $year = $request->input('year');
        $month = $request->input('month');
        $subsegment = $request->input('subsegment');

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $this->analyticsService->getCompanyDetails($year, $month, $subsegment),
                'subsegment' => $subsegment,
                'month' => $month,
                'year' => $year
            ]
        ]);
    }

    /**
     * Get subsegment companies with revenue details
     */
    public function getSubsegmentDetails(Request $request)
    {
        $request->validate([
            'subsegment' => 'required|string',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12'
        ]);

        $subsegment = $request->input('subsegment');
        $year = $request->input('year');
        $month = $request->input('month');

        $companies = $this->analyticsService->getCompanyDetails($year, $month, $subsegment);

        // Calculate summary
        $totalRevenue = collect($companies)->sum('revenue');
        $totalCompanies = count($companies);
        $avgRevenue = $totalCompanies > 0 ? $totalRevenue / $totalCompanies : 0;

        // Get regional breakdown
        $regionalBreakdown = $this->getRegionalBreakdown($year, $month, $subsegment);

        $summaryData = [
            'total_revenue' => $totalRevenue,
            'total_companies' => $totalCompanies,
            'avg_revenue' => $avgRevenue,
            'formatted_total_revenue' => 'Rp ' . number_format($totalRevenue / 1000000000, 2) . 'M',
            'formatted_avg_revenue' => 'Rp ' . number_format($avgRevenue / 1000000000, 2) . 'M'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
                'summary' => $summaryData,
                'regional_breakdown' => $regionalBreakdown,
                'subsegment' => $subsegment,
                'year' => $year,
                'month' => $month
            ]
        ]);
    }

    /**
     * Get regional breakdown for subsegment
     */
    private function getRegionalBreakdown(int $year, ?int $month, string $subsegment): array
    {
        $query = \DB::table('revenues')
            ->join('companies', 'revenues.company_id', '=', 'companies.id')
            ->leftJoin('regions', 'companies.primary_region_id', '=', 'regions.id')
            ->where('companies.subsegment', $subsegment)
            ->where('revenues.tahun', $year)
            ->whereNotNull('companies.primary_region_id'); // Only include companies with regions

        if ($month) {
            $query->where('revenues.bulan', $month);
        }

        $regionalData = $query
            ->select(
                'regions.id',
                'regions.code',
                'regions.name',
                \DB::raw('COUNT(DISTINCT companies.id) as total_companies'),
                \DB::raw('SUM(revenues.revenue) as total_revenue')
            )
            ->groupBy('regions.id', 'regions.code', 'regions.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return $regionalData->map(function ($item) {
            $revenue = $item->total_revenue ?? 0;
            return [
                'region_id' => $item->id,
                'region_code' => $item->code,
                'region_name' => $item->name,
                'total_companies' => $item->total_companies,
                'total_revenue' => (float) $revenue,
                'formatted_revenue' => $this->formatCurrency($revenue, 2),
                'percentage' => 0 // Will be calculated on frontend
            ];
        })->toArray();
    }

    /**
     * Get individual company details with historical data
     */
    public function getIndividualCompanyDetails(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id'
        ]);

        $companyId = $request->input('company_id');
        
        // Get company basic info
        $company = \App\Models\Company::findOrFail($companyId);
        
        // Get all revenue history for summary calculations
        $allMonthlyData = \App\Models\Revenue::where('company_id', $companyId)
            ->selectRaw('tahun, bulan, revenue')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();
        
        // Get last 12 months revenue data for chart
        $monthlyData = \App\Models\Revenue::where('company_id', $companyId)
            ->selectRaw('tahun, bulan, revenue')
            ->selectRaw('CASE 
                WHEN bulan = 1 THEN "Januari"
                WHEN bulan = 2 THEN "Februari" 
                WHEN bulan = 3 THEN "Maret"
                WHEN bulan = 4 THEN "April"
                WHEN bulan = 5 THEN "Mei"
                WHEN bulan = 6 THEN "Juni"
                WHEN bulan = 7 THEN "Juli"
                WHEN bulan = 8 THEN "Agustus"
                WHEN bulan = 9 THEN "September"
                WHEN bulan = 10 THEN "Oktober"
                WHEN bulan = 11 THEN "November"
                WHEN bulan = 12 THEN "Desember"
                END as bulan_name')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->limit(12)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'bulan' => $item->bulan,
                    'bulan_name' => $item->bulan_name,
                    'revenue' => $item->revenue,
                    'formatted_revenue' => 'Rp ' . number_format($item->revenue / 1000000000, 2) . 'M',
                    'period_label' => $item->bulan_name . ' ' . $item->tahun
                ];
            });

        // Get yearly totals
        $yearlyData = \App\Models\Revenue::where('company_id', $companyId)
            ->selectRaw('tahun, SUM(revenue) as total_revenue, COUNT(*) as months_count')
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'total_revenue' => $item->total_revenue,
                    'formatted_total_revenue' => 'Rp ' . number_format($item->total_revenue / 1000000000, 2) . 'M',
                    'months_count' => $item->months_count
                ];
            });

        // Calculate summary using all data
        $totalRevenue = $allMonthlyData->sum('revenue');
        $avgMonthlyRevenue = $allMonthlyData->count() > 0 ? $totalRevenue / $allMonthlyData->count() : 0;
        $bestMonthRecord = $allMonthlyData->sortByDesc('revenue')->first();
        $bestYear = $yearlyData->sortByDesc('total_revenue')->first();
        
        // Get month name for best month
        $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                      7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        $summaryData = [
            'total_revenue' => $totalRevenue,
            'avg_monthly_revenue' => $avgMonthlyRevenue,
            'best_month' => $bestMonthRecord ? $monthNames[$bestMonthRecord->bulan] . ' ' . $bestMonthRecord->tahun : null,
            'best_year' => $bestYear ? $bestYear['tahun'] : null,
            'formatted_total_revenue' => 'Rp ' . number_format($totalRevenue / 1000000000, 2) . 'M',
            'formatted_avg_monthly' => 'Rp ' . number_format($avgMonthlyRevenue / 1000000000, 2) . 'M'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company,
                'monthly_data' => $monthlyData,
                'yearly_data' => $yearlyData,
                'summary' => $summaryData
            ]
        ]);
    }

    /**
     * Get subsegment trend data
     */
    public function getSubsegmentTrend(Request $request)
    {
        $request->validate([
            'subsegment' => 'required|string|in:Airport,Hospital,PTN,PTS,Media',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $subsegment = $request->input('subsegment');
        $year = $request->input('year');

        return response()->json([
            'success' => true,
            'data' => [
                'trend' => $this->analyticsService->getSubsegmentTrend($subsegment, $year),
                'subsegment' => $subsegment,
                'year' => $year
            ]
        ]);
    }

    /**
     * Get yearly comparison data
     */
    public function getYearlyComparison(Request $request)
    {
        $startYear = $request->input('start_year', date('Y') - 4);
        $endYear = $request->input('end_year', date('Y'));

        return response()->json([
            'success' => true,
            'data' => [
                'yearly_revenue' => $this->analyticsService->getYearlyRevenue($startYear, $endYear),
                'start_year' => $startYear,
                'end_year' => $endYear
            ]
        ]);
    }

    /**
     * Get dashboard analytics summary
     */
    public function getAnalyticsSummary(Request $request)
    {
        $year = $request->input('year', date('Y'));

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $this->analyticsService->getDashboardSummary($year),
                'top_companies' => $this->analyticsService->getTopCompanies($year, 10),
                'subsegment_breakdown' => $this->analyticsService->getSubsegmentRevenue($year),
                'year' => $year
            ]
        ]);
    }

    /**
     * Format currency helper
     */
    private function formatCurrency(float $value, int $decimals = 1): string
    {
        if ($value >= 1000000000000) {
            // Triliun (>= 1000 Miliar)
            return 'Rp ' . number_format($value / 1000000000000, $decimals) . 'T';
        } else {
            // Miliar
            return 'Rp ' . number_format($value / 1000000000, $decimals) . 'M';
        }
    }
}
