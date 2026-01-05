<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\AccountManagerCompany;
use Illuminate\Support\Facades\DB;

class AmPerformanceDetailController extends Controller
{
    /**
     * Get detailed performance information for a specific Account Manager
     */
    public function getAmPerformanceDetail(Request $request)
    {
        $nikAm = $request->input('nik_am');
        $quarter = (int) $request->input('quarter');
        $year = (int) $request->input('year');
        $segment = $request->input('segment');

        // Validate required parameters
        if (!$nikAm || !$quarter || !$year || !$segment) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters'
            ], 400);
        }

        // Get AM Info with relations
        $amInfo = AccountManager::with(['witel.region'])
            ->where('nik', $nikAm)
            ->first();

        if (!$amInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Account Manager not found'
            ], 404);
        }

        // Get historical data (current + 2 previous quarters)
        $historicalData = $this->getHistoricalData($nikAm, $quarter, $year, $segment);

        // Calculate summary from current period
        $currentPeriod = collect($historicalData)->first();
        $summary = $this->calculateSummary($currentPeriod);

        // Find best period based on highest NKI
        $bestPeriod = $this->findBestPeriod($historicalData);

        \Log::info("Response data structure:", [
            'total_historical_data' => count($historicalData),
            'first_3_periods' => array_slice(array_map(fn($p) => $p['period_display'] ?? 'unknown', $historicalData), 0, 3),
            'all_years' => array_unique(array_column($historicalData, 'year'))
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'am_info' => [
                    'nik_am' => $amInfo->nik,
                    'nama_am' => $amInfo->nama,
                    'posisi' => $amInfo->posisi ?? 'Account Manager',
                    'no_gsm' => $amInfo->no_gsm ?? '-',
                    'witel_name' => optional($amInfo->witel)->nama_witels ?? '-',
                    'region_name' => optional(optional($amInfo->witel)->region)->name ?? '-',
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

    /**
     * Get historical performance data for AM (last 3 years worth of data for yearly chart)
     */
    private function getHistoricalData($nikAm, $currentQuarter, $currentYear, $segment)
    {
        // First, get current quarter + 2 previous quarters for table display
        $tablePeriods = $this->calculatePreviousPeriods($currentQuarter, $currentYear, 3);
        
        // Then get all quarters for 3 years for chart (current year + 2 previous years)
        $chartPeriods = [];
        for ($yearOffset = 0; $yearOffset <= 2; $yearOffset++) {
            $targetYear = $currentYear - $yearOffset;
            for ($q = 1; $q <= 4; $q++) {
                // Skip periods already in tablePeriods to avoid duplicates
                $isDuplicate = false;
                foreach ($tablePeriods as $tp) {
                    if ($tp['quarter'] == $q && $tp['year'] == $targetYear) {
                        $isDuplicate = true;
                        break;
                    }
                }
                if (!$isDuplicate) {
                    $chartPeriods[] = ['quarter' => $q, 'year' => $targetYear];
                }
            }
        }
        
        // Combine: table periods first (for table display), then chart periods
        $periods = array_merge($tablePeriods, $chartPeriods);
        
        $historicalData = [];

        \Log::info("AmPerformanceDetail - Processing periods for NIK: {$nikAm}, Current: Q{$currentQuarter} {$currentYear}");

        foreach ($periods as $period) {
            $quarter = $period['quarter'];
            $year = $period['year'];
            $quartalEnum = "Q{$quarter}"; // Convert 1,2,3,4 to Q1,Q2,Q3,Q4

            // Get lini_waktu (time period) records for this AM and period
            $liniWaktuRecords = \App\Models\LiniWaktu::where('nik_am', $nikAm)
                ->where('quartal', $quartalEnum)
                ->where('tahun', $year)
                ->get();

            if ($liniWaktuRecords->isEmpty()) {
                // Add placeholder with null values for missing periods
                $historicalData[] = [
                    'period_display' => "Q{$quarter} {$year}",
                    'quarter' => $quarter,
                    'year' => $year,
                    'nki_adjustment' => null,
                    'ach_result' => null,
                    'ach_proses' => null,
                ];
                continue;
            }

            // Get all lini_waktu_ids for this period
            $liniWaktuIds = $liniWaktuRecords->pluck('id')->toArray();

            // Get account_manager_company records for this AM and segment
            // This ensures we only get targets that belong to this AM in this specific segment
            $amCompanyIds = DB::table('account_manager_company as amc')
                ->where('amc.nik_am', $nikAm)
                ->where('amc.segment', $segment)
                ->pluck('amc.id')
                ->toArray();

            if (empty($amCompanyIds)) {
                // Add placeholder with null values
                $historicalData[] = [
                    'period_display' => "Q{$quarter} {$year}",
                    'quarter' => $quarter,
                    'year' => $year,
                    'nki_adjustment' => null,
                    'ach_result' => null,
                    'ach_proses' => null,
                ];
                continue;
            }

            // Get targets based on account_manager_company_id
            // This matches the exact query logic used in RegionNkiController::getWitelNkiDetail
            $targets = \App\Models\TargetAccountM::whereIn('account_manager_company_id', $amCompanyIds)->get();
            $targetIds = $targets->pluck('id')->toArray();

            if (empty($targetIds)) {
                // Add placeholder with null values
                $historicalData[] = [
                    'period_display' => "Q{$quarter} {$year}",
                    'quarter' => $quarter,
                    'year' => $year,
                    'nki_adjustment' => null,
                    'ach_result' => null,
                    'ach_proses' => null,
                ];
                continue;
            }

            // Get performance data from lini_waktu_target pivot and target_account_m
            $aggregated = $this->aggregatePerformanceDataFromLiniWaktu($nikAm, $quartalEnum, $year, $targetIds);
            $aggregated['period_display'] = "Q{$quarter} {$year}";
            $aggregated['quarter'] = $quarter;
            $aggregated['year'] = $year;

            $historicalData[] = $aggregated;
        }

        \Log::info("AmPerformanceDetail - Total periods sent: " . count($historicalData));
        \Log::info("AmPerformanceDetail - Years in data: " . json_encode(array_unique(array_column($historicalData, 'year'))));

        return $historicalData;
    }

    /**
     * Calculate previous periods (quarters)
     */
    private function calculatePreviousPeriods($currentQuarter, $currentYear, $count)
    {
        $periods = [];
        $quarter = $currentQuarter;
        $year = $currentYear;

        for ($i = 0; $i < $count; $i++) {
            $periods[] = [
                'quarter' => $quarter,
                'year' => $year
            ];

            // Move to previous quarter
            $quarter--;
            if ($quarter < 1) {
                $quarter = 4;
                $year--;
            }
        }

        return $periods;
    }

    /**
     * Aggregate performance data from lini_waktu and target tables
     */
    private function aggregatePerformanceDataFromLiniWaktu($nikAm, $quartalEnum, $year, $targetIds)
    {
        // Get lini_waktu for this AM and period
        $liniWaktu = \App\Models\LiniWaktu::where('nik_am', $nikAm)
            ->where('quartal', $quartalEnum)
            ->where('tahun', $year)
            ->first();

        if (!$liniWaktu) {
            return [
                'nki_adjustment' => null,
                'ach_result' => null,
                'ach_proses' => null,
            ];
        }

        // Get pivot data with targets and proporsi - same method as RegionNkiController
        $pivotData = DB::table('lini_waktu_target as lwt')
            ->join('target_account_m as t', 'lwt.target_id', '=', 't.id')
            ->join('account_manager_company as amc', 't.account_manager_company_id', '=', 'amc.id')
            ->where('lwt.lini_waktu_id', $liniWaktu->id)
            ->whereIn('lwt.target_id', $targetIds)
            ->select('lwt.*', 't.*', 'amc.proporsi')
            ->get();

        if ($pivotData->isEmpty()) {
            return [
                'nki_adjustment' => null,
                'ach_result' => null,
                'ach_proses' => null,
            ];
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

        // Aggregate targets and realisasi with proporsi - same calculation as RegionNkiController
        $tRevenue = $pivotData->sum(function($row) {
            return $row->t_revenue * ($row->proporsi / 100);
        });
        $rRevenue = $pivotData->sum(function($row) {
            return $row->r_revenue * ($row->proporsi / 100);
        });
        $tScaling = $pivotData->sum(function($row) {
            return $row->t_scalling * ($row->proporsi / 100);
        });
        $rScaling = $pivotData->sum(function($row) {
            return $row->r_scalling * ($row->proporsi / 100);
        });
        $tDatin = $pivotData->sum(function($row) {
            return $row->t_datin * ($row->proporsi / 100);
        });
        $rDatin = $pivotData->sum(function($row) {
            return $row->r_datin * ($row->proporsi / 100);
        });
        $tHsi = $pivotData->sum(function($row) {
            return $row->t_hsi * ($row->proporsi / 100);
        });
        $rHsi = $pivotData->sum(function($row) {
            return $row->r_hsi * ($row->proporsi / 100);
        });
        $tWireline = $pivotData->sum(function($row) {
            return $row->t_wireline * ($row->proporsi / 100);
        });
        $rWireline = $pivotData->sum(function($row) {
            return $row->r_wireline * ($row->proporsi / 100);
        });
        $tWifi = $pivotData->sum(function($row) {
            return $row->t_wifi * ($row->proporsi / 100);
        });
        $rWifi = $pivotData->sum(function($row) {
            return $row->r_wifi * ($row->proporsi / 100);
        });
        $tCyc = $pivotData->sum(function($row) {
            return $row->t_cyc * ($row->proporsi / 100);
        });
        $rCyc = $pivotData->sum(function($row) {
            return $row->r_cyc * ($row->proporsi / 100);
        });
        $tCr = $pivotData->sum(function($row) {
            return $row->t_cr * ($row->proporsi / 100);
        });
        $rCr = $pivotData->sum(function($row) {
            return $row->r_cr * ($row->proporsi / 100);
        });
        $tProfit = $pivotData->sum(function($row) {
            return $row->t_profit * ($row->proporsi / 100);
        });
        $rProfit = $pivotData->sum(function($row) {
            return $row->r_profit * ($row->proporsi / 100);
        });
        $tNps = $pivotData->sum(function($row) {
            return $row->t_nps * ($row->proporsi / 100);
        });
        $rNps = $pivotData->sum(function($row) {
            return $row->r_nps * ($row->proporsi / 100);
        });
        $tMaps = $pivotData->sum(function($row) {
            return $row->t_maps * ($row->proporsi / 100);
        });
        $rMaps = $pivotData->sum(function($row) {
            return $row->r_maps * ($row->proporsi / 100);
        });
        $tLop = $pivotData->sum(function($row) {
            return $row->t_lop * ($row->proporsi / 100);
        });
        $rLop = $pivotData->sum(function($row) {
            return $row->r_lop * ($row->proporsi / 100);
        });
        $tCapability = $pivotData->sum(function($row) {
            return $row->t_capability * ($row->proporsi / 100);
        });
        $rCapability = $pivotData->sum(function($row) {
            return $row->r_capability * ($row->proporsi / 100);
        });
        $tCc = $pivotData->sum(function($row) {
            return $row->t_cc * ($row->proporsi / 100);
        });
        $rCc = $pivotData->sum(function($row) {
            return $row->r_cc * ($row->proporsi / 100);
        });

        // Calculate achievements with bobot - same calculation as RegionNkiController
        $achRevenue = $tRevenue > 0 ? ($rRevenue / $tRevenue) * $bobotRevenue : 0;
        $achScaling = $tScaling > 0 ? ($rScaling / $tScaling) * $bobotScaling : 0;
        $achDatin = $tDatin > 0 ? ($rDatin / $tDatin) * $bobotDatin : 0;
        $achHsi = $tHsi > 0 ? ($rHsi / $tHsi) * $bobotHsi : 0;
        $achWireline = $tWireline > 0 ? ($rWireline / $tWireline) * $bobotWireline : 0;
        $achWifi = $tWifi > 0 ? ($rWifi / $tWifi) * $bobotWifi : 0;
        $achCyc = $tCyc > 0 ? ($rCyc / $tCyc) * $bobotCyc : 0;
        $achCr = $tCr > 0 ? ($rCr / $tCr) * $bobotCr : 0;
        $achProfit = $tProfit > 0 ? ($rProfit / $tProfit) * $bobotProfit : 0;
        $achNps = $tNps > 0 ? ($rNps / $tNps) * $bobotNps : 0;
        $achMaps = $tMaps > 0 ? ($rMaps / $tMaps) * $bobotMaps : 0;
        $achLop = $tLop > 0 ? ($rLop / $tLop) * $bobotLop : 0;
        $achCapability = $tCapability > 0 ? ($rCapability / $tCapability) * $bobotCapability : 0;
        $achCc = $tCc > 0 ? ($rCc / $tCc) * $bobotCc : 0;

        // Get ach_result, ach_proses, nki_adjustment from pivot
        $achResult = $pivotData->avg('ach_result') ?? 0;
        $achProses = $pivotData->avg('ach_proses') ?? 0;
        $nkiAdjustment = $pivotData->avg('nki_adjustment') ?? 0;

        // Format currency values
        $formattedTRevenue = $this->formatCurrency($tRevenue);
        $formattedRRevenue = $this->formatCurrency($rRevenue);
        $formattedTScaling = $this->formatCurrency($tScaling);
        $formattedRScaling = $this->formatCurrency($rScaling);
        $formattedTLop = $this->formatCurrency($tLop);
        $formattedRLop = $this->formatCurrency($rLop);

        return [
            't_revenue' => $tRevenue,
            'r_revenue' => $rRevenue,
            't_scaling' => $tScaling,
            'r_scaling' => $rScaling,
            't_sales_datin' => $tDatin,
            'r_sales_datin' => $rDatin,
            't_hsi' => $tHsi,
            'r_hsi' => $rHsi,
            't_wireline' => $tWireline,
            'r_wireline' => $rWireline,
            't_wifi' => $tWifi,
            'r_wifi' => $rWifi,
            't_cyc' => $tCyc,
            'r_cyc' => $rCyc,
            't_cr' => $tCr,
            'r_cr' => $rCr,
            't_profit' => $tProfit,
            'r_profit' => $rProfit,
            't_nps' => $tNps,
            'r_nps' => $rNps,
            't_maps' => $tMaps,
            'r_maps' => $rMaps,
            't_lop' => $tLop,
            'r_lop' => $rLop,
            't_capability' => $tCapability,
            'r_capability' => $rCapability,
            't_cc' => $tCc,
            'r_cc' => $rCc,
            'ach_revenue' => $achRevenue,
            'ach_revenue_plan' => $achRevenue,
            'ach_scaling' => $achScaling,
            'ach_sales_datin' => $achDatin,
            'ach_hsi' => $achHsi,
            'ach_wireline' => $achWireline,
            'ach_wifi' => $achWifi,
            'ach_cyc' => $achCyc,
            'ach_cr' => $achCr,
            'ach_profit' => $achProfit,
            'ach_nps' => $achNps,
            'ach_maps' => $achMaps,
            'ach_lop' => $achLop,
            'ach_capability' => $achCapability,
            'ach_cc' => $achCc,
            'ach_result' => $achResult,
            'ach_proses' => $achProses,
            'nki_adjustment' => $nkiAdjustment,
            'formatted_t_revenue' => $formattedTRevenue,
            'formatted_r_revenue' => $formattedRRevenue,
            'formatted_t_scaling' => $formattedTScaling,
            'formatted_r_scaling' => $formattedRScaling,
            'formatted_t_lop' => $formattedTLop,
            'formatted_r_lop' => $formattedRLop,
        ];
    }

    /**
     * Aggregate performance data across all companies (OLD METHOD - DEPRECATED)
     */
    private function aggregatePerformanceData($performanceData)
    {
        $totals = [
            't_revenue' => 0,
            'r_revenue' => 0,
            't_scaling' => 0,
            'r_scaling' => 0,
            't_sales_datin' => 0,
            'r_sales_datin' => 0,
            't_hsi' => 0,
            'r_hsi' => 0,
            't_wireline' => 0,
            'r_wireline' => 0,
            't_wifi' => 0,
            'r_wifi' => 0,
            't_cyc' => 0,
            'r_cyc' => 0,
            't_cr' => 0,
            'r_cr' => 0,
            't_profit' => 0,
            'r_profit' => 0,
            't_nps' => 0,
            'r_nps' => 0,
            't_maps' => 0,
            'r_maps' => 0,
            't_lop' => 0,
            'r_lop' => 0,
            't_capability' => 0,
            'r_capability' => 0,
            't_cc' => 0,
            'r_cc' => 0,
        ];

        foreach ($performanceData as $data) {
            foreach ($totals as $key => $value) {
                $totals[$key] += $data->$key ?? 0;
            }
        }

        // Calculate achievement percentages
        $achievements = [];
        $resultParams = ['revenue', 'scaling', 'sales_datin', 'hsi', 'wireline', 'wifi', 'cyc', 'cr', 'profit', 'nps'];
        $prosesParams = ['maps', 'lop', 'capability', 'cc'];

        foreach (array_merge($resultParams, $prosesParams) as $param) {
            $target = $totals["t_{$param}"];
            $real = $totals["r_{$param}"];
            $achievements["ach_{$param}"] = $target > 0 ? round(($real / $target) * 100, 2) : 0;
        }

        // Calculate average ach_result and ach_proses
        $achResultSum = 0;
        foreach ($resultParams as $param) {
            $achResultSum += $achievements["ach_{$param}"];
        }
        $achievements['ach_result'] = count($resultParams) > 0 ? round($achResultSum / count($resultParams), 2) : 0;

        $achProsesSum = 0;
        foreach ($prosesParams as $param) {
            $achProsesSum += $achievements["ach_{$param}"];
        }
        $achievements['ach_proses'] = count($prosesParams) > 0 ? round($achProsesSum / count($prosesParams), 2) : 0;

        // Calculate NKI adjustment (weighted average: 70% result, 30% proses)
        $achievements['nki_adjustment'] = round(
            ($achievements['ach_result'] * 0.7) + ($achievements['ach_proses'] * 0.3),
            2
        );

        // Format currency values
        $totals['formatted_t_revenue'] = $this->formatCurrency($totals['t_revenue']);
        $totals['formatted_r_revenue'] = $this->formatCurrency($totals['r_revenue']);
        $totals['formatted_t_scaling'] = $this->formatCurrency($totals['t_scaling']);
        $totals['formatted_r_scaling'] = $this->formatCurrency($totals['r_scaling']);
        $totals['formatted_t_lop'] = $this->formatCurrency($totals['t_lop']);
        $totals['formatted_r_lop'] = $this->formatCurrency($totals['r_lop']);

        return array_merge($totals, $achievements);
    }

    /**
     * Calculate summary metrics for current period
     */
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

        // Parameter Proses: MAPS, LOP, Capability, CC
        $targetProses = ($currentData['t_maps'] ?? 0) + 
                       ($currentData['t_lop'] ?? 0) + 
                       ($currentData['t_capability'] ?? 0) + 
                       ($currentData['t_cc'] ?? 0);

        $realisasiProses = ($currentData['r_maps'] ?? 0) + 
                          ($currentData['r_lop'] ?? 0) + 
                          ($currentData['r_capability'] ?? 0) + 
                          ($currentData['r_cc'] ?? 0);

        // Parameter Result: Revenue, Scaling, Sales Datin, HSI, Wireline, WiFi, CYC, CR, Profit, NPS
        $targetResult = ($currentData['t_revenue'] ?? 0) + 
                       ($currentData['t_scaling'] ?? 0) + 
                       ($currentData['t_sales_datin'] ?? 0) + 
                       ($currentData['t_hsi'] ?? 0) + 
                       ($currentData['t_wireline'] ?? 0) + 
                       ($currentData['t_wifi'] ?? 0) + 
                       ($currentData['t_cyc'] ?? 0) + 
                       ($currentData['t_cr'] ?? 0) + 
                       ($currentData['t_profit'] ?? 0) + 
                       ($currentData['t_nps'] ?? 0);

        $realisasiResult = ($currentData['r_revenue'] ?? 0) + 
                          ($currentData['r_scaling'] ?? 0) + 
                          ($currentData['r_sales_datin'] ?? 0) + 
                          ($currentData['r_hsi'] ?? 0) + 
                          ($currentData['r_wireline'] ?? 0) + 
                          ($currentData['r_wifi'] ?? 0) + 
                          ($currentData['r_cyc'] ?? 0) + 
                          ($currentData['r_cr'] ?? 0) + 
                          ($currentData['r_profit'] ?? 0) + 
                          ($currentData['r_nps'] ?? 0);

        return [
            'target_proses' => $targetProses,
            'realisasi_proses' => $realisasiProses,
            'target_result' => $targetResult,
            'realisasi_result' => $realisasiResult
        ];
    }

    /**
     * Find period with best (highest) NKI adjustment
     */
    private function findBestPeriod($historicalData)
    {
        if (empty($historicalData)) {
            return [
                'period_display' => '-',
                'nki_adjustment' => 0
            ];
        }

        $best = collect($historicalData)
            ->sortByDesc('nki_adjustment')
            ->first();

        return [
            'period_display' => $best['period_display'] ?? '-',
            'nki_adjustment' => $best['nki_adjustment'] ?? 0
        ];
    }

    /**
     * Format number as Indonesian Rupiah currency
     */
    private function formatCurrency($value)
    {
        if ($value >= 1000000000000) {
            return 'Rp ' . number_format($value / 1000000000000, 2, ',', '.') . ' T';
        } elseif ($value >= 1000000000) {
            return 'Rp ' . number_format($value / 1000000000, 2, ',', '.') . ' M';
        } elseif ($value >= 1000000) {
            return 'Rp ' . number_format($value / 1000000, 2, ',', '.') . ' Jt';
        } else {
            return 'Rp ' . number_format($value, 0, ',', '.');
        }
    }
}
