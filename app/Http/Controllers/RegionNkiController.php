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
        
        // Check if current period has no data
        if ($currentData === null) {
            return response()->json([
                'error' => "Belum ada data NKI {$region->name} untuk periode Q{$quarter} {$year}"
            ], 404);
        }
        
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
            
            // Check if comparison period has no data
            if ($comparisonData === null) {
                return response()->json([
                    'error' => "Belum ada data NKI {$region->name} untuk periode perbandingan Q{$compareQuarter} {$compareYear}"
                ], 404);
            }
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
            $allNkiValues = []; // Collect all individual nki_adjustment values
            $avgNkiValues = []; // Collect average NKI per AM for overall average

            // Calculate per AM (not per pivot)
            foreach ($pivotsByAM as $liniWaktuId => $pivots) {
                $liniWaktu = $liniWaktuRecords[$liniWaktuId];
                
                // Calculate AVERAGE achievements for this AM across all their assignments
                // Sum all achievements then divide by number of assignments
                $avgAchResult = $pivots->avg('ach_result');
                $avgAchProses = $pivots->avg('ach_proses');
                
                // Get average NKI for this AM (for overall average calculation)
                $avgNki = $pivots->avg('nki_adjustment');
                $avgNkiValues[] = $avgNki;
                
                // Collect all individual nki_adjustment values for highest/lowest
                foreach ($pivots as $pivot) {
                    $allNkiValues[] = $pivot->nki_adjustment;
                }

                // Compare AVERAGE with thresholds (not sum)
                if ($avgAchResult >= $liniWaktu->percentage_result) {
                    $resultAch++;
                } else {
                    $resultNotAch++;
                }

                if ($avgAchProses >= $liniWaktu->percentage_proses) {
                    $prosesAch++;
                } else {
                    $prosesNotAch++;
                }

                // NKI analysis: Count AM based on total nki_adjustment
                $totalNki = $pivots->sum('nki_adjustment');
                
                if ($totalNki >= 100) {
                    $nkiAbove100++;
                } else {
                    $nkiBelow100++;
                }
            }

            // Filter nki values > 0 for lowest calculation
            $nkiValuesAboveZero = array_filter($allNkiValues, function($value) {
                return $value > 0;
            });
            
            // Calculate average NKI: average of each AM's average NKI
            // $avgNkiValues already contains the average NKI per AM
            $totalAMs = $pivotsByAM->count();
            $averageNki = $totalAMs > 0 && count($avgNkiValues) > 0 
                ? (float) round(array_sum($avgNkiValues) / count($avgNkiValues), 2) 
                : 0.0;
            
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
                'highest_nki' => count($allNkiValues) > 0 ? (float) max($allNkiValues) : 0.0,
                'lowest_nki' => count($nkiValuesAboveZero) > 0 ? (float) min($nkiValuesAboveZero) : 0.0,
                'avg_nki' => $averageNki,
                'total_am' => $totalAMs
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
            ['name' => 'Customer (NPS)', 'field' => 'ach_nps', 'percentage_field' => 'percentage_customer']
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
                // Calculate AVERAGE achievement for this AM across all their assignments
                $avgAch = $pivots->avg($param['field']);
                
                // Compare average with bobot
                if ($avgAch >= $bobot) {
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
                // Calculate AVERAGE achievement for this AM across all their assignments
                $avgAch = $pivots->avg($param['field']);
                
                // Compare average with bobot
                if ($avgAch >= $bobot) {
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
            $formatted = 'Rp ' . number_format($value / 1000000000000, $decimals, ',', '.') . ' T';
        } elseif ($value >= 1000000000) {
            // Miliar (>= 1000 Juta)
            $formatted = 'Rp ' . number_format($value / 1000000000, $decimals, ',', '.') . ' M';
        } elseif ($value >= 1000000) {
            // Juta (>= 1 Juta)
            $formatted = 'Rp ' . number_format($value / 1000000, 0, ',', '.') . ' Jt';
        } elseif ($value >= 1000) {
            // Ribu (>= 1000)
            $formatted = 'Rp ' . number_format($value / 1000, 0, ',', '.') . ' Rb';
        } else {
            // Di bawah 1000
            $formatted = 'Rp ' . number_format($value, 0, ',', '.');
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
                    'parameters' => []
                ]
            ]);
        }

        // Get lini_waktu IDs for this region and period
        $liniWaktuIds = DB::table('lini_waktu')
            ->whereIn('nik_am', $amNiks)
            ->where('tahun', $year)
            ->where('quartal', $quartalCode)
            ->pluck('id');

        if ($liniWaktuIds->isEmpty()) {
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
                    'parameters' => []
                ]
            ]);
        }

        // Get all lini_waktu for bobot (percentage fields)
        // Use average percentage across all AMs in case they differ
        $allLiniWaktu = DB::table('lini_waktu')
            ->whereIn('id', $liniWaktuIds)
            ->get();

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

        // Count unique AMs
        $totalAMs = $liniWaktuIds->count();

        // Define parameters mapping
        $parameters = [
            [
                'name' => 'Revenue',
                'target_field' => 't_revenue',
                'realisasi_field' => 'r_revenue',
                'bobot_field' => 'percentage_revenue',
                'ach_field' => 'ach_revenue_plan'
            ],
            [
                'name' => 'Scaling',
                'target_field' => 't_scalling',
                'realisasi_field' => 'r_scalling',
                'bobot_field' => 'percentage_scaling',
                'ach_field' => 'ach_scaling'
            ],
            [
                'name' => 'Sales-Datin',
                'target_field' => 't_datin',
                'realisasi_field' => 'r_datin',
                'bobot_field' => 'percentage_datin',
                'ach_field' => 'ach_sales_datin'
            ],
            [
                'name' => 'Sales-HSI',
                'target_field' => 't_hsi',
                'realisasi_field' => 'r_hsi',
                'bobot_field' => 'percentage_hsi',
                'ach_field' => 'ach_hsi'
            ],
            [
                'name' => 'Sales-Wireline',
                'target_field' => 't_wireline',
                'realisasi_field' => 'r_wireline',
                'bobot_field' => 'percentage_wireline',
                'ach_field' => 'ach_wireline'
            ],
            [
                'name' => 'Sales-Wifi',
                'target_field' => 't_wifi',
                'realisasi_field' => 'r_wifi',
                'bobot_field' => 'percentage_wifi',
                'ach_field' => 'ach_wifi'
            ],
            [
                'name' => 'CYC',
                'target_field' => 't_cyc',
                'realisasi_field' => 'r_cyc',
                'bobot_field' => 'percentage_cyc',
                'ach_field' => 'ach_cyc'
            ],
            [
                'name' => 'CR',
                'target_field' => 't_cr',
                'realisasi_field' => 'r_cr',
                'bobot_field' => 'percentage_cr',
                'ach_field' => 'ach_cr'
            ],
            [
                'name' => 'Profitability',
                'target_field' => 't_profit',
                'realisasi_field' => 'r_profit',
                'bobot_field' => 'percentage_profit',
                'ach_field' => 'ach_profit'
            ],
            [
                'name' => 'Customer (NPS)',
                'target_field' => 't_nps',
                'realisasi_field' => 'r_nps',
                'bobot_field' => 'percentage_customer',
                'ach_field' => 'ach_nps'
            ],
            [
                'name' => 'MAPS',
                'target_field' => 't_maps',
                'realisasi_field' => 'r_maps',
                'bobot_field' => 'percentage_maps',
                'ach_field' => 'ach_maps'
            ],
            [
                'name' => 'Kecukupan LOP',
                'target_field' => 't_lop',
                'realisasi_field' => 'r_lop',
                'bobot_field' => 'percentage_lop',
                'ach_field' => 'ach_lop'
            ],
            [
                'name' => 'Capability',
                'target_field' => 't_capability',
                'realisasi_field' => 'r_capability',
                'bobot_field' => 'percentage_capability',
                'ach_field' => 'ach_capability'
            ],
            [
                'name' => 'Behavior',
                'target_field' => 't_cc',
                'realisasi_field' => 'r_cc',
                'bobot_field' => 'percentage_cc',
                'ach_field' => 'ach_cc'
            ]
        ];

        $chartData = [];

        foreach ($parameters as $param) {
            // Calculate total target
            $totalTarget = $targets->sum($param['target_field']);

            // Calculate total realisasi
            $totalRealisasi = $realisasi->sum($param['realisasi_field']);

            // Get average bobot (percentage) from all lini_waktu
            // In case different AMs have different percentage thresholds
            $bobot = $allLiniWaktu->avg($param['bobot_field']) ?? 0;

            // Calculate average ach per AM from lini_waktu_target
            // Group by lini_waktu_id to handle multiple targets per AM
            // First: Calculate average per AM (sum of ach / count of companies per AM)
            // Then: Calculate average of all AM averages
            $achByLiniWaktu = $realisasi->groupBy('lini_waktu_id')
                ->map(function($group) use ($param) {
                    // Average achievement per AM = avg of all their assignments
                    return $group->avg($param['ach_field']);
                });
            
            // Average of averages: sum all AM averages / total AMs
            $totalAchFromLiniWaktu = $achByLiniWaktu->sum();
            $avgAchPerAM = $totalAMs > 0 ? round($totalAchFromLiniWaktu / $totalAMs, 2) : 0;

            // Database stores percentages as actual percentage values (30.000 for 30%)
            // Both ach and bobot are already in percentage format - use as-is
            $bobotPercentage = $bobot; // Already in percentage format

            $chartData[] = [
                'parameter' => $param['name'],
                'target' => round($totalTarget, 2),
                'realisasi' => round($totalRealisasi, 2),
                'bobot' => round($bobotPercentage, 2), // Send as percentage (30 for 30%)
                'ach' => round($avgAchPerAM, 2), // Already in percentage format
                'avg_ach_per_am' => $avgAchPerAM / 100  // Convert to decimal for Top Parameter (0.30 for 30%)
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

            // Aggregate targets and realisasi for this AM
            // ONLY Revenue & Scaling are summed (stored in all company records)
            // ALL OTHER parameters are stored only in FIRST record during import
            $amTargetRevenue = $pivotData->sum('t_revenue');
            \Log::info("AM {$am->nik} Target Revenue:", ['amount' => $amTargetRevenue, 'targets_count' => $pivotData->count()]);
            $amRealisasiRevenue = $pivotData->sum('r_revenue');
            $amTargetScaling = $pivotData->sum('t_scalling');
            $amRealisasiScaling = $pivotData->sum('r_scalling');
            
            // All other parameters: use FIRST record only (import only saves to first record)
            // This includes: Datin, HSI, Wireline, WiFi, CYC, CR, Profit, NPS, MAPS, LOP, Capability, CC
            $firstRecord = $pivotData->first();
            $amTargetDatin = $firstRecord->t_datin ?? 0;
            $amRealisasiDatin = $firstRecord->r_datin ?? 0;
            $amTargetHsi = $firstRecord->t_hsi ?? 0;
            $amRealisasiHsi = $firstRecord->r_hsi ?? 0;
            $amTargetWireline = $firstRecord->t_wireline ?? 0;
            $amRealisasiWireline = $firstRecord->r_wireline ?? 0;
            $amTargetWifi = $firstRecord->t_wifi ?? 0;
            $amRealisasiWifi = $firstRecord->r_wifi ?? 0;
            
            $amTargetCyc = $firstRecord->t_cyc ?? 0;
            $amRealisasiCyc = $firstRecord->r_cyc ?? 0;
            $amTargetCr = $firstRecord->t_cr ?? 0;
            $amRealisasiCr = $firstRecord->r_cr ?? 0;
            $amTargetProfit = $firstRecord->t_profit ?? 0;
            $amRealisasiProfit = $firstRecord->r_profit ?? 0;
            
            $amTargetNps = $firstRecord->t_nps ?? 0;
            $amRealisasiNps = $firstRecord->r_nps ?? 0;
            
            $amTargetMaps = $firstRecord->t_maps ?? 0;
            $amRealisasiMaps = $firstRecord->r_maps ?? 0;
            
            $amTargetLop = $firstRecord->t_lop ?? 0;
            $amRealisasiLop = $firstRecord->r_lop ?? 0;
            $amTargetCapability = $firstRecord->t_capability ?? 0;
            $amRealisasiCapability = $firstRecord->r_capability ?? 0;
            $amTargetCc = $firstRecord->t_cc ?? 0;
            $amRealisasiCc = $firstRecord->r_cc ?? 0;

            // Get achievements: sum of all ach values divided by number of companies
            $companyCount = $pivotData->count();
            $achRevenue = $companyCount > 0 ? $pivotData->sum('ach_revenue_plan') / $companyCount : 0;
            $achScaling = $companyCount > 0 ? $pivotData->sum('ach_scaling') / $companyCount : 0;
            $achDatin = $companyCount > 0 ? $pivotData->sum('ach_sales_datin') / $companyCount : 0;
            $achHsi = $companyCount > 0 ? $pivotData->sum('ach_hsi') / $companyCount : 0;
            $achWireline = $companyCount > 0 ? $pivotData->sum('ach_wireline') / $companyCount : 0;
            $achWifi = $companyCount > 0 ? $pivotData->sum('ach_wifi') / $companyCount : 0;
            $achCyc = $companyCount > 0 ? $pivotData->sum('ach_cyc') / $companyCount : 0;
            $achCr = $companyCount > 0 ? $pivotData->sum('ach_cr') / $companyCount : 0;
            $achProfit = $companyCount > 0 ? $pivotData->sum('ach_profit') / $companyCount : 0;
            $achNps = $companyCount > 0 ? $pivotData->sum('ach_nps') / $companyCount : 0;
            $achMaps = $companyCount > 0 ? $pivotData->sum('ach_maps') / $companyCount : 0;
            $achLop = $companyCount > 0 ? $pivotData->sum('ach_lop') / $companyCount : 0;
            $achCapability = $companyCount > 0 ? $pivotData->sum('ach_capability') / $companyCount : 0;
            $achCc = $companyCount > 0 ? $pivotData->sum('ach_cc') / $companyCount : 0;

            // Get ach_result, ach_proses, nki_adjustment from pivot
            $achResult = $pivotData->avg('ach_result') ?? 0;
            $achProses = $pivotData->avg('ach_proses') ?? 0;
            $nkiAdjustment = $pivotData->avg('nki_adjustment') ?? 0;

            $totalTargetRevenue += $amTargetRevenue;
            $totalRealisasiRevenue += $amRealisasiRevenue;
            // Sum ALL nki_adjustment from all records, divide by number of AMs (not records)
            $totalNki += $pivotData->sum('nki_adjustment');
            $nkiCount++; // Count number of AMs
            
            \Log::info("AM {$am->nik} NKI Debug:", [
                'pivot_count' => $pivotData->count(),
                'nki_values' => $pivotData->pluck('nki_adjustment')->toArray(),
                'sum_nki' => $pivotData->sum('nki_adjustment'),
                'running_totalNki' => $totalNki,
                'running_amCount' => $nkiCount
            ]);
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
                // Percentage-based parameters: multiply by 100 for display
                't_cyc' => $amTargetCyc * 100,
                'r_cyc' => $amRealisasiCyc * 100,
                'ach_cyc' => $achCyc,
                't_cr' => $amTargetCr * 100,
                'r_cr' => $amRealisasiCr * 100,
                'ach_cr' => $achCr,
                't_profit' => $amTargetProfit * 100,
                'r_profit' => $amRealisasiProfit * 100,
                'ach_profit' => $achProfit,
                't_nps' => $amTargetNps,
                'r_nps' => $amRealisasiNps,
                'ach_nps' => $achNps,
                't_maps' => $amTargetMaps * 100,
                'r_maps' => $amRealisasiMaps * 100,
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
        
        \Log::info("FINAL NKI Calculation:", [
            'totalNki' => $totalNki,
            'nkiCount' => $nkiCount,
            'avgNki' => $avgNki,
            'totalAMs' => count($amList)
        ]);

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

    /**
     * Get AM details by witel for region modal expansion
     */
    public function getWitelAMDetails(Request $request)
    {
        $request->validate([
            'witel_name' => 'required|string',
            'region_code' => 'required|string',
            'year' => 'required|integer|min:2020',
            'quartal' => 'required|string',
            'ytd' => 'nullable|in:0,1'
        ]);

        $witelName = $request->witel_name;
        $regionCode = $request->region_code;
        $year = $request->year;
        $quartal = $request->quartal;
        $isYearToDate = $request->ytd === '1';

        // Parse quarter
        $quarterNumber = (int) str_replace('Q', '', $quartal);

        try {
            // Get Account Managers for this witel using JOIN with witels table
            $accountManagers = DB::table('account_managers as am')
                ->join('witels as w', 'am.idwitels', '=', 'w.idwitels')
                ->join('regions as r', 'w.region_id', '=', 'r.id')
                ->where('w.nama_witels', $witelName)
                ->where('r.name', $regionCode)
                ->select('am.nik', 'am.nama', 'am.posisi', 'am.no_gsm')
                ->distinct()
                ->get();

            if ($accountManagers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'witel_name' => $witelName,
                        'region_code' => $regionCode,
                        'am_list' => []
                    ]
                ]);
            }

            $amNiks = $accountManagers->pluck('nik')->toArray();
            $amList = [];

            foreach ($accountManagers as $am) {
                // Build query based on YTD or single quarter
                $liniWaktuQuery = LiniWaktu::where('tahun', $year)
                    ->where('nik_am', $am->nik);

                if ($isYearToDate) {
                    // YTD: Q1 to current quarter
                    $liniWaktuQuery->whereIn('quartal', range(1, $quarterNumber));
                } else {
                    // Single quarter
                    $liniWaktuQuery->where('quartal', $quarterNumber);
                }

                $liniWaktuData = $liniWaktuQuery->get();

                if ($liniWaktuData->isEmpty()) {
                    continue;
                }

                $liniWaktuIds = $liniWaktuData->pluck('id')->toArray();

                // Get targets and realisasi from pivot table
                $pivotData = DB::table('lini_waktu_target as lwt')
                    ->join('target_account_m as tam', 'lwt.target_id', '=', 'tam.id')
                    ->join('account_manager_company as amc', 'tam.account_manager_company_id', '=', 'amc.id')
                    ->whereIn('lwt.lini_waktu_id', $liniWaktuIds)
                    ->where('amc.nik_am', $am->nik)
                    ->get();

                if ($pivotData->isEmpty()) {
                    continue;
                }

                // Aggregate without proporsi
                $totalTargetRevenue = $pivotData->sum('t_revenue');
                $totalRealisasiRevenue = $pivotData->sum('r_revenue');

                // Count unique companies (nip_nas)
                $uniqueCompanies = $pivotData->pluck('nip_nas')->unique()->filter()->values();
                $companyHandled = $uniqueCompanies->count();

                // Calculate achievement
                $achievement = 0;
                if ($totalTargetRevenue > 0) {
                    $achievement = ($totalRealisasiRevenue / $totalTargetRevenue) * 100;
                }

                $amList[] = [
                    'am_nik' => $am->nik,
                    'am_name' => $am->nama,
                    'am_posisi' => $am->posisi ?? 'N/A',
                    'target_revenue' => $totalTargetRevenue,
                    'formatted_target_revenue' => $this->formatCurrency($totalTargetRevenue),
                    'realisasi_revenue' => $totalRealisasiRevenue,
                    'formatted_realisasi_revenue' => $this->formatCurrency($totalRealisasiRevenue),
                    'company_handled' => $companyHandled,
                    'achievement_percentage' => round($achievement, 2)
                ];
            }

            // Sort by target revenue descending
            usort($amList, function($a, $b) {
                return $b['target_revenue'] <=> $a['target_revenue'];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'witel_name' => $witelName,
                    'region_code' => $regionCode,
                    'period' => $isYearToDate 
                        ? "Q1 - {$quartal} {$year} (YTD)"
                        : "{$quartal} {$year}",
                    'am_list' => $amList
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching witel AM details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch AM details: ' . $e->getMessage()
            ], 500);
        }
    }
}

