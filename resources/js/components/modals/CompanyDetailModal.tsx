import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, Building2, TrendingUp, Calendar, BarChart3, Info } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts';
import { CompanyData, MonthlyRevenue, YearlyRevenue } from '@/types/dashboard';
import axios from '@/lib/axios';

interface CompanyDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    company: CompanyData | null;
}

const CompanyDetailModal: React.FC<CompanyDetailModalProps> = ({
    isOpen,
    onClose,
    company
}) => {
    const [monthlyData, setMonthlyData] = useState<MonthlyRevenue[]>([]);
    const [yearlyData, setYearlyData] = useState<YearlyRevenue[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [summary, setSummary] = useState<any>(null);
    
    // Detect dark mode
    const [isDarkMode, setIsDarkMode] = useState(false);

    useEffect(() => {
        const checkDarkMode = () => {
            setIsDarkMode(document.documentElement.classList.contains('dark'));
        };
        
        checkDarkMode();
        
        // Watch for dark mode changes
        const observer = new MutationObserver(checkDarkMode);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (isOpen && company) {
            fetchCompanyDetails();
        }
    }, [isOpen, company]);

    const fetchCompanyDetails = async () => {
        if (!company) return;
        
        setLoading(true);
        setError(null);
        
        try {
            const response = await axios.get(`/api/dashboard/individual-company-details`, {
                params: {
                    company_id: company.id
                }
            });
            
            if (response.data.success) {
                setMonthlyData(response.data.data.monthly_data);
                setYearlyData(response.data.data.yearly_data);
                setSummary(response.data.data.summary);
            } else {
                setError('Failed to fetch company details');
            }
        } catch (err) {
            setError('Error loading company data');
            console.error('Error fetching company details:', err);
        } finally {
            setLoading(false);
        }
    };

    const getStatusBadge = (status: string) => {
        const statusStyles = {
            'Active': 'bg-green-100 dark:bg-green-950 text-green-800 dark:text-green-300 border-green-200 dark:border-green-900',
            'Inactive': 'bg-red-100 dark:bg-red-950 text-red-800 dark:text-red-300 border-red-200 dark:border-red-900',
            'Suspended': 'bg-yellow-100 dark:bg-yellow-950 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-900'
        };
        
        return (
            <span className={`px-3 py-1 text-sm font-medium rounded-full border ${statusStyles[status as keyof typeof statusStyles] || 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700'}`}>
                {status}
            </span>
        );
    };

    if (!company) return null;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <Building2 className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">{company.nama_perusahaan}</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Detailed revenue analysis and performance metrics
                    </DialogDescription>
                </DialogHeader>

                {/* Company Info Card */}
                <div className="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-950/30 dark:to-pink-950/30 rounded-lg border border-red-100 dark:border-red-900 p-6 mb-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <Info className="h-4 w-4 text-red-600 dark:text-red-400" />
                                <span className="text-sm font-medium text-red-700 dark:text-red-300">NIP-NAS</span>
                            </div>
                            <p className="font-mono text-gray-900 dark:text-gray-100">{company.nip_nas || 'N/A'}</p>
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <Building2 className="h-4 w-4 text-red-600 dark:text-red-400" />
                                <span className="text-sm font-medium text-red-700 dark:text-red-300">Subsegment</span>
                            </div>
                            <p className="font-semibold text-gray-900 dark:text-gray-100">{company.subsegment}</p>
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <span className="text-sm font-medium text-red-700 dark:text-red-300">Status</span>
                            </div>
                            {getStatusBadge(company.status)}
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <span className="text-sm font-medium text-red-700 dark:text-red-300">Data Source</span>
                            </div>
                            <p className="text-gray-900 dark:text-gray-100">{company.source_data}</p>
                        </div>
                    </div>
                </div>

                {loading && (
                    <div className="flex items-center justify-center py-8">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading company analytics...</span>
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

                {!loading && !error && (
                    <>
                        {/* Summary Cards - Always show if we have summary data */}
                        {summary ? (
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 p-4 rounded-lg border border-blue-100 dark:border-blue-900">
                                    <div className="flex items-center gap-2 mb-2">
                                        <TrendingUp className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        <span className="text-sm font-medium text-blue-700 dark:text-blue-300">Total Revenue</span>
                                    </div>
                                    <p className="text-xl font-bold text-blue-900 dark:text-blue-100">{summary.formatted_total_revenue || 'Rp 0.00M'}</p>
                                </div>
                                
                                <div className="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 p-4 rounded-lg border border-green-100 dark:border-green-900">
                                    <div className="flex items-center gap-2 mb-2">
                                        <BarChart3 className="h-5 w-5 text-green-600 dark:text-green-400" />
                                        <span className="text-sm font-medium text-green-700 dark:text-green-300">Avg Monthly</span>
                                    </div>
                                    <p className="text-xl font-bold text-green-900 dark:text-green-100">{summary.formatted_avg_monthly || 'Rp 0.00M'}</p>
                                </div>
                                
                                <div className="bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-950/30 dark:to-violet-950/30 p-4 rounded-lg border border-purple-100 dark:border-purple-900">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Calendar className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                        <span className="text-sm font-medium text-purple-700 dark:text-purple-300">Best Month</span>
                                    </div>
                                    <p className="text-xl font-bold text-purple-900 dark:text-purple-100">{summary.best_month || 'N/A'}</p>
                                </div>
                                
                                <div className="bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-950/30 dark:to-red-950/30 p-4 rounded-lg border border-orange-100 dark:border-orange-900">
                                    <div className="flex items-center gap-2 mb-2">
                                        <TrendingUp className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                        <span className="text-sm font-medium text-orange-700 dark:text-orange-300">Best Year</span>
                                    </div>
                                    <p className="text-xl font-bold text-orange-900 dark:text-orange-100">{summary.best_year || 'N/A'}</p>
                                </div>
                            </div>
                        ) : (
                            <div className="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <p className="text-gray-600 dark:text-gray-400">Loading summary data...</p>
                            </div>
                        )}

                        {/* Charts Section */}
                        {(monthlyData.length > 0 || yearlyData.length > 0) && (
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                {/* Monthly Revenue Trend */}
                                {monthlyData.length > 0 && (
                                    <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Monthly Revenue Trend (Last 12 Months)</h3>
                                        <ResponsiveContainer width="100%" height={320}>
                                            <LineChart data={monthlyData}>
                                                <CartesianGrid strokeDasharray="3 3" stroke={isDarkMode ? '#374151' : '#f0f0f0'} />
                                                <XAxis 
                                                    dataKey="bulan_name" 
                                                    tick={{ fontSize: 11, fill: isDarkMode ? '#e5e7eb' : '#666' }}
                                                    stroke={isDarkMode ? '#6b7280' : '#666'}
                                                    angle={-45}
                                                    textAnchor="end"
                                                    height={60}
                                                />
                                                <YAxis 
                                                    tick={{ fontSize: 12, fill: isDarkMode ? '#e5e7eb' : '#666' }}
                                                    stroke={isDarkMode ? '#6b7280' : '#666'}
                                                    tickFormatter={(value) => `${(value / 1000000000).toFixed(1)}M`}
                                                />
                                                <Tooltip 
                                                    formatter={(value: number) => [`Rp ${(value / 1000000000).toFixed(2)}M`, 'Revenue']}
                                                    labelFormatter={(label, payload) => {
                                                        if (payload && payload.length > 0) {
                                                            const data = payload[0].payload;
                                                            return `${data.bulan_name} ${data.tahun}`;
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
                                                <Line 
                                                    type="monotone" 
                                                    dataKey="revenue" 
                                                    stroke={isDarkMode ? '#f87171' : '#dc2626'}
                                                    strokeWidth={3}
                                                    dot={{ fill: isDarkMode ? '#f87171' : '#dc2626', strokeWidth: 2, r: 4 }}
                                                    activeDot={{ r: 6, stroke: isDarkMode ? '#f87171' : '#dc2626', strokeWidth: 2 }}
                                                />
                                            </LineChart>
                                        </ResponsiveContainer>
                                    </div>
                                )}

                                {/* Yearly Revenue Comparison */}
                                {yearlyData.length > 0 && (
                                    <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Yearly Revenue Comparison</h3>
                                        <ResponsiveContainer width="100%" height={320}>
                                            <BarChart data={yearlyData}>
                                                <CartesianGrid strokeDasharray="3 3" stroke={isDarkMode ? '#374151' : '#f0f0f0'} />
                                                <XAxis 
                                                    dataKey="tahun" 
                                                    tick={{ fontSize: 12, fill: isDarkMode ? '#e5e7eb' : '#666' }}
                                                    stroke={isDarkMode ? '#6b7280' : '#666'}
                                                />
                                                <YAxis 
                                                    tick={{ fontSize: 12, fill: isDarkMode ? '#e5e7eb' : '#666' }}
                                                    stroke={isDarkMode ? '#6b7280' : '#666'}
                                                    tickFormatter={(value) => `${(value / 1000000000).toFixed(1)}M`}
                                                />
                                                <Tooltip 
                                                    formatter={(value: number) => [`Rp ${(value / 1000000000).toFixed(2)}M`, 'Revenue']}
                                                    labelFormatter={(label) => `Year: ${label}`}
                                                    contentStyle={{
                                                        backgroundColor: isDarkMode ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                                        border: isDarkMode ? '1px solid #374151' : '1px solid #e5e7eb',
                                                        borderRadius: '8px',
                                                        color: isDarkMode ? '#e5e7eb' : '#111827'
                                                    }}
                                                />
                                                <Bar 
                                                    dataKey="total_revenue" 
                                                    fill={isDarkMode ? '#f87171' : '#dc2626'}
                                                    radius={[4, 4, 0, 0]}
                                                />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                )}
                            </div>
                        )}
                    </>
                )}

                {!loading && !error && monthlyData.length === 0 && yearlyData.length === 0 && (
                    <div className="text-center py-8">
                        <BarChart3 className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                        <p className="text-gray-500 dark:text-gray-400">No revenue data available for this company</p>
                    </div>
                )}

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

export default CompanyDetailModal;