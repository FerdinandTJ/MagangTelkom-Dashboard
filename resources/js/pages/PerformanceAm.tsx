import React, { useState, useRef, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, Legend } from 'recharts';
import { Users, Target, Calendar, FileSpreadsheet, Download, Upload, MapPin } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import AMRevenueDetailModal from '@/components/modals/AMRevenueDetailModal';
import RegionNkiModal from '@/components/RegionNkiModal';

interface PerformanceAMProps {
    amMetrics: {
        total_am: number;
        revenue_target: number;
        formatted_revenue_target: string;
        revenue_actual: number;
        formatted_revenue_actual: string;
        year: number;
        month_start: string | null;
        month_end: string | null;
        quartal: string;
    };
    availableYears: number[];
    availableQuartals: string[];
    amRevenueRanking: Array<{
        nik: string;
        am_name: string;
        region_code: string;
        t_revenue: number;
        formatted_revenue: string;
        r_revenue: number;
        formatted_r_revenue: string;
    }>;
    regionDistribution: Array<{
        region_id: number;
        region_code: string;
        region_name: string;
        am_count: number;
        percentage: number;
    }>;
    regionalPerformance: Array<{
        region_code: string;
        revenue: number;
        formatted_revenue: string;
        growth: number;
        formatted_growth: string;
        company_count: number;
        top_ams: Array<{
            nik: string;
            am_name: string;
            revenue: number;
            formatted_revenue: string;
            achievement: number;
            formatted_achievement: string;
        }>;
    }>;
    bestPerformance: Array<{
        nik: string;
        am_name: string;
        region_code: string;
        revenue: number;
        formatted_revenue: string;
        growth: number;
        formatted_growth: string;
        company_count: number;
    }>;
    accountManagerList: Array<{
        no: number;
        nik: string;
        nama: string;
        posisi: string;
        no_gsm: string;
        lokasi_am: string;
    }>;
    currentYear: number;
    currentQuartal: string;
    currentRegion?: string;
    currentYtd?: boolean;
}

// Colors untuk Region Distribution Pie Chart (6 regions)
// HQ TREG2, TREG1, TREG2, TREG3, TREG4, TREG5
const COLORS = ['#8b5cf6', '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6'];

export default function PerformanceAM({
    amMetrics,
    availableYears,
    availableQuartals,
    amRevenueRanking,
    regionDistribution,
    regionalPerformance,
    bestPerformance,
    accountManagerList,
    currentYear,
    currentQuartal,
    currentRegion = 'ALL',
    currentYtd = false
}: PerformanceAMProps) {
    const [selectedYear, setSelectedYear] = useState(currentYear);
    const [selectedQuartal, setSelectedQuartal] = useState(
        currentYtd && currentQuartal !== 'Q1' ? `${currentQuartal} YTD` : currentQuartal
    );
    const [selectedRegion, setSelectedRegion] = useState<string>(currentRegion);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [selectedAMNik, setSelectedAMNik] = useState<string | null>(null);
    const [activeRevenueTab, setActiveRevenueTab] = useState<'chart' | 'regional'>('regional');
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 15;

    // State for Region NKI Modal
    const [isRegionNkiModalOpen, setIsRegionNkiModalOpen] = useState(false);
    const [selectedRegionId, setSelectedRegionId] = useState<number | null>(null);
    const [selectedRegionName, setSelectedRegionName] = useState<string>('');

    // Ref untuk menghitung tinggi kolom kanan
    const rightColumnRef = useRef<HTMLDivElement>(null);
    const [rightColumnHeight, setRightColumnHeight] = useState<number>(0);

    // Calculate right column height dynamically
    useEffect(() => {
        const calculateHeight = () => {
            if (rightColumnRef.current) {
                const height = rightColumnRef.current.offsetHeight;
                setRightColumnHeight(height);
            }
        };

        calculateHeight();
        window.addEventListener('resize', calculateHeight);
        
        // Delay calculation to ensure content is rendered
        const timer = setTimeout(calculateHeight, 100);

        return () => {
            window.removeEventListener('resize', calculateHeight);
            clearTimeout(timer);
        };
    }, [activeRevenueTab, bestPerformance]);

    // Parse quartal to determine if YTD and base quartal
    const isYearToDate = selectedQuartal.includes('YTD');
    const baseQuartal = selectedQuartal.replace(' YTD', '');

    // Generate quartal options with YTD
    const quartalOptions = [
        { value: 'Q1', label: 'Q1' },
        { value: 'Q2', label: 'Q2' },
        { value: 'Q2 YTD', label: 'Q2 YTD' },
        { value: 'Q3', label: 'Q3' },
        { value: 'Q3 YTD', label: 'Q3 YTD' },
        { value: 'Q4', label: 'Q4' },
        { value: 'Q4 YTD', label: 'Q4 YTD' }
    ];

    // Get unique regions dari amRevenueRanking
    const availableRegions = ['ALL', ...Array.from(new Set(amRevenueRanking.map(am => am.region_code)))].filter(Boolean);

    // Filter amRevenueRanking berdasarkan region yang dipilih (untuk chart)
    const filteredAmRevenueRanking = selectedRegion === 'ALL' 
        ? amRevenueRanking 
        : amRevenueRanking.filter(am => am.region_code === selectedRegion);

    // Filter regionalPerformance berdasarkan region yang dipilih (untuk table)
    const filteredRegionalPerformance = selectedRegion === 'ALL'
        ? regionalPerformance
        : regionalPerformance.filter(region => region.region_code === selectedRegion);

    // Create mapping NIK to region_code from amRevenueRanking
    const nikToRegionMap = new Map(
        amRevenueRanking.map(am => [am.nik, am.region_code])
    );

    // Filter accountManagerList berdasarkan region yang dipilih (untuk table)
    const filteredAccountManagerList = selectedRegion === 'ALL'
        ? accountManagerList
        : accountManagerList.filter(am => nikToRegionMap.get(am.nik) === selectedRegion);

    // Pagination calculations
    const totalPages = Math.ceil(filteredAccountManagerList.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedAccountManagerList = filteredAccountManagerList.slice(startIndex, endIndex);

    // Reset to page 1 when filter changes
    React.useEffect(() => {
        setCurrentPage(1);
    }, [selectedRegion]);

    // Fungsi untuk handle perubahan filter tahun
    const handleYearChange = (year: string) => {
        setSelectedYear(parseInt(year));
        const ytdValue = selectedQuartal.includes('YTD') ? '1' : '0';
        const baseQ = selectedQuartal.replace(' YTD', '');
        router.get('/performance-am', { year: year, quartal: baseQ, ytd: ytdValue, region: selectedRegion }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    // Fungsi untuk handle perubahan filter quartal (with YTD)
    const handleQuartalChange = (quartalWithYtd: string) => {
        setSelectedQuartal(quartalWithYtd);
        const isYtd = quartalWithYtd.includes('YTD');
        const baseQ = quartalWithYtd.replace(' YTD', '');
        router.get('/performance-am', { year: selectedYear, quartal: baseQ, ytd: isYtd ? '1' : '0', region: selectedRegion }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    // Fungsi untuk handle perubahan filter region
    const handleRegionChange = (region: string) => {
        setSelectedRegion(region);
        const ytdValue = selectedQuartal.includes('YTD') ? '1' : '0';
        const baseQ = selectedQuartal.replace(' YTD', '');
        router.get('/performance-am', { year: selectedYear, quartal: baseQ, ytd: ytdValue, region: region }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    // Format date untuk tampilan bulan
    const formatMonthRange = () => {
        if (!amMetrics.month_start || !amMetrics.month_end) return '-';
        
        const startDate = new Date(amMetrics.month_start);
        const endDate = new Date(amMetrics.month_end);
        
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        
        return `${startDate.getDate()} ${months[startDate.getMonth()]} - ${endDate.getDate()} ${months[endDate.getMonth()]}`;
    };

    // Handler untuk klik bar chart
    const handleBarClick = (data: any) => {
        if (data && data.nik) {
            setSelectedAMNik(data.nik);
            setIsModalOpen(true);
        }
    };

    // Handler untuk click pada pie chart region
    const handlePieClick = (data: any) => {
        // Hanya buka modal untuk HQ TREG2
        // HQ TREG2 memiliki code "HQ TREG2" atau "Headquarters TREG2"
        if (data && data.region_id) {
            const regionCode = data.region_code || '';
            const regionName = data.region_name || '';
            
            // Filter: Hanya HQ TREG2 yang bisa buka modal
            const isHqTreg2 = regionCode.includes('HQ') || regionName.includes('Headquarters');
            
            if (isHqTreg2) {
                setSelectedRegionId(data.region_id);
                setSelectedRegionName(regionName || regionCode);
                setIsRegionNkiModalOpen(true);
            }
        }
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Performance AM', href: '/performance-am' }
            ]}
        >   
            <Head title="Performance AM" />
            <div className="min-h-screen bg-gradient-to-br from-red-50/70 via-white to-pink-50/70 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900 p-4 sm:p-6 lg:p-6">
                {/* Metrics Cards - Fungsi ini untuk menampilkan metrik utama Performance AM */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    {/* Card 1: Total AM - Total Account Manager yang terdaftar */}
                    <Card className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total AM</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">{amMetrics.total_am}</p>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        {selectedRegion === 'ALL' ? 'Account Managers' : `Region ${selectedRegion}`}
                                    </p>
                                </div>
                                <div className="flex-shrink-0 ml-4">
                                    <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                        <Users className="h-6 w-6 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>

                    {/* Card 2: Revenue Target - Total target revenue dari semua AM */}
                    <Card className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Revenue Target</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                        {amMetrics.formatted_revenue_target}
                                    </p>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        {selectedRegion === 'ALL' ? 'Target periode ini' : `Region ${selectedRegion}`}
                                    </p>
                                </div>
                                <div className="flex-shrink-0 ml-4">
                                    <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                        <Target className="h-6 w-6 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>

                    {/* Card 3: Revenue Actual - Total actual revenue dari semua AM */}
                    <Card className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Revenue Actual</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                        {amMetrics.formatted_revenue_actual}
                                    </p>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">Realisasi periode ini</p>
                                </div>
                                <div className="flex-shrink-0 ml-4">
                                    <div className="p-2 bg-green-50 dark:bg-green-950 rounded-lg">
                                        <Target className="h-6 w-6 text-green-600 dark:text-green-400" />
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>

                    {/* Card 4: Period (Year & Quartal) - Tahun dan Quartal dengan dropdown */}
                    <Card className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Period</p>
                                    <div className="flex gap-2 mt-2">
                                        <Select value={selectedYear.toString()} onValueChange={handleYearChange}>
                                            <SelectTrigger className="w-full bg-white dark:bg-gray-800">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableYears.map(year => (
                                                    <SelectItem key={year} value={year.toString()}>
                                                        {year}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <Select value={selectedQuartal} onValueChange={handleQuartalChange}>
                                            <SelectTrigger className="w-full bg-white dark:bg-gray-800">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {quartalOptions.map(option => (
                                                    <SelectItem key={option.value} value={option.value}>
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                        {isYearToDate && baseQuartal !== 'Q1' 
                                            ? `Q1 - ${baseQuartal} (YTD)` 
                                            : formatMonthRange()
                                        }
                                    </p>
                                </div>
                                <div className="flex-shrink-0 ml-4">
                                    <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                        <Calendar className="h-6 w-6 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>

                    {/* Card 5: Export/Import Buttons - Placeholder untuk fitur nanti */}
                    <Card className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Data Actions</p>
                                    <div className="flex flex-col gap-2 mt-2">
                                        <Button 
                                            variant="outline" 
                                            size="sm" 
                                            className="w-full"
                                            onClick={() => alert('Export feature coming soon')}
                                        >
                                            <Download className="h-4 w-4 mr-2" />
                                            Export
                                        </Button>
                                        <Button 
                                            variant="outline" 
                                            size="sm" 
                                            className="w-full"
                                            onClick={() => alert('Import feature coming soon')}
                                        >
                                            <Upload className="h-4 w-4 mr-2" />
                                            Import
                                        </Button>
                                    </div>
                                </div>
                                <div className="flex-shrink-0 ml-4">
                                    <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                        <FileSpreadsheet className="h-6 w-6 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>
                </div>

                {/* Charts Section - Fungsi ini untuk menampilkan visualisasi data dalam bentuk chart */}
                <div className="grid grid-cols-1 lg:grid-cols-10 gap-6 mb-8 items-start">
                    {/* Target Revenue AM Chart - Chart dengan lebar 70% (7 kolom dari 10) */}
                    <div className="lg:col-span-7 flex flex-col gap-6">
                        <Card 
                            className="flex flex-col"
                            style={activeRevenueTab === 'regional' && rightColumnHeight > 0 ? { height: `${rightColumnHeight}px` } : undefined}
                        >
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="flex items-center gap-2">
                                            <Target className="h-5 w-5 text-red-600" />
                                            {activeRevenueTab === 'regional' ? 'Performance AM' : 'Target Revenue AM'}
                                        </CardTitle>
                                        <CardDescription>
                                            {activeRevenueTab === 'regional' 
                                                ? 'Regional performance with top Account Managers'
                                                : 'Total target revenue per Account Manager'
                                            }
                                        </CardDescription>
                                    </div>
                                    {/* Filter Region untuk chart ini */}
                                    <div className="flex items-center gap-2">
                                        <span className="text-sm text-gray-600">Region:</span>
                                        <Select value={selectedRegion} onValueChange={handleRegionChange}>
                                            <SelectTrigger className="w-[140px]">
                                                <SelectValue placeholder="All Regions" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableRegions.map((region) => (
                                                    <SelectItem key={region} value={region}>
                                                        {region === 'ALL' ? 'All Regions' : region}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                                {/* Tab Switcher */}
                                <div className="flex gap-2 mt-4">
                                    <Button
                                        variant={activeRevenueTab === 'regional' ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => setActiveRevenueTab('regional')}
                                        className={activeRevenueTab === 'regional' ? 'bg-red-600 hover:bg-red-700' : ''}
                                    >
                                        Regional Performance
                                    </Button>
                                    <Button
                                        variant={activeRevenueTab === 'chart' ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => setActiveRevenueTab('chart')}
                                        className={activeRevenueTab === 'chart' ? 'bg-red-600 hover:bg-red-700' : ''}
                                    >
                                        Chart View
                                    </Button>
                                </div>
                            </CardHeader>
                        <CardContent className={activeRevenueTab === 'regional' ? 'flex-1 overflow-hidden' : ''}>
                            {activeRevenueTab === 'regional' ? (
                                /* Regional Performance Table with dynamic height */
                                <div className="overflow-auto h-full">
                                    <table className="w-full text-sm border-collapse">
                                        <thead>
                                            <tr className="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-300">
                                                <th className="text-left p-4 font-bold text-gray-800 uppercase tracking-wide text-xs border-r border-gray-300">Regional</th>
                                                <th className="text-left p-4 font-bold text-gray-800 uppercase tracking-wide text-xs border-r border-gray-300">Top Account Manager</th>
                                                <th className="text-right p-4 font-bold text-gray-800 uppercase tracking-wide text-xs border-r border-gray-300">Revenue</th>
                                                <th className="text-right p-4 font-bold text-gray-800 uppercase tracking-wide text-xs">Achievement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {filteredRegionalPerformance.map((region, regionIdx) => (
                                                <React.Fragment key={region.region_code}>
                                                    {region.top_ams.map((am, idx) => (
                                                        <tr 
                                                            key={`${region.region_code}-${am.nik}`} 
                                                            className={`border-b border-gray-200 hover:bg-gray-50 transition-colors duration-150 ${
                                                                idx === region.top_ams.length - 1 && regionIdx < filteredRegionalPerformance.length - 1 
                                                                    ? 'border-b-2 border-gray-300' 
                                                                    : ''
                                                            }`}
                                                        >
                                                            {idx === 0 ? (
                                                                <td className="p-4 align-top bg-gray-50/50 border-r border-gray-300 pointer-events-none" rowSpan={region.top_ams.length}>
                                                                    <div className="space-y-3">
                                                                        <div className="flex items-center gap-2">
                                                                            <div className="w-1 h-8 bg-red-600 rounded-full"></div>
                                                                            <span className="font-bold text-lg text-gray-900">{region.region_code}</span>
                                                                        </div>
                                                                        <div className="pl-3 space-y-2 text-sm border-l-2 border-gray-200">
                                                                            <div className="flex items-center justify-between">
                                                                                <span className="text-gray-600 font-medium min-w-[60px]">Revenue:</span>
                                                                                <span className="text-gray-900 font-bold">{region.formatted_revenue}</span>
                                                                            </div>
                                                                            <div className="flex items-center justify-between">
                                                                                <span className="text-gray-600 font-medium min-w-[60px]">Growth:</span>
                                                                                <span className={`font-bold flex items-center gap-1 ${
                                                                                    region.growth >= 0 ? 'text-green-600' : 'text-red-600'
                                                                                }`}>
                                                                                    {region.growth >= 0 ? '↗' : '↘'}
                                                                                    {region.formatted_growth}
                                                                                </span>
                                                                            </div>
                                                                            <div className="flex items-center justify-between">
                                                                                <span className="text-gray-600 font-medium min-w-[60px]">CC:</span>
                                                                                <span className="text-gray-900 font-bold">{region.company_count} companies</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            ) : null}
                                                            <td className="pl-4 border-r border-gray-300">
                                                                <div className="flex items-center gap-3">
                                                                    <div className={`flex-shrink-0 inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full shadow-sm ${
                                                                        idx === 0 ? 'bg-yellow-500' : idx === 1 ? 'bg-gray-400' : 'bg-orange-600'
                                                                    }`}>
                                                                        {idx + 1}
                                                                    </div>
                                                                    <span className="font-medium text-gray-900">{am.am_name}</span>
                                                                </div>
                                                            </td>
                                                            <td className="p-4 text-right border-r border-gray-300">
                                                                <span className="font-semibold text-gray-900">{am.formatted_revenue}</span>
                                                            </td>
                                                            <td className="p-4 text-right">
                                                                <span className={`font-bold text-sm ${
                                                                    am.achievement >= 100 ? 'text-green-600' : 'text-gray-700'
                                                                }`}>
                                                                    {am.formatted_achievement}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </React.Fragment>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                /* Chart View */
                                <div className="overflow-x-auto">
                                    <div style={{ width: `${Math.max(filteredAmRevenueRanking.length * 100, 800)}px`, minHeight: '400px' }}>
                                        <ResponsiveContainer width="100%" height={400}>
                                            <BarChart data={filteredAmRevenueRanking} barGap={4} barCategoryGap={10}>
                                                <XAxis 
                                                    dataKey="am_name" 
                                                    tick={{ fontSize: 12 }}
                                                    angle={-45}
                                                    textAnchor="end"
                                                    height={100}
                                                    interval={0}
                                                />
                                                <YAxis 
                                                    tick={{ fontSize: 12 }}
                                                    tickFormatter={(value) => {
                                                        if (value >= 1000000000000) {
                                                            return `${(value / 1000000000000).toFixed(0)}T`;
                                                        }
                                                        return `${(value / 1000000000).toFixed(0)}M`;
                                                    }}
                                                />
                                                <Tooltip 
                                                    content={({ active, payload, label }) => {
                                                        if (active && payload && payload.length) {
                                                            return (
                                                                <div
                                                                    style={{
                                                                        backgroundColor: '#ffffff',
                                                                        border: '1px solid #e5e7eb',
                                                                        borderRadius: '8px',
                                                                        boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                                                                        padding: '12px',
                                                                        color: '#374151'
                                                                    }}
                                                                >
                                                                    <p style={{ fontWeight: 600, marginBottom: '8px', color: '#374151' }}>
                                                                        AM: {label}
                                                                    </p>
                                                                    {payload.map((entry: any, index: number) => {
                                                                        const formatValue = (val: number) => {
                                                                            if (val >= 1000000000000) {
                                                                                return `Rp ${(val / 1000000000000).toFixed(2)}T`;
                                                                            } else {
                                                                                return `Rp ${(val / 1000000000).toFixed(2)}M`;
                                                                            }
                                                                        };
                                                                        
                                                                        return (
                                                                            <p key={index} style={{ margin: '4px 0', color: '#374151' }}>
                                                                                <span style={{ 
                                                                                    display: 'inline-block',
                                                                                    width: '12px',
                                                                                    height: '12px',
                                                                                    backgroundColor: entry.color,
                                                                                    marginRight: '8px',
                                                                                    borderRadius: '2px'
                                                                                }}></span>
                                                                                {entry.dataKey === 't_revenue' ? 'Target Revenue' : 'Actual Revenue'}: {formatValue(entry.value)}
                                                                            </p>
                                                                        );
                                                                    })}
                                                                </div>
                                                            );
                                                        }
                                                        return null;
                                                    }}
                                                />
                                                <Bar 
                                                    dataKey="t_revenue" 
                                                    fill="#dc2626" 
                                                    radius={[4, 4, 0, 0]} 
                                                    onClick={handleBarClick}
                                                    cursor="pointer"
                                                    name="Target Revenue"
                                                />
                                                <Bar 
                                                    dataKey="r_revenue" 
                                                    fill="#16a34a" 
                                                    radius={[4, 4, 0, 0]} 
                                                    onClick={handleBarClick}
                                                    cursor="pointer"
                                                    name="Actual Revenue"
                                                />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                        </Card>
                    </div>

                    {/* Right Column - Region Distribution and Best Performance */}
                    <div ref={rightColumnRef} className="lg:col-span-3 flex flex-col gap-6">
                        {/* Region Distribution Chart */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <MapPin className="h-5 w-5 text-red-600" />
                                    Region Distribution
                                </CardTitle>
                                <CardDescription>Distribusi AM per Region</CardDescription>
                            </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        data={regionDistribution}
                                        cx="50%"
                                        cy="50%"
                                        labelLine={false}
                                        label={({ cx, cy, midAngle, innerRadius, outerRadius, percent }: any) => {
                                            if (percent < 0.05) return null;
                                            
                                            const RADIAN = Math.PI / 180;
                                            const radius = innerRadius + (outerRadius - innerRadius) * 0.5;
                                            const x = cx + radius * Math.cos(-midAngle * RADIAN);
                                            const y = cy + radius * Math.sin(-midAngle * RADIAN);

                                            return (
                                                <text 
                                                    x={x} 
                                                    y={y} 
                                                    fill="white" 
                                                    textAnchor={x > cx ? 'start' : 'end'} 
                                                    dominantBaseline="central"
                                                    fontSize={12}
                                                    fontWeight="bold"
                                                >
                                                    {`${(percent * 100).toFixed(0)}%`}
                                                </text>
                                            );
                                        }}
                                        outerRadius={100}
                                        fill="#8884d8"
                                        dataKey="am_count"
                                        className="cursor-pointer"
                                        onClick={handlePieClick}
                                    >
                                        {regionDistribution.map((entry, index) => (
                                            <Cell 
                                                key={`cell-${index}`} 
                                                fill={COLORS[index % COLORS.length]}
                                                className="hover:opacity-80 transition-opacity"
                                            />
                                        ))}
                                    </Pie>
                                    <Tooltip 
                                        formatter={(value: any, name: any, props: any) => [
                                            `${value} Account Manager`,
                                            props.payload.region_code
                                        ]}
                                        labelFormatter={() => ''}
                                        contentStyle={{
                                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                            border: '1px solid #e5e7eb',
                                            borderRadius: '8px',
                                            boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)'
                                        }}
                                    />
                                    <Legend 
                                        verticalAlign="bottom"
                                        height={36}
                                        formatter={(value, entry: any) => entry.payload.region_code}
                                        wrapperStyle={{ fontSize: '12px' }}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                        </CardContent>
                        </Card>

                        {/* Best Performance Card - Hanya muncul saat tab Regional Performance aktif */}
                        {activeRevenueTab === 'regional' && bestPerformance && bestPerformance.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Target className="h-5 w-5 text-red-600" />
                                        Best Performance
                                    </CardTitle>
                                    <CardDescription>Top 3 Account Managers</CardDescription>
                                </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {bestPerformance.map((am, index) => {
                                        const medals = ['🥇', '🥈', '🥉'];
                                        const borderColors = ['border-yellow-400', 'border-gray-400', 'border-orange-400'];
                                        const bgGradients = [
                                            'from-yellow-50 to-orange-50',
                                            'from-gray-50 to-gray-100',
                                            'from-orange-50 to-amber-50'
                                        ];
                                        
                                        return (
                                            <div key={am.nik} className={`p-4 bg-gradient-to-r ${bgGradients[index]} border-2 ${borderColors[index]} rounded-xl`}>
                                                {/* Header dengan Rank dan Nama */}
                                                <div className="flex items-center gap-3 mb-3">
                                                    <div className="flex-shrink-0">
                                                        <div className="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md">
                                                            <span className="text-xl">{medals[index]}</span>
                                                        </div>
                                                    </div>
                                                    <div className="flex-1 min-w-0">
                                                        <h3 className="font-bold text-base text-gray-900 truncate">{am.am_name}</h3>
                                                        <p className="text-xs text-gray-600">Region: {am.region_code}</p>
                                                    </div>
                                                </div>
                                                
                                                {/* Metrics dalam satu baris */}
                                                <div className="grid grid-cols-3 gap-2">
                                                    <div className="bg-white/70 backdrop-blur-sm rounded-lg p-2 text-center">
                                                        <p className="text-xs text-gray-600 mb-1">Revenue</p>
                                                        <p className="text-sm font-bold text-gray-900">{am.formatted_revenue}</p>
                                                    </div>
                                                    <div className="bg-white/70 backdrop-blur-sm rounded-lg p-2 text-center">
                                                        <p className="text-xs text-gray-600 mb-1">Growth</p>
                                                        <p className={`text-sm font-bold ${am.growth >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                            {am.growth >= 0 ? '↗' : '↘'} {am.formatted_growth}
                                                        </p>
                                                    </div>
                                                    <div className="bg-white/70 backdrop-blur-sm rounded-lg p-2 text-center">
                                                        <p className="text-xs text-gray-600 mb-1">CC</p>
                                                        <p className="text-sm font-bold text-gray-900">{am.company_count}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </CardContent>
                        </Card>
                        )}
                    </div>
                </div>

                {/* List Account Manager Table - Fungsi ini untuk menampilkan daftar semua Account Manager */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Users className="h-5 w-5 text-red-600" />
                            List Account Manager
                            {selectedRegion !== 'ALL' && (
                                <span className="text-sm font-normal text-gray-600 dark:text-gray-400">
                                    - Region: {selectedRegion}
                                </span>
                            )}
                        </CardTitle>
                        <CardDescription>
                            Daftar Account Manager {selectedRegion === 'ALL' ? 'lengkap' : `di region ${selectedRegion}`} dengan detail informasi
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200">
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">No</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Account Manager</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">NIK</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Posisi</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">No GSM</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Lokasi AM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {paginatedAccountManagerList.length > 0 ? (
                                        paginatedAccountManagerList.map((am, index) => (
                                            <tr key={am.nik} className="border-b border-gray-100 hover:bg-gray-50">
                                                <td className="py-3 px-4">
                                                    <div className="text-gray-700">{startIndex + index + 1}</div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="font-medium text-gray-900">{am.nama}</div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="text-gray-700 font-mono text-sm">{am.nik}</div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="text-gray-700">{am.posisi}</div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="text-gray-700 font-mono text-sm">{am.no_gsm}</div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <MapPin className="h-4 w-4 text-gray-500" />
                                                        <span className="text-gray-700">{am.lokasi_am}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={6} className="py-8 px-4 text-center text-gray-500">
                                                Tidak ada Account Manager di region {selectedRegion}
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                        {/* Pagination Controls */}
                        {totalPages > 1 && (
                            <div className="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                                <div className="text-sm text-gray-600">
                                    Showing {startIndex + 1} to {Math.min(endIndex, filteredAccountManagerList.length)} of {filteredAccountManagerList.length} Account Managers
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                                        disabled={currentPage === 1}
                                        className="h-8"
                                    >
                                        Previous
                                    </Button>
                                    <div className="flex items-center gap-1">
                                        {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
                                            <Button
                                                key={page}
                                                variant={currentPage === page ? 'default' : 'outline'}
                                                size="sm"
                                                onClick={() => setCurrentPage(page)}
                                                className={`h-8 w-8 ${currentPage === page ? 'bg-red-600 hover:bg-red-700' : ''}`}
                                            >
                                                {page}
                                            </Button>
                                        ))}
                                    </div>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))}
                                        disabled={currentPage === totalPages}
                                        className="h-8"
                                    >
                                        Next
                                    </Button>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* AM Revenue Detail Modal */}
                <AMRevenueDetailModal
                    isOpen={isModalOpen}
                    onClose={() => setIsModalOpen(false)}
                    amNik={selectedAMNik}
                    year={selectedYear}
                    quartal={baseQuartal}
                    isYearToDate={isYearToDate}
                />

                {/* Region NKI Modal */}
                {selectedRegionId && (
                    <RegionNkiModal
                        isOpen={isRegionNkiModalOpen}
                        onClose={() => setIsRegionNkiModalOpen(false)}
                        regionId={selectedRegionId}
                        regionName={selectedRegionName}
                        quarter={parseInt(baseQuartal.replace('Q', ''))}
                        year={selectedYear}
                    />
                )}
            </div>
        </AppSidebarLayout>
    );
}
