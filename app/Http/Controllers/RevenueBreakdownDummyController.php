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
        
        // DUMMY DATA - Hierarchical Revenue Structure
        $revenueBreakdown = [
            [
                'id' => 'internet',
                'name' => 'Internet Services',
                'type' => 'category',
                'revenue' => 150000000,
                'target' => 140000000,
                'achievement_percentage' => 107.14,
                'children' => [
                    [
                        'id' => 'metro-ethernet',
                        'name' => 'Metro Ethernet',
                        'type' => 'subcategory',
                        'revenue' => 90000000,
                        'target' => 80000000,
                        'achievement_percentage' => 112.5,
                        'children' => [
                            [
                                'id' => 'metro-100',
                                'name' => 'Metro E 100Mbps',
                                'type' => 'product',
                                'revenue' => 55000000,
                                'target' => 50000000,
                                'achievement_percentage' => 110,
                                'children' => [
                                    [
                                        'id' => 'prj-001',
                                        'name' => 'Project A - Bank Mandiri HQ',
                                        'type' => 'project',
                                        'revenue' => 30000000,
                                        'target' => 28000000,
                                        'achievement_percentage' => 107.14,
                                        'metadata' => [
                                            'period' => 'Jan - Nov 2025',
                                            'status' => 'active',
                                            'am_name' => 'John Doe'
                                        ]
                                    ],
                                    [
                                        'id' => 'prj-002',
                                        'name' => 'Project B - BCA Branch Office',
                                        'type' => 'project',
                                        'revenue' => 25000000,
                                        'target' => 22000000,
                                        'achievement_percentage' => 113.64,
                                        'metadata' => [
                                            'period' => 'Feb - Dec 2025',
                                            'status' => 'active',
                                            'am_name' => 'Jane Smith'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'metro-1g',
                                'name' => 'Metro E 1Gbps',
                                'type' => 'product',
                                'revenue' => 35000000,
                                'target' => 30000000,
                                'achievement_percentage' => 116.67,
                                'children' => [
                                    [
                                        'id' => 'prj-003',
                                        'name' => 'Project C - Telkomsel Data Center',
                                        'type' => 'project',
                                        'revenue' => 35000000,
                                        'target' => 30000000,
                                        'achievement_percentage' => 116.67,
                                        'metadata' => [
                                            'period' => 'Jan - Dec 2025',
                                            'status' => 'active',
                                            'am_name' => 'John Doe'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'dia',
                        'name' => 'Dedicated Internet Access (DIA)',
                        'type' => 'subcategory',
                        'revenue' => 60000000,
                        'target' => 60000000,
                        'achievement_percentage' => 100,
                        'children' => [
                            [
                                'id' => 'dia-500',
                                'name' => 'DIA 500Mbps',
                                'type' => 'product',
                                'revenue' => 60000000,
                                'target' => 60000000,
                                'achievement_percentage' => 100,
                                'children' => [
                                    [
                                        'id' => 'prj-004',
                                        'name' => 'Project D - Universitas Indonesia',
                                        'type' => 'project',
                                        'revenue' => 60000000,
                                        'target' => 60000000,
                                        'achievement_percentage' => 100,
                                        'metadata' => [
                                            'period' => 'Jan - Dec 2025',
                                            'status' => 'completed',
                                            'am_name' => 'Bob Wilson'
                                        ]
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
                'type' => 'category',
                'revenue' => 85000000,
                'target' => 90000000,
                'achievement_percentage' => 94.44,
                'children' => [
                    [
                        'id' => 'iaas',
                        'name' => 'Infrastructure as a Service (IaaS)',
                        'type' => 'subcategory',
                        'revenue' => 50000000,
                        'target' => 55000000,
                        'achievement_percentage' => 90.91,
                        'children' => [
                            [
                                'id' => 'vm-standard',
                                'name' => 'Virtual Machine - Standard',
                                'type' => 'product',
                                'revenue' => 30000000,
                                'target' => 32000000,
                                'achievement_percentage' => 93.75,
                                'children' => [
                                    [
                                        'id' => 'prj-005',
                                        'name' => 'Project E - E-Commerce Platform',
                                        'type' => 'project',
                                        'revenue' => 30000000,
                                        'target' => 32000000,
                                        'achievement_percentage' => 93.75,
                                        'metadata' => [
                                            'period' => 'Mar - Nov 2025',
                                            'status' => 'active',
                                            'am_name' => 'Sarah Lee'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'vm-premium',
                                'name' => 'Virtual Machine - Premium',
                                'type' => 'product',
                                'revenue' => 20000000,
                                'target' => 23000000,
                                'achievement_percentage' => 86.96,
                                'children' => [
                                    [
                                        'id' => 'prj-006',
                                        'name' => 'Project F - Banking Core System',
                                        'type' => 'project',
                                        'revenue' => 20000000,
                                        'target' => 23000000,
                                        'achievement_percentage' => 86.96,
                                        'metadata' => [
                                            'period' => 'Jan - Oct 2025',
                                            'status' => 'active',
                                            'am_name' => 'Michael Chen'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'saas',
                        'name' => 'Software as a Service (SaaS)',
                        'type' => 'subcategory',
                        'revenue' => 35000000,
                        'target' => 35000000,
                        'achievement_percentage' => 100,
                        'children' => [
                            [
                                'id' => 'crm',
                                'name' => 'CRM Solution',
                                'type' => 'product',
                                'revenue' => 20000000,
                                'target' => 20000000,
                                'achievement_percentage' => 100,
                                'children' => [
                                    [
                                        'id' => 'prj-007',
                                        'name' => 'Project G - Sales Automation',
                                        'type' => 'project',
                                        'revenue' => 20000000,
                                        'target' => 20000000,
                                        'achievement_percentage' => 100,
                                        'metadata' => [
                                            'period' => 'Jan - Dec 2025',
                                            'status' => 'active',
                                            'am_name' => 'Emily Wang'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'erp',
                                'name' => 'ERP Solution',
                                'type' => 'product',
                                'revenue' => 15000000,
                                'target' => 15000000,
                                'achievement_percentage' => 100,
                                'children' => [
                                    [
                                        'id' => 'prj-008',
                                        'name' => 'Project H - Manufacturing ERP',
                                        'type' => 'project',
                                        'revenue' => 15000000,
                                        'target' => 15000000,
                                        'achievement_percentage' => 100,
                                        'metadata' => [
                                            'period' => 'Feb - Nov 2025',
                                            'status' => 'active',
                                            'am_name' => 'David Kim'
                                        ]
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
                'type' => 'category',
                'revenue' => 65000000,
                'target' => 70000000,
                'achievement_percentage' => 92.86,
                'children' => [
                    [
                        'id' => 'colo-rack',
                        'name' => 'Rack Colocation',
                        'type' => 'subcategory',
                        'revenue' => 40000000,
                        'target' => 42000000,
                        'achievement_percentage' => 95.24,
                        'children' => [
                            [
                                'id' => 'rack-quarter',
                                'name' => 'Quarter Rack',
                                'type' => 'product',
                                'revenue' => 15000000,
                                'target' => 16000000,
                                'achievement_percentage' => 93.75,
                                'children' => [
                                    [
                                        'id' => 'prj-009',
                                        'name' => 'Project I - Startup Hosting',
                                        'type' => 'project',
                                        'revenue' => 15000000,
                                        'target' => 16000000,
                                        'achievement_percentage' => 93.75,
                                        'metadata' => [
                                            'period' => 'Apr - Dec 2025',
                                            'status' => 'active',
                                            'am_name' => 'Lisa Anderson'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'rack-full',
                                'name' => 'Full Rack',
                                'type' => 'product',
                                'revenue' => 25000000,
                                'target' => 26000000,
                                'achievement_percentage' => 96.15,
                                'children' => [
                                    [
                                        'id' => 'prj-010',
                                        'name' => 'Project J - Financial Data Center',
                                        'type' => 'project',
                                        'revenue' => 25000000,
                                        'target' => 26000000,
                                        'achievement_percentage' => 96.15,
                                        'metadata' => [
                                            'period' => 'Jan - Dec 2025',
                                            'status' => 'active',
                                            'am_name' => 'Tom Harris'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'managed-services',
                        'name' => 'Managed Services',
                        'type' => 'subcategory',
                        'revenue' => 25000000,
                        'target' => 28000000,
                        'achievement_percentage' => 89.29,
                        'children' => [
                            [
                                'id' => 'managed-server',
                                'name' => 'Managed Server',
                                'type' => 'product',
                                'revenue' => 25000000,
                                'target' => 28000000,
                                'achievement_percentage' => 89.29,
                                'children' => [
                                    [
                                        'id' => 'prj-011',
                                        'name' => 'Project K - 24/7 Server Management',
                                        'type' => 'project',
                                        'revenue' => 25000000,
                                        'target' => 28000000,
                                        'achievement_percentage' => 89.29,
                                        'metadata' => [
                                            'period' => 'Jan - Nov 2025',
                                            'status' => 'active',
                                            'am_name' => 'Rachel Green'
                                        ]
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
                'note' => 'This is dummy/sample data for development and testing purposes only'
            ]
        ]);
    }
}
