import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, Legend } from 'recharts';
import { Users, Target, Calendar, FileSpreadsheet, Download, Upload, MapPin } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import AMRevenueDetailModal from '@/components/modals/AMRevenueDetailModal';

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
    accountManagerList,
    currentYear,
    currentQuartal,
    currentRegion = 'ALL',
    currentYtd = false
}: PerformanceAMProps) {
    const [selectedYear, setSelectedYear] = useState(currentYear);
    const [selectedQuartal, setSelectedQuartal] = useState(currentQuartal);
    const [selectedRegion, setSelectedRegion] = useState<string>(currentRegion);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [selectedAMNik, setSelectedAMNik] = useState<string | null>(null);
    const [isYearToDate, setIsYearToDate] = useState(currentYtd);
    const [activeRevenueTab, setActiveRevenueTab] = useState<'chart' | 'regional'>('chart');

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

    // Fungsi untuk handle perubahan filter tahun
    const handleYearChange = (year: string) => {
        setSelectedYear(parseInt(year));
        router.get('/performance-am', { year: year, quartal: selectedQuartal, ytd: isYearToDate ? '1' : '0', region: selectedRegion }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    // Fungsi untuk handle perubahan filter quartal
    const handleQuartalChange = (quartal: string) => {
        setSelectedQuartal(quartal);
        router.get('/performance-am', { year: selectedYear, quartal: quartal, ytd: isYearToDate ? '1' : '0', region: selectedRegion }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    // Fungsi untuk handle perubahan YTD checkbox
    const handleYTDChange = (checked: boolean) => {
        setIsYearToDate(checked);
        router.get('/performance-am', { year: selectedYear, quartal: selectedQuartal, ytd: checked ? '1' : '0', region: selectedRegion }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    // Fungsi untuk handle perubahan filter region
    const handleRegionChange = (region: string) => {
        setSelectedRegion(region);
        router.get('/performance-am', { year: selectedYear, quartal: selectedQuartal, ytd: isYearToDate ? '1' : '0', region: region }, {
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

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Performance AM', href: '/performance-am' }
            ]}
        >   
            <Head title="Performance AM" />
            <div className="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-6">
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
                                                {availableQuartals.map(quartal => (
                                                    <SelectItem key={quartal} value={quartal}>
                                                        {quartal}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="flex items-center gap-2 mt-2">
                                        <input
                                            type="checkbox"
                                            id="ytd-checkbox"
                                            checked={isYearToDate}
                                            onChange={(e) => handleYTDChange(e.target.checked)}
                                            className="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 dark:focus:ring-red-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 cursor-pointer"
                                        />
                                        <label htmlFor="ytd-checkbox" className="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                            Year to Date
                                        </label>
                                    </div>
                                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {isYearToDate && selectedQuartal !== 'Q1' 
                                            ? `Q1 - ${selectedQuartal} (YTD)` 
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
                <div className="grid grid-cols-1 lg:grid-cols-10 gap-6 mb-8">
                    {/* Target Revenue AM Chart - Chart dengan lebar 70% (7 kolom dari 10) */}
                    <Card className="lg:col-span-7">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        <Target className="h-5 w-5 text-red-600" />
                                        {activeRevenueTab === 'chart' ? 'Target Revenue AM' : 'Performance AM'}
                                    </CardTitle>
                                    <CardDescription>
                                        {activeRevenueTab === 'chart' 
                                            ? 'Total target revenue per Account Manager'
                                            : 'Regional performance with top Account Managers'
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
                                    variant={activeRevenueTab === 'chart' ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => setActiveRevenueTab('chart')}
                                    className={activeRevenueTab === 'chart' ? 'bg-red-600 hover:bg-red-700' : ''}
                                >
                                    Chart View
                                </Button>
                                <Button
                                    variant={activeRevenueTab === 'regional' ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => setActiveRevenueTab('regional')}
                                    className={activeRevenueTab === 'regional' ? 'bg-red-600 hover:bg-red-700' : ''}
                                >
                                    Regional Performance
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {activeRevenueTab === 'chart' ? (
                                /* Chart View */
                                <div className="overflow-x-auto">
                                    <div style={{ width: `${Math.max(filteredAmRevenueRanking.length * 60, 800)}px`, minHeight: '300px' }}>
                                        <ResponsiveContainer width="100%" height={300}>
                                            <BarChart data={filteredAmRevenueRanking}>
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
                                                    formatter={(value: any, name: any, props: any) => [
                                                        props.payload.formatted_revenue,
                                                        'Target Revenue'
                                                    ]}
                                                />
                                                <Bar 
                                                    dataKey="t_revenue" 
                                                    fill="#dc2626" 
                                                    radius={[4, 4, 0, 0]} 
                                                    onClick={handleBarClick}
                                                    cursor="pointer"
                                                />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>
                            ) : (
                                /* Regional Performance Table */
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm border-collapse">
                                        <thead>
                                            <tr className="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-300">
                                                <th className="text-left p-4 font-bold text-gray-800 uppercase tracking-wide text-xs">Regional</th>
                                                <th className="text-left p-4 font-bold text-gray-800 uppercase tracking-wide text-xs">Top Account Manager</th>
                                                <th className="text-right p-4 font-bold text-gray-800 uppercase tracking-wide text-xs">Revenue</th>
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
                                                                <td className="p-4 align-top bg-gray-50/50" rowSpan={region.top_ams.length}>
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
                                                            <td className="p-4">
                                                                <div className="flex items-center gap-3">
                                                                    <div className={`flex-shrink-0 inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full shadow-sm ${
                                                                        idx === 0 ? 'bg-yellow-500' : idx === 1 ? 'bg-gray-400' : 'bg-orange-600'
                                                                    }`}>
                                                                        {idx + 1}
                                                                    </div>
                                                                    <span className="font-medium text-gray-900">{am.am_name}</span>
                                                                </div>
                                                            </td>
                                                            <td className="p-4 text-right">
                                                                <span className="font-semibold text-gray-900">{am.formatted_revenue}</span>
                                                            </td>
                                                            <td className="p-4 text-right">
                                                                <div className="inline-flex items-center gap-2">
                                                                    <div className={`h-2 w-16 rounded-full overflow-hidden bg-gray-200 ${
                                                                        am.achievement >= 100 ? '' : 'opacity-50'
                                                                    }`}>
                                                                        <div 
                                                                            className={`h-full transition-all duration-300 ${
                                                                                am.achievement >= 100 ? 'bg-green-500' : 'bg-gray-400'
                                                                            }`}
                                                                            style={{ width: `${Math.min(am.achievement, 100)}%` }}
                                                                        ></div>
                                                                    </div>
                                                                    <span className={`font-bold text-sm min-w-[60px] ${
                                                                        am.achievement >= 100 ? 'text-green-600' : 'text-gray-700'
                                                                    }`}>
                                                                        {am.formatted_achievement}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </React.Fragment>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Region Distribution Chart - Chart dengan lebar 30% (3 kolom dari 10) */}
                    <Card className="lg:col-span-3">
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
                                    {filteredAccountManagerList.length > 0 ? (
                                        filteredAccountManagerList.map((am, index) => (
                                            <tr key={am.nik} className="border-b border-gray-100 hover:bg-gray-50">
                                                <td className="py-3 px-4">
                                                    <div className="text-gray-700">{index + 1}</div>
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
                    </CardContent>
                </Card>

                {/* AM Revenue Detail Modal */}
                <AMRevenueDetailModal
                    isOpen={isModalOpen}
                    onClose={() => setIsModalOpen(false)}
                    amNik={selectedAMNik}
                    year={selectedYear}
                    quartal={selectedQuartal}
                    isYearToDate={isYearToDate}
                />
            </div>
        </AppSidebarLayout>
    );
}
