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

        return DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->select(
                'r.tahun',
                DB::raw('SUM(r.revenue_realisasi) as total_revenue'),
                DB::raw('COUNT(DISTINCT group1.company_id) as total_companies')
            )
            ->whereBetween('r.tahun', [$startYear, $endYear])
            ->groupBy('r.tahun')
            ->orderBy('r.tahun')
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
        // Get monthly revenue and target from revenues table (normalized schema)
        $monthlyData = DB::table('revenues as r')
            ->where('r.tahun', $year)
            ->select(
                'r.bulan',
                DB::raw('SUM(r.revenue_realisasi) as total_revenue'),
                DB::raw('SUM(r.revenue_target) as target_revenue'),
                DB::raw('COUNT(DISTINCT r.group4_id) as total_entries')
            )
            ->groupBy('r.bulan')
            ->orderBy('r.bulan')
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
                'total_companies' => $data ? $data->total_entries : 0,
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

        $currentYtd = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('r.tahun', $year)
            ->where('r.bulan', '<=', $currentMonth)
            ->sum('r.revenue_realisasi');

        $previousYtd = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('r.tahun', $previousYear)
            ->where('r.bulan', '<=', $currentMonth)
            ->sum('r.revenue_realisasi');

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
     * Get custom Year-to-Date comparison between any two periods
     */
    public function getCustomYtdComparison(int $currentYear, int $currentMonth, int $previousYear, int $previousMonth): array
    {
        // Get current period revenue (YTD)
        $currentRevenue = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('r.tahun', $currentYear)
            ->where('r.bulan', '<=', $currentMonth)
            ->sum('r.revenue_realisasi');

        // Get previous period revenue (YTD)
        $previousRevenue = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('r.tahun', $previousYear)
            ->where('r.bulan', '<=', $previousMonth)
            ->sum('r.revenue_realisasi');

        $growth = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        // Build hierarchical tree breakdown (Group1 -> Group2 -> Group3 -> Group4)
        $hierarchicalBreakdown = $this->buildYtdHierarchicalTree($currentYear, $currentMonth, $previousYear, $previousMonth);

        return [
            'current_year' => $currentYear,
            'current_month' => $currentMonth,
            'current_month_name' => $this->getMonthName($currentMonth),
            'current_revenue' => (float) $currentRevenue,
            'previous_year' => $previousYear,
            'previous_month' => $previousMonth,
            'previous_month_name' => $this->getMonthName($previousMonth),
            'previous_revenue' => (float) $previousRevenue,
            'growth_percentage' => round($growth, 2),
            'growth_amount' => (float) ($currentRevenue - $previousRevenue),
            'formatted_current_revenue' => 'Rp ' . number_format($currentRevenue, 0, ',', '.'),
            'formatted_previous_revenue' => 'Rp ' . number_format($previousRevenue, 0, ',', '.'),
            'formatted_growth_amount' => 'Rp ' . number_format(abs($currentRevenue - $previousRevenue), 0, ',', '.'),
            'is_positive_growth' => $growth >= 0,
            'hierarchical_breakdown' => $hierarchicalBreakdown
        ];
    }

    /**
     * Build hierarchical tree breakdown for YTD comparison
     * AGGREGATES by Group1/2/3 to avoid duplication per company
     */
    private function buildYtdHierarchicalTree(int $currentYear, int $currentMonth, int $previousYear, int $previousMonth): array
    {
        // Get AGGREGATED current period data using normalized schema
        // JOIN group4 (product master) with revenues (time-series) and companies
        $currentData = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
            ->where('r.tahun', $currentYear)
            ->where('r.bulan', '<=', $currentMonth)
            ->select(
                'group1.idGroup1',
                'group1.nama_group1',
                'group1.company_id',
                'companies.nama_perusahaan',
                'companies.subsegment',
                'group2.idGroup2',
                'group2.nama_group2',
                'group3.idGroup3',
                'group3.nama_group3',
                'p.idGroup4',
                'p.nama_group4',
                DB::raw('SUM(r.revenue_realisasi) as current_revenue')
            )
            ->groupBy(
                'group1.idGroup1', 'group1.nama_group1', 'group1.company_id',
                'companies.nama_perusahaan', 'companies.subsegment',
                'group2.idGroup2', 'group2.nama_group2',
                'group3.idGroup3', 'group3.nama_group3',
                'p.idGroup4', 'p.nama_group4'
            )
            ->get();

        // Get AGGREGATED previous period data using normalized schema
        $previousData = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
            ->where('r.tahun', $previousYear)
            ->where('r.bulan', '<=', $previousMonth)
            ->select(
                'p.idGroup4',
                'companies.nip_nas',
                DB::raw('SUM(r.revenue_realisasi) as previous_revenue')
            )
            ->groupBy('p.idGroup4', 'companies.nip_nas')
            ->get();

        // Build lookup map for previous period revenues by composite key (product + company)
        $previousRevenueMap = [];
        foreach ($previousData as $item) {
            $key = "{$item->idGroup4}_{$item->nip_nas}";
            $previousRevenueMap[$key] = (float) $item->previous_revenue;
        }

        // Build hierarchical structure - AGGREGATING at parent levels
        $tree = [];
        
        foreach ($currentData as $row) {
            $compositeKey = "{$row->idGroup4}_{$row->company_id}";
            $previousRevenue = $previousRevenueMap[$compositeKey] ?? 0;
            $currentRevenue = (float) $row->current_revenue;

            // Build Group1 by NAME (not ID) - AGGREGATE across all companies with same name
            $g1Key = 'g1_' . strtolower(str_replace(' ', '_', $row->nama_group1));
            if (!isset($tree[$g1Key])) {
                $tree[$g1Key] = [
                    'id' => $g1Key,
                    'name' => $row->nama_group1,
                    'type' => 'group1',
                    'current_revenue' => 0,
                    'previous_revenue' => 0,
                    'children' => []
                ];
            }

            // Build Group2 by NAME (not ID) - AGGREGATE across all companies with same name
            $g2Key = 'g2_' . strtolower(str_replace(' ', '_', $row->nama_group2));
            if (!isset($tree[$g1Key]['children'][$g2Key])) {
                $tree[$g1Key]['children'][$g2Key] = [
                    'id' => $g2Key,
                    'name' => $row->nama_group2,
                    'type' => 'group2',
                    'current_revenue' => 0,
                    'previous_revenue' => 0,
                    'children' => []
                ];
            }

            // Build Group3 by NAME (not ID) - AGGREGATE across all companies with same name
            $g3Key = 'g3_' . strtolower(str_replace(' ', '_', $row->nama_group3));
            if (!isset($tree[$g1Key]['children'][$g2Key]['children'][$g3Key])) {
                $tree[$g1Key]['children'][$g2Key]['children'][$g3Key] = [
                    'id' => $g3Key,
                    'name' => $row->nama_group3,
                    'type' => 'group3',
                    'current_revenue' => 0,
                    'previous_revenue' => 0,
                    'children' => []
                ];
            }

            // Build Group4 with company - UNIQUE per company (key = product_id + company_id)
            $g4Key = 'g4_' . $row->idGroup4 . '_' . $row->company_id;
            if (!isset($tree[$g1Key]['children'][$g2Key]['children'][$g3Key]['children'][$g4Key])) {
                // Format product name with company inline
                $productNameWithCompany = "{$row->nama_group4} ({$row->nama_perusahaan})";
                
                $tree[$g1Key]['children'][$g2Key]['children'][$g3Key]['children'][$g4Key] = [
                    'id' => $g4Key,
                    'name' => $productNameWithCompany, // Display: "Service A (PT Telkom)"
                    'type' => 'group4',
                    'current_revenue' => $currentRevenue,
                    'previous_revenue' => $previousRevenue,
                    'children' => []
                ];

                // Accumulate revenue UP the tree (only when adding new Group4)
                $tree[$g1Key]['current_revenue'] += $currentRevenue;
                $tree[$g1Key]['previous_revenue'] += $previousRevenue;

                $tree[$g1Key]['children'][$g2Key]['current_revenue'] += $currentRevenue;
                $tree[$g1Key]['children'][$g2Key]['previous_revenue'] += $previousRevenue;

                $tree[$g1Key]['children'][$g2Key]['children'][$g3Key]['current_revenue'] += $currentRevenue;
                $tree[$g1Key]['children'][$g2Key]['children'][$g3Key]['previous_revenue'] += $previousRevenue;
            }
        }

        // Calculate growth metrics for all nodes and convert children to indexed arrays
        foreach ($tree as &$g1) {
            $g1['children'] = array_values($g1['children']);
            $this->calculateGrowthMetricsForNode($g1);
            
            foreach ($g1['children'] as &$g2) {
                $g2['children'] = array_values($g2['children']);
                $this->calculateGrowthMetricsForNode($g2);
                
                foreach ($g2['children'] as &$g3) {
                    $g3['children'] = array_values($g3['children']);
                    $this->calculateGrowthMetricsForNode($g3);
                    
                    foreach ($g3['children'] as &$g4) {
                        // Format company info for Group4
                        if (isset($g4['company_info'])) {
                            $company = $g4['company_info'];
                            $growth = $company['previous_revenue'] > 0 
                                ? (($company['current_revenue'] - $company['previous_revenue']) / $company['previous_revenue']) * 100 
                                : 0;
                            
                            $g4['company_info'] = [
                                'company_id' => $company['company_id'],
                                'company_name' => $company['company_name'],
                                'subsegment' => $company['subsegment'],
                                'current_revenue' => $company['current_revenue'],
                                'previous_revenue' => $company['previous_revenue'],
                                'growth_percentage' => round($growth, 2),
                                'formatted_current' => 'Rp ' . number_format($company['current_revenue'], 0, ',', '.'),
                                'formatted_previous' => 'Rp ' . number_format($company['previous_revenue'], 0, ',', '.'),
                                'is_positive' => $growth >= 0
                            ];
                        }
                        $this->calculateGrowthMetricsForNode($g4);
                    }
                }
            }
        }

        return array_values($tree);
    }

    /**
     * Calculate growth metrics for a node
     */
    private function calculateGrowthMetricsForNode(array &$node): void
    {
        $prevRevenue = $node['previous_revenue'];
        $currRevenue = $node['current_revenue'];
        $growthPct = $prevRevenue > 0 ? (($currRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
        
        $node['growth_percentage'] = round($growthPct, 2);
        $node['growth_amount'] = (float) ($currRevenue - $prevRevenue);
        $node['formatted_current'] = 'Rp ' . number_format($currRevenue, 0, ',', '.');
        $node['formatted_previous'] = 'Rp ' . number_format($prevRevenue, 0, ',', '.');
        $node['formatted_growth'] = 'Rp ' . number_format(abs($currRevenue - $prevRevenue), 0, ',', '.');
        $node['is_positive'] = $growthPct >= 0;
    }

    /**
     * Get revenue breakdown by subsegment
     */
    public function getSubsegmentRevenue(int $year, ?int $month = null): array
    {
        $query = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
            ->where('r.tahun', $year)
            ->select(
                'companies.subsegment',
                DB::raw('SUM(r.revenue_realisasi) as total_revenue'),
                DB::raw('COUNT(DISTINCT group1.company_id) as total_companies'),
                DB::raw('AVG(r.revenue_realisasi) as avg_revenue')
            );

        if ($month) {
            $query->where('r.bulan', $month);
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
        $query = DB::table('companies')
            ->join('group1', 'companies.nip_nas', '=', 'group1.company_id')
            ->join('group2', 'group1.idGroup1', '=', 'group2.group1_id')
            ->join('group3', 'group2.idGroup2', '=', 'group3.group2_id')
            ->join('group4 as p', 'group3.idGroup3', '=', 'p.group3_id')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->where('r.tahun', $year)
            ->where('companies.subsegment', $subsegment)
            ->select(
                'companies.nip_nas',
                'companies.nama_perusahaan',
                'companies.subsegment',
                'companies.source_data',
                DB::raw('SUM(r.revenue_realisasi) as total_revenue'),
                DB::raw('COUNT(p.idGroup4) as payment_count'),
                DB::raw('AVG(r.revenue_realisasi) as avg_revenue'),
                DB::raw('MAX(r.revenue_realisasi) as max_payment'),
                DB::raw('MIN(r.revenue_realisasi) as min_payment')
            );
            
        if ($month !== null) {
            $query->where('r.bulan', $month);
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
        return DB::table('companies')
            ->join('group1', 'companies.nip_nas', '=', 'group1.company_id')
            ->join('group2', 'group1.idGroup1', '=', 'group2.group1_id')
            ->join('group3', 'group2.idGroup2', '=', 'group3.group2_id')
            ->join('group4 as p', 'group3.idGroup3', '=', 'p.group3_id')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->where('r.tahun', $year)
            ->select(
                'companies.nip_nas',
                'companies.nama_perusahaan',
                'companies.subsegment',
                'companies.source_data',
                DB::raw('SUM(r.revenue_realisasi) as total_revenue')
            )
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
        return DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
            ->where('companies.subsegment', $subsegment)
            ->where('r.tahun', $year)
            ->select(
                'r.bulan',
                DB::raw('SUM(r.revenue_realisasi) as total_revenue'),
                DB::raw('COUNT(DISTINCT group1.company_id) as total_companies')
            )
            ->groupBy('r.bulan')
            ->orderBy('r.bulan')
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
        $totalRevenue = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('r.tahun', $year)
            ->sum('r.revenue_realisasi');
            
        $totalCompanies = Company::count();
        $activeSubsegments = Company::distinct('subsegment')->count('subsegment');
        
        // Current month always uses actual current date (not affected by year filter)
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        $currentMonthRevenue = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('r.tahun', $currentYear)
            ->where('r.bulan', $currentMonth)
            ->sum('r.revenue_realisasi');

        $currentMonthTarget = DB::table('revenues as r')
            ->where('r.tahun', $currentYear)
            ->where('r.bulan', $currentMonth)
            ->sum('r.revenue_target');

        return [
            'total_revenue' => (float) $totalRevenue,
            'total_companies' => $totalCompanies,
            'active_subsegments' => $activeSubsegments,
            'current_month_revenue' => (float) $currentMonthRevenue,
            'current_month_target' => (float) $currentMonthTarget,
            'formatted_total_revenue' => $this->formatCurrency($totalRevenue),
            'formatted_current_month_revenue' => $this->formatCurrency($currentMonthRevenue),
            'formatted_current_month_target' => $this->formatCurrency($currentMonthTarget),
            'current_month_achievement' => $currentMonthTarget > 0 ? round(($currentMonthRevenue / $currentMonthTarget) * 100, 1) : 0,
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
        $totalRevenue = DB::table('group4 as p')
            ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
            ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
            ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
            ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
            ->where('r.tahun', $year)
            ->sum('r.revenue_realisasi');

        foreach ($subsegments as $subsegment) {
            // Get subsegment totals
            $subsegmentRevenue = DB::table('group4 as p')
                ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
                ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
                ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
                ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
                ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
                ->where('companies.subsegment', $subsegment)
                ->where('r.tahun', $year)
                ->sum('r.revenue_realisasi');

            $subsegmentCompanies = DB::table('group4 as p')
                ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
                ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
                ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
                ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
                ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
                ->where('companies.subsegment', $subsegment)
                ->where('r.tahun', $year)
                ->distinct('companies.nip_nas')
                ->count('companies.nip_nas');

            // Get regional breakdown
            $regionalBreakdown = [];
            $regions = \DB::table('regions')->whereNotNull('code')->orderBy('code')->get();

            foreach ($regions as $region) {
                // Get region revenue for this subsegment through company's witel
                $regionRevenue = DB::table('group4 as p')
                    ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
                    ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
                    ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
                    ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
                    ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
                    ->join('witels', 'companies.idwitels', '=', 'witels.idwitels')
                    ->where('witels.region_id', $region->id)
                    ->where('companies.subsegment', $subsegment)
                    ->where('r.tahun', $year)
                    ->sum('r.revenue_realisasi');

                if ($regionRevenue > 0) {
                    // Get top 3 companies in this region for this subsegment
                    $topCompanies = DB::table('companies')
                        ->join('group1', 'companies.nip_nas', '=', 'group1.company_id')
                        ->join('group2', 'group1.idGroup1', '=', 'group2.group1_id')
                        ->join('group3', 'group2.idGroup2', '=', 'group3.group2_id')
                        ->join('group4 as p', 'group3.idGroup3', '=', 'p.group3_id')
                        ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
                        ->join('witels', 'companies.idwitels', '=', 'witels.idwitels')
                        ->where('witels.region_id', $region->id)
                        ->where('companies.subsegment', $subsegment)
                        ->where('r.tahun', $year)
                        ->select(
                            'companies.nip_nas',
                            'companies.nama_perusahaan',
                            DB::raw('SUM(r.revenue_realisasi) as total_revenue')
                        )
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

                    $regionCompanyCount = DB::table('group4 as p')
                        ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
                        ->join('group3', 'p.group3_id', '=', 'group3.idGroup3')
                        ->join('group2', 'group3.group2_id', '=', 'group2.idGroup2')
                        ->join('group1', 'group2.group1_id', '=', 'group1.idGroup1')
                        ->join('companies', 'group1.company_id', '=', 'companies.nip_nas')
                        ->join('witels', 'companies.idwitels', '=', 'witels.idwitels')
                        ->where('witels.region_id', $region->id)
                        ->where('companies.subsegment', $subsegment)
                        ->where('r.tahun', $year)
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