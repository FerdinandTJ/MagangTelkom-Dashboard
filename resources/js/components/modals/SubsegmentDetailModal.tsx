import React, { useState, useEffect, useMemo } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, Building2, TrendingUp, Users, Calendar } from 'lucide-react';
import { CompanyData } from '@/types/dashboard';
import axios from '@/lib/axios';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';

interface RegionalData {
    region_id: number;
    region_code: string;
    region_name: string;
    total_companies: number;
    total_revenue: number;
    formatted_revenue: string;
    percentage: number;
}

interface SubsegmentDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    subsegment: string | null;
    year?: number;
    month?: number;
    onCompanyClick?: (company: CompanyData) => void;
}

const SubsegmentDetailModal: React.FC<SubsegmentDetailModalProps> = ({
    isOpen,
    onClose,
    subsegment,
    year = new Date().getFullYear(),
    month,
    onCompanyClick
}) => {
    const [companies, setCompanies] = useState<CompanyData[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [summary, setSummary] = useState<any>(null);
    const [regionalBreakdown, setRegionalBreakdown] = useState<RegionalData[]>([]);

    useEffect(() => {
        if (isOpen && subsegment) {
            fetchCompanyDetails();
        }
    }, [isOpen, subsegment, year, month]);

    const fetchCompanyDetails = async () => {
        if (!subsegment) return;
        
        setLoading(true);
        setError(null);
        
        try {
            const params: any = {
                subsegment: subsegment,
                year: year
            };
            
            if (month) {
                params.month = month;
            }
            
            const response = await axios.get(`/api/dashboard/subsegment-details`, {
                params
            });
            
            if (response.data.success) {
                const data = response.data.data;
                setCompanies(data.companies);
                setSummary(data.summary);
                
                // Calculate percentages for regional breakdown
                const totalRevenue = data.summary.total_revenue || 0;
                const regionalData = (data.regional_breakdown || []).map((region: RegionalData) => ({
                    ...region,
                    percentage: totalRevenue > 0 ? (region.total_revenue / totalRevenue) * 100 : 0
                }));
                setRegionalBreakdown(regionalData);
            } else {
                setError('Failed to fetch company details');
            }
        } catch (err) {
            setError('Error loading company data');
        } finally {
            setLoading(false);
        }
    };

    const handleCompanyClick = (company: CompanyData) => {
        if (onCompanyClick) {
            onCompanyClick(company);
        }
    };

    // Check if dark mode is enabled
    const isDarkMode = document.documentElement.classList.contains('dark');

    // Regional chart colors
    const REGIONAL_COLORS = [
        '#ef4444', // red-500
        '#f97316', // orange-500
        '#f59e0b', // amber-500
        '#eab308', // yellow-500
        '#84cc16', // lime-500
        '#22c55e', // green-500
    ];

    // Prepare data for pie chart
    const regionalChartData = useMemo(() => {
        const data = regionalBreakdown.map((region, index) => ({
            name: region.region_code,
            value: region.total_revenue,
            fullName: region.region_name,
            companies: region.total_companies,
            formatted: region.formatted_revenue,
            percentage: region.percentage,
            color: REGIONAL_COLORS[index % REGIONAL_COLORS.length]
        }));
        console.log('Regional chart data computed:', data);
        return data;
    }, [regionalBreakdown]);

    // Status badge removed - status column intentionally not displayed in the company list

    if (!subsegment) return null;

    const periodText = month 
        ? `${new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(new Date(year, month - 1))} ${year}`
        : `${year}`;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <Building2 className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">{subsegment} - Company Details</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Daftar perusahaan dan performance revenue untuk subsegment {subsegment} periode {periodText}
                    </DialogDescription>
                </DialogHeader>

                {/* Summary Cards */}
                {summary ? (
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div className="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-950/30 dark:to-pink-950/30 p-4 rounded-lg border border-red-100 dark:border-red-900">
                            <div className="flex items-center gap-2 mb-2">
                                <TrendingUp className="h-5 w-5 text-red-600 dark:text-red-400" />
                                <span className="text-sm font-medium text-red-700 dark:text-red-300">Total Revenue</span>
                            </div>
                            <p className="text-xl font-bold text-red-900 dark:text-red-100">{summary.formatted_total_revenue || 'Rp 0.00M'}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 p-4 rounded-lg border border-blue-100 dark:border-blue-900">
                            <div className="flex items-center gap-2 mb-2">
                                <Users className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <span className="text-sm font-medium text-blue-700 dark:text-blue-300">Companies</span>
                            </div>
                            <p className="text-xl font-bold text-blue-900 dark:text-blue-100">{summary.total_companies || 0}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 p-4 rounded-lg border border-green-100 dark:border-green-900">
                            <div className="flex items-center gap-2 mb-2">
                                <TrendingUp className="h-5 w-5 text-green-600 dark:text-green-400" />
                                <span className="text-sm font-medium text-green-700 dark:text-green-300">Avg Revenue</span>
                            </div>
                            <p className="text-xl font-bold text-green-900 dark:text-green-100">{summary.formatted_avg_revenue || 'Rp 0.00M'}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-950/30 dark:to-violet-950/30 p-4 rounded-lg border border-purple-100 dark:border-purple-900">
                            <div className="flex items-center gap-2 mb-2">
                                <Calendar className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                <span className="text-sm font-medium text-purple-700 dark:text-purple-300">Period</span>
                            </div>
                            <p className="text-xl font-bold text-purple-900 dark:text-purple-100">{periodText}</p>
                        </div>
                    </div>
                ) : (
                    <div className="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <p className="text-gray-600 dark:text-gray-400">Loading summary data...</p>
                    </div>
                )}

                {/* Main Content: Regional Chart and Company Table Side by Side */}
                <div className="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
                    {/* Left Side: Regional Distribution Pie Chart */}
                    <div className="lg:col-span-2 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm p-6">
                        <div className="flex items-center gap-2 mb-4">
                            <svg className="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <h3 className="font-semibold text-gray-900 dark:text-gray-100">Regional Distribution</h3>
                        </div>
                        
                        {regionalBreakdown.length > 0 && regionalChartData.length > 0 ? (
                            /* Pie Chart */
                            <ResponsiveContainer width="100%" height={350}>
                                <PieChart>
                                    <Pie
                                        data={regionalChartData}
                                        cx="50%"
                                        cy="50%"
                                        labelLine={false}
                                        label={(entry: any) => `${entry.name}\n${entry.percentage.toFixed(1)}%`}
                                        outerRadius={110}
                                        fill="#8884d8"
                                        dataKey="value"
                                    >
                                        {regionalChartData.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={entry.color} />
                                        ))}
                                    </Pie>
                                    <Tooltip
                                        formatter={(value: number, name: string, props: any) => [
                                            props.payload.formatted,
                                            'Revenue'
                                        ]}
                                        labelFormatter={(label, payload) => {
                                            if (payload && payload.length > 0) {
                                                const data = payload[0].payload;
                                                return data.fullName;
                                            }
                                            return label;
                                        }}
                                        contentStyle={{
                                            backgroundColor: isDarkMode ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                            border: isDarkMode ? '1px solid #374151' : '1px solid #e5e7eb',
                                            borderRadius: '8px',
                                            color: isDarkMode ? '#e5e7eb' : '#111827'
                                        }}
                                    />
                                    <Legend 
                                        verticalAlign="bottom" 
                                        height={36}
                                        formatter={(value, entry: any) => {
                                            const data = entry.payload;
                                            return `${data.fullName} (${data.companies} companies)`;
                                        }}
                                        wrapperStyle={{
                                            fontSize: '12px',
                                            color: isDarkMode ? '#e5e7eb' : '#111827'
                                        }}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="flex items-center justify-center h-[350px]">
                                <p className="text-gray-500 dark:text-gray-400">No regional data available</p>
                            </div>
                        )}
                    </div>

                    {/* Right Side: Company Table */}
                    <div className="lg:col-span-3">

                        {loading && (
                            <div className="flex items-center justify-center py-8">
                                <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                                <span className="ml-2 text-gray-600 dark:text-gray-400">Loading company details...</span>
                            </div>
                        )}

                        {error && (
                            <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4 mb-6">
                                <p className="text-red-700 dark:text-red-300">{error}</p>
                                <Button 
                                    onClick={fetchCompanyDetails}
                                    variant="outline" 
                                    size="sm" 
                                    className="mt-2"
                                >
                                    Retry
                                </Button>
                            </div>
                        )}

                        {!loading && !error && companies.length > 0 && (
                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div className="overflow-hidden rounded-lg">
                            <div className="max-h-[450px] overflow-y-auto">
                                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead className="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Company
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                NIP-NAS
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Region
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Revenue
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white dark:bg-gray-950 divide-y divide-gray-200 dark:divide-gray-800">
                                        {companies.map((company) => (
                                            <tr 
                                                key={company.id}
                                                className="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors"
                                            >
                                                <td className="px-4 py-3">
                                                    <div>
                                                        <p className="font-medium text-gray-900 dark:text-white text-sm leading-tight">{company.nama_perusahaan}</p>
                                                        <p className="text-xs text-gray-500 dark:text-gray-400">{company.subsegment}</p>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="text-sm font-mono text-gray-600 dark:text-gray-400">
                                                        {company.nip_nas || 'N/A'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex flex-wrap gap-1">
                                                        {company.regions && company.regions.length > 0 ? (
                                                            company.regions.map((region, idx) => (
                                                                <span 
                                                                    key={idx}
                                                                    title={`${region.region_name}${region.witel_name ? ` - ${region.witel_name}` : ''}`}
                                                                    className={`inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium ${
                                                                        region.is_primary 
                                                                            ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800' 
                                                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700'
                                                                    }`}
                                                                >
                                                                    {region.is_primary && <span className="text-yellow-500">★</span>}
                                                                    {region.region_code}
                                                                </span>
                                                            ))
                                                        ) : (
                                                            <span className="text-xs text-gray-400 dark:text-gray-500">-</span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div>
                                                        <p className="font-semibold text-gray-900 dark:text-white text-sm">
                                                            {company.formatted_total_revenue}
                                                        </p>
                                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                                            {periodText}
                                                        </p>
                                                    </div>
                                                </td>
                                                {/* Status column removed */}
                                                <td className="px-4 py-3">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleCompanyClick(company)}
                                                        className="text-red-600 dark:text-red-400 border-red-200 dark:border-red-900 hover:bg-red-50 dark:hover:bg-red-950/30 text-xs px-3 py-1"
                                                    >
                                                        View Details
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                            </div>
                        )}

                        {!loading && !error && companies.length === 0 && (
                            <div className="text-center py-8">
                                <Building2 className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                <p className="text-gray-500 dark:text-gray-400">No companies found for {subsegment} in {periodText}</p>
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200 dark:border-gray-800">
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

export default SubsegmentDetailModal;