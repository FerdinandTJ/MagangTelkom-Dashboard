import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, User, Target, Calendar, Building2, MapPin, Phone, Briefcase, ChevronDown, ChevronUp } from 'lucide-react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend, BarChart, Bar, XAxis, YAxis, CartesianGrid } from 'recharts';
import axios from '@/lib/axios';
import { formatCurrency } from '@/utils/currency';

interface CompanyDetail {
    nip_nas: string;
    nama_perusahaan: string;
    subsegment: string;
    nama_witels: string;
    region_code: string;
    t_revenue: number;
    formatted_revenue: string;
    pembagian: string;
    proporsi: number;
    t_sustain: number;
    t_scalling: number;
    t_ngtma: number;
    formatted_sustain: string;
    formatted_scalling: string;
    formatted_ngtma: string;
}

interface RegionDistribution {
    region_code: string;
    company_count: number;
    percentage: number;
    [key: string]: string | number; // Add index signature for Recharts compatibility
}

interface AMRevenueDetailData {
    am_name: string;
    am_nik: string;
    am_posisi: string;
    am_no_gsm: string;
    am_witel: string;
    am_region: string;
    total_target_revenue: number;
    formatted_total_revenue: string;
    total_companies: number;
    year: number;
    quartal: string;
    period_display: string;
    region_distribution: RegionDistribution[];
    companies: CompanyDetail[];
}

interface AMRevenueDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    amNik: string | null;
    year: number;
    quartal: string;
    isYearToDate?: boolean;
}

const COLORS = ['#8b5cf6', '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#ec4899', '#6366f1'];

const AMRevenueDetailModal: React.FC<AMRevenueDetailModalProps> = ({
    isOpen,
    onClose,
    amNik,
    year,
    quartal,
    isYearToDate = false
}) => {
    const [data, setData] = useState<AMRevenueDetailData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [isDarkMode, setIsDarkMode] = useState(false);
    const [expandedCompanies, setExpandedCompanies] = useState<Set<string>>(new Set());
    const [activeChartTab, setActiveChartTab] = useState<'witel' | 'breakdown'>('witel');

    useEffect(() => {
        const checkDarkMode = () => {
            setIsDarkMode(document.documentElement.classList.contains('dark'));
        };
        
        checkDarkMode();
        
        const observer = new MutationObserver(checkDarkMode);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (isOpen && amNik) {
            fetchAMDetails();
        }
    }, [isOpen, amNik, year, quartal, isYearToDate]);

    const fetchAMDetails = async () => {
        if (!amNik) return;
        
        setLoading(true);
        setError(null);
        
        try {
            const response = await axios.get(`/api/dashboard/am-revenue-details`, {
                params: {
                    am_nik: amNik,
                    year: year,
                    quartal: quartal,
                    ytd: isYearToDate ? '1' : '0'
                }
            });
            
            if (response.data.success) {
                setData(response.data.data);
            } else {
                setError('Failed to fetch AM details');
            }
        } catch (err) {
            setError('Error loading AM data');
            console.error('Error fetching AM details:', err);
        } finally {
            setLoading(false);
        }
    };

    const toggleCompany = (nipNas: string) => {
        const newExpanded = new Set(expandedCompanies);
        if (newExpanded.has(nipNas)) {
            newExpanded.delete(nipNas);
        } else {
            newExpanded.add(nipNas);
        }
        setExpandedCompanies(newExpanded);
    };

    if (!amNik) return null;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <User className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">
                            Target Revenue Details - {data?.am_name || 'Loading...'}
                        </span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Target revenue breakdown and company distribution analysis
                    </DialogDescription>
                </DialogHeader>

                {/* AM Info Card */}
                {!loading && !error && data && (
                    <div className="bg-white dark:bg-gray-900 rounded-2xl border-2 border-red-600 dark:border-red-500 shadow-sm p-6 mb-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <div className="p-1.5 bg-red-50 dark:bg-red-950 rounded">
                                        <User className="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span className="text-[15px] font-bold text-gray-700 dark:text-gray-300">NIK</span>
                                </div>
                                <p className="font-mono text-sm text-gray-900 dark:text-gray-100">{data.am_nik || 'N/A'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <div className="p-1.5 bg-red-50 dark:bg-red-950 rounded">
                                        <Briefcase className="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span className="text-[15px] font-bold text-gray-700 dark:text-gray-300">Posisi</span>
                                </div>
                                <p className="text-sm text-gray-900 dark:text-gray-100">{data.am_posisi || '-'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <div className="p-1.5 bg-red-50 dark:bg-red-950 rounded">
                                        <User className="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span className="text-[15px] font-bold text-gray-700 dark:text-gray-300">EAM</span>
                                </div>
                                <p className="text-sm text-gray-900 dark:text-gray-100">{data.am_name || '-'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <div className="p-1.5 bg-red-50 dark:bg-red-950 rounded">
                                        <Phone className="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span className="text-[15px] font-bold text-gray-700 dark:text-gray-300">No. GSM</span>
                                </div>
                                <p className="font-mono text-sm text-gray-900 dark:text-gray-100">{data.am_no_gsm || '-'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <div className="p-1.5 bg-red-50 dark:bg-red-950 rounded">
                                        <MapPin className="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span className="text-[15px] font-bold text-gray-700 dark:text-gray-300">Witel</span>
                                </div>
                                <p className="text-sm text-gray-900 dark:text-gray-100">{data.am_witel || '-'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <div className="p-1.5 bg-red-50 dark:bg-red-950 rounded">
                                        <svg className="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                    </div>
                                    <span className="text-[15px] font-bold text-gray-700 dark:text-gray-300">Region</span>
                                </div>
                                <p className="text-sm text-gray-900 dark:text-gray-100">{data.am_region || '-'}</p>
                            </div>
                        </div>
                    </div>
                )}

                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading AM details...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4 mb-6">
                        <p className="text-red-700 dark:text-red-300">{error}</p>
                        <Button 
                            onClick={fetchAMDetails}
                            variant="outline" 
                            size="sm" 
                            className="mt-2"
                        >
                            Retry
                        </Button>
                    </div>
                )}

                {!loading && !error && data && (
                    <>
                        {/* Summary Cards */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            {/* Card 1: Target Revenue */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Target Revenue AM</p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                {data.formatted_total_revenue}
                                            </p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">Total target periode ini</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <Target className="h-6 w-6 text-red-600 dark:text-red-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Card 2: Total Companies */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Companies Handled</p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                {data.total_companies}
                                            </p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">Perusahaan terdaftar</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <Building2 className="h-6 w-6 text-red-600 dark:text-red-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Card 3: Period */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Period</p>
                                            {isYearToDate && quartal !== 'Q1' ? (
                                                <div className="mb-1">
                                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
                                                        {year}
                                                    </p>
                                                    <p className="text-xl font-bold text-gray-900 dark:text-gray-100">
                                                        Q1 - {quartal} (YTD)
                                                    </p>
                                                </div>
                                            ) : (
                                                <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                    {data.period_display}
                                                </p>
                                            )}
                                            <p className="text-sm text-gray-500 dark:text-gray-400">Periode aktif</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <Calendar className="h-6 w-6 text-red-600 dark:text-red-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Content: Pie Chart and Table */}
                        <div className="grid grid-cols-1 lg:grid-cols-20 gap-6">
                            {/* Left: Charts - Region Distribution & Target Breakdown (45%) */}
                            <div className="lg:col-span-9 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                {/* Tab Navigation */}
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <MapPin className="h-5 w-5 text-red-600 dark:text-red-400" />
                                        {activeChartTab === 'witel' ? 'Company Distribution By Region' : 'Target Revenue Details'}
                                    </h3>
                                    <div className="flex gap-2">
                                        <button
                                            onClick={() => setActiveChartTab('witel')}
                                            className={`px-3 py-1.5 text-sm font-medium rounded-lg transition-colors ${
                                                activeChartTab === 'witel'
                                                    ? 'bg-red-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
                                            }`}
                                        >
                                            By Region
                                        </button>
                                        <button
                                            onClick={() => setActiveChartTab('breakdown')}
                                            className={`px-3 py-1.5 text-sm font-medium rounded-lg transition-colors ${
                                                activeChartTab === 'breakdown'
                                                    ? 'bg-red-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
                                            }`}
                                        >
                                            Breakdown
                                        </button>
                                    </div>
                                </div>
                                
                                {/* Chart Content */}
                                {activeChartTab === 'witel' ? (
                                    // Region Distribution Pie Chart
                                    data.region_distribution && data.region_distribution.length > 0 ? (
                                        <ResponsiveContainer width="100%" height={300}>
                                        <PieChart>
                                            <Pie
                                                data={data.region_distribution}
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
                                                outerRadius={120}
                                                fill="#8884d8"
                                                dataKey="company_count"
                                                className="cursor-pointer"
                                            >
                                                {data.region_distribution.map((entry: RegionDistribution, index: number) => (
                                                    <Cell 
                                                        key={`cell-${index}`} 
                                                        fill={COLORS[index % COLORS.length]}
                                                        className="hover:opacity-80 transition-opacity"
                                                    />
                                                ))}
                                            </Pie>
                                            <Tooltip 
                                                formatter={(value: number, name: string, props: any) => [
                                                    `${value} ${value === 1 ? 'Company' : 'Companies'}`,
                                                    props.payload.region_code
                                                ]}
                                                labelFormatter={() => ''}
                                                contentStyle={{
                                                    backgroundColor: isDarkMode ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                                    border: isDarkMode ? '1px solid #374151' : '1px solid #e5e7eb',
                                                    borderRadius: '8px',
                                                    boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                                                    color: isDarkMode ? '#e5e7eb' : '#374151'
                                                }}
                                                itemStyle={{
                                                    color: isDarkMode ? '#e5e7eb' : '#374151'
                                                }}
                                                labelStyle={{ 
                                                    color: isDarkMode ? '#e5e7eb' : '#374151'
                                                }}
                                            />
                                            <Legend 
                                                verticalAlign="bottom"
                                                height={36}
                                                formatter={(value, entry: any) => (
                                                    <span style={{ color: isDarkMode ? '#e5e7eb' : '#374151' }}>
                                                        {entry.payload.region_code}
                                                    </span>
                                                )}
                                                wrapperStyle={{ color: isDarkMode ? '#e5e7eb' : '#374151' }}
                                            />
                                        </PieChart>
                                    </ResponsiveContainer>
                                ) : (
                                    <div className="flex items-center justify-center h-[400px] text-gray-500 dark:text-gray-400">
                                        No distribution data available
                                    </div>
                                )
                            ) : (
                                // Target Breakdown Horizontal Bar Chart
                                data.companies && data.companies.length > 0 ? (
                                    <div style={{ width: '100%', height: `${Math.max(data.companies.length * 100 + 50, 370)}px`, overflowY: 'auto' }}>
                                        <ResponsiveContainer width="100%" height={Math.max(data.companies.length * 100, 320)}>
                                            <BarChart 
                                                data={data.companies} 
                                                layout="vertical"
                                                margin={{ top: 20, right: 30, left: 10, bottom: 5 }}
                                                barSize={20}
                                                barGap={2}
                                            >
                                                <XAxis 
                                                    type="number"
                                                    tick={{ fontSize: 11, fill: isDarkMode ? '#9ca3af' : '#6b7280' }}
                                                    tickFormatter={(value) => {
                                                        if (value >= 1000000000) {
                                                            return `${(value / 1000000000).toFixed(0)}M`;
                                                        }
                                                        return `${(value / 1000000).toFixed(0)}Jt`;
                                                    }}
                                                />
                                                <YAxis 
                                                    type="category"
                                                    dataKey="nama_perusahaan" 
                                                    tick={{ fontSize: 14, fill: isDarkMode ? '#9ca3af' : '#6b7280' }}
                                                    width={150}
                                                />
                                                <Tooltip 
                                                    formatter={(value: any) => formatCurrency(value, 2)}
                                                    contentStyle={{
                                                        backgroundColor: isDarkMode ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                                        border: isDarkMode ? '1px solid #374151' : '1px solid #e5e7eb',
                                                        borderRadius: '8px',
                                                        color: isDarkMode ? '#e5e7eb' : '#111827'
                                                    }}
                                                />
                                                <Legend 
                                                    verticalAlign="top" 
                                                    align="left" 
                                                    height={25} 
                                                    iconType="rect" 
                                                    layout="horizontal"
                                                    wrapperStyle={{ paddingLeft: '160px' }}
                                                />
                                                <Bar dataKey="t_sustain" fill="#3b82f6" name="Sustain" radius={[0, 4, 4, 0]} />
                                                <Bar dataKey="t_scalling" fill="#22c55e" name="Scaling" radius={[0, 4, 4, 0]} />
                                                <Bar dataKey="t_ngtma" fill="#a855f7" name="NGTMA" radius={[0, 4, 4, 0]} />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                ) : (
                                    <div className="flex items-center justify-center h-[320px] text-gray-500 dark:text-gray-400">
                                        No company data available
                                    </div>
                                )
                            )}
                            </div>

                            {/* Right: Table - Company List (55%) */}
                            <div className="lg:col-span-11 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <Building2 className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    Company Details
                                </h3>
                                
                                <div 
                                    className="space-y-2 overflow-y-auto"
                                    style={{ maxHeight: `${Math.max(data.companies.length * 100 + 50, 370)}px` }}
                                >
                                    {data.companies && data.companies.length > 0 ? (
                                        data.companies.map((company) => {
                                            const isExpanded = expandedCompanies.has(company.nip_nas);
                                            
                                            return (
                                                <div key={company.nip_nas} className="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                                    {/* Company Header - Clickable */}
                                                    <div 
                                                        className="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-850 p-3 cursor-pointer hover:from-gray-100 hover:to-gray-200 dark:hover:from-gray-750 dark:hover:to-gray-800 transition-all"
                                                        onClick={() => toggleCompany(company.nip_nas)}
                                                    >
                                                        <div className="flex items-center justify-between">
                                                            <div className="flex items-center gap-3 flex-1">
                                                                {/* Company Info */}
                                                                <div className="min-w-[200px]">
                                                                    <h4 className="font-bold text-gray-900 dark:text-gray-100 text-sm">{company.nama_perusahaan}</h4>
                                                                    <p className="text-xs text-gray-500 dark:text-gray-400 font-mono">{company.nip_nas}</p>
                                                                </div>

                                                                {/* Stats Grid */}
                                                                <div className="grid grid-cols-5 gap-3 flex-1">
                                                                    <div className="text-center">
                                                                        <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Witel</p>
                                                                        <p className="font-semibold text-gray-900 dark:text-gray-100 text-xs">{company.nama_witels}</p>
                                                                    </div>
                                                                    <div className="text-center">
                                                                        <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Target AM</p>
                                                                        <p className="font-bold text-gray-900 dark:text-gray-100 text-xs">{company.formatted_revenue}</p>
                                                                    </div>
                                                                    <div className="text-center">
                                                                        <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Subsegment</p>
                                                                        <p className="font-semibold text-gray-900 dark:text-gray-100 text-xs">{company.subsegment}</p>
                                                                    </div>
                                                                    <div className="text-center">
                                                                        <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Pembagian</p>
                                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                                                                            company.pembagian === 'SINGLE' 
                                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                                                : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                                                        }`}>
                                                                            {company.pembagian}
                                                                        </span>
                                                                    </div>
                                                                    <div className="text-center">
                                                                        <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Proporsi</p>
                                                                        <p className="font-bold text-gray-900 dark:text-gray-100 text-xs">{company.proporsi}%</p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {/* Chevron Icon */}
                                                            <div className="ml-4">
                                                                {isExpanded ? (
                                                                    <ChevronUp className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                                ) : (
                                                                    <ChevronDown className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {/* Expanded Content - Target Breakdown Table */}
                                                    {isExpanded && (
                                                        <div className="bg-white dark:bg-gray-950">
                                                            <table className="w-full">
                                                                <thead className="bg-gray-100 dark:bg-gray-800">
                                                                    <tr>
                                                                        <th className="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                            Category
                                                                        </th>
                                                                        <th className="px-4 py-2 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                            Target Amount
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                                    <tr className="hover:bg-blue-50 dark:hover:bg-blue-950/20 transition-colors">
                                                                        <td className="px-4 py-3">
                                                                            <div className="flex items-center gap-2">
                                                                                <div className="w-3 h-3 bg-blue-500 rounded"></div>
                                                                                <span className="font-medium text-gray-900 dark:text-gray-100">Sustain</span>
                                                                            </div>
                                                                        </td>
                                                                        <td className="px-4 py-3 text-right font-bold text-blue-900 dark:text-blue-100">
                                                                            {company.formatted_sustain}
                                                                        </td>
                                                                    </tr>
                                                                    <tr className="hover:bg-green-50 dark:hover:bg-green-950/20 transition-colors">
                                                                        <td className="px-4 py-3">
                                                                            <div className="flex items-center gap-2">
                                                                                <div className="w-3 h-3 bg-green-500 rounded"></div>
                                                                                <span className="font-medium text-gray-900 dark:text-gray-100">Scaling</span>
                                                                            </div>
                                                                        </td>
                                                                        <td className="px-4 py-3 text-right font-bold text-green-900 dark:text-green-100">
                                                                            {company.formatted_scalling}
                                                                        </td>
                                                                    </tr>
                                                                    <tr className="hover:bg-purple-50 dark:hover:bg-purple-950/20 transition-colors">
                                                                        <td className="px-4 py-3">
                                                                            <div className="flex items-center gap-2">
                                                                                <div className="w-3 h-3 bg-purple-500 rounded"></div>
                                                                                <span className="font-medium text-gray-900 dark:text-gray-100">NGTMA</span>
                                                                            </div>
                                                                        </td>
                                                                        <td className="px-4 py-3 text-right font-bold text-purple-900 dark:text-purple-100">
                                                                            {company.formatted_ngtma}
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })
                                    ) : (
                                        <div className="py-8 text-center text-gray-500 dark:text-gray-400">
                                            No companies assigned to this Account Manager
                                        </div>
                                    )}
                                </div>
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

export default AMRevenueDetailModal;
