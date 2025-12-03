<?php

namespace App\Http\Controllers;

use App\Services\RevenueAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $comparisonYear = $request->input('comparison_year');
        
        // Get monthly revenue with optional comparison
        $monthlyRevenue = $this->analyticsService->getMonthlyRevenue($currentYear);
        
        // If comparison year is provided, merge comparison data
        if ($comparisonYear && $comparisonYear != $currentYear) {
            $comparisonMonthlyRevenue = $this->analyticsService->getMonthlyRevenue($comparisonYear);
            
            // Create a map of comparison data by month
            $comparisonMap = [];
            foreach ($comparisonMonthlyRevenue as $compData) {
                $comparisonMap[$compData['bulan']] = $compData;
            }
            
            // Merge comparison data into current year data
            foreach ($monthlyRevenue as &$monthData) {
                $bulan = $monthData['bulan'];
                if (isset($comparisonMap[$bulan])) {
                    $compData = $comparisonMap[$bulan];
                    $monthData['comparison_revenue'] = $compData['total_revenue'];
                    $monthData['comparison_formatted_revenue'] = $compData['formatted_revenue'];
                    
                    // Calculate growth
                    if ($compData['total_revenue'] > 0) {
                        $growth = (($monthData['total_revenue'] - $compData['total_revenue']) / $compData['total_revenue']) * 100;
                        $monthData['growth_percentage'] = round($growth, 1);
                        $monthData['growth_amount'] = $monthData['total_revenue'] - $compData['total_revenue'];
                    } else {
                        $monthData['growth_percentage'] = 0;
                        $monthData['growth_amount'] = $monthData['total_revenue'];
                    }
                }
            }
        }
        
        return Inertia::render('Dashboard', [
            'dashboardSummary' => $this->analyticsService->getDashboardSummary($currentYear),
            'yearlyRevenue' => $this->analyticsService->getYearlyRevenue(),
            'monthlyRevenue' => $monthlyRevenue,
            'ytdComparison' => $this->analyticsService->getYtdComparison($currentYear),
            'subsegmentRevenue' => $this->analyticsService->getSubsegmentRevenue($currentYear),
            'subsegmentRegionalData' => $this->analyticsService->getSubsegmentWithRegionalBreakdown($currentYear),
            'topCompanies' => $this->analyticsService->getTopCompanies($currentYear, 5),
            'currentYear' => (int)$currentYear,
            'comparisonYear' => $comparisonYear ? (int)$comparisonYear : null,
            'hasComparison' => !is_null($comparisonYear) && $comparisonYear != $currentYear,
        ]);
    }

    /**
     * Display Performance AM page
     * Fungsi ini untuk menampilkan halaman Performance Account Manager dengan data dari database
     */
    public function performanceAM(Request $request)
    {
        // Get current year or from request
        $currentYear = $request->input('year', date('Y'));
        
        // Get current quarter or from request
        $currentQuartal = $request->input('quartal', $this->getCurrentQuartal());
        
        // Get YTD flag from request
        $isYearToDate = $request->input('ytd', '0') === '1';
        
        // Get region filter from request
        $currentRegion = $request->input('region', 'ALL');
        
        // Get available years and quartals from lini_waktu
        $availableYears = $this->getAvailableYears();
        $availableQuartals = $this->getAvailableQuartals($currentYear);
        
        // Get current period details (bulan_awal, bulan_akhir)
        $periodDetails = $this->getPeriodDetails($currentYear, $currentQuartal);
        
        // Get quartals to include based on YTD
        $quartalsToInclude = $this->getQuartalsForYTD($currentQuartal, $isYearToDate);
        
        $revenueTarget = $this->getTotalRevenueTarget($currentYear, $quartalsToInclude, $currentRegion);
        $revenueActual = $this->getTotalRevenueActual($currentYear, $quartalsToInclude, $currentRegion);
        
        return Inertia::render('PerformanceAm', [
            // Metrics untuk nav cards
            'amMetrics' => [
                // Fungsi ini untuk mendapatkan total Account Manager yang terdaftar
                'total_am' => $this->getTotalAM($currentRegion),
                
                // Fungsi ini untuk mendapatkan total revenue target dari semua AM
                'revenue_target' => $revenueTarget,
                'formatted_revenue_target' => $this->formatCurrency($revenueTarget, 2),
                
                // Fungsi ini untuk mendapatkan total revenue actual dari semua AM
                'revenue_actual' => $revenueActual,
                'formatted_revenue_actual' => $this->formatCurrency($revenueActual, 2),
                
                // Year info dengan bulan range
                'year' => $currentYear,
                'month_start' => $periodDetails['bulan_awal'] ?? null,
                'month_end' => $periodDetails['bulan_akhir'] ?? null,
                
                // Quartal info
                'quartal' => $currentQuartal,
            ],
            
            // Dropdown options
            'availableYears' => $availableYears,
            'availableQuartals' => $availableQuartals,
            
            // Chart data - Target Revenue AM Ranking
            'amRevenueRanking' => $this->getAMRevenueRanking($currentYear, $quartalsToInclude),
            
            // Chart data - Region Distribution
            'regionDistribution' => $this->getRegionDistribution(),
            
            // Table data - Regional Performance with Top 3 AM
            'regionalPerformance' => $this->getRegionalPerformance($currentYear, $quartalsToInclude),
            
            // Best Performance across all regions
            'bestPerformance' => $this->getBestPerformance($currentYear, $quartalsToInclude),
            
            // Table data - List Account Manager
            'accountManagerList' => $this->getAccountManagerList(),
            
            'currentYear' => $currentYear,
            'currentQuartal' => $currentQuartal,
            'currentRegion' => $currentRegion,
            'currentYtd' => $isYearToDate,
        ]);
    }

    /**
     * Fungsi ini untuk mendapatkan total Account Manager yang terdaftar di database
     * Bisa difilter berdasarkan region
     */
    private function getTotalAM(?string $region = null): int
    {
        $query = \DB::table('account_managers');
        
        // Filter by region if specified
        if ($region && $region !== 'ALL') {
            $query->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                ->join('regions', 'witels.region_id', '=', 'regions.id')
                ->where('regions.code', $region);
        }
        
        return $query->count();
    }

    /**
     * Fungsi ini untuk mendapatkan list quartals berdasarkan YTD
     * Jika YTD true, return Q1 sampai quartal yang dipilih
     * Jika YTD false, return hanya quartal yang dipilih
     */
    private function getQuartalsForYTD(string $currentQuartal, bool $isYearToDate): array
    {
        if (!$isYearToDate) {
            return [$currentQuartal];
        }
        
        // Map quartals untuk YTD
        $quartalMap = [
            'Q1' => ['Q1'],
            'Q2' => ['Q1', 'Q2'],
            'Q3' => ['Q1', 'Q2', 'Q3'],
            'Q4' => ['Q1', 'Q2', 'Q3', 'Q4'],
        ];
        
        return $quartalMap[$currentQuartal] ?? [$currentQuartal];
    }

    /**
     * Fungsi ini untuk mendapatkan total revenue target dari semua AM berdasarkan tahun, quartals (support YTD), dan region
     */
    private function getTotalRevenueTarget(int $year, array $quartals, ?string $region = null): float
    {
        // Get all lini_waktu_ids for the selected year and quartals
        $liniWaktuQuery = \DB::table('lini_waktu')
            ->where('tahun', $year)
            ->whereIn('quartal', $quartals);
        
        // Filter by region if specified
        if ($region && $region !== 'ALL') {
            $liniWaktuQuery->join('account_managers', 'lini_waktu.nik_am', '=', 'account_managers.nik')
                ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                ->join('regions', 'witels.region_id', '=', 'regions.id')
                ->where('regions.code', $region);
        }
        
        $liniWaktuIds = $liniWaktuQuery->pluck('lini_waktu.id');
        
        if ($liniWaktuIds->isEmpty()) {
            return 0;
        }
        
        // Get sum of t_revenue from target_account_m through lini_waktu_target
        $totalRevenue = \DB::table('lini_waktu_target')
            ->join('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
            ->whereIn('lini_waktu_target.lini_waktu_id', $liniWaktuIds)
            ->sum('target_account_m.t_revenue');
        
        return (float) $totalRevenue;
    }

    /**
     * Fungsi ini untuk mendapatkan total revenue actual dari semua AM berdasarkan tahun, quartals (support YTD), dan region
     */
    private function getTotalRevenueActual(int $year, array $quartals, ?string $region = null): float
    {
        // Get all lini_waktu_ids for the selected year and quartals
        $liniWaktuQuery = \DB::table('lini_waktu')
            ->where('tahun', $year)
            ->whereIn('quartal', $quartals);
        
        // Filter by region if specified
        if ($region && $region !== 'ALL') {
            $liniWaktuQuery->join('account_managers', 'lini_waktu.nik_am', '=', 'account_managers.nik')
                ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                ->join('regions', 'witels.region_id', '=', 'regions.id')
                ->where('regions.code', $region);
        }
        
        $liniWaktuIds = $liniWaktuQuery->pluck('lini_waktu.id');
        
        if ($liniWaktuIds->isEmpty()) {
            return 0;
        }
        
        // Get sum of r_revenue from lini_waktu_target (not from target_account_m)
        $totalActual = \DB::table('lini_waktu_target')
            ->whereIn('lini_waktu_id', $liniWaktuIds)
            ->sum('r_revenue');
        
        return (float) $totalActual;
    }

    /**
     * Fungsi ini untuk mendapatkan quartal saat ini berdasarkan bulan sekarang
     */
    private function getCurrentQuartal(): string
    {
        $currentMonth = (int) date('n');
        
        if ($currentMonth >= 1 && $currentMonth <= 3) {
            return 'Q1';
        } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
            return 'Q2';
        } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
            return 'Q3';
        } else {
            return 'Q4';
        }
    }

    /**
     * Fungsi ini untuk mendapatkan list tahun yang tersedia di database
     */
    private function getAvailableYears(): array
    {
        return \DB::table('lini_waktu')
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
    }

    /**
     * Fungsi ini untuk mendapatkan list quartal yang tersedia untuk tahun tertentu
     */
    private function getAvailableQuartals(int $year): array
    {
        return \DB::table('lini_waktu')
            ->select('quartal')
            ->where('tahun', $year)
            ->distinct()
            ->orderBy('quartal', 'asc')
            ->pluck('quartal')
            ->toArray();
    }

    /**
     * Fungsi ini untuk mendapatkan detail periode (bulan_awal dan bulan_akhir) berdasarkan tahun dan quartal
     */
    private function getPeriodDetails(int $year, string $quartal): array
    {
        $period = \DB::table('lini_waktu')
            ->where('tahun', $year)
            ->where('quartal', $quartal)
            ->first(['bulan_awal', 'bulan_akhir']);
        
        if (!$period) {
            return [
                'bulan_awal' => null,
                'bulan_akhir' => null
            ];
        }
        
        return [
            'bulan_awal' => $period->bulan_awal,
            'bulan_akhir' => $period->bulan_akhir
        ];
    }

    /**
     * Fungsi ini untuk mendapatkan data ranking revenue target per Account Manager
     * Target revenue diambil berdasarkan filter tahun dan quartals (support YTD) yang dipilih
     * Flow: account_managers -> lini_waktu (filter tahun & quartals) -> lini_waktu_target -> target_account_m (t_revenue)
     * Data diurutkan berdasarkan t_revenue tertinggi
     */
    private function getAMRevenueRanking(int $year, array $quartals): array
    {
        // Get all Account Managers with their target revenue filtered by year and quartals
        // Include region_code untuk filter dropdown
        $rankingData = \DB::table('account_managers')
            ->select(
                'account_managers.nik',
                'account_managers.nama as am_name',
                'regions.code as region_code',
                \DB::raw('COALESCE(SUM(target_account_m.t_revenue), 0) as t_revenue'),
                \DB::raw('COALESCE(SUM(lini_waktu_target.r_revenue), 0) as r_revenue')
            )
            ->leftJoin('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->leftJoin('regions', 'witels.region_id', '=', 'regions.id')
            ->leftJoin('lini_waktu', function($join) use ($year, $quartals) {
                $join->on('account_managers.nik', '=', 'lini_waktu.nik_am')
                     ->where('lini_waktu.tahun', '=', $year)
                     ->whereIn('lini_waktu.quartal', $quartals);
            })
            ->leftJoin('lini_waktu_target', 'lini_waktu.id', '=', 'lini_waktu_target.lini_waktu_id')
            ->leftJoin('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
            ->groupBy('account_managers.nik', 'account_managers.nama', 'regions.code')
            ->orderBy('t_revenue', 'desc')
            ->get();
        
        return $rankingData->map(function($item) {
            return [
                'nik' => $item->nik,
                'am_name' => $item->am_name,
                'region_code' => $item->region_code,
                't_revenue' => (float) $item->t_revenue,
                'formatted_revenue' => $this->formatCurrency($item->t_revenue, 2),
                'r_revenue' => (float) $item->r_revenue,
                'formatted_r_revenue' => $this->formatCurrency($item->r_revenue, 2)
            ];
        })->toArray();
    }

    /**
     * Fungsi ini untuk mendapatkan distribusi Account Manager per Region
     * Data diambil dari table regions, dengan perhitungan:
     * - idwitels di account_managers -> region_id di witels -> code di regions
     * - Persentase: (jumlah AM di region / total AM) * 100
     */
    private function getRegionDistribution(): array
    {
        // Get total AM
        $totalAM = \DB::table('account_managers')->count();
        
        if ($totalAM === 0) {
            return [];
        }
        
        // Get AM count per region menggunakan kolom 'code' dari table regions
        $distribution = \DB::table('account_managers')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->join('regions', 'witels.region_id', '=', 'regions.id')
            ->select(
                'regions.id as region_id',
                'regions.code as region_code',
                'regions.code as region_name',
                \DB::raw('COUNT(account_managers.nik) as am_count')
            )
            ->groupBy('regions.id', 'regions.code')
            ->orderBy('am_count', 'desc')
            ->get();
        
        return $distribution->map(function($item) use ($totalAM) {
            $percentage = ($item->am_count / $totalAM) * 100;
            
            return [
                'region_id' => $item->region_id,
                'region_code' => $item->region_code,
                'region_name' => $item->region_name,
                'am_count' => $item->am_count,
                'percentage' => round($percentage, 2)
            ];
        })->toArray();
    }

    /**
     * Fungsi ini untuk mendapatkan list semua Account Manager dengan detail lengkap
     * Termasuk: nama, NIK, posisi, no_gsm, dan lokasi (nama witel)
     */
    private function getAccountManagerList(): array
    {
        $amList = \DB::table('account_managers')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->select(
                'account_managers.nik',
                'account_managers.nama',
                'account_managers.posisi',
                'account_managers.no_gsm',
                'witels.nama_witels as lokasi_am'
            )
            ->orderBy('account_managers.nama', 'asc')
            ->get();
        
        return $amList->map(function($am, $index) {
            return [
                'no' => $index + 1,
                'nik' => $am->nik,
                'nama' => $am->nama,
                'posisi' => $am->posisi,
                'no_gsm' => $am->no_gsm,
                'lokasi_am' => $am->lokasi_am
            ];
        })->toArray();
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

        // Get monthly summary (revenue, target, companies)
        $monthlySummary = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('r.tahun', $year)
            ->where('r.bulan', $month)
            ->selectRaw('
                SUM(r.revenue_realisasi) as total_revenue,
                SUM(r.revenue_target) as total_target,
                COUNT(DISTINCT group1.company_id) as total_companies
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'subsegments' => $this->analyticsService->getSubsegmentRevenue($year, $month),
                'month_name' => $monthNames[$month],
                'month' => $month,
                'year' => $year,
                'total_revenue' => (float) $monthlySummary->total_revenue,
                'total_target' => (float) $monthlySummary->total_target,
                'total_companies' => (int) $monthlySummary->total_companies,
                'formatted_total_revenue' => 'Rp ' . number_format($monthlySummary->total_revenue, 0, ',', '.'),
                'formatted_total_target' => 'Rp ' . number_format($monthlySummary->total_target, 0, ',', '.'),
                'achievement_percentage' => $monthlySummary->total_target > 0 
                    ? round(($monthlySummary->total_revenue / $monthlySummary->total_target) * 100, 1) 
                    : 0
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
            'formatted_total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.')
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
        $query = \DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
            ->join('account_manager_company', 'companies.nip_nas', '=', 'account_manager_company.nip_nas')
            ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->join('regions', 'witels.region_id', '=', 'regions.id')
            ->where('companies.subsegment', $subsegment)
            ->where('r.tahun', $year);

        if ($month) {
            $query->where('r.bulan', $month);
        }

        $regionalData = $query
            ->select(
                'regions.id',
                'regions.code',
                'regions.description as name',
                \DB::raw('COUNT(DISTINCT companies.nip_nas) as total_companies'),
                \DB::raw('SUM(r.revenue_realisasi) as total_revenue')
            )
            ->groupBy('regions.id', 'regions.code', 'regions.description')
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
            'company_id' => 'required|string|exists:companies,nip_nas',
            'year' => 'nullable|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12'
        ]);

        $companyId = $request->input('company_id');
        $filterYear = $request->input('year');
        $filterMonth = $request->input('month');
        
        // Get company basic info with Account Managers and Witel->Region
        $company = \App\Models\Company::where('nip_nas', $companyId)
            ->with(['accountManagers.witel.region', 'witel.region'])
            ->firstOrFail();
        
        // Base query for filtered data
        $baseQuery = \DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('group1.company_id', $companyId);
        
        // Apply filters if provided
        if ($filterYear) {
            $baseQuery->where('r.tahun', $filterYear);
        }
        if ($filterMonth) {
            $baseQuery->where('r.bulan', $filterMonth);
        }
        
        // Get all revenue history for summary calculations (with filters applied)
        $allMonthlyData = (clone $baseQuery)
            ->select('r.tahun', 'r.bulan', \DB::raw('SUM(r.revenue_realisasi) as total_revenue'))
            ->groupBy('r.tahun', 'r.bulan')
            ->orderBy('r.tahun', 'asc')
            ->orderBy('r.bulan', 'asc')
            ->get();
        
        // Get last 12 months revenue data for chart (with filters applied)
        $monthlyQuery = (clone $baseQuery)
            ->select(
                'r.tahun',
                'r.bulan',
                \DB::raw('SUM(r.revenue_realisasi) as total_revenue'),
                \DB::raw('CASE 
                    WHEN r.bulan = 1 THEN "Januari"
                    WHEN r.bulan = 2 THEN "Februari" 
                    WHEN r.bulan = 3 THEN "Maret"
                    WHEN r.bulan = 4 THEN "April"
                    WHEN r.bulan = 5 THEN "Mei"
                    WHEN r.bulan = 6 THEN "Juni"
                    WHEN r.bulan = 7 THEN "Juli"
                    WHEN r.bulan = 8 THEN "Agustus"
                    WHEN r.bulan = 9 THEN "September"
                    WHEN r.bulan = 10 THEN "Oktober"
                    WHEN r.bulan = 11 THEN "November"
                    WHEN r.bulan = 12 THEN "Desember"
                    END as bulan_name')
            )
            ->groupBy('r.tahun', 'r.bulan')
            ->orderBy('r.tahun', 'desc')
            ->orderBy('r.bulan', 'desc');
        
        // Only limit to 12 if no filters applied
        if (!$filterYear && !$filterMonth) {
            $monthlyQuery->limit(12);
        }
        
        $monthlyData = $monthlyQuery
            ->get()
            ->reverse()
            ->values()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'bulan' => $item->bulan,
                    'bulan_name' => $item->bulan_name,
                    'revenue' => $item->total_revenue,
                    'formatted_revenue' => 'Rp ' . number_format($item->total_revenue / 1000000000, 2) . 'M',
                    'period_label' => $item->bulan_name . ' ' . $item->tahun
                ];
            });

        // Get yearly totals (with filters applied)
        $yearlyData = (clone $baseQuery)
            ->select(
                'r.tahun',
                \DB::raw('SUM(r.revenue_realisasi) as total_revenue'),
                \DB::raw('COUNT(DISTINCT CONCAT(r.tahun, "-", r.bulan)) as months_count')
            )
            ->groupBy('r.tahun')
            ->orderBy('r.tahun', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'total_revenue' => $item->total_revenue,
                    'formatted_total_revenue' => 'Rp ' . number_format($item->total_revenue / 1000000000, 2) . 'M',
                    'months_count' => $item->months_count
                ];
            });

        // Calculate summary using filtered data
        $totalRevenue = $allMonthlyData->sum('total_revenue');
        $avgMonthlyRevenue = $allMonthlyData->count() > 0 ? $totalRevenue / $allMonthlyData->count() : 0;
        $bestMonthRecord = $allMonthlyData->sortByDesc('total_revenue')->first();
        $bestYear = $yearlyData->sortByDesc('total_revenue')->first();
        
        // Get month name for best month
        $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                      7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        // Build period description
        $periodText = 'All Time';
        if ($filterYear && $filterMonth) {
            $periodText = $monthNames[$filterMonth] . ' ' . $filterYear;
        } elseif ($filterYear) {
            $periodText = 'Year ' . $filterYear;
        }

        $summaryData = [
            'total_revenue' => $totalRevenue,
            'avg_monthly_revenue' => $avgMonthlyRevenue,
            'best_month' => $bestMonthRecord ? $monthNames[$bestMonthRecord->bulan] . ' ' . $bestMonthRecord->tahun : null,
            'best_year' => $bestYear ? $bestYear['tahun'] : null,
            'formatted_total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
            'formatted_avg_monthly' => 'Rp ' . number_format($avgMonthlyRevenue, 0, ',', '.'),
            'period' => $periodText,
            'filter_year' => $filterYear,
            'filter_month' => $filterMonth
        ];

        // Format Account Managers data
        $accountManagers = $company->accountManagers->map(function ($am) {
            return [
                'nik' => $am->nik,
                'nama' => $am->nama,
                'posisi' => $am->posisi,
                'no_gsm' => $am->no_gsm,
                'proporsi' => $am->pivot->proporsi,
                'pembagian' => $am->pivot->pembagian,
                'segment' => $am->pivot->segment,
                'witel_name' => $am->witel ? $am->witel->nama_witels : null,
                'region_name' => $am->witel && $am->witel->region ? $am->witel->region->name : null,
                'region_code' => $am->witel && $am->witel->region ? $am->witel->region->code : null,
            ];
        });

        // Format Region data dari company->witel->region
        $regions = [];
        if ($company->witel && $company->witel->region) {
            $regions[] = [
                'region_code' => $company->witel->region->code,
                'region_name' => $company->witel->region->name,
                'witel_name' => $company->witel->nama_witels,
                'is_primary' => true, // Company hanya punya 1 witel, jadi selalu primary
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company,
                'account_managers' => $accountManagers,
                'regions' => $regions,
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
     * Get AM Revenue Details
     * Fungsi untuk mendapatkan detail target revenue AM beserta company breakdown
     */
    public function getAMRevenueDetails(Request $request)
    {
        $request->validate([
            'am_nik' => 'required|string',
            'year' => 'required|integer',
            'quartal' => 'required|string|in:Q1,Q2,Q3,Q4'
        ]);

        $amNik = $request->input('am_nik');
        $year = $request->input('year');
        $quartal = $request->input('quartal');
        $isYearToDate = $request->input('ytd', '0') === '1';

        // Get quartals to include based on YTD setting
        $quartalsToInclude = $this->getQuartalsForYTD($quartal, $isYearToDate);

        // Get AM basic info with witel and region
        $accountManager = \DB::table('account_managers')
            ->leftJoin('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->leftJoin('regions', 'witels.region_id', '=', 'regions.id')
            ->where('account_managers.nik', $amNik)
            ->select(
                'account_managers.nik',
                'account_managers.nama',
                'account_managers.posisi',
                'account_managers.no_gsm',
                'witels.nama_witels',
                'regions.code as region_code'
            )
            ->first();

        if (!$accountManager) {
            return response()->json([
                'success' => false,
                'message' => 'Account Manager not found'
            ], 404);
        }

        // Get lini_waktu for this AM, year, and quartals (support YTD)
        $liniWaktuIds = \DB::table('lini_waktu')
            ->where('nik_am', $amNik)
            ->where('tahun', $year)
            ->whereIn('quartal', $quartalsToInclude)
            ->pluck('id');

        if ($liniWaktuIds->isEmpty()) {
            // Build period display
            $periodDisplay = $isYearToDate && $quartal !== 'Q1' 
                ? "{$year} - Q1 - {$quartal} (YTD)" 
                : "{$year} - {$quartal}";

            return response()->json([
                'success' => true,
                'data' => [
                    'am_name' => $accountManager->nama,
                    'am_nik' => $accountManager->nik,
                    'am_posisi' => $accountManager->posisi,
                    'am_no_gsm' => $accountManager->no_gsm,
                    'am_witel' => $accountManager->nama_witels,
                    'am_region' => $accountManager->region_code,
                    'total_target_revenue' => 0,
                    'formatted_total_revenue' => 'Rp 0.00M',
                    'total_companies' => 0,
                    'year' => $year,
                    'quartal' => $quartal,
                    'period_display' => $periodDisplay,
                    'region_distribution' => [],
                    'companies' => []
                ]
            ]);
        }

        // Get all targets for this AM in these periods via lini_waktu_target
        $targets = \DB::table('lini_waktu_target as lwt')
            ->join('target_account_m as t', 'lwt.target_id', '=', 't.id')
            ->join('account_manager_company as amc', 't.account_manager_company_id', '=', 'amc.id')
            ->join('companies as c', 'amc.nip_nas', '=', 'c.nip_nas')
            ->leftJoin('witels as w', 'c.idwitels', '=', 'w.idwitels')
            ->leftJoin('regions as r', 'w.region_id', '=', 'r.id')
            ->whereIn('lwt.lini_waktu_id', $liniWaktuIds)
            ->where('amc.nik_am', $amNik)
            ->select(
                'c.nip_nas',
                'c.nama_perusahaan',
                'c.subsegment',
                'w.nama_witels',
                \DB::raw('SUM(t.t_revenue) as t_revenue'),
                'w.idwitels',
                'r.code as region_code',
                'amc.pembagian',
                'amc.proporsi',
                \DB::raw('SUM(t.t_sustain) as t_sustain'),
                \DB::raw('SUM(t.t_scalling) as t_scalling'),
                \DB::raw('SUM(t.t_ngtma) as t_ngtma')
            )
            ->groupBy('c.nip_nas', 'c.nama_perusahaan', 'c.subsegment', 'w.nama_witels', 'w.idwitels', 'r.code', 'amc.pembagian', 'amc.proporsi')
            ->get();

        // Calculate total target revenue
        $totalTargetRevenue = $targets->sum('t_revenue');

        // Group by region for distribution
        $regionGroups = $targets->groupBy('region_code')->map(function ($companies, $regionCode) use ($targets) {
            $count = $companies->count();
            return [
                'region_code' => $regionCode ?: 'Unassigned',
                'company_count' => $count,
                'percentage' => $targets->count() > 0 ? ($count / $targets->count()) * 100 : 0
            ];
        })->values();

        // Format companies data
        $companiesData = $targets->map(function ($company) {
            return [
                'nip_nas' => $company->nip_nas,
                'nama_perusahaan' => $company->nama_perusahaan,
                'subsegment' => $company->subsegment ?: '-',
                'nama_witels' => $company->nama_witels ?: 'Unassigned',
                'region_code' => $company->region_code ?: 'Unassigned',
                't_revenue' => (float) $company->t_revenue,
                'formatted_revenue' => $this->formatCurrency($company->t_revenue, 2),
                'pembagian' => $company->pembagian,
                'proporsi' => (float) $company->proporsi,
                't_sustain' => (float) $company->t_sustain,
                't_scalling' => (float) $company->t_scalling,
                't_ngtma' => (float) $company->t_ngtma,
                'formatted_sustain' => $this->formatCurrency($company->t_sustain, 2),
                'formatted_scalling' => $this->formatCurrency($company->t_scalling, 2),
                'formatted_ngtma' => $this->formatCurrency($company->t_ngtma, 2)
            ];
        });

        // Build period display
        $periodDisplay = $isYearToDate && $quartal !== 'Q1' 
            ? "{$year} - Q1 - {$quartal} (YTD)" 
            : "{$year} - {$quartal}";

        return response()->json([
            'success' => true,
            'data' => [
                'am_name' => $accountManager->nama,
                'am_nik' => $accountManager->nik,
                'am_posisi' => $accountManager->posisi,
                'am_no_gsm' => $accountManager->no_gsm,
                'am_witel' => $accountManager->nama_witels,
                'am_region' => $accountManager->region_code,
                'total_target_revenue' => (float) $totalTargetRevenue,
                'formatted_total_revenue' => $this->formatCurrency($totalTargetRevenue, 2),
                'total_companies' => $targets->count(),
                'year' => $year,
                'quartal' => $quartal,
                'period_display' => $periodDisplay,
                'region_distribution' => $regionGroups,
                'companies' => $companiesData
            ]
        ]);
    }

    /**
     * Get available years and months from revenue data
     */
    public function getAvailablePeriods()
    {
        $periods = DB::table('revenues')
            ->select('tahun', 'bulan')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        $years = $periods->pluck('tahun')->unique()->values()->toArray();
        
        // Group months by year
        $monthsByYear = [];
        foreach ($years as $year) {
            $monthsByYear[$year] = $periods
                ->where('tahun', $year)
                ->pluck('bulan')
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'years' => $years,
                'months_by_year' => $monthsByYear,
            ]
        ]);
    }

    /** Get custom YTD comparison between any two periods
     */
    public function getCustomYtdComparison(Request $request)
    {
        $request->validate([
            'current_year' => 'required|integer|min:2020|max:2030',
            'current_month' => 'required|integer|min:1|max:12',
            'previous_year' => 'required|integer|min:2020|max:2030',
            'previous_month' => 'required|integer|min:1|max:12',
        ]);

        $currentYear = $request->input('current_year');
        $currentMonth = $request->input('current_month');
        $previousYear = $request->input('previous_year');
        $previousMonth = $request->input('previous_month');

        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->getCustomYtdComparison(
                $currentYear,
                $currentMonth,
                $previousYear,
                $previousMonth
            )
        ]);
    }

    /**
     * Fungsi untuk mendapatkan data regional performance dengan top 3 AM
     * Growth untuk YTD dihitung dengan menjumlahkan growth dari setiap quartal
     */
    private function getRegionalPerformance(int $year, array $quartals): array
    {
        // Get all regions
        $regions = \DB::table('regions')
            ->select('id', 'code')
            ->orderBy('code')
            ->get();

        $regionalData = [];

        foreach ($regions as $region) {
            // Get lini_waktu_ids for this period and region
            $liniWaktuIds = \DB::table('lini_waktu')
                ->join('account_managers', 'lini_waktu.nik_am', '=', 'account_managers.nik')
                ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                ->where('lini_waktu.tahun', $year)
                ->whereIn('lini_waktu.quartal', $quartals)
                ->where('witels.region_id', $region->id)
                ->pluck('lini_waktu.id');

            if ($liniWaktuIds->isEmpty()) {
                continue;
            }

            // Get total r_revenue for current period
            $currentRevenue = \DB::table('lini_waktu_target')
                ->whereIn('lini_waktu_id', $liniWaktuIds)
                ->sum('r_revenue');

            // Calculate growth - if YTD (multiple quartals), sum individual growth for each quartal
            $growth = 0;
            if (count($quartals) > 1) {
                // YTD: Sum growth from each quartal
                foreach ($quartals as $quartal) {
                    $quarterGrowth = $this->calculateQuarterGrowth($year, $quartal, $region->id);
                    $growth += $quarterGrowth;
                }
            } else {
                // Single quartal: Calculate growth normally
                $growth = $this->calculateQuarterGrowth($year, $quartals[0], $region->id);
            }

            // Get unique company count for this region in current period
            $companyCount = \DB::table('lini_waktu_target')
                ->join('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
                ->join('account_manager_company', 'target_account_m.account_manager_company_id', '=', 'account_manager_company.id')
                ->whereIn('lini_waktu_target.lini_waktu_id', $liniWaktuIds)
                ->distinct('account_manager_company.nip_nas')
                ->count('account_manager_company.nip_nas');

            // Get top 3 AM by r_revenue for this region
            $topAMs = \DB::table('account_managers')
                ->select(
                    'account_managers.nik',
                    'account_managers.nama as am_name',
                    \DB::raw('COALESCE(SUM(lini_waktu_target.r_revenue), 0) as total_revenue')
                )
                ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                ->join('lini_waktu', 'account_managers.nik', '=', 'lini_waktu.nik_am')
                ->join('lini_waktu_target', 'lini_waktu.id', '=', 'lini_waktu_target.lini_waktu_id')
                ->where('witels.region_id', $region->id)
                ->where('lini_waktu.tahun', $year)
                ->whereIn('lini_waktu.quartal', $quartals)
                ->groupBy('account_managers.nik', 'account_managers.nama')
                ->orderBy('total_revenue', 'desc')
                ->limit(3)
                ->get();

            // Get achievement (ach) for each top AM
            $topAMsWithAch = $topAMs->map(function ($am) use ($year, $quartals) {
                // Get lini_waktu_ids for this AM
                $amLiniWaktuIds = \DB::table('lini_waktu')
                    ->where('nik_am', $am->nik)
                    ->where('tahun', $year)
                    ->whereIn('quartal', $quartals)
                    ->pluck('id');

                // Get total t_revenue (target) and r_revenue (actual)
                $revenueData = \DB::table('lini_waktu_target')
                    ->join('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
                    ->whereIn('lini_waktu_target.lini_waktu_id', $amLiniWaktuIds)
                    ->select(
                        \DB::raw('SUM(target_account_m.t_revenue) as total_target'),
                        \DB::raw('SUM(lini_waktu_target.r_revenue) as total_actual')
                    )
                    ->first();

                $achievement = 0;
                if ($revenueData && $revenueData->total_target > 0) {
                    $achievement = ($revenueData->total_actual / $revenueData->total_target) * 100;
                }

                return [
                    'nik' => $am->nik,
                    'am_name' => $am->am_name,
                    'revenue' => (float) $am->total_revenue,
                    'formatted_revenue' => $this->formatCurrency($am->total_revenue, 2),
                    'achievement' => round($achievement, 2),
                    'formatted_achievement' => number_format($achievement, 2) . '%'
                ];
            });

            $regionalData[] = [
                'region_code' => $region->code,
                'revenue' => (float) $currentRevenue,
                'formatted_revenue' => $this->formatCurrency($currentRevenue, 2),
                'growth' => round($growth, 2),
                'formatted_growth' => number_format($growth, 2) . '%',
                'company_count' => $companyCount,
                'top_ams' => $topAMsWithAch->toArray()
            ];
        }

        return $regionalData;
    }

    /**
     * Helper function to get previous quartals for growth calculation
     */
    private function getPreviousQuartals(int $year, array $quartals): array
    {
        // Get the earliest quartal from the array
        $firstQuartal = $quartals[0];
        
        $quartalMap = [
            'Q1' => 'Q4',
            'Q2' => 'Q1',
            'Q3' => 'Q2',
            'Q4' => 'Q3'
        ];
        
        $previousQuartal = $quartalMap[$firstQuartal] ?? 'Q4';
        $previousYear = $firstQuartal === 'Q1' ? $year - 1 : $year;
        
        // If current is YTD (multiple quartals), previous should be same range in previous year
        if (count($quartals) > 1) {
            return [
                'year' => $year - 1,
                'quartals' => $quartals
            ];
        }
        
        return [
            'year' => $previousYear,
            'quartals' => [$previousQuartal]
        ];
    }

    /**
     * Helper function to calculate growth for a single quarter
     */
    private function calculateQuarterGrowth(int $year, string $quartal, int $regionId): float
    {
        // Get current quarter revenue
        $currentLiniWaktuIds = \DB::table('lini_waktu')
            ->join('account_managers', 'lini_waktu.nik_am', '=', 'account_managers.nik')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->where('lini_waktu.tahun', $year)
            ->where('lini_waktu.quartal', $quartal)
            ->where('witels.region_id', $regionId)
            ->pluck('lini_waktu.id');

        $currentRevenue = 0;
        if ($currentLiniWaktuIds->isNotEmpty()) {
            $currentRevenue = \DB::table('lini_waktu_target')
                ->whereIn('lini_waktu_id', $currentLiniWaktuIds)
                ->sum('r_revenue');
        }

        // Get previous quarter info
        $quartalMap = [
            'Q1' => ['quartal' => 'Q4', 'year_offset' => -1],
            'Q2' => ['quartal' => 'Q1', 'year_offset' => 0],
            'Q3' => ['quartal' => 'Q2', 'year_offset' => 0],
            'Q4' => ['quartal' => 'Q3', 'year_offset' => 0]
        ];

        $previousInfo = $quartalMap[$quartal] ?? ['quartal' => 'Q4', 'year_offset' => -1];
        $previousYear = $year + $previousInfo['year_offset'];
        $previousQuartal = $previousInfo['quartal'];

        // Get previous quarter revenue
        $previousLiniWaktuIds = \DB::table('lini_waktu')
            ->join('account_managers', 'lini_waktu.nik_am', '=', 'account_managers.nik')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->where('lini_waktu.tahun', $previousYear)
            ->where('lini_waktu.quartal', $previousQuartal)
            ->where('witels.region_id', $regionId)
            ->pluck('lini_waktu.id');

        $previousRevenue = 0;
        if ($previousLiniWaktuIds->isNotEmpty()) {
            $previousRevenue = \DB::table('lini_waktu_target')
                ->whereIn('lini_waktu_id', $previousLiniWaktuIds)
                ->sum('r_revenue');
        }

        // Calculate growth percentage
        if ($previousRevenue > 0) {
            return (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
        }

        return 0;
    }

    /**
     * Fungsi untuk mendapatkan Best Performance AM dari semua region
     * Berdasarkan total r_revenue tertinggi - Top 3
     */
    private function getBestPerformance(int $year, array $quartals): array
    {
        // Get top 3 performing AMs by r_revenue
        $topAMs = \DB::table('account_managers')
            ->select(
                'account_managers.nik',
                'account_managers.nama as am_name',
                'regions.code as region_code',
                \DB::raw('COALESCE(SUM(lini_waktu_target.r_revenue), 0) as total_revenue'),
                \DB::raw('COALESCE(AVG(lini_waktu_target.ach_revenue_plan), 0) as avg_growth')
            )
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->join('regions', 'witels.region_id', '=', 'regions.id')
            ->join('lini_waktu', 'account_managers.nik', '=', 'lini_waktu.nik_am')
            ->join('lini_waktu_target', 'lini_waktu.id', '=', 'lini_waktu_target.lini_waktu_id')
            ->where('lini_waktu.tahun', $year)
            ->whereIn('lini_waktu.quartal', $quartals)
            ->groupBy('account_managers.nik', 'account_managers.nama', 'regions.code')
            ->orderBy('total_revenue', 'desc')
            ->limit(3)
            ->get();

        if ($topAMs->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($topAMs as $am) {
            // Get company count for this AM
            $liniWaktuIds = \DB::table('lini_waktu')
                ->where('nik_am', $am->nik)
                ->where('tahun', $year)
                ->whereIn('quartal', $quartals)
                ->pluck('id');

            $companyCount = 0;
            if ($liniWaktuIds->isNotEmpty()) {
                $companyCount = \DB::table('lini_waktu_target')
                    ->join('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
                    ->join('account_manager_company', 'target_account_m.account_manager_company_id', '=', 'account_manager_company.id')
                    ->whereIn('lini_waktu_target.lini_waktu_id', $liniWaktuIds)
                    ->distinct('account_manager_company.nip_nas')
                    ->count('account_manager_company.nip_nas');
            }

            $result[] = [
                'nik' => $am->nik,
                'am_name' => $am->am_name,
                'region_code' => $am->region_code,
                'revenue' => (float) $am->total_revenue,
                'formatted_revenue' => $this->formatCurrency($am->total_revenue, 2),
                'growth' => round($am->avg_growth, 2),
                'formatted_growth' => number_format($am->avg_growth, 2) . '%',
                'company_count' => $companyCount
            ];
        }

        return $result;
    }

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
