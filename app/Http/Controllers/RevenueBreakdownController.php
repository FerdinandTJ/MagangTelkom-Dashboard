<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Group1;
use App\Models\Group2;
use App\Models\Group3;
use App\Models\Group4;
use Illuminate\Support\Facades\DB;

class RevenueBreakdownController extends Controller
{
    /**
     * Get hierarchical revenue breakdown for a company
     * Optional filters: tahun, bulan
     * 
     * @param Request $request
     * @param string $companyId - nip_nas or id of company
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBreakdown(Request $request, $companyId)
    {
        try {
            // Find company by nip_nas (primary key)
            $company = Company::where('nip_nas', $companyId)->first();
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found',
                ], 404);
            }

            // Get filters
            $tahun = $request->input('tahun');
            $bulan = $request->input('bulan');

            // Build hierarchical data
            $breakdown = $this->buildHierarchy($company, $tahun, $bulan);

            return response()->json([
                'success' => true,
                'data' => $breakdown,
                'filters' => [
                    'company_id' => $company->nip_nas,
                    'company_name' => $company->nama_perusahaan,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                'note' => 'Hierarchical revenue breakdown from database',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching revenue breakdown: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build hierarchical revenue structure
     * 
     * @param Company $company
     * @param int|null $tahun
     * @param int|null $bulan
     * @return array
     */
    private function buildHierarchy(Company $company, $tahun = null, $bulan = null)
    {
        $result = [];

        // Get all Group1 for this company (using nip_nas)
        $group1s = Group1::where('company_id', $company->nip_nas)->get();

        foreach ($group1s as $g1) {
            $group1Data = [
                'id' => 'group1-' . $g1->idGroup1,
                'name' => $g1->nama_group1,
                'revenue' => 0,
                'children' => [],
            ];

            // Get all Group2 for this Group1
            $group2s = Group2::where('group1_id', $g1->idGroup1)->get();

            foreach ($group2s as $g2) {
                $group2Data = [
                    'id' => 'group2-' . $g2->idGroup2,
                    'name' => $g2->nama_group2,
                    'revenue' => 0,
                    'children' => [],
                ];

                // Get all Group3 for this Group2
                $group3s = Group3::where('group2_id', $g2->idGroup2)->get();

                foreach ($group3s as $g3) {
                    $group3Data = [
                        'id' => 'group3-' . $g3->idGroup3,
                        'name' => $g3->nama_group3,
                        'revenue' => 0,
                        'children' => [],
                    ];

                    // Get all Group4 products for this Group3 with revenues
                    $group4Query = DB::table('group4 as p')
                        ->join('revenues as r', 'p.idGroup4', '=', 'r.group4_id')
                        ->where('p.group3_id', $g3->idGroup3);
                    
                    // Apply filters if provided
                    if ($tahun) {
                        $group4Query->where('r.tahun', $tahun);
                    }
                    if ($bulan) {
                        $group4Query->where('r.bulan', $bulan);
                    }
                    
                    $group4s = $group4Query->select(
                        'p.idGroup4',
                        'p.nama_group4',
                        'r.tahun',
                        'r.bulan',
                        'r.revenue_realisasi'
                    )->get();

                    // If no month filter, aggregate by nama_group4
                    if (!$bulan) {
                        $aggregated = [];
                        foreach ($group4s as $g4) {
                            $key = $g4->nama_group4;
                            if (!isset($aggregated[$key])) {
                                $aggregated[$key] = [
                                    'id' => 'group4-' . $g4->idGroup4,
                                    'name' => $g4->nama_group4,
                                    'revenue' => 0,
                                    'children' => [],
                                ];
                            }
                            $aggregated[$key]['revenue'] += (float) $g4->revenue_realisasi;
                        }
                        
                        // Add aggregated items to group3
                        foreach ($aggregated as $item) {
                            $group3Data['children'][] = $item;
                            $group3Data['revenue'] += $item['revenue'];
                        }
                    } else {
                        // With month filter, show individual entries
                        foreach ($group4s as $g4) {
                            $group4Data = [
                                'id' => 'group4-' . $g4->idGroup4,
                                'name' => $g4->nama_group4 . ' (' . $this->getMonthName($g4->bulan) . ' ' . $g4->tahun . ')',
                                'revenue' => (float) $g4->revenue_realisasi,
                                'children' => [],
                            ];

                            // Add to group3 children
                            $group3Data['children'][] = $group4Data;
                            $group3Data['revenue'] += (float) $g4->revenue_realisasi;
                        }
                    }

                    // Add to group2 children if has data
                    if (count($group3Data['children']) > 0) {
                        $group2Data['children'][] = $group3Data;
                        $group2Data['revenue'] += $group3Data['revenue'];
                    }
                }

                // Add to group1 children if has data
                if (count($group2Data['children']) > 0) {
                    $group1Data['children'][] = $group2Data;
                    $group1Data['revenue'] += $group2Data['revenue'];
                }
            }

            // Add to result if has data
            if (count($group1Data['children']) > 0) {
                $result[] = $group1Data;
            }
        }

        return $result;
    }

    /**
     * Helper: Get month name in Indonesian
     * 
     * @param int $month
     * @return string
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$month] ?? $month;
    }
}
