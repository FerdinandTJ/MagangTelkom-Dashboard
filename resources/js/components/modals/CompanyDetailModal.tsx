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
import axios from 'axios';

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
            'Active': 'bg-green-100 text-green-800 border-green-200',
            'Inactive': 'bg-red-100 text-red-800 border-red-200',
            'Suspended': 'bg-yellow-100 text-yellow-800 border-yellow-200'
        };
        
        return (
            <span className={`px-3 py-1 text-sm font-medium rounded-full border ${statusStyles[status as keyof typeof statusStyles] || 'bg-gray-100 text-gray-800 border-gray-200'}`}>
                {status}
            </span>
        );
    };

    if (!company) return null;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl">
                        <Building2 className="h-5 w-5 sm:h-6 sm:w-6 text-red-600" />
                        <span className="truncate">{company.nama_perusahaan}</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600">
                        Detailed revenue analysis and performance metrics
                    </DialogDescription>
                </DialogHeader>

                {/* Company Info Card */}
                <div className="bg-gradient-to-r from-red-50 to-pink-50 rounded-lg border border-red-100 p-6 mb-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <Info className="h-4 w-4 text-red-600" />
                                <span className="text-sm font-medium text-red-700">NIP-NAS</span>
                            </div>
                            <p className="font-mono text-gray-900">{company.nip_nas || 'N/A'}</p>
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <Building2 className="h-4 w-4 text-red-600" />
                                <span className="text-sm font-medium text-red-700">Subsegment</span>
                            </div>
                            <p className="font-semibold text-gray-900">{company.subsegment}</p>
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <span className="text-sm font-medium text-red-700">Status</span>
                            </div>
                            {getStatusBadge(company.status)}
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <span className="text-sm font-medium text-red-700">Data Source</span>
                            </div>
                            <p className="text-gray-900">{company.source_data}</p>
                        </div>
                    </div>
                </div>

                {loading && (
                    <div className="flex items-center justify-center py-8">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600" />
                        <span className="ml-2 text-gray-600">Loading company analytics...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <p className="text-red-700">{error}</p>
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
                                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-100">
                                    <div className="flex items-center gap-2 mb-2">
                                        <TrendingUp className="h-5 w-5 text-blue-600" />
                                        <span className="text-sm font-medium text-blue-700">Total Revenue</span>
                                    </div>
                                    <p className="text-xl font-bold text-blue-900">{summary.formatted_total_revenue || 'Rp 0.00M'}</p>
                                </div>
                                
                                <div className="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg border border-green-100">
                                    <div className="flex items-center gap-2 mb-2">
                                        <BarChart3 className="h-5 w-5 text-green-600" />
                                        <span className="text-sm font-medium text-green-700">Avg Monthly</span>
                                    </div>
                                    <p className="text-xl font-bold text-green-900">{summary.formatted_avg_monthly || 'Rp 0.00M'}</p>
                                </div>
                                
                                <div className="bg-gradient-to-r from-purple-50 to-violet-50 p-4 rounded-lg border border-purple-100">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Calendar className="h-5 w-5 text-purple-600" />
                                        <span className="text-sm font-medium text-purple-700">Best Month</span>
                                    </div>
                                    <p className="text-xl font-bold text-purple-900">{summary.best_month || 'N/A'}</p>
                                </div>
                                
                                <div className="bg-gradient-to-r from-orange-50 to-red-50 p-4 rounded-lg border border-orange-100">
                                    <div className="flex items-center gap-2 mb-2">
                                        <TrendingUp className="h-5 w-5 text-orange-600" />
                                        <span className="text-sm font-medium text-orange-700">Best Year</span>
                                    </div>
                                    <p className="text-xl font-bold text-orange-900">{summary.best_year || 'N/A'}</p>
                                </div>
                            </div>
                        ) : (
                            <div className="mb-6 p-4 bg-gray-50 rounded-lg">
                                <p className="text-gray-600">Loading summary data...</p>
                            </div>
                        )}

                        {/* Charts Section */}
                        {(monthlyData.length > 0 || yearlyData.length > 0) && (
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                {/* Monthly Revenue Trend */}
                                {monthlyData.length > 0 && (
                                    <div className="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue Trend</h3>
                                        <ResponsiveContainer width="100%" height={320}>
                                            <LineChart data={monthlyData}>
                                                <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                                                <XAxis 
                                                    dataKey="bulan_name" 
                                                    tick={{ fontSize: 12 }}
                                                    stroke="#666"
                                                />
                                                <YAxis 
                                                    tick={{ fontSize: 12 }}
                                                    stroke="#666"
                                                    tickFormatter={(value) => `${(value / 1000000000).toFixed(1)}M`}
                                                />
                                                <Tooltip 
                                                    formatter={(value: number) => [`Rp ${(value / 1000000000).toFixed(2)}M`, 'Revenue']}
                                                    labelFormatter={(label) => `Month: ${label}`}
                                                    contentStyle={{
                                                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                                        border: '1px solid #e5e7eb',
                                                        borderRadius: '8px'
                                                    }}
                                                />
                                                <Line 
                                                    type="monotone" 
                                                    dataKey="revenue" 
                                                    stroke="#dc2626" 
                                                    strokeWidth={3}
                                                    dot={{ fill: '#dc2626', strokeWidth: 2, r: 4 }}
                                                    activeDot={{ r: 6, stroke: '#dc2626', strokeWidth: 2 }}
                                                />
                                            </LineChart>
                                        </ResponsiveContainer>
                                    </div>
                                )}

                                {/* Yearly Revenue Comparison */}
                                {yearlyData.length > 0 && (
                                    <div className="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Yearly Revenue Comparison</h3>
                                        <ResponsiveContainer width="100%" height={320}>
                                            <BarChart data={yearlyData}>
                                                <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                                                <XAxis 
                                                    dataKey="tahun" 
                                                    tick={{ fontSize: 12 }}
                                                    stroke="#666"
                                                />
                                                <YAxis 
                                                    tick={{ fontSize: 12 }}
                                                    stroke="#666"
                                                    tickFormatter={(value) => `${(value / 1000000000).toFixed(1)}M`}
                                                />
                                                <Tooltip 
                                                    formatter={(value: number) => [`Rp ${(value / 1000000000).toFixed(2)}M`, 'Revenue']}
                                                    labelFormatter={(label) => `Year: ${label}`}
                                                    contentStyle={{
                                                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                                        border: '1px solid #e5e7eb',
                                                        borderRadius: '8px'
                                                    }}
                                                />
                                                <Bar 
                                                    dataKey="total_revenue" 
                                                    fill="#dc2626"
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
                        <p className="text-gray-500">No revenue data available for this company</p>
                    </div>
                )}

                <div className="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200">
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