<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Region;

class RegionRevenueController extends Controller
{
    public function getRegionRevenue(Request $request)
    {
        try {
            $year = $request->input('year');
            $quartal = $request->input('quartal');
            $isYtd = $request->input('ytd', '0') === '1';

            // Get all regions
            $regions = Region::all();
            $regionData = [];

            foreach ($regions as $region) {
                // Build quarter list
                if ($isYtd) {
                    $quarters = $this->getQuartersUpTo($quartal);
                } else {
                    $quarters = [$quartal];
                }

                // Get AM NIKs in this region
                $amNiks = DB::table('account_managers')
                    ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                    ->where('witels.region_id', $region->id)
                    ->pluck('account_managers.nik');

                if ($amNiks->isEmpty()) {
                    continue;
                }

                // Get lini_waktu IDs for this period
                $liniWaktuIds = DB::table('lini_waktu')
                    ->where('tahun', $year)
                    ->whereIn('quartal', $quarters)
                    ->whereIn('nik_am', $amNiks)
                    ->pluck('id');

                if ($liniWaktuIds->isEmpty()) {
                    continue;
                }

                // Get target IDs
                $targetIds = DB::table('account_manager_company as amc')
                    ->join('target_account_m as tam', 'amc.id', '=', 'tam.account_manager_company_id')
                    ->whereIn('amc.nik_am', $amNiks)
                    ->pluck('tam.id');

                // Calculate target revenue with proporsi
                $targetRevenue = DB::table('target_account_m as tam')
                    ->join('account_manager_company as amc', 'tam.account_manager_company_id', '=', 'amc.id')
                    ->join('lini_waktu_target as lwt', 'tam.id', '=', 'lwt.target_id')
                    ->whereIn('lwt.lini_waktu_id', $liniWaktuIds)
                    ->whereIn('tam.id', $targetIds)
                    ->get()
                    ->sum(function($row) {
                        return $row->t_revenue * ($row->proporsi / 100);
                    });

                // Calculate realisasi revenue with proporsi
                $realisasiRevenue = DB::table('lini_waktu_target as lwt')
                    ->join('target_account_m as tam', 'lwt.target_id', '=', 'tam.id')
                    ->join('account_manager_company as amc', 'tam.account_manager_company_id', '=', 'amc.id')
                    ->whereIn('lwt.lini_waktu_id', $liniWaktuIds)
                    ->get()
                    ->sum(function($row) {
                        return $row->r_revenue * ($row->proporsi / 100);
                    });

                if ($targetRevenue == 0 && $realisasiRevenue == 0) {
                    continue;
                }

                $achievementPercentage = $targetRevenue > 0 
                    ? ($realisasiRevenue / $targetRevenue) * 100 
                    : 0;

                $variancePercentage = $achievementPercentage - 100;

                $regionData[] = [
                    'region_id' => $region->id,
                    'region_code' => $region->name,
                    'region_name' => $region->name,
                    'target_revenue' => round($targetRevenue, 2),
                    'realisasi_revenue' => round($realisasiRevenue, 2),
                    'achievement_percentage' => round($achievementPercentage, 2),
                    'variance_percentage' => round($variancePercentage, 2),
                    'formatted_target' => $this->formatCurrency($targetRevenue),
                    'formatted_realisasi' => $this->formatCurrency($realisasiRevenue),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $regionData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getRegionRevenue: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching region revenue data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getQuartersUpTo($targetQuartal)
    {
        $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
        $index = array_search($targetQuartal, $quarters);
        return array_slice($quarters, 0, $index + 1);
    }

    private function formatCurrency(float $value, int $decimals = 2): string
    {
        if ($value >= 1000000000000) {
            return 'Rp ' . number_format($value / 1000000000000, $decimals, '.', ',') . 'T';
        } else {
            return 'Rp ' . number_format($value / 1000000000, $decimals, '.', ',') . 'M';
        }
    }
}
