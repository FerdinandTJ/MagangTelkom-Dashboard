import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import { Users, Target, Calendar, FileSpreadsheet, Download, Upload, MapPin } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';

interface PerformanceAMProps {
    amMetrics: {
        total_am: number;
        revenue_target: number;
        formatted_revenue_target: string;
        year: number;
        month_start: string | null;
        month_end: string | null;
        quartal: string;
    };
    availableYears: number[];
    availableQuartals: string[];
    amRevenueRanking: Array<{
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
    accountManagerList,
    currentYear,
    currentQuartal
}: PerformanceAMProps) {
    const [selectedYear, setSelectedYear] = useState(currentYear);
    const [selectedQuartal, setSelectedQuartal] = useState(currentQuartal);
    const [selectedRegion, setSelectedRegion] = useState<string>('ALL');

    // Get unique regions dari amRevenueRanking
    const availableRegions = ['ALL', ...Array.from(new Set(amRevenueRanking.map(am => am.region_code)))].filter(Boolean);

    // Filter amRevenueRanking berdasarkan region yang dipilih
    const filteredAmRevenueRanking = selectedRegion === 'ALL' 
        ? amRevenueRanking 
        : amRevenueRanking.filter(am => am.region_code === selectedRegion);

    // Fungsi untuk handle perubahan filter tahun
    const handleYearChange = (year: string) => {
        setSelectedYear(parseInt(year));
        router.get('/performance-am', { year: year, quartal: selectedQuartal }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    // Fungsi untuk handle perubahan filter quartal
    const handleQuartalChange = (quartal: string) => {
        setSelectedQuartal(quartal);
        router.get('/performance-am', { year: selectedYear, quartal: quartal }, {
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

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Performance AM', href: '/performance-am' }
            ]}
        >
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
                                    <p className="text-sm text-gray-500 dark:text-gray-400">Account Managers</p>
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
                                    <p className="text-sm text-gray-500 dark:text-gray-400">Target periode ini</p>
                                </div>
                                <div className="flex-shrink-0 ml-4">
                                    <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                        <Target className="h-6 w-6 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>

                    {/* Card 3: Year - Tahun dengan dropdown dan rentang bulan */}
                    <Card className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tahun</p>
                                    <Select value={selectedYear.toString()} onValueChange={handleYearChange}>
                                        <SelectTrigger className="w-full bg-white mt-2">
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
                                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-2">{formatMonthRange()}</p>
                                </div>
                                <div className="flex-shrink-0 ml-4">
                                    <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                        <Calendar className="h-6 w-6 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>

                    {/* Card 4: Quartal - Quartal dengan dropdown */}
                    <Card className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Quartal</p>
                                    <Select value={selectedQuartal} onValueChange={handleQuartalChange}>
                                        <SelectTrigger className="w-full bg-white mt-2">
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
                                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-2">Periode saat ini</p>
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
                                        Target Revenue AM
                                    </CardTitle>
                                    <CardDescription>
                                        Total target revenue per Account Manager
                                    </CardDescription>
                                </div>
                                {/* Filter Region untuk chart ini */}
                                <div className="flex items-center gap-2">
                                    <span className="text-sm text-gray-600">Region:</span>
                                    <Select value={selectedRegion} onValueChange={setSelectedRegion}>
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
                        </CardHeader>
                        <CardContent>
                            {/* Wrapper dengan horizontal scroll */}
                            <div className="overflow-x-auto">
                                <div style={{ width: `${Math.max(filteredAmRevenueRanking.length * 60, 800)}px`, minHeight: '300px' }}>
                                    <ResponsiveContainer width="100%" height={300}>
                                        <BarChart data={filteredAmRevenueRanking}>
                                            <CartesianGrid strokeDasharray="3 3" />
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
                                            <Bar dataKey="t_revenue" fill="#dc2626" radius={[4, 4, 0, 0]} />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
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
                                        labelLine={true}
                                        label={({ region_code, percentage, x, y, cx, cy, midAngle, innerRadius, outerRadius }: any) => {
                                            const RADIAN = Math.PI / 180;
                                            const radius = innerRadius + (outerRadius - innerRadius) * 0.5;
                                            const x_inner = cx + radius * Math.cos(-midAngle * RADIAN);
                                            const y_inner = cy + radius * Math.sin(-midAngle * RADIAN);

                                            return (
                                                <>
                                                    {/* Label di luar (region code) */}
                                                    <text 
                                                        x={x} 
                                                        y={y} 
                                                        fill="black" 
                                                        textAnchor={x > cx ? 'start' : 'end'} 
                                                        dominantBaseline="central"
                                                        className="text-sm font-medium"
                                                    >
                                                        {region_code}
                                                    </text>
                                                    {/* Label di dalam (percentage) */}
                                                    <text 
                                                        x={x_inner} 
                                                        y={y_inner} 
                                                        fill="white" 
                                                        textAnchor="middle" 
                                                        dominantBaseline="central"
                                                        className="text-xs font-bold"
                                                    >
                                                        {percentage.toFixed(1)}%
                                                    </text>
                                                </>
                                            );
                                        }}
                                        outerRadius={80}
                                        fill="#8884d8"
                                        dataKey="am_count"
                                    >
                                        {regionDistribution.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip 
                                        formatter={(value: any, name: any, props: any) => [
                                            `${value} Account Manager`,
                                            props.payload.region_code
                                        ]}
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
                        </CardTitle>
                        <CardDescription>Daftar lengkap Account Manager dengan detail informasi</CardDescription>
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
                                    {accountManagerList.map((am) => (
                                        <tr key={am.nik} className="border-b border-gray-100 hover:bg-gray-50">
                                            <td className="py-3 px-4">
                                                <div className="text-gray-700">{am.no}</div>
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
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
}
