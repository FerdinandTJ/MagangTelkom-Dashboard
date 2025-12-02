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

        \Log::info('Region NKI Request:', [
            'region_id' => $regionId,
            'quarter' => $quarter,
            'year' => $year,
            'compare' => $enableCompare
        ]);

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
            \Log::warning('No AM companies found in region');
            return null;
        }

        // Get targets for these account_manager_company records
        $targets = TargetAccountM::whereIn('account_manager_company_id', $amCompanyIds)->get();

        \Log::info('Targets found:', ['target_count' => $targets->count(), 'target_ids' => $targets->pluck('id')->toArray()]);

        if ($targets->isEmpty()) {
            \Log::warning('No targets found for AM companies');
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

        // Get pivot data with realizations for this period
        $pivotData = DB::table('lini_waktu_target as lwt')
            ->join('target_account_m as t', 'lwt.target_id', '=', 't.id')
            ->whereIn('lwt.target_id', $targets->pluck('id'))
            ->whereIn('lwt.lini_waktu_id', $liniWaktuIds)
            ->select('lwt.*', 't.t_revenue')
            ->get();

        \Log::info('Pivot data found:', ['pivot_count' => $pivotData->count()]);

        // Calculate summary metrics from pivot data (target from target_account_m, realisasi from pivot)
        $targetRevenue = $pivotData->sum('t_revenue');
        $realisasiRevenue = $pivotData->sum('r_revenue');
        
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

            // Get pivot records for calculation with target revenue
            $segmentPivots = DB::table('lini_waktu_target as lwt')
                ->join('target_account_m as t', 'lwt.target_id', '=', 't.id')
                ->whereIn('lwt.target_id', $targetIds)
                ->whereIn('lwt.lini_waktu_id', $liniWaktuIds)
                ->select('lwt.*', 't.t_revenue')
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
        
        \Log::info('Format currency:', ['value' => $value, 'formatted' => $formatted]);
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
}
