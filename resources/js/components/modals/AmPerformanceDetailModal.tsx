import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, User, MapPin, Phone, Building2, Calendar, TrendingUp, TrendingDown, Target, Award, ArrowUp, ArrowDown } from 'lucide-react';
import axios from '@/lib/axios';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface AMInfo {
    nik_am: string;
    nama_am: string;
    posisi: string;
    no_gsm: string;
    witel_name: string;
    region_name: string;
}

interface PeriodData {
    period_display: string;
    quarter: number;
    year: number;
    t_revenue: number;
    r_revenue: number;
    ach_revenue_plan: number;
    t_scaling: number;
    r_scaling: number;
    ach_scaling: number;
    t_sales_datin: number;
    r_sales_datin: number;
    ach_sales_datin: number;
    t_hsi: number;
    r_hsi: number;
    ach_hsi: number;
    t_wireline: number;
    r_wireline: number;
    ach_wireline: number;
    t_wifi: number;
    r_wifi: number;
    ach_wifi: number;
    t_cyc: number;
    r_cyc: number;
    ach_cyc: number;
    t_cr: number;
    r_cr: number;
    ach_cr: number;
    t_profit: number;
    r_profit: number;
    ach_profit: number;
    t_nps: number;
    r_nps: number;
    ach_nps: number;
    t_maps: number;
    r_maps: number;
    ach_maps: number;
    t_lop: number;
    r_lop: number;
    ach_lop: number;
    t_capability: number;
    r_capability: number;
    ach_capability: number;
    t_cc: number;
    r_cc: number;
    ach_cc: number;
    ach_result: number;
    ach_proses: number;
    nki_adjustment: number;
    formatted_t_revenue?: string;
    formatted_r_revenue?: string;
    formatted_t_scaling?: string;
    formatted_r_scaling?: string;
    formatted_t_lop?: string;
    formatted_r_lop?: string;
}

interface AMDetailData {
    am_info: AMInfo;
    current_period: {
        quarter: number;
        year: number;
        period_display: string;
    };
    summary: {
        target_proses: number;
        realisasi_proses: number;
        target_result: number;
        realisasi_result: number;
    };
    historical_data: PeriodData[];
    best_period: {
        period_display: string;
        nki_adjustment: number;
    };
}

interface AmPerformanceDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    nikAm: string;
    quarter: number;
    year: number;
    segment: string;
}

const AmPerformanceDetailModal: React.FC<AmPerformanceDetailModalProps> = ({
    isOpen,
    onClose,
    nikAm,
    quarter,
    year,
    segment
}) => {
    const [data, setData] = useState<AMDetailData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [chartFilter, setChartFilter] = useState<'quarter' | 'year'>('quarter');

    useEffect(() => {
        if (isOpen && nikAm) {
            fetchAMDetails();
        }
    }, [isOpen, nikAm, quarter, year, segment]);

    const fetchAMDetails = async () => {
        setLoading(true);
        setError(null);
        
        console.log('Fetching AM Details with params:', {
            nik_am: nikAm,
            quarter: quarter,
            year: year,
            segment: segment
        });
        
        try {
            const response = await axios.get('/api/dashboard/am-performance-detail', {
                params: {
                    nik_am: nikAm,
                    quarter: quarter,
                    year: year,
                    segment: segment
                }
            });
            
            console.log('AM Details Response:', response.data);
            console.log('Response success:', response.data.success);
            console.log('Response data:', response.data.data);
            
            if (response.data.success) {
                setData(response.data.data);
                console.log('Data set successfully:', response.data.data);
            } else {
                const errorMsg = response.data.message || 'Failed to fetch AM details';
                console.error('API returned error:', errorMsg);
                setError(errorMsg);
            }
        } catch (err: any) {
            console.error('Fetch error:', err);
            console.error('Error response:', err.response?.data);
            console.error('Error status:', err.response?.status);
            setError(err.response?.data?.message || 'Error loading AM data');
        } finally {
            setLoading(false);
        }
    };

    // Prepare chart data based on filter
    const prepareChartData = () => {
        if (!data || !data.historical_data) return [];

        if (chartFilter === 'year') {
            // Yearly mode: Compare current year with 2 previous years (total 3 years)
            const currentYear = parseInt(String(year)); // Ensure year prop is number
            const targetYears = [currentYear, currentYear - 1, currentYear - 2];
            const yearlyData: { [key: number]: any } = {};
            
            console.log('=== YEARLY CHART DEBUG ===');
            console.log('Props year:', year, 'typeof:', typeof year);
            console.log('Parsed currentYear:', currentYear, 'typeof:', typeof currentYear);
            console.log('Target Years:', targetYears);
            console.log('Total Historical Data Records:', data.historical_data.length);
            console.log('Full Historical Data:', JSON.stringify(data.historical_data, null, 2));
            
            // Initialize data structure for target years only
            targetYears.forEach(yr => {
                yearlyData[yr] = {
                    year: yr,
                    Q1: null,
                    Q2: null,
                    Q3: null,
                    Q4: null
                };
            });
            
            // Fill in data from historical_data
            data.historical_data.forEach((period, index) => {
                const periodYear = period.year; // Already number from backend
                const periodQuarter = period.quarter; // Already number from backend
                
                console.log(`Period ${index}:`, {
                    periodYear,
                    periodQuarter,
                    typeof_year: typeof periodYear,
                    targetYears,
                    includes: targetYears.includes(periodYear),
                    nki: period.nki_adjustment
                });
                
                // Only include data for target years
                if (targetYears.includes(periodYear)) {
                    console.log(`✓✓✓ ADDING Q${periodQuarter} ${periodYear} NKI: ${period.nki_adjustment}`);
                    yearlyData[periodYear][`Q${periodQuarter}`] = period.nki_adjustment;
                }
            });

            console.log('Final Yearly Data Structure:', JSON.stringify(yearlyData, null, 2));

            // Convert to array and create data points for each quarter
            const chartData: any[] = [];
            ['Q1', 'Q2', 'Q3', 'Q4'].forEach(quarter => {
                const dataPoint: any = { quarter };
                targetYears.forEach(yr => {
                    const value = yearlyData[yr][quarter];
                    // Only add to dataPoint if value is not null (connectNulls=false will handle gaps)
                    dataPoint[`year${yr}`] = value;
                });
                chartData.push(dataPoint);
            });

            console.log('Chart Data:', chartData);

            return chartData;
        } else {
            // Quarterly mode: Show all parameters (Revenue to CC) for last 3 quarters
            const last3Quarters = data.historical_data.slice(0, 3);
            
            const parameters = [
                { key: 'ach_revenue_plan', label: 'Revenue' },
                { key: 'ach_scaling', label: 'Scaling' },
                { key: 'ach_sales_datin', label: 'Sales Datin' },
                { key: 'ach_hsi', label: 'HSI' },
                { key: 'ach_wireline', label: 'Wireline' },
                { key: 'ach_wifi', label: 'WiFi' },
                { key: 'ach_cyc', label: 'CYC' },
                { key: 'ach_cr', label: 'CR' },
                { key: 'ach_profit', label: 'Profit' },
                { key: 'ach_nps', label: 'NPS' },
                { key: 'ach_maps', label: 'MAPS' },
                { key: 'ach_lop', label: 'LOP' },
                { key: 'ach_capability', label: 'Capability' },
                { key: 'ach_cc', label: 'CC' }
            ];

            return parameters.map(param => ({
                parameter: param.label,
                ...Object.fromEntries(
                    last3Quarters.map((period) => [
                        `Q${period.quarter} ${period.year}`,
                        period[param.key as keyof PeriodData]
                    ])
                )
            }));
        }
    };

    // Get max value from quarterly data for dynamic Y-axis
    const getMaxValueFromQuarterlyData = () => {
        if (!data || !data.historical_data) return 120;
        
        const last3Quarters = data.historical_data.slice(0, 3);
        const paramKeys = [
            'ach_revenue_plan', 'ach_scaling', 'ach_sales_datin', 'ach_hsi', 
            'ach_wireline', 'ach_wifi', 'ach_cyc', 'ach_cr', 'ach_profit', 
            'ach_nps', 'ach_maps', 'ach_lop', 'ach_capability', 'ach_cc'
        ];

        let maxValue = 0;
        last3Quarters.forEach(period => {
            paramKeys.forEach(key => {
                const value = period[key as keyof PeriodData] as number;
                if (value && value > maxValue) {
                    maxValue = value;
                }
            });
        });

        // Round up to nearest 10 for cleaner display
        return Math.ceil(maxValue / 10) * 10;
    };

    // Generate dynamic ticks for quarterly mode (10 iterations)
    const getQuarterlyTicks = () => {
        const maxValue = getMaxValueFromQuarterlyData();
        const step = maxValue / 10;
        return Array.from({ length: 11 }, (_, i) => Math.round(step * i * 100) / 100);
    };

    // Get years for legend (only current year and 2 previous years)
    const getYearsFromData = () => {
        return [year, year - 1, year - 2];
    };

    // Get quarters for legend (quarterly mode)
    const getQuartersFromData = () => {
        if (!data || !data.historical_data) return [];
        const last3 = data.historical_data.slice(0, 3);
        return last3.map(p => `Q${p.quarter} ${p.year}`);
    };

    const formatNumber = (value: number | string, decimals: number = 2, isCurrency: boolean = true) => {
        const num = typeof value === 'string' ? parseFloat(value) : value;
        if (isNaN(num)) return isCurrency ? 'Rp 0' : '0';
        const formatted = num.toLocaleString('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
        return isCurrency ? 'Rp ' + formatted : formatted;
    };

    // Helper function to render achievement cell with indicator
    const renderAchCell = (achValue: number) => {
        const isAboveTarget = achValue >= 100;
        
        return (
            <td className={`text-right p-2 text-sm font-bold border-r border-gray-300 dark:border-gray-600 bg-green-50 dark:bg-green-950 ${
                isAboveTarget 
                    ? 'text-green-600 dark:text-green-400' 
                    : 'text-red-600 dark:text-red-400'
            }`}>
                <div className="flex items-center justify-end gap-1 whitespace-nowrap">
                    {isAboveTarget ? (
                        <ArrowUp className="h-3 w-3 flex-shrink-0" />
                    ) : (
                        <ArrowDown className="h-3 w-3 flex-shrink-0" />
                    )}
                    <span>{formatNumber(achValue, 2, false)}%</span>
                </div>
            </td>
        );
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[95vw] w-[95vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <User className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">
                            Account Manager Performance Detail
                        </span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Detailed performance metrics and historical data
                    </DialogDescription>
                </DialogHeader>

                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                    </div>
                )}

                {error && (
                    <div className="text-center py-8 text-red-600 dark:text-red-400">
                        {error}
                    </div>
                )}

                {data && !loading && (
                    <>
                        {/* AM Info Card */}
                        <div className="bg-white dark:from-gray-900 dark:to-gray-850 rounded-lg border border-red-200 dark:border-gray-800 p-6 mb-6">
                            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-red-100 dark:bg-red-950 rounded-lg">
                                        <User className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">NIK</p>
                                        <p className="font-semibold text-gray-900 dark:text-white">{data.am_info.nik_am}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-red-100 dark:bg-red-950 rounded-lg">
                                        <Award className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">Posisi</p>
                                        <p className="font-semibold text-gray-900 dark:text-white">{data.am_info.posisi}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-red-100 dark:bg-red-950 rounded-lg">
                                        <User className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">Nama</p>
                                        <p className="font-semibold text-gray-900 dark:text-white">{data.am_info.nama_am}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-red-100 dark:bg-red-950 rounded-lg">
                                        <Phone className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">No GSM</p>
                                        <p className="font-semibold text-gray-900 dark:text-white">{data.am_info.no_gsm}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-red-100 dark:bg-red-950 rounded-lg">
                                        <Building2 className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">Witel</p>
                                        <p className="font-semibold text-gray-900 dark:text-white">{data.am_info.witel_name}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Summary Cards */}
                        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Target Parameter Proses</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{formatNumber(data.summary.target_proses, 0)}</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-green-50 dark:bg-green-950 rounded-lg">
                                                <Target className="h-5 w-5 text-green-600 dark:text-green-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Realisasi Parameter Proses</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{formatNumber(data.summary.realisasi_proses, 0)}</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-green-50 dark:bg-green-950 rounded-lg">
                                                <TrendingUp className="h-5 w-5 text-green-600 dark:text-green-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Target Parameter Result</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{formatNumber(data.summary.target_result, 0)}</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-blue-50 dark:bg-blue-950 rounded-lg">
                                                <Target className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Realisasi Parameter Result</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{formatNumber(data.summary.realisasi_result, 0)}</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-blue-50 dark:bg-blue-950 rounded-lg">
                                                <TrendingUp className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Period</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{data.current_period.period_display}</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-purple-50 dark:bg-purple-950 rounded-lg">
                                                <Calendar className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Historical Performance Table and Best Period */}
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            {/* Table - 2 columns */}
                            <div className="lg:col-span-2 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <TrendingUp className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    Historical Performance
                                </h3>
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm border-collapse">
                                        <thead>
                                            <tr className="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-850 border-b-2 border-gray-300 dark:border-gray-600">
                                                <th className="text-left p-3 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600" rowSpan={2}>Periode</th>
                                                {/* Aspek Result - Blue Background */}
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Revenue</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Scaling</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Sales Datin</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>HSI</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Wireline</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>WiFi</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>CYC</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>CR</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Profit</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>NPS</th>
                                                {/* Aspek Process - Green Background */}
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-green-100 dark:bg-green-900" colSpan={3}>MAPS</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-green-100 dark:bg-green-900" colSpan={3}>LOP</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-green-100 dark:bg-green-900" colSpan={3}>Capability</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-green-100 dark:bg-green-900" colSpan={3}>CC</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600" rowSpan={2}>Ach Result</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600" rowSpan={2}>Ach Process</th>
                                                <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs" rowSpan={2}>NKI</th>
                                            </tr>
                                            <tr className="bg-gray-100 dark:bg-gray-800 border-b border-gray-300 dark:border-gray-600">
                                                {/* Revenue */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* Scaling */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* Sales Datin */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* HSI */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* Wireline */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* WiFi */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* CYC */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* CR */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* Profit */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* NPS */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* MAPS */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* LOP */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* Capability */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                {/* CC */}
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {data.historical_data && data.historical_data.length > 0 ? (
                                                // Only show first 3 periods (current quarter + 2 previous) for table
                                                data.historical_data.slice(0, 3).map((period, idx) => (
                                                    <tr 
                                                        key={idx} 
                                                        className={`border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors ${
                                                            idx % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-850'
                                                        }`}
                                                    >
                                                        <td className="p-3 font-medium text-sm border-r border-gray-300 dark:border-gray-600 min-w-[120px] whitespace-nowrap">{period.period_display}</td>
                                                        {/* Revenue */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[120px] whitespace-nowrap">{period.formatted_t_revenue || formatNumber(period.t_revenue, 0, true)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[120px] whitespace-nowrap">{period.formatted_r_revenue || formatNumber(period.r_revenue, 0, true)}</td>
                                                        {renderAchCell(period.ach_revenue_plan)}
                                                        {/* Scaling */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[120px] whitespace-nowrap">{period.formatted_t_scaling || formatNumber(period.t_scaling, 0, true)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[120px] whitespace-nowrap">{period.formatted_r_scaling || formatNumber(period.r_scaling, 0, true)}</td>
                                                        {renderAchCell(period.ach_scaling)}
                                                        {/* Sales Datin */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_sales_datin, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_sales_datin, 0, false)}</td>
                                                        {renderAchCell(period.ach_sales_datin)}
                                                        {/* HSI */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_hsi, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_hsi, 0, false)}</td>
                                                        {renderAchCell(period.ach_hsi)}
                                                        {/* Wireline */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_wireline, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_wireline, 0, false)}</td>
                                                        {renderAchCell(period.ach_wireline)}
                                                        {/* WiFi */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_wifi, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_wifi, 0, false)}</td>
                                                        {renderAchCell(period.ach_wifi)}
                                                        {/* CYC */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_cyc, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_cyc, 0, false)}</td>
                                                        {renderAchCell(period.ach_cyc)}
                                                        {/* CR */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_cr, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_cr, 0, false)}</td>
                                                        {renderAchCell(period.ach_cr)}
                                                        {/* Profit */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_profit, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_profit, 0, false)}</td>
                                                        {renderAchCell(period.ach_profit)}
                                                        {/* NPS */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_nps, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_nps, 0, false)}</td>
                                                        {renderAchCell(period.ach_nps)}
                                                        {/* MAPS */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_maps, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_maps, 0, false)}</td>
                                                        {renderAchCell(period.ach_maps)}
                                                        {/* LOP */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[120px] whitespace-nowrap">{period.formatted_t_lop || formatNumber(period.t_lop, 0, true)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[120px] whitespace-nowrap">{period.formatted_r_lop || formatNumber(period.r_lop, 0, true)}</td>
                                                        {renderAchCell(period.ach_lop)}
                                                        {/* Capability */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_capability, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_capability, 0, false)}</td>
                                                        {renderAchCell(period.ach_capability)}
                                                        {/* CC */}
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.t_cc, 0, false)}</td>
                                                        <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(period.r_cc, 0, false)}</td>
                                                        {renderAchCell(period.ach_cc)}
                                                        {/* Summary */}
                                                        <td className="text-right p-2 text-sm font-bold border-r border-gray-300 dark:border-gray-600 bg-green-50 dark:bg-green-950 text-gray-900 dark:text-gray-100">
                                                            {formatNumber(period.ach_result, 2, false)}%
                                                        </td>
                                                        <td className="text-right p-2 text-sm font-bold border-r border-gray-300 dark:border-gray-600 bg-green-50 dark:bg-green-950 text-gray-900 dark:text-gray-100">
                                                            {formatNumber(period.ach_proses, 2, false)}%
                                                        </td>
                                                        <td className="text-right p-2 text-sm font-bold text-gray-900 dark:text-gray-100">
                                                            {formatNumber(period.nki_adjustment, 2, false)}%
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan={45} className="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                        No historical data available
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Best Period - 1 column */}
                            <div className="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-950 dark:to-orange-950 rounded-lg border border-yellow-200 dark:border-yellow-800 p-5 shadow-sm flex flex-col">
                                <div className="flex items-center gap-2 mb-4">
                                    <Award className="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">Best Period</h3>
                                </div>
                                <div className="flex-1 flex items-center justify-center">
                                    <div className="bg-white dark:bg-gray-900 rounded-lg border-2 border-orange-300 dark:border-orange-700 p-4 shadow-inner w-full">
                                        <div className="text-center">
                                            <p className="text-2xl font-bold text-gray-900 dark:text-white mb-1">{data.best_period.period_display}</p>
                                            <div className="bg-white dark:bg-gray-900 rounded-lg pt-3 px-3 pb-1">
                                                <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">NKI Achievement</p>
                                                <p className="text-6xl font-bold text-yellow-600 dark:text-yellow-400">{formatNumber(data.best_period.nki_adjustment, 2, false)}%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Performance Trend Chart */}
                        <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">Performance Trend</h3>
                                <div className="flex gap-2">
                                    <Button
                                        size="sm"
                                        variant={chartFilter === 'quarter' ? 'default' : 'outline'}
                                        onClick={() => setChartFilter('quarter')}
                                    >
                                        Quarterly
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant={chartFilter === 'year' ? 'default' : 'outline'}
                                        onClick={() => setChartFilter('year')}
                                    >
                                        Yearly
                                    </Button>
                                </div>
                            </div>
                            <div className="h-[400px] w-full">
                                {chartFilter === 'year' ? (
                                    prepareChartData().length > 0 ? (
                                        <ResponsiveContainer width="100%" height={400} minWidth={300}>
                                            <LineChart data={prepareChartData()} margin={{ top: 20, right: 30, left: 20, bottom: 20 }}>
                                            <CartesianGrid strokeDasharray="3 3" className="stroke-gray-300 dark:stroke-gray-700" />
                                            <XAxis 
                                                dataKey="quarter" 
                                                label={{ value: 'Quartal', position: 'insideBottom', offset: -10 }}
                                                className="text-gray-700 dark:text-gray-300"
                                            />
                                            <YAxis 
                                                label={{ value: 'NKI (%)', angle: -90, position: 'insideLeft' }}
                                                className="text-gray-700 dark:text-gray-300"
                                                ticks={[0, 15, 30, 45, 60, 75, 90, 105, 120]}
                                                domain={[0, 120]}
                                            />
                                            <Tooltip 
                                                contentStyle={{ 
                                                    backgroundColor: 'rgba(255, 255, 255, 0.95)', 
                                                    border: '1px solid #ccc',
                                                    borderRadius: '8px'
                                                }}
                                                formatter={(value: any) => `${value?.toFixed(2)}%`}
                                            />
                                            <Legend layout="horizontal" verticalAlign="bottom" align="left" wrapperStyle={{ paddingLeft: '50px' }} />
                                            {getYearsFromData().map((yearItem, index) => {
                                                // Check if this year has any non-null data
                                                const hasData = prepareChartData().some(point => point[`year${yearItem}`] != null);
                                                
                                                return (
                                                    <Line
                                                        key={yearItem}
                                                        type="monotone"
                                                        dataKey={`year${yearItem}`}
                                                        name={`${yearItem}`}
                                                        stroke={yearItem === year ? '#3b82f6' : '#10b981'}
                                                        strokeWidth={2}
                                                        dot={hasData ? { fill: yearItem === year ? '#3b82f6' : '#10b981', r: 4 } : false}
                                                        activeDot={{ r: 6 }}
                                                        connectNulls={false}
                                                        strokeDasharray={hasData ? "0" : "5 5"}
                                                    />
                                                );
                                            })}
                                        </LineChart>
                                    </ResponsiveContainer>
                                    ) : (
                                        <div className="flex items-center justify-center h-full text-gray-500">No data available for yearly chart</div>
                                    )
                                ) : (
                                    prepareChartData().length > 0 ? (
                                        <ResponsiveContainer width="100%" height={400} minWidth={300}>
                                            <LineChart data={prepareChartData()} margin={{ top: 20, right: 30, left: 20, bottom: 20 }}>
                                            <CartesianGrid strokeDasharray="3 3" className="stroke-gray-300 dark:stroke-gray-700" />
                                            <XAxis 
                                                dataKey="parameter" 
                                                label={{ value: 'Parameter', position: 'insideBottom', offset: -10 }}
                                                className="text-gray-700 dark:text-gray-300"
                                            />
                                            <YAxis 
                                                label={{ value: 'Achievement (%)', angle: -90, position: 'insideLeft' }}
                                                className="text-gray-700 dark:text-gray-300"
                                                ticks={getQuarterlyTicks()}
                                                domain={[0, getMaxValueFromQuarterlyData()]}
                                            />
                                            <Tooltip 
                                                contentStyle={{ 
                                                    backgroundColor: 'rgba(255, 255, 255, 0.95)', 
                                                    border: '1px solid #ccc',
                                                    borderRadius: '8px'
                                                }}
                                                formatter={(value: any) => `${value?.toFixed(2)}%`}
                                            />
                                            <Legend layout="horizontal" verticalAlign="bottom" align="left" wrapperStyle={{ paddingLeft: '50px' }} />
                                            {getQuartersFromData().map((quarterLabel, index) => {
                                                // First quarter is the selected period (green), others are previous quarters (black)
                                                const isCurrentQuarter = index === 0;
                                                return (
                                                    <Line
                                                        key={quarterLabel}
                                                        type="monotone"
                                                        dataKey={quarterLabel}
                                                        name={quarterLabel}
                                                        stroke={isCurrentQuarter ? '#10b981' : '#000000'}
                                                        strokeWidth={2}
                                                        dot={{ fill: isCurrentQuarter ? '#10b981' : '#000000', r: 4 }}
                                                        activeDot={{ r: 6 }}
                                                    />
                                                );
                                            })}
                                        </LineChart>
                                    </ResponsiveContainer>
                                    ) : (
                                        <div className="flex items-center justify-center h-full text-gray-500">No data available for quarterly chart</div>
                                    )
                                )}
                            </div>
                        </div>
                    </>
                )}

                <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <Button 
                        variant="outline" 
                        onClick={onClose}
                        className="px-6 py-2"
                    >
                        Close
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
};

export default AmPerformanceDetailModal;
