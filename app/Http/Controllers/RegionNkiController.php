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
            'year' => 'required|integer|min:2020'
        ]);

        $quarter = $request->quarter;
        $year = $request->year;

        \Log::info('Region NKI Request:', [
            'region_id' => $regionId,
            'quarter' => $quarter,
            'year' => $year
        ]);

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
            return response()->json([
                'error' => 'Tidak ada Account Manager di region ini'
            ], 404);
        }

        // Get targets for these account_manager_company records
        $targets = TargetAccountM::whereIn('account_manager_company_id', $amCompanyIds)->get();

        \Log::info('Targets found:', ['target_count' => $targets->count(), 'target_ids' => $targets->pluck('id')->toArray()]);

        if ($targets->isEmpty()) {
            \Log::warning('No targets found for AM companies');
            return response()->json([
                'error' => 'Tidak ada target untuk AM di region ini'
            ], 404);
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
            return response()->json([
                'error' => 'Periode tidak ditemukan untuk AM di region ini'
            ], 404);
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
        
        // Get first lini_waktu for threshold values (they're same for all AMs in same period)
        $sampleLiniWaktu = LiniWaktu::whereIn('id', $liniWaktuIds)->first();
        
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

            $resultAch = 0;
            $resultNotAch = 0;
            $prosesAch = 0;
            $prosesNotAch = 0;
            $nkiAbove100 = 0;
            $nkiBelow100 = 0;
            $nkiValues = [];

            foreach ($segmentPivots as $pivot) {
                // Use stored achievement values from database
                $resultPercentage = $pivot->ach_result; // Already stored as sum of 10 fields
                $prosesPercentage = $pivot->ach_proses; // Already stored as sum of 4 fields

                // Compare with lini_waktu thresholds (70% for result, 30% for process)
                // Achievement is in percentage format (e.g., 85.50 means 85.50%)
                // Threshold needs to be converted to match (70.00 means 70%)
                if ($resultPercentage >= $sampleLiniWaktu->percentage_result) {
                    $resultAch++;
                } else {
                    $resultNotAch++;
                }

                if ($prosesPercentage >= $sampleLiniWaktu->percentage_proses) {
                    $prosesAch++;
                } else {
                    $prosesNotAch++;
                }

                // NKI analysis (nki_adjustment is already in percentage: 70-130%)
                if ($pivot->nki_adjustment >= 100) {
                    $nkiAbove100++;
                } else {
                    $nkiBelow100++;
                }

                $nkiValues[] = $pivot->nki_adjustment;
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
                'avg_nki' => count($nkiValues) > 0 ? (float) round(array_sum($nkiValues) / count($nkiValues), 2) : 0.0
            ];
        }

        // Calculate parameter statistics for Result aspect
        $resultParameters = [
            ['name' => 'Revenue', 'field' => 'ach_revenue_plan', 'percentage_field' => 'percentage_revenue'],
            ['name' => 'Scaling', 'field' => 'ach_scaling', 'percentage_field' => 'percentage_scaling'],
            ['name' => 'Sales-Datin', 'field' => 'ach_sales_datin', 'percentage_field' => 'percentage_sales_datin'],
            ['name' => 'Sales-HSI', 'field' => 'ach_sales_hsi', 'percentage_field' => 'percentage_sales_hsi'],
            ['name' => 'Sales-Wireline', 'field' => 'ach_sales_wireline', 'percentage_field' => 'percentage_sales_wireline'],
            ['name' => 'Sales-Wifi', 'field' => 'ach_sales_wifi', 'percentage_field' => 'percentage_sales_wifi'],
            ['name' => 'CYC', 'field' => 'ach_cyc', 'percentage_field' => 'percentage_cyc'],
            ['name' => 'CR', 'field' => 'ach_cr', 'percentage_field' => 'percentage_cr'],
            ['name' => 'Profitability', 'field' => 'ach_profitability', 'percentage_field' => 'percentage_profitability'],
            ['name' => 'Customer(NPS)', 'field' => 'ach_customer', 'percentage_field' => 'percentage_customer']
        ];

        $resultParameterStats = [];
        foreach ($resultParameters as $param) {
            $ach = 0;
            $notAch = 0;
            $bobot = $sampleLiniWaktu->{$param['percentage_field']} ?? 0;

            foreach ($pivotData as $pivot) {
                $achValue = $pivot->{$param['field']} ?? 0;
                if ($achValue >= $bobot) {
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
            ['name' => 'Behavior', 'field' => 'ach_behavior', 'percentage_field' => 'percentage_behavior']
        ];

        $prosesParameterStats = [];
        foreach ($prosesParameters as $param) {
            $ach = 0;
            $notAch = 0;
            $bobot = $sampleLiniWaktu->{$param['percentage_field']} ?? 0;

            foreach ($pivotData as $pivot) {
                $achValue = $pivot->{$param['field']} ?? 0;
                if ($achValue >= $bobot) {
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

        return response()->json([
            'region' => [
                'id' => $region->id,
                'name' => $region->name
            ],
            'period' => [
                'quarter' => $quarter,
                'year' => $year
            ],
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
        ]);
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
}
