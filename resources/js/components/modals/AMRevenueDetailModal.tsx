import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, User, Target, Calendar, Building2, MapPin, Phone, Briefcase } from 'lucide-react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';
import axios from '@/lib/axios';
import { formatCurrency } from '@/utils/currency';

interface CompanyDetail {
    nip_nas: string;
    nama_perusahaan: string;
    subsegment: string;
    nama_witels: string;
    t_revenue: number;
    formatted_revenue: string;
}

interface WitelDistribution {
    witel_name: string;
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
    witel_distribution: WitelDistribution[];
    companies: CompanyDetail[];
}

interface AMRevenueDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    amNik: string | null;
    year: number;
    quartal: string;
}

const COLORS = ['#8b5cf6', '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#ec4899', '#6366f1'];

const AMRevenueDetailModal: React.FC<AMRevenueDetailModalProps> = ({
    isOpen,
    onClose,
    amNik,
    year,
    quartal
}) => {
    const [data, setData] = useState<AMRevenueDetailData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [isDarkMode, setIsDarkMode] = useState(false);

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
    }, [isOpen, amNik, year, quartal]);

    const fetchAMDetails = async () => {
        if (!amNik) return;
        
        setLoading(true);
        setError(null);
        
        try {
            const response = await axios.get(`/api/dashboard/am-revenue-details`, {
                params: {
                    am_nik: amNik,
                    year: year,
                    quartal: quartal
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
                    <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 rounded-lg border border-blue-100 dark:border-blue-900 p-6 mb-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <div className="flex items-center gap-2 mb-1">
                                    <User className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    <span className="text-sm font-medium text-blue-700 dark:text-blue-300">NIK</span>
                                </div>
                                <p className="font-mono text-gray-900 dark:text-gray-100">{data.am_nik || 'N/A'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-1">
                                    <Briefcase className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    <span className="text-sm font-medium text-blue-700 dark:text-blue-300">Posisi</span>
                                </div>
                                <p className="font-semibold text-gray-900 dark:text-gray-100">{data.am_posisi || '-'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-1">
                                    <Phone className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    <span className="text-sm font-medium text-blue-700 dark:text-blue-300">No. GSM</span>
                                </div>
                                <p className="font-mono text-gray-900 dark:text-gray-100">{data.am_no_gsm || '-'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-1">
                                    <MapPin className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    <span className="text-sm font-medium text-blue-700 dark:text-blue-300">Witel</span>
                                </div>
                                <p className="text-gray-900 dark:text-gray-100">{data.am_witel || '-'}</p>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 mb-1">
                                    <svg className="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    <span className="text-sm font-medium text-blue-700 dark:text-blue-300">Region</span>
                                </div>
                                <p className="text-gray-900 dark:text-gray-100">{data.am_region || '-'}</p>
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
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Target Revenue</p>
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
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Companies</p>
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
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                {data.period_display}
                                            </p>
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
                            {/* Left: Pie Chart - Witel Distribution (35%) */}
                            <div className="lg:col-span-7 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <MapPin className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    Company Distribution by Witel
                                </h3>
                                
                                {data.witel_distribution && data.witel_distribution.length > 0 ? (
                                    <ResponsiveContainer width="100%" height={320}>
                                        <PieChart>
                                            <Pie
                                                data={data.witel_distribution}
                                                cx="50%"
                                                cy="50%"
                                                labelLine={false}
                                                label={(props: any) => `${props.witel_name}: ${props.percentage.toFixed(1)}%`}
                                                outerRadius={100}
                                                fill="#8884d8"
                                                dataKey="company_count"
                                            >
                                                {data.witel_distribution.map((entry, index) => (
                                                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                                ))}
                                            </Pie>
                                            <Tooltip 
                                                formatter={(value: number, name: string, props: any) => [
                                                    `${value} ${value === 1 ? 'Company' : 'Companies'}`,
                                                    props.payload.witel_name
                                                ]}
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
                                                formatter={(value, entry: any) => (
                                                    <span style={{ color: isDarkMode ? '#e5e7eb' : '#374151' }}>
                                                        {entry.payload.witel_name}
                                                    </span>
                                                )}
                                            />
                                        </PieChart>
                                    </ResponsiveContainer>
                                ) : (
                                    <div className="flex items-center justify-center h-[320px] text-gray-500 dark:text-gray-400">
                                        No distribution data available
                                    </div>
                                )}
                            </div>

                            {/* Right: Table - Company List (65%) */}
                            <div className="lg:col-span-13 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <Building2 className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    Company Details
                                </h3>
                                
                                <div className="overflow-x-auto max-h-[320px] overflow-y-auto">
                                    <table className="w-full text-sm">
                                        <thead className="bg-gray-50 dark:bg-gray-800 sticky top-0">
                                            <tr>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Company
                                                </th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    NIP NAS
                                                </th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Witel
                                                </th>
                                                <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Target Revenue
                                                </th>
                                                <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Subsegment
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                            {data.companies && data.companies.length > 0 ? (
                                                data.companies.map((company, idx) => (
                                                    <tr key={company.nip_nas} className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                        <td className="px-3 py-3 text-gray-900 dark:text-gray-100">
                                                            {company.nama_perusahaan}
                                                        </td>
                                                        <td className="px-3 py-3 font-mono text-gray-700 dark:text-gray-300 text-xs">
                                                            {company.nip_nas}
                                                        </td>
                                                        <td className="px-3 py-3 text-gray-700 dark:text-gray-300">
                                                            {company.nama_witels}
                                                        </td>
                                                        <td className="px-3 py-3 text-right font-semibold text-gray-900 dark:text-gray-100">
                                                            {company.formatted_revenue}
                                                        </td>
                                                        <td className="px-3 py-3 text-gray-700 dark:text-gray-300">
                                                            {company.subsegment}
                                                        </td>
                                                        <td className="px-3 py-3 text-center">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                className="text-xs"
                                                            >
                                                                Detail
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan={6} className="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                                                        No companies assigned to this Account Manager
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
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
