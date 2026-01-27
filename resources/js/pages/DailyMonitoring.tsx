import React, { useRef, useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Upload, RefreshCw } from 'lucide-react';
import { dailyMonitoring } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { PageProps } from '@inertiajs/core';
import UpdateHarianModal from '@/components/modals/UpdateHarianModal';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Daily Monitoring',
        href: dailyMonitoring().url,
    },
];

interface Metric {
    label: string;
    value: string;
    type: string;
}

interface Achievement {
    percentage: number;
    label: string;
    formattedValue: string;
    type: string;
}

interface TableRow {
    idlop: string;
    am: string;
    treg: string;
    namaCC: string;
    project: string;
    scaling: string;
    progress: string;
}

interface DailyMonitoringProps extends PageProps {
    metricsCM: Metric[];
    metricsMonthly?: Metric[];
    metricsYTD: Metric[];
    achievementCM: Achievement;
    achievementMonthly?: Achievement;
    achievementYTD: Achievement;
    tableData: TableRow[];
    currentMonth: number;
    currentYear: number;
    currentDate: number;
    availableYears: number[];
    availableMonths: number[];
    availableDates: number[];
}

// Gauge Chart Component
const GaugeChart: React.FC<{ percentage: number; color: string }> = ({ percentage, color }) => {
    // Display actual percentage (can exceed 100%)
    const displayPercentage = Math.max(percentage, 0);
    
    // Limit visual rotation to 0-100 for gauge/needle
    const visualPercentage = Math.min(Math.max(percentage, 0), 100);
    
    // Calculate the rotation angle (180 degrees for semicircle, capped at 100%)
    const rotation = (visualPercentage / 100) * 180;
    
    return (
        <div className="relative w-full" style={{ paddingBottom: '60%' }}>
            <svg 
                viewBox="0 0 200 120" 
                className="absolute inset-0 w-full h-full"
                preserveAspectRatio="xMidYMid meet"
            >
                {/* Background arc */}
                <path
                    d="M 20 100 A 80 80 0 0 1 180 100"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="20"
                    className="text-gray-200 dark:text-gray-700"
                    strokeLinecap="round"
                />
                
                {/* Progress arc */}
                <path
                    d="M 20 100 A 80 80 0 0 1 180 100"
                    fill="none"
                    stroke={color}
                    strokeWidth="20"
                    strokeLinecap="round"
                    strokeDasharray={`${(rotation / 180) * 251.2} 251.2`}
                    className="transition-all duration-1000 ease-out"
                    style={{
                        filter: 'drop-shadow(0 0 8px currentColor)',
                    }}
                />
                
                {/* Center dot */}
                <circle cx="100" cy="100" r="5" fill={color} />
                
                {/* Needle */}
                <line
                    x1="100"
                    y1="100"
                    x2="100"
                    y2="30"
                    stroke={color}
                    strokeWidth="3"
                    strokeLinecap="round"
                    transform={`rotate(${rotation - 90} 100 100)`}
                    style={{
                        transition: 'transform 1s ease-out',
                    }}
                />
                
                {/* Percentage text - shows actual value even if >100% */}
                <text
                    x="100"
                    y="85"
                    textAnchor="middle"
                    fontSize="32"
                    fontWeight="bold"
                    fill="currentColor"
                >
                    {displayPercentage.toFixed(0)}%
                </text>
            </svg>
        </div>
    );
};

export default function DailyMonitoring() {
    const props = usePage<DailyMonitoringProps>().props;
    const { auth, metricsCM, metricsYTD, achievementCM, achievementYTD, tableData, currentMonth, currentYear, currentDate, availableYears, availableMonths, availableDates } = props;
    
    // Safe access with fallbacks for monthly data
    const metricsMonthly = props.metricsMonthly || metricsCM;
    const achievementMonthly = props.achievementMonthly || achievementCM;
    
    const isAdmin = auth.user.role === 'admin';
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [isUpdateHarianModalOpen, setIsUpdateHarianModalOpen] = useState(false);
    const [cmTab, setCmTab] = useState<'daily' | 'monthly'>('daily');

    // Format number as Indonesian Rupiah currency
    const formatCurrency = (value: string | number): string => {
        let numValue: number;
        if (typeof value === 'string') {
            // Remove all dots (thousand separators) and replace comma with dot for decimal
            const cleanValue = value.replace(/\./g, '').replace(',', '.');
            numValue = parseFloat(cleanValue);
        } else {
            numValue = value;
        }
        if (isNaN(numValue)) return 'Rp.0';
        return `Rp.${numValue.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
    };

    const handleUploadBulanan = () => {
        fileInputRef.current?.click();
    };

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            const formData = new FormData();
            formData.append('file', file);

            router.post('/daily-monitoring/upload-bulanan', formData, {
                onSuccess: () => {
                    alert('Data bulanan berhasil diupload!');
                    // Reset input
                    if (fileInputRef.current) {
                        fileInputRef.current.value = '';
                    }
                },
                onError: (errors) => {
                    console.error('Upload error:', errors);
                    alert('Gagal upload data: ' + (errors.file || 'Unknown error'));
                },
            });
        }
    };

    const handleFilterChange = (type: 'date' | 'month' | 'year', value: number) => {
        const params: Record<string, number> = {
            date: currentDate,
            month: currentMonth,
            year: currentYear,
        };
        
        params[type] = value;
        
        router.get(dailyMonitoring().url, params, {
            preserveState: true,
            preserveScroll: true,
        });
    };


    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Daily Monitoring" />

            <div className="min-h-screen max-h-screen overflow-hidden bg-gradient-to-br from-red-50/70 via-white to-pink-50/70 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900 p-2 sm:p-3">
                <div className="h-full flex flex-col gap-2 max-h-screen overflow-hidden">
                    {/* Header with Filters and Admin Buttons */}
                    <div className="flex-shrink-0">
                        <div className="flex items-center justify-between mb-1.5 sm:mb-2 gap-3">
                            <div className="min-w-0 flex-1">
                                <h1 className="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100 truncate">
                                    Daily Monitoring
                                </h1>
                                
                                {/* Filter Tanggal, Bulan, Tahun */}
                                <div className="flex gap-2 mt-1.5 items-center flex-wrap">
                                    <div className="flex items-center gap-1.5">
                                        <label className="text-[10px] sm:text-xs font-medium text-gray-700 dark:text-gray-300">
                                            Tahun:
                                        </label>
                                        <select
                                            value={currentYear}
                                            onChange={(e) => handleFilterChange('year', parseInt(e.target.value))}
                                            className="text-[10px] sm:text-xs px-2 py-1 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                                        >
                                            {availableYears.map((year) => (
                                                <option key={year} value={year}>
                                                    {year}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="flex items-center gap-1.5">
                                        <label className="text-[10px] sm:text-xs font-medium text-gray-700 dark:text-gray-300">
                                            Bulan:
                                        </label>
                                        <select
                                            value={currentMonth}
                                            onChange={(e) => handleFilterChange('month', parseInt(e.target.value))}
                                            className="text-[10px] sm:text-xs px-2 py-1 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                                        >
                                            {availableMonths.map((month) => {
                                                const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                return (
                                                    <option key={month} value={month}>
                                                        {monthNames[month - 1]}
                                                    </option>
                                                );
                                            })}
                                        </select>
                                    </div>

                                    <div className="flex items-center gap-1.5">
                                        <label className="text-[10px] sm:text-xs font-medium text-gray-700 dark:text-gray-300">
                                            Tanggal:
                                        </label>
                                        <select
                                            value={currentDate}
                                            onChange={(e) => handleFilterChange('date', parseInt(e.target.value))}
                                            className="text-[10px] sm:text-xs px-2 py-1 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                                        >
                                            {availableDates.map((day) => (
                                                <option key={day} value={day}>
                                                    {day}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            {isAdmin && (
                                <div className="flex gap-1.5 sm:gap-2 flex-shrink-0 ml-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        className="gap-1 sm:gap-1.5 text-[10px] sm:text-xs h-7 sm:h-8 px-2 sm:px-3 whitespace-nowrap"
                                        onClick={() => setIsUpdateHarianModalOpen(true)}
                                    >
                                        <RefreshCw className="h-3 sm:h-3.5 w-3 sm:w-3.5" />
                                        <span className="hidden sm:inline">Update Harian</span>
                                        <span className="sm:hidden">Update</span>
                                    </Button>
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept=".xlsx,.xls"
                                        className="hidden"
                                        onChange={handleFileChange}
                                    />
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        className="gap-1 sm:gap-1.5 text-[10px] sm:text-xs h-7 sm:h-8 px-2 sm:px-3 whitespace-nowrap"
                                        onClick={handleUploadBulanan}
                                    >
                                        <Upload className="h-3 sm:h-3.5 w-3 sm:w-3.5" />
                                        <span className="hidden sm:inline">Upload Bulanan</span>
                                        <span className="sm:hidden">Upload</span>
                                    </Button>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Metrics Section - 3 Columns with Achievement on Right */}
                    <div className="grid grid-cols-1 lg:grid-cols-[1.5fr_1.5fr_2fr] gap-1.5 sm:gap-2 flex-shrink-0" style={{height: 'clamp(200px, 40vh, 400px)'}}>
                        {/* CM Card - Full */}
                        <Card className="bg-white dark:bg-gray-900 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col min-h-0 overflow-hidden">
                            <CardHeader className="flex-shrink-0 py-0.5 px-2 sm:px-3 pb-0">
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100">
                                        Current Month (CM)
                                    </CardTitle>
                                    {/* Tab Switcher */}
                                    <div className="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-0.5">
                                        <button
                                            onClick={() => setCmTab('daily')}
                                            className={`px-2 py-0.5 text-[9px] sm:text-[10px] font-medium rounded transition-colors ${
                                                cmTab === 'daily'
                                                    ? 'bg-white dark:bg-gray-700 text-red-600 dark:text-red-400 shadow-sm'
                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                                            }`}
                                        >
                                            Daily
                                        </button>
                                        <button
                                            onClick={() => setCmTab('monthly')}
                                            className={`px-2 py-0.5 text-[9px] sm:text-[10px] font-medium rounded transition-colors ${
                                                cmTab === 'monthly'
                                                    ? 'bg-white dark:bg-gray-700 text-red-600 dark:text-red-400 shadow-sm'
                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                                            }`}
                                        >
                                            Monthly
                                        </button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="flex-1 min-h-0 overflow-y-auto px-1.5 sm:px-2 pb-1.5 sm:pb-2 pt-0.5 space-y-1 sm:space-y-1.5">
                                {(cmTab === 'daily' ? metricsCM : metricsMonthly).map((metric, idx) => (
                                    <div key={idx} className="flex items-center justify-between p-1.5 sm:p-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <div className="flex-1 min-w-0 mr-1.5 sm:mr-2">
                                            <p className="text-[9px] sm:text-[10px] font-medium text-gray-600 dark:text-gray-400 truncate">
                                                {metric.label}
                                            </p>
                                            <p className="text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 truncate">
                                                {formatCurrency(metric.value)}
                                            </p>
                                        </div>
                                        <div className="flex-shrink-0">
                                            <div className="p-1 sm:p-1.5 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <div className="h-3 w-3 sm:h-4 sm:w-4 text-red-600 dark:text-red-400" />
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>

                        {/* YTD Card - Full */}
                        <Card className="bg-white dark:bg-gray-900 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col min-h-0 overflow-hidden">
                            <CardHeader className="flex-shrink-0 py-0.5 px-2 sm:px-3 pb-0">
                                <CardTitle className="text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100">
                                    Year-to-Date (YTD)
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex-1 min-h-0 overflow-y-auto px-1.5 sm:px-2 pb-1.5 sm:pb-2 pt-0.5 space-y-1 sm:space-y-1.5">
                                {metricsYTD.map((metric, idx) => (
                                    <div key={idx} className="flex items-center justify-between p-1.5 sm:p-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <div className="flex-1 min-w-0 mr-1.5 sm:mr-2">
                                            <p className="text-[9px] sm:text-[10px] font-medium text-gray-600 dark:text-gray-400 truncate">
                                                {metric.label}
                                            </p>
                                            <p className="text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 truncate">
                                                {formatCurrency(metric.value)}
                                            </p>
                                        </div>
                                        <div className="flex-shrink-0">
                                            <div className="p-1 sm:p-1.5 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <div className="h-3 w-3 sm:h-4 sm:w-4 text-red-600 dark:text-red-400" />
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>

                        {/* Achievement Column - Stacked */}
                        <div className="flex flex-col gap-1.5 sm:gap-2 min-h-0">
                            {/* Achievement CM */}
                            <Card className="bg-white dark:bg-gray-900 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col flex-1 min-h-0 overflow-hidden">
                                <CardHeader className="flex-shrink-0 py-0.5 px-2 pb-0 pl-3">
                                    <CardTitle className="text-[10px] sm:text-xs font-bold text-gray-900 dark:text-gray-100 text-left truncate">
                                        {cmTab === 'daily' ? achievementCM.label : achievementMonthly.label}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="flex-1 min-h-0 flex items-center justify-center px-1 sm:px-1.5 pb-1 sm:pb-1.5 pt-0">
                                    <div className="w-full" style={{maxWidth: 'min(180px, 100%)'}}>
                                        <GaugeChart 
                                            percentage={cmTab === 'daily' ? achievementCM.percentage : achievementMonthly.percentage} 
                                            color="#ef4444" 
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Achievement YTD */}
                            <Card className="bg-white dark:bg-gray-900 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col flex-1 min-h-0 overflow-hidden">
                                <CardHeader className="flex-shrink-0 py-0.5 px-2 pb-0 pl-3">
                                    <CardTitle className="text-[10px] sm:text-xs font-bold text-gray-900 dark:text-gray-100 text-left truncate">
                                        {achievementYTD.label}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="flex-1 min-h-0 flex items-center justify-center px-1 sm:px-1.5 pb-1 sm:pb-1.5 pt-0">
                                    <div className="w-full" style={{maxWidth: 'min(180px, 100%)'}}>
                                        <GaugeChart 
                                            percentage={achievementYTD.percentage} 
                                            color="#ef4444" 
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    {/* Data Table */}
                    <Card className="bg-white dark:bg-gray-900 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col flex-1 min-h-0 overflow-hidden">
                        <CardHeader className="flex-shrink-0 py-1.5 sm:py-2 px-2 sm:px-3">
                            <CardTitle className="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">
                                Data LOP Monitoring
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex-1 min-h-0 overflow-auto p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead className="sticky top-0 bg-gray-100 dark:bg-gray-100 z-10">
                                        <tr className="border-b border-gray-700">
                                            <th className="text-left py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs font-semibold text-black whitespace-nowrap border-r border-gray-700">ID LOP</th>
                                            <th className="text-left py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs font-semibold text-black whitespace-nowrap border-r border-gray-700">AM</th>
                                            <th className="text-left py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs font-semibold text-black whitespace-nowrap border-r border-gray-700">TREG</th>
                                            <th className="text-left py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs font-semibold text-black whitespace-nowrap border-r border-gray-700">NAMA CC</th>
                                            <th className="text-left py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs font-semibold text-black whitespace-nowrap border-r border-gray-700">PROJECT</th>
                                            <th className="text-right py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs font-semibold text-black whitespace-nowrap border-r border-gray-700">SCALING</th>
                                            <th className="text-right py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs font-semibold text-black whitespace-nowrap">PROGRESS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {tableData.length > 0 ? (
                                            tableData.map((row, idx) => (
                                                <tr key={idx} className="border-b border-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                    <td className="py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs text-gray-700 whitespace-nowrap border-r border-gray-200">{row.idlop || '-'}</td>
                                                    <td className="py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs font-medium text-gray-900 whitespace-nowrap border-r border-gray-200">{row.am}</td>
                                                    <td className="py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs text-gray-700 whitespace-nowrap border-r border-gray-200">{row.treg}</td>
                                                    <td className="py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs text-gray-700 border-r border-gray-200">{row.namaCC}</td>
                                                    <td className="py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs text-gray-700 border-r border-gray-200">{row.project}</td>
                                                    <td className="py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs text-right font-semibold text-gray-900 whitespace-nowrap border-r border-gray-200">{row.scaling}</td>
                                                    <td className="py-1.5 sm:py-2 px-2 sm:px-3 text-[10px] sm:text-xs text-right font-semibold text-gray-900 whitespace-nowrap">{row.progress}</td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan={7} className="text-center py-4 sm:py-6 text-gray-500 dark:text-gray-400 text-[10px] sm:text-xs">
                                                    Tidak ada data
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Update Harian Modal */}
            <UpdateHarianModal
                isOpen={isUpdateHarianModalOpen}
                onClose={() => setIsUpdateHarianModalOpen(false)}
                currentDate={currentDate}
                currentMonth={currentMonth}
                currentYear={currentYear}
            />
        </AppSidebarLayout>
    );
}
