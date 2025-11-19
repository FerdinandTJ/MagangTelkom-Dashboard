<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * DUMMY CONTROLLER - SAMPLE DATA ONLY
 * Controller ini hanya untuk testing tampilan Revenue Breakdown Tree
 * Data di sini adalah contoh/dummy untuk development
 */
class RevenueBreakdownDummyController extends Controller
{
    /**
     * Get dummy revenue breakdown data untuk company
     * Endpoint ini nanti bisa diganti dengan data real dari database
     */
    public function getDummyRevenueBreakdown(Request $request)
    {
        $companyId = $request->input('company_id');
        
        // DUMMY DATA - Hierarchical Revenue Structure (Simplified - only id, name, revenue, children)
        $revenueBreakdown = [
            [
                'id' => 'internet',
                'name' => 'Internet Services',
                'revenue' => 150000000,
                'children' => [
                    [
                        'id' => 'metro-ethernet',
                        'name' => 'Metro Ethernet',
                        'revenue' => 90000000,
                        'children' => [
                            [
                                'id' => 'metro-100',
                                'name' => 'Metro E 100Mbps',
                                'revenue' => 55000000,
                                'children' => [
                                    [
                                        'id' => 'prj-001',
                                        'name' => 'Project A - Bank Mandiri HQ',
                                        'revenue' => 30000000
                                    ],
                                    [
                                        'id' => 'prj-002',
                                        'name' => 'Project B - BCA Branch Office',
                                        'revenue' => 25000000
                                    ]
                                ]
                            ],
                            [
                                'id' => 'metro-1g',
                                'name' => 'Metro E 1Gbps',
                                'revenue' => 35000000,
                                'children' => [
                                    [
                                        'id' => 'prj-003',
                                        'name' => 'Project C - Telkomsel Data Center',
                                        'revenue' => 35000000
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'dia',
                        'name' => 'Dedicated Internet Access (DIA)',
                        'revenue' => 60000000,
                        'children' => [
                            [
                                'id' => 'dia-500',
                                'name' => 'DIA 500Mbps',
                                'revenue' => 60000000,
                                'children' => [
                                    [
                                        'id' => 'prj-004',
                                        'name' => 'Project D - Universitas Indonesia',
                                        'revenue' => 60000000
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'id' => 'cloud',
                'name' => 'Cloud Services',
                'revenue' => 85000000,
                'children' => [
                    [
                        'id' => 'iaas',
                        'name' => 'Infrastructure as a Service (IaaS)',
                        'revenue' => 50000000,
                        'children' => [
                            [
                                'id' => 'vm-standard',
                                'name' => 'Virtual Machine - Standard',
                                'revenue' => 30000000,
                                'children' => [
                                    [
                                        'id' => 'prj-005',
                                        'name' => 'Project E - E-Commerce Platform',
                                        'revenue' => 30000000
                                    ]
                                ]
                            ],
                            [
                                'id' => 'vm-premium',
                                'name' => 'Virtual Machine - Premium',
                                'revenue' => 20000000,
                                'children' => [
                                    [
                                        'id' => 'prj-006',
                                        'name' => 'Project F - Banking Core System',
                                        'revenue' => 20000000
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'saas',
                        'name' => 'Software as a Service (SaaS)',
                        'revenue' => 35000000,
                        'children' => [
                            [
                                'id' => 'crm',
                                'name' => 'CRM Solution',
                                'revenue' => 20000000,
                                'children' => [
                                    [
                                        'id' => 'prj-007',
                                        'name' => 'Project G - Sales Automation',
                                        'revenue' => 20000000
                                    ]
                                ]
                            ],
                            [
                                'id' => 'erp',
                                'name' => 'ERP Solution',
                                'revenue' => 15000000,
                                'children' => [
                                    [
                                        'id' => 'prj-008',
                                        'name' => 'Project H - Manufacturing ERP',
                                        'revenue' => 15000000
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'id' => 'data-center',
                'name' => 'Data Center & Colocation',
                'revenue' => 65000000,
                'children' => [
                    [
                        'id' => 'colo-rack',
                        'name' => 'Rack Colocation',
                        'revenue' => 40000000,
                        'children' => [
                            [
                                'id' => 'rack-quarter',
                                'name' => 'Quarter Rack',
                                'revenue' => 15000000,
                                'children' => [
                                    [
                                        'id' => 'prj-009',
                                        'name' => 'Project I - Startup Hosting',
                                        'revenue' => 15000000
                                    ]
                                ]
                            ],
                            [
                                'id' => 'rack-full',
                                'name' => 'Full Rack',
                                'revenue' => 25000000,
                                'children' => [
                                    [
                                        'id' => 'prj-010',
                                        'name' => 'Project J - Financial Data Center',
                                        'revenue' => 25000000
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'managed-services',
                        'name' => 'Managed Services',
                        'revenue' => 25000000,
                        'children' => [
                            [
                                'id' => 'managed-server',
                                'name' => 'Managed Server',
                                'revenue' => 25000000,
                                'children' => [
                                    [
                                        'id' => 'prj-011',
                                        'name' => 'Project K - 24/7 Server Management',
                                        'revenue' => 25000000
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'revenue_breakdown' => $revenueBreakdown,
                'company_id' => $companyId,
                'note' => 'Ultra-simplified: only id, name, revenue, and children fields'
            ]
        ]);
    }
}
