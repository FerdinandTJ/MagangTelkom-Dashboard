<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\AccountManager;
use App\Models\LiniWaktu;
use App\Models\TargetAccountM;
use Illuminate\Support\Facades\DB;

class RegionNkiController extends Controller
{
    public function getRegionNkiData(Request $request, $regionId)
    {
        $request->validate([
            'quarter' => 'required|integer|min:1|max:4',
            'year' => 'required|integer|min:2020',
            'compare' => 'nullable|in:true,false,1,0',
            'compare_quarter' => 'nullable|integer|min:1|max:4',
            'compare_year' => 'nullable|integer|min:2020'
        ]);

        $quarter = $request->quarter;
        $year = $request->year;
        $enableCompare = $request->boolean('compare', false) && $request->has('compare_quarter') && $request->has('compare_year');

        // Validate: Cannot compare with the same period
        if ($enableCompare) {
            $compareQuarter = $request->compare_quarter;
            $compareYear = $request->compare_year;
            
            if ($compareQuarter == $quarter && $compareYear == $year) {
                return response()->json([
                    'error' => 'Cannot compare with the same period. Please select a different quarter or year.'
                ], 400);
            }
        }

        // Get region info
        $region = Region::findOrFail($regionId);
        
        // Get current period data
        $currentData = $this->getPeriodData($regionId, $quarter, $year);
        
        // Get comparison period data if compare is enabled
        $comparisonData = null;
        $comparisonPeriod = null;
        
        if ($enableCompare) {
            $compareQuarter = $request->compare_quarter;
            $compareYear = $request->compare_year;
            $comparisonPeriod = [
                'quarter' => $compareQuarter,
                'year' => $compareYear
            ];
            $comparisonData = $this->getPeriodData($regionId, $compareQuarter, $compareYear);
        }
        
        // Return different structure based on compare mode
        if ($enableCompare && $comparisonData) {
            return response()->json([
                'region' => [
                    'id' => $region->id,
                    'name' => $region->name
                ],
                'current_period' => [
                    'quarter' => $quarter,
                    'year' => $year,
                    'label' => "Q{$quarter} {$year}",
                    'data' => $currentData
                ],
                'comparison_period' => [
                    'quarter' => $comparisonPeriod['quarter'],
                    'year' => $comparisonPeriod['year'],
                    'label' => "Q{$comparisonPeriod['quarter']} {$comparisonPeriod['year']}",
                    'data' => $comparisonData
                ],
                'compare_enabled' => true
            ]);
        } else {
            // Default mode - old structure
            return response()->json([
                'region' => [
                    'id' => $region->id,
                    'name' => $region->name
                ],
                'period' => [
                    'quarter' => $quarter,
                    'year' => $year
                ],
                'summary' => $currentData['summary'],
                'segment_stats' => $currentData['segment_stats'],
                'parameter_result' => $currentData['parameter_result'],
                'parameter_proses' => $currentData['parameter_proses']
            ]);
        }
    }
    
    private function getPreviousPeriod($quarter, $year)
    {
        if ($quarter == 1) {
            return ['quarter' => 4, 'year' => $year - 1];
        } else {
            return ['quarter' => $quarter - 1, 'year' => $year];
        }
    }
    
    private function getPeriodData($regionId, $quarter, $year)
    {
        // Get region info
        $region = Region::findOrFail($regionId);
        
        \Log::info('Region found:', ['region' => $region->toArray()]);

        // Get all account_manager_company IDs in this region (through witels)
        $amCompanyIds = DB::table('account_manager_company')
            ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->where('witels.region_id', $regionId)
            ->pluck('account_manager_company.id');
        
        \Log::info('AM Company IDs in region:', ['count' => $amCompanyIds->count(), 'ids' => $amCompanyIds->toArray()]);

        if ($amCompanyIds->isEmpty()) {

            return null;
        }

        // Get targets for these account_manager_company records
        $targets = TargetAccountM::whereIn('account_manager_company_id', $amCompanyIds)->get();

        \Log::info('Targets found:', ['target_count' => $targets->count(), 'target_ids' => $targets->pluck('id')->toArray()]);

        if ($targets->isEmpty()) {

            return null;
        }

        // Get NIKs for the AMs in this region
        $amNiks = DB::table('account_manager_company')
            ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->where('witels.region_id', $regionId)
            ->distinct()
            ->pluck('account_managers.nik');

        \Log::info('AM NIKs in region:', ['niks' => $amNiks->toArray()]);

        // Get lini_waktu for these AMs in the specified period
        $liniWaktuIds = LiniWaktu::whereIn('nik_am', $amNiks)
            ->where('quartal', 'Q' . $quarter)
            ->where('tahun', $year)
            ->pluck('id');

        \Log::info('Lini Waktu IDs found:', ['count' => $liniWaktuIds->count(), 'ids' => $liniWaktuIds->toArray()]);

        if ($liniWaktuIds->isEmpty()) {
            \Log::error('Lini Waktu not found for AMs in region', ['quarter' => $quarter, 'year' => $year, 'niks' => $amNiks->toArray()]);
            return null;
        }

        // Get pivot data with realizations for this period, with proporsi from account_manager_company
        $pivotData = DB::table('lini_waktu_target as lwt')
            ->join('target_account_m as t', 'lwt.target_id', '=', 't.id')
            ->join('account_manager_company as amc', 't.account_manager_company_id', '=', 'amc.id')
            ->whereIn('lwt.target_id', $targets->pluck('id'))
            ->whereIn('lwt.lini_waktu_id', $liniWaktuIds)
            ->select('lwt.*', 't.t_revenue', 'amc.proporsi')
            ->get();

        \Log::info('Pivot data found:', ['pivot_count' => $pivotData->count()]);

        // Calculate summary metrics from pivot data (target from target_account_m, realisasi from pivot)
        // Apply proporsi to avoid double-counting when companies are shared between AMs
        $targetRevenue = $pivotData->sum(function($row) {
            return $row->t_revenue * ($row->proporsi / 100);
        });
        $realisasiRevenue = $pivotData->sum(function($row) {
            return $row->r_revenue * ($row->proporsi / 100);
        });
        
        // Count unique AMs in the region
        $totalAm = DB::table('account_manager_company')
            ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->where('witels.region_id', $regionId)
            ->distinct('account_managers.nik')
            ->count('account_managers.nik');

        // Get segments data with their target IDs (through witels join)
        $segmentData = DB::table('account_manager_company')
            ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->join('target_account_m', 'account_manager_company.id', '=', 'target_account_m.account_manager_company_id')
            ->where('witels.region_id', $regionId)
            ->whereNotNull('account_manager_company.segment')
            ->select(
                'account_manager_company.segment',
                'target_account_m.id as target_id'
            )
            ->get()
            ->groupBy('segment');

        \Log::info('Segment data:', ['segment_count' => $segmentData->count()]);

        // Calculate segment statistics
        $segmentStats = [];
        
        // Get lini_waktu records for threshold values
        $liniWaktuRecords = LiniWaktu::whereIn('id', $liniWaktuIds)->get()->keyBy('id');
        $sampleLiniWaktu = $liniWaktuRecords->first();
        
        foreach ($segmentData as $segment => $targets) {
            $targetIds = $targets->pluck('target_id');

            // Get pivot records for calculation with target revenue and proporsi
            $segmentPivots = DB::table('lini_waktu_target as lwt')
                ->join('target_account_m as t', 'lwt.target_id', '=', 't.id')
                ->join('account_manager_company as amc', 't.account_manager_company_id', '=', 'amc.id')
                ->whereIn('lwt.target_id', $targetIds)
                ->whereIn('lwt.lini_waktu_id', $liniWaktuIds)
                ->select('lwt.*', 't.t_revenue', 'amc.proporsi')
                ->get();

            \Log::info("Segment '{$segment}' pivots:", ['count' => $segmentPivots->count()]);

            // Group pivots by lini_waktu_id (per AM)
            $pivotsByAM = $segmentPivots->groupBy('lini_waktu_id');
            
            \Log::info("Segment '{$segment}' unique AMs:", ['am_count' => $pivotsByAM->count()]);

            $resultAch = 0;
            $resultNotAch = 0;
            $prosesAch = 0;
            $prosesNotAch = 0;
            $nkiAbove100 = 0;
            $nkiBelow100 = 0;
            $nkiValues = [];

            // Calculate per AM (not per pivot)
            foreach ($pivotsByAM as $liniWaktuId => $pivots) {
                $liniWaktu = $liniWaktuRecords[$liniWaktuId];
                
                // Sum all achievements for this AM across all their assignments in this segment
                $totalAchResult = $pivots->sum('ach_result');
                $totalAchProses = $pivots->sum('ach_proses');
                
                // Get average NKI for this AM
                $avgNki = $pivots->avg('nki_adjustment');
                $nkiValues[] = $avgNki;

                // Compare with thresholds
                if ($totalAchResult >= $liniWaktu->percentage_result) {
                    $resultAch++;
                } else {
                    $resultNotAch++;
                }

                if ($totalAchProses >= $liniWaktu->percentage_proses) {
                    $prosesAch++;
                } else {
                    $prosesNotAch++;
                }

                // NKI analysis (nki_adjustment is already in percentage: 70-130%)
                if ($avgNki >= 100) {
                    $nkiAbove100++;
                } else {
                    $nkiBelow100++;
                }
            }

            $segmentStats[] = [
                'segment' => $segment,
                'result' => [
                    'ach' => $resultAch,
                    'not_ach' => $resultNotAch
                ],
                'proses' => [
                    'ach' => $prosesAch,
                    'not_ach' => $prosesNotAch
                ],
                'nki' => [
                    'above_100' => $nkiAbove100,
                    'below_100' => $nkiBelow100
                ],
                'highest_nki' => count($nkiValues) > 0 ? (float) max($nkiValues) : 0.0,
                'lowest_nki' => count($nkiValues) > 0 ? (float) min($nkiValues) : 0.0,
                'avg_nki' => count($nkiValues) > 0 ? (float) round(array_sum($nkiValues) / count($nkiValues), 2) : 0.0,
                'total_am' => $pivotsByAM->count() // Store total AM for this segment
            ];
        }

        // Calculate parameter statistics for Result aspect
        $resultParameters = [
            ['name' => 'Revenue', 'field' => 'ach_revenue_plan', 'percentage_field' => 'percentage_revenue'],
            ['name' => 'Scaling', 'field' => 'ach_scaling', 'percentage_field' => 'percentage_scaling'],
            ['name' => 'Sales-Datin', 'field' => 'ach_sales_datin', 'percentage_field' => 'percentage_datin'],
            ['name' => 'Sales-HSI', 'field' => 'ach_hsi', 'percentage_field' => 'percentage_hsi'],
            ['name' => 'Sales-Wireline', 'field' => 'ach_wireline', 'percentage_field' => 'percentage_wireline'],
            ['name' => 'Sales-Wifi', 'field' => 'ach_wifi', 'percentage_field' => 'percentage_wifi'],
            ['name' => 'CYC', 'field' => 'ach_cyc', 'percentage_field' => 'percentage_cyc'],
            ['name' => 'CR', 'field' => 'ach_cr', 'percentage_field' => 'percentage_cr'],
            ['name' => 'Profitability', 'field' => 'ach_profit', 'percentage_field' => 'percentage_profit'],
            ['name' => 'Customer(NPS)', 'field' => 'ach_nps', 'percentage_field' => 'percentage_customer']
        ];

        // Group pivot data by lini_waktu_id (one per AM)
        $pivotsByLiniWaktu = $pivotData->groupBy('lini_waktu_id');
        $liniWaktuRecords = LiniWaktu::whereIn('id', $liniWaktuIds)->get()->keyBy('id');

        $resultParameterStats = [];
        foreach ($resultParameters as $param) {
            $ach = 0;
            $notAch = 0;
            
            // Get bobot from first lini_waktu (should be same for all AMs in same period)
            $bobot = $sampleLiniWaktu->{$param['percentage_field']} ?? 0;

            // Calculate per AM (per lini_waktu_id)
            foreach ($pivotsByLiniWaktu as $liniWaktuId => $pivots) {
                // Sum all achievements for this AM across all their assignments
                $totalAch = $pivots->sum($param['field']);
                
                // Compare with bobot
                if ($totalAch >= $bobot) {
                    $ach++;
                } else {
                    $notAch++;
                }
            }

            $resultParameterStats[] = [
                'parameter' => $param['name'],
                'bobot' => $bobot,
                'ach' => $ach,
                'not_ach' => $notAch
            ];
        }

        // Calculate parameter statistics for Process aspect
        $prosesParameters = [
            ['name' => 'MAPS', 'field' => 'ach_maps', 'percentage_field' => 'percentage_maps'],
            ['name' => 'Kecukupan LOP', 'field' => 'ach_lop', 'percentage_field' => 'percentage_lop'],
            ['name' => 'Capability', 'field' => 'ach_capability', 'percentage_field' => 'percentage_capability'],
            ['name' => 'Behavior', 'field' => 'ach_cc', 'percentage_field' => 'percentage_cc']
        ];

        $prosesParameterStats = [];
        foreach ($prosesParameters as $param) {
            $ach = 0;
            $notAch = 0;
            
            // Get bobot from first lini_waktu
            $bobot = $sampleLiniWaktu->{$param['percentage_field']} ?? 0;

            // Calculate per AM (per lini_waktu_id)
            foreach ($pivotsByLiniWaktu as $liniWaktuId => $pivots) {
                // Sum all achievements for this AM across all their assignments
                $totalAch = $pivots->sum($param['field']);
                
                // Compare with bobot
                if ($totalAch >= $bobot) {
                    $ach++;
                } else {
                    $notAch++;
                }
            }

            $prosesParameterStats[] = [
                'parameter' => $param['name'],
                'bobot' => $bobot,
                'ach' => $ach,
                'not_ach' => $notAch
            ];
        }

        // Calculate "Parameter To Be Improve" for each segment
        // Parameters with Ach < 50% of total AM in segment
        foreach ($segmentStats as &$segmentStat) {
            $segment = $segmentStat['segment'];
            $totalAmInSegment = $segmentStat['total_am'];
            $threshold = $totalAmInSegment * 0.5; // 50% threshold
            
            $parametersToImprove = [];
            
            // Check Result parameters
            foreach ($resultParameterStats as $param) {
                if ($param['ach'] < $threshold) {
                    $parametersToImprove[] = $param['parameter'];
                }
            }
            
            // Check Process parameters
            foreach ($prosesParameterStats as $param) {
                if ($param['ach'] < $threshold) {
                    $parametersToImprove[] = $param['parameter'];
                }
            }
            
            // Add to segment stat
            $segmentStat['parameters_to_improve'] = implode(', ', $parametersToImprove);
        }

        return [
            'summary' => [
                'target_revenue' => $targetRevenue,
                'formatted_target_revenue' => $this->formatCurrency($targetRevenue, 2),
                'realisasi_revenue' => $realisasiRevenue,
                'formatted_realisasi_revenue' => $this->formatCurrency($realisasiRevenue, 2),
                'total_am' => $totalAm
            ],
            'segment_stats' => $segmentStats,
            'parameter_result' => [
                'percentage_result' => $sampleLiniWaktu->percentage_result ?? 0,
                'parameters' => $resultParameterStats
            ],
            'parameter_proses' => [
                'percentage_proses' => $sampleLiniWaktu->percentage_proses ?? 0,
                'parameters' => $prosesParameterStats
            ]
        ];
    }

    private function formatCurrency(float $value, int $decimals = 2): string
    {
        if ($value >= 1000000000000) {
            // Triliun (>= 1000 Miliar)
            $formatted = 'Rp ' . number_format($value / 1000000000000, $decimals, '.', ',') . 'T';
        } else {
            // Miliar
            $formatted = 'Rp ' . number_format($value / 1000000000, $decimals, '.', ',') . 'M';
        }

        return $formatted;
    }

    /**
     * Get available quarters and years from lini_waktu table
     */
    public function getAvailablePeriods(Request $request)
    {
        // Get all unique years and quarters from lini_waktu
        $periods = DB::table('lini_waktu')
            ->select('tahun as year', 'quartal as quarter')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->orderBy('quartal', 'desc')
            ->get();

        // Get unique years
        $years = $periods->pluck('year')->unique()->sort()->values()->toArray();
        
        // Get quarters for each year
        $quartersByYear = [];
        foreach ($years as $year) {
            $quarters = $periods->where('year', $year)
                ->pluck('quarter')
                ->map(function($q) {
                    return (int) str_replace('Q', '', $q);
                })
                ->unique()
                ->sort()
                ->values()
                ->toArray();
            
            $quartersByYear[$year] = $quarters;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'years' => $years,
                'quarters_by_year' => $quartersByYear
            ]
        ]);
    }

    /**
     * Get parameter chart data (Target, Realisasi, Achievement) for specific region and period
     */
    public function getParameterChartData(Request $request, $regionId)
    {
        $request->validate([
            'quarter' => 'required|integer|min:1|max:4',
            'year' => 'required|integer|min:2020'
        ]);

        $quarter = $request->quarter;
        $year = $request->year;
        $quartalCode = "Q{$quarter}";

        // Get region
        $region = Region::findOrFail($regionId);

        // Get AM NIKs in this region
        $amNiks = DB::table('account_managers')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->where('witels.region_id', $regionId)
            ->pluck('account_managers.nik');

        if ($amNiks->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No Account Managers found in this region'
            ], 404);
        }

        // Get lini_waktu IDs for this region and period
        $liniWaktuIds = DB::table('lini_waktu')
            ->whereIn('nik_am', $amNiks)
            ->where('tahun', $year)
            ->where('quartal', $quartalCode)
            ->pluck('id');

        if ($liniWaktuIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for this period'
            ], 404);
        }

        // Get sample lini_waktu for bobot (percentage fields)
        $sampleLiniWaktu = DB::table('lini_waktu')
            ->whereIn('id', $liniWaktuIds)
            ->first();

        // Get all lini_waktu_target for these lini_waktu IDs
        $liniWaktuTargets = DB::table('lini_waktu_target')
            ->whereIn('lini_waktu_id', $liniWaktuIds)
            ->pluck('target_id');

        // Get all target_account_m records
        $targets = DB::table('target_account_m')
            ->whereIn('id', $liniWaktuTargets)
            ->get();

        // Get all realisasi from lini_waktu_target
        $realisasi = DB::table('lini_waktu_target')
            ->whereIn('lini_waktu_id', $liniWaktuIds)
            ->get();

        // Define parameters mapping
        $parameters = [
            [
                'name' => 'Revenue',
                'target_field' => 't_revenue',
                'realisasi_field' => 'r_revenue',
                'bobot_field' => 'percentage_revenue'
            ],
            [
                'name' => 'Scaling',
                'target_field' => 't_scalling',
                'realisasi_field' => 'r_scalling',
                'bobot_field' => 'percentage_scaling'
            ],
            [
                'name' => 'Sales-Datin',
                'target_field' => 't_datin',
                'realisasi_field' => 'r_datin',
                'bobot_field' => 'percentage_datin'
            ],
            [
                'name' => 'Sales-HSI',
                'target_field' => 't_hsi',
                'realisasi_field' => 'r_hsi',
                'bobot_field' => 'percentage_hsi'
            ],
            [
                'name' => 'Sales-Wireline',
                'target_field' => 't_wireline',
                'realisasi_field' => 'r_wireline',
                'bobot_field' => 'percentage_wireline'
            ],
            [
                'name' => 'Sales-Wifi',
                'target_field' => 't_wifi',
                'realisasi_field' => 'r_wifi',
                'bobot_field' => 'percentage_wifi'
            ],
            [
                'name' => 'CYC',
                'target_field' => 't_cyc',
                'realisasi_field' => 'r_cyc',
                'bobot_field' => 'percentage_cyc'
            ],
            [
                'name' => 'CR',
                'target_field' => 't_cr',
                'realisasi_field' => 'r_cr',
                'bobot_field' => 'percentage_cr'
            ],
            [
                'name' => 'Profitability',
                'target_field' => 't_profit',
                'realisasi_field' => 'r_profit',
                'bobot_field' => 'percentage_profit'
            ],
            [
                'name' => 'Customer (NPS)',
                'target_field' => 't_nps',
                'realisasi_field' => 'r_nps',
                'bobot_field' => 'percentage_customer'
            ],
            [
                'name' => 'MAPS',
                'target_field' => 't_maps',
                'realisasi_field' => 'r_maps',
                'bobot_field' => 'percentage_maps'
            ],
            [
                'name' => 'Kecukupan LOP',
                'target_field' => 't_lop',
                'realisasi_field' => 'r_lop',
                'bobot_field' => 'percentage_lop'
            ],
            [
                'name' => 'Capability',
                'target_field' => 't_capability',
                'realisasi_field' => 'r_capability',
                'bobot_field' => 'percentage_capability'
            ],
            [
                'name' => 'Behavior',
                'target_field' => 't_cc',
                'realisasi_field' => 'r_cc',
                'bobot_field' => 'percentage_cc'
            ]
        ];

        $chartData = [];

        foreach ($parameters as $param) {
            // Calculate total target
            $totalTarget = $targets->sum($param['target_field']);

            // Calculate total realisasi
            $totalRealisasi = $realisasi->sum($param['realisasi_field']);

            // Get bobot from lini_waktu
            $bobot = $sampleLiniWaktu->{$param['bobot_field']} ?? 0;

            // Calculate achievement percentage
            // Ach = (Realisasi / Target) * Bobot
            $achPercentage = 0;
            if ($totalTarget > 0) {
                $achPercentage = ($totalRealisasi / $totalTarget) * $bobot;
            }

            $chartData[] = [
                'parameter' => $param['name'],
                'target' => round($totalTarget, 2),
                'realisasi' => round($totalRealisasi, 2),
                'bobot' => round($bobot, 2),
                'ach' => round($achPercentage, 2)
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'region' => [
                    'id' => $region->id,
                    'name' => $region->name
                ],
                'period' => [
                    'quarter' => $quarter,
                    'year' => $year
                ],
                'parameters' => $chartData
            ]
        ]);
    }

    public function getWitelNkiDetail(Request $request)
    {
        $request->validate([
            'region_id' => 'required|integer',
            'quarter' => 'required|integer|min:1|max:4',
            'year' => 'required|integer|min:2020',
            'segment' => 'required|string',
            'witel_id' => 'nullable|integer'
        ]);

        $regionId = $request->region_id;
        $quarter = $request->quarter;
        $year = $request->year;
        $segment = $request->segment;
        $witelId = $request->witel_id;

        // Get region info
        $region = Region::findOrFail($regionId);

        // Get all account managers in the region for the specified segment
        $amQuery = DB::table('account_managers as am')
            ->join('witels as w', 'am.idwitels', '=', 'w.idwitels')
            ->join('account_manager_company as amc', 'am.nik', '=', 'amc.nik_am')
            ->where('w.region_id', $regionId)
            ->where('amc.segment', $segment);

        if ($witelId) {
            $amQuery->where('am.idwitels', $witelId);
        }

        $accountManagers = $amQuery
            ->select('am.nik', 'am.nama', 'w.idwitels as witel_id', 'w.nama_witels as witel_name')
            ->distinct()
            ->get();

        \Log::info('Account Managers found:', ['count' => $accountManagers->count()]);

        if ($accountManagers->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'witel_info' => [
                        'witel_id' => $witelId,
                        'witel_name' => 'N/A',
                        'region_code' => $region->name,
                        'segment' => $segment
                    ],
                    'period' => [
                        'quarter' => $quarter,
                        'year' => $year,
                        'period_display' => "Q{$quarter} {$year}"
                    ],
                    'summary' => [
                        'total_am' => 0,
                        'total_target_revenue' => 0,
                        'formatted_total_target_revenue' => 'Rp 0',
                        'total_realisasi_revenue' => 0,
                        'formatted_total_realisasi_revenue' => 'Rp 0',
                        'avg_nki' => 0
                    ],
                    'am_list' => [],
                    'parameter_result' => ['percentage_result' => 75, 'parameters' => []],
                    'parameter_proses' => ['percentage_proses' => 25, 'parameters' => []]
                ]
            ]);
        }

        $amNiks = $accountManagers->pluck('nik')->toArray();

        // Get lini_waktu for these AMs in the specified period
        $liniWaktuData = LiniWaktu::whereIn('nik_am', $amNiks)
            ->where('quartal', 'Q' . $quarter)
            ->where('tahun', $year)
            ->get()
            ->keyBy('nik_am');

        \Log::info('Lini Waktu found:', ['count' => $liniWaktuData->count()]);

        if ($liniWaktuData->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'witel_info' => [
                        'witel_id' => $witelId,
                        'witel_name' => $accountManagers->first()->witel_name ?? 'N/A',
                        'region_code' => $region->name,
                        'segment' => $segment
                    ],
                    'period' => [
                        'quarter' => $quarter,
                        'year' => $year,
                        'period_display' => "Q{$quarter} {$year}"
                    ],
                    'summary' => [
                        'total_am' => $accountManagers->count(),
                        'total_target_revenue' => 0,
                        'formatted_total_target_revenue' => 'Rp 0',
                        'total_realisasi_revenue' => 0,
                        'formatted_total_realisasi_revenue' => 'Rp 0',
                        'avg_nki' => 0
                    ],
                    'am_list' => [],
                    'parameter_result' => ['percentage_result' => 75, 'parameters' => []],
                    'parameter_proses' => ['percentage_proses' => 25, 'parameters' => []]
                ]
            ]);
        }

        $liniWaktuIds = $liniWaktuData->pluck('id')->toArray();

        // Get AM list with their performance data
        $amList = [];
        $totalTargetRevenue = 0;
        $totalRealisasiRevenue = 0;
        $totalNki = 0;
        $nkiCount = 0;

        // Parameter aggregation
        $parameterStats = [
            'result' => [
                'Revenue' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'Scaling' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'Sales Datin' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'HSI' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'Wireline' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'WiFi' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'CYC' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'CR' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'Profit' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'NPS' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0]
            ],
            'proses' => [
                'MAPS' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'LOP' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'Capability' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0],
                'CC' => ['ach' => 0, 'not_ach' => 0, 'total_ach' => 0, 'bobot' => 0]
            ]
        ];

        foreach ($accountManagers as $am) {
            $liniWaktu = $liniWaktuData->get($am->nik);
            
            if (!$liniWaktu) {

                continue;
            }

            // Get bobot (percentage) for each parameter from lini_waktu
            $bobotRevenue = $liniWaktu->percentage_revenue ?? 0;
            $bobotScaling = $liniWaktu->percentage_scaling ?? 0;
            $bobotDatin = $liniWaktu->percentage_datin ?? 0;
            $bobotHsi = $liniWaktu->percentage_hsi ?? 0;
            $bobotWireline = $liniWaktu->percentage_wireline ?? 0;
            $bobotWifi = $liniWaktu->percentage_wifi ?? 0;
            $bobotCyc = $liniWaktu->percentage_cyc ?? 0;
            $bobotCr = $liniWaktu->percentage_cr ?? 0;
            $bobotProfit = $liniWaktu->percentage_profit ?? 0;
            $bobotNps = $liniWaktu->percentage_customer ?? 0;
            $bobotMaps = $liniWaktu->percentage_maps ?? 0;
            $bobotLop = $liniWaktu->percentage_lop ?? 0;
            $bobotCapability = $liniWaktu->percentage_capability ?? 0;
            $bobotCc = $liniWaktu->percentage_cc ?? 0;

            // Get pivot data with targets and proporsi - same method as getPeriodData
            $pivotData = DB::table('lini_waktu_target as lwt')
                ->join('target_account_m as t', 'lwt.target_id', '=', 't.id')
                ->join('account_manager_company as amc', 't.account_manager_company_id', '=', 'amc.id')
                ->where('lwt.lini_waktu_id', $liniWaktu->id)
                ->where('amc.nik_am', $am->nik)
                ->where('amc.segment', $segment)
                ->select('lwt.*', 't.*', 'amc.proporsi')
                ->get();

            \Log::info("AM {$am->nik} pivot data:", ['count' => $pivotData->count()]);

            if ($pivotData->isEmpty()) {
                continue;
            }

            // Aggregate targets and realisasi for this AM with proporsi - same calculation as getPeriodData
            $amTargetRevenue = $pivotData->sum(function($row) {
                return $row->t_revenue * ($row->proporsi / 100);
            });
            \Log::info("AM {$am->nik} Target Revenue:", ['amount' => $amTargetRevenue, 'targets_count' => $pivotData->count()]);
            $amRealisasiRevenue = $pivotData->sum(function($row) {
                return $row->r_revenue * ($row->proporsi / 100);
            });
            $amTargetScaling = $pivotData->sum(function($row) {
                return $row->t_scalling * ($row->proporsi / 100);
            });
            $amRealisasiScaling = $pivotData->sum(function($row) {
                return $row->r_scalling * ($row->proporsi / 100);
            });
            $amTargetDatin = $pivotData->sum(function($row) {
                return $row->t_datin * ($row->proporsi / 100);
            });
            $amRealisasiDatin = $pivotData->sum(function($row) {
                return $row->r_datin * ($row->proporsi / 100);
            });
            $amTargetHsi = $pivotData->sum(function($row) {
                return $row->t_hsi * ($row->proporsi / 100);
            });
            $amRealisasiHsi = $pivotData->sum(function($row) {
                return $row->r_hsi * ($row->proporsi / 100);
            });
            $amTargetWireline = $pivotData->sum(function($row) {
                return $row->t_wireline * ($row->proporsi / 100);
            });
            $amRealisasiWireline = $pivotData->sum(function($row) {
                return $row->r_wireline * ($row->proporsi / 100);
            });
            $amTargetWifi = $pivotData->sum(function($row) {
                return $row->t_wifi * ($row->proporsi / 100);
            });
            $amRealisasiWifi = $pivotData->sum(function($row) {
                return $row->r_wifi * ($row->proporsi / 100);
            });
            $amTargetCyc = $pivotData->sum(function($row) {
                return $row->t_cyc * ($row->proporsi / 100);
            });
            $amRealisasiCyc = $pivotData->sum(function($row) {
                return $row->r_cyc * ($row->proporsi / 100);
            });
            $amTargetCr = $pivotData->sum(function($row) {
                return $row->t_cr * ($row->proporsi / 100);
            });
            $amRealisasiCr = $pivotData->sum(function($row) {
                return $row->r_cr * ($row->proporsi / 100);
            });
            $amTargetProfit = $pivotData->sum(function($row) {
                return $row->t_profit * ($row->proporsi / 100);
            });
            $amRealisasiProfit = $pivotData->sum(function($row) {
                return $row->r_profit * ($row->proporsi / 100);
            });
            $amTargetNps = $pivotData->sum(function($row) {
                return $row->t_nps * ($row->proporsi / 100);
            });
            $amRealisasiNps = $pivotData->sum(function($row) {
                return $row->r_nps * ($row->proporsi / 100);
            });
            $amTargetMaps = $pivotData->sum(function($row) {
                return $row->t_maps * ($row->proporsi / 100);
            });
            $amRealisasiMaps = $pivotData->sum(function($row) {
                return $row->r_maps * ($row->proporsi / 100);
            });
            $amTargetLop = $pivotData->sum(function($row) {
                return $row->t_lop * ($row->proporsi / 100);
            });
            $amRealisasiLop = $pivotData->sum(function($row) {
                return $row->r_lop * ($row->proporsi / 100);
            });
            $amTargetCapability = $pivotData->sum(function($row) {
                return $row->t_capability * ($row->proporsi / 100);
            });
            $amRealisasiCapability = $pivotData->sum(function($row) {
                return $row->r_capability * ($row->proporsi / 100);
            });
            $amTargetCc = $pivotData->sum(function($row) {
                return $row->t_cc * ($row->proporsi / 100);
            });
            $amRealisasiCc = $pivotData->sum(function($row) {
                return $row->r_cc * ($row->proporsi / 100);
            });

            // Calculate achievements with bobot
            $achRevenue = $amTargetRevenue > 0 ? ($amRealisasiRevenue / $amTargetRevenue) * $bobotRevenue : 0;
            $achScaling = $amTargetScaling > 0 ? ($amRealisasiScaling / $amTargetScaling) * $bobotScaling : 0;
            $achDatin = $amTargetDatin > 0 ? ($amRealisasiDatin / $amTargetDatin) * $bobotDatin : 0;
            $achHsi = $amTargetHsi > 0 ? ($amRealisasiHsi / $amTargetHsi) * $bobotHsi : 0;
            $achWireline = $amTargetWireline > 0 ? ($amRealisasiWireline / $amTargetWireline) * $bobotWireline : 0;
            $achWifi = $amTargetWifi > 0 ? ($amRealisasiWifi / $amTargetWifi) * $bobotWifi : 0;
            $achCyc = $amTargetCyc > 0 ? ($amRealisasiCyc / $amTargetCyc) * $bobotCyc : 0;
            $achCr = $amTargetCr > 0 ? ($amRealisasiCr / $amTargetCr) * $bobotCr : 0;
            $achProfit = $amTargetProfit > 0 ? ($amRealisasiProfit / $amTargetProfit) * $bobotProfit : 0;
            $achNps = $amTargetNps > 0 ? ($amRealisasiNps / $amTargetNps) * $bobotNps : 0;
            $achMaps = $amTargetMaps > 0 ? ($amRealisasiMaps / $amTargetMaps) * $bobotMaps : 0;
            $achLop = $amTargetLop > 0 ? ($amRealisasiLop / $amTargetLop) * $bobotLop : 0;
            $achCapability = $amTargetCapability > 0 ? ($amRealisasiCapability / $amTargetCapability) * $bobotCapability : 0;
            $achCc = $amTargetCc > 0 ? ($amRealisasiCc / $amTargetCc) * $bobotCc : 0;

            // Get ach_result, ach_proses, nki_adjustment from pivot
            $achResult = $pivotData->avg('ach_result') ?? 0;
            $achProses = $pivotData->avg('ach_proses') ?? 0;
            $nkiAdjustment = $pivotData->avg('nki_adjustment') ?? 0;

            $totalTargetRevenue += $amTargetRevenue;
            $totalRealisasiRevenue += $amRealisasiRevenue;
            $totalNki += $nkiAdjustment;
            $nkiCount++;
            
            \Log::info("Running Total Target Revenue:", ['total' => $totalTargetRevenue]);

            // Format currency
            $formattedTargetRevenue = $this->formatCurrency($amTargetRevenue);
            $formattedRealisasiRevenue = $this->formatCurrency($amRealisasiRevenue);
            $formattedTargetScaling = $this->formatCurrency($amTargetScaling);
            $formattedRealisasiScaling = $this->formatCurrency($amRealisasiScaling);
            $formattedTargetLop = $this->formatCurrency($amTargetLop);
            $formattedRealisasiLop = $this->formatCurrency($amRealisasiLop);

            $amList[] = [
                'nik_am' => $am->nik,
                'nama_am' => $am->nama,
                't_revenue' => $amTargetRevenue,
                'r_revenue' => $amRealisasiRevenue,
                'ach_revenue_plan' => $achRevenue,
                't_scaling' => $amTargetScaling,
                'r_scaling' => $amRealisasiScaling,
                'ach_scaling' => $achScaling,
                't_sales_datin' => $amTargetDatin,
                'r_sales_datin' => $amRealisasiDatin,
                'ach_sales_datin' => $achDatin,
                't_hsi' => $amTargetHsi,
                'r_hsi' => $amRealisasiHsi,
                'ach_hsi' => $achHsi,
                't_wireline' => $amTargetWireline,
                'r_wireline' => $amRealisasiWireline,
                'ach_wireline' => $achWireline,
                't_wifi' => $amTargetWifi,
                'r_wifi' => $amRealisasiWifi,
                'ach_wifi' => $achWifi,
                't_cyc' => $amTargetCyc,
                'r_cyc' => $amRealisasiCyc,
                'ach_cyc' => $achCyc,
                't_cr' => $amTargetCr,
                'r_cr' => $amRealisasiCr,
                'ach_cr' => $achCr,
                't_profit' => $amTargetProfit,
                'r_profit' => $amRealisasiProfit,
                'ach_profit' => $achProfit,
                't_nps' => $amTargetNps,
                'r_nps' => $amRealisasiNps,
                'ach_nps' => $achNps,
                't_maps' => $amTargetMaps,
                'r_maps' => $amRealisasiMaps,
                'ach_maps' => $achMaps,
                't_lop' => $amTargetLop,
                'r_lop' => $amRealisasiLop,
                'ach_lop' => $achLop,
                't_capability' => $amTargetCapability,
                'r_capability' => $amRealisasiCapability,
                'ach_capability' => $achCapability,
                't_cc' => $amTargetCc,
                'r_cc' => $amRealisasiCc,
                'ach_cc' => $achCc,
                'ach_result' => $achResult,
                'ach_proses' => $achProses,
                'nki_adjustment' => $nkiAdjustment,
                'formatted_t_revenue' => $formattedTargetRevenue,
                'formatted_r_revenue' => $formattedRealisasiRevenue,
                'formatted_t_scaling' => $formattedTargetScaling,
                'formatted_r_scaling' => $formattedRealisasiScaling,
                'formatted_t_lop' => $formattedTargetLop,
                'formatted_r_lop' => $formattedRealisasiLop
            ];

            // Aggregate parameter stats
            $bobotRevenue = $liniWaktu->percentage_revenue ?? 0;
            $bobotScaling = $liniWaktu->percentage_scaling ?? 0;
            $bobotDatin = $liniWaktu->percentage_datin ?? 0;
            $bobotHsi = $liniWaktu->percentage_hsi ?? 0;
            $bobotWireline = $liniWaktu->percentage_wireline ?? 0;
            $bobotWifi = $liniWaktu->percentage_wifi ?? 0;
            $bobotCyc = $liniWaktu->percentage_cyc ?? 0;
            $bobotCr = $liniWaktu->percentage_cr ?? 0;
            $bobotProfit = $liniWaktu->percentage_profit ?? 0;
            $bobotNps = $liniWaktu->percentage_customer ?? 0;
            $bobotMaps = $liniWaktu->percentage_maps ?? 0;
            $bobotLop = $liniWaktu->percentage_lop ?? 0;
            $bobotCapability = $liniWaktu->percentage_capability ?? 0;
            $bobotCc = $liniWaktu->percentage_cc ?? 0;

            // Result parameters
            $parameterStats['result']['Revenue']['ach'] += $achRevenue >= 100 ? 1 : 0;
            $parameterStats['result']['Revenue']['not_ach'] += $achRevenue < 100 ? 1 : 0;
            $parameterStats['result']['Revenue']['total_ach'] += $achRevenue;
            $parameterStats['result']['Revenue']['bobot'] = $bobotRevenue;

            $parameterStats['result']['Scaling']['ach'] += $achScaling >= 100 ? 1 : 0;
            $parameterStats['result']['Scaling']['not_ach'] += $achScaling < 100 ? 1 : 0;
            $parameterStats['result']['Scaling']['total_ach'] += $achScaling;
            $parameterStats['result']['Scaling']['bobot'] = $bobotScaling;

            $parameterStats['result']['Sales Datin']['ach'] += $achDatin >= 100 ? 1 : 0;
            $parameterStats['result']['Sales Datin']['not_ach'] += $achDatin < 100 ? 1 : 0;
            $parameterStats['result']['Sales Datin']['total_ach'] += $achDatin;
            $parameterStats['result']['Sales Datin']['bobot'] = $bobotDatin;

            $parameterStats['result']['HSI']['ach'] += $achHsi >= 100 ? 1 : 0;
            $parameterStats['result']['HSI']['not_ach'] += $achHsi < 100 ? 1 : 0;
            $parameterStats['result']['HSI']['total_ach'] += $achHsi;
            $parameterStats['result']['HSI']['bobot'] = $bobotHsi;

            $parameterStats['result']['Wireline']['ach'] += $achWireline >= 100 ? 1 : 0;
            $parameterStats['result']['Wireline']['not_ach'] += $achWireline < 100 ? 1 : 0;
            $parameterStats['result']['Wireline']['total_ach'] += $achWireline;
            $parameterStats['result']['Wireline']['bobot'] = $bobotWireline;

            $parameterStats['result']['WiFi']['ach'] += $achWifi >= 100 ? 1 : 0;
            $parameterStats['result']['WiFi']['not_ach'] += $achWifi < 100 ? 1 : 0;
            $parameterStats['result']['WiFi']['total_ach'] += $achWifi;
            $parameterStats['result']['WiFi']['bobot'] = $bobotWifi;

            $parameterStats['result']['CYC']['ach'] += $achCyc >= 100 ? 1 : 0;
            $parameterStats['result']['CYC']['not_ach'] += $achCyc < 100 ? 1 : 0;
            $parameterStats['result']['CYC']['total_ach'] += $achCyc;
            $parameterStats['result']['CYC']['bobot'] = $bobotCyc;

            $parameterStats['result']['CR']['ach'] += $achCr >= 100 ? 1 : 0;
            $parameterStats['result']['CR']['not_ach'] += $achCr < 100 ? 1 : 0;
            $parameterStats['result']['CR']['total_ach'] += $achCr;
            $parameterStats['result']['CR']['bobot'] = $bobotCr;

            $parameterStats['result']['Profit']['ach'] += $achProfit >= 100 ? 1 : 0;
            $parameterStats['result']['Profit']['not_ach'] += $achProfit < 100 ? 1 : 0;
            $parameterStats['result']['Profit']['total_ach'] += $achProfit;
            $parameterStats['result']['Profit']['bobot'] = $bobotProfit;

            $parameterStats['result']['NPS']['ach'] += $achNps >= 100 ? 1 : 0;
            $parameterStats['result']['NPS']['not_ach'] += $achNps < 100 ? 1 : 0;
            $parameterStats['result']['NPS']['total_ach'] += $achNps;
            $parameterStats['result']['NPS']['bobot'] = $bobotNps;

            // Process parameters
            $parameterStats['proses']['MAPS']['ach'] += $achMaps >= 100 ? 1 : 0;
            $parameterStats['proses']['MAPS']['not_ach'] += $achMaps < 100 ? 1 : 0;
            $parameterStats['proses']['MAPS']['total_ach'] += $achMaps;
            $parameterStats['proses']['MAPS']['bobot'] = $bobotMaps;

            $parameterStats['proses']['LOP']['ach'] += $achLop >= 100 ? 1 : 0;
            $parameterStats['proses']['LOP']['not_ach'] += $achLop < 100 ? 1 : 0;
            $parameterStats['proses']['LOP']['total_ach'] += $achLop;
            $parameterStats['proses']['LOP']['bobot'] = $bobotLop;

            $parameterStats['proses']['Capability']['ach'] += $achCapability >= 100 ? 1 : 0;
            $parameterStats['proses']['Capability']['not_ach'] += $achCapability < 100 ? 1 : 0;
            $parameterStats['proses']['Capability']['total_ach'] += $achCapability;
            $parameterStats['proses']['Capability']['bobot'] = $bobotCapability;

            $parameterStats['proses']['CC']['ach'] += $achCc >= 100 ? 1 : 0;
            $parameterStats['proses']['CC']['not_ach'] += $achCc < 100 ? 1 : 0;
            $parameterStats['proses']['CC']['total_ach'] += $achCc;
            $parameterStats['proses']['CC']['bobot'] = $bobotCc;
        }

        $avgNki = $nkiCount > 0 ? $totalNki / $nkiCount : 0;

        // Format parameter data
        $resultParameters = [];
        foreach ($parameterStats['result'] as $name => $stats) {
            $totalAm = $stats['ach'] + $stats['not_ach'];
            $avgAch = $totalAm > 0 ? $stats['total_ach'] / $totalAm : 0;
            
            $resultParameters[] = [
                'parameter' => $name,
                'bobot' => $stats['bobot'],
                'ach_count' => $stats['ach'],
                'not_ach_count' => $stats['not_ach'],
                'avg_achievement' => $avgAch
            ];
        }

        $prosesParameters = [];
        foreach ($parameterStats['proses'] as $name => $stats) {
            $totalAm = $stats['ach'] + $stats['not_ach'];
            $avgAch = $totalAm > 0 ? $stats['total_ach'] / $totalAm : 0;
            
            $prosesParameters[] = [
                'parameter' => $name,
                'bobot' => $stats['bobot'],
                'ach_count' => $stats['ach'],
                'not_ach_count' => $stats['not_ach'],
                'avg_achievement' => $avgAch
            ];
        }

        \Log::info('Final AM List count:', ['count' => count($amList)]);
        \Log::info('FINAL Total Target Revenue:', ['amount' => $totalTargetRevenue, 'formatted' => $this->formatCurrency($totalTargetRevenue)]);

        // Get percentage_result and percentage_proses from lini_waktu
        $sampleLiniWaktu = $liniWaktuData->first();
        $percentageResult = $sampleLiniWaktu ? $sampleLiniWaktu->percentage_result : 75;
        $percentageProses = $sampleLiniWaktu ? $sampleLiniWaktu->percentage_proses : 25;

        return response()->json([
            'success' => true,
            'data' => [
                'witel_info' => [
                    'witel_id' => $witelId,
                    'witel_name' => $accountManagers->first()->witel_name ?? 'N/A',
                    'region_code' => $region->name,
                    'segment' => $segment
                ],
                'period' => [
                    'quarter' => $quarter,
                    'year' => $year,
                    'period_display' => "Q{$quarter} {$year}"
                ],
                'summary' => [
                    'total_am' => count($amList),
                    'total_target_revenue' => $totalTargetRevenue,
                    'formatted_total_target_revenue' => $this->formatCurrency($totalTargetRevenue),
                    'total_realisasi_revenue' => $totalRealisasiRevenue,
                    'formatted_total_realisasi_revenue' => $this->formatCurrency($totalRealisasiRevenue),
                    'avg_nki' => $avgNki
                ],
                'am_list' => $amList,
                'parameter_result' => [
                    'percentage_result' => $percentageResult,
                    'parameters' => $resultParameters
                ],
                'parameter_proses' => [
                    'percentage_proses' => $percentageProses,
                    'parameters' => $prosesParameters
                ]
            ]
        ]);
    }
}

