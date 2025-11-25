import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, Building2, TrendingUp, Calendar, BarChart3, Info, User } from 'lucide-react';
import { CompanyData, MonthlyRevenue, YearlyRevenue } from '@/types/dashboard';
import axios from '@/lib/axios';
import { formatCurrency, formatCurrencyShort } from '@/utils/currency';
import RevenueBreakdownTree from '@/components/RevenueBreakdownTree';

interface AccountManagerData {
    nik: string;
    nama: string;
}

interface RegionData {
    region_code: string;
    region_name: string;
    witel_name?: string;
    is_primary: boolean;
}

interface CompanyDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    company: CompanyData | null;
    currentMonth?: string;
    currentYear?: number;
    year?: number;  // Filter year
    month?: number; // Filter month
}

const CompanyDetailModal: React.FC<CompanyDetailModalProps> = ({
    isOpen,
    onClose,
    company,
    currentMonth,
    currentYear,
    year,
    month
}) => {
    const [monthlyData, setMonthlyData] = useState<MonthlyRevenue[]>([]);
    const [yearlyData, setYearlyData] = useState<YearlyRevenue[]>([]);
    const [revenueBreakdown, setRevenueBreakdown] = useState<any[]>([]);
    const [accountManagers, setAccountManagers] = useState<AccountManagerData[]>([]);
    const [regions, setRegions] = useState<RegionData[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [summary, setSummary] = useState<any>(null);
    
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
        if (isOpen && company) {
            fetchCompanyDetails();
        }
    }, [isOpen, company, year, month]);

    const fetchCompanyDetails = async () => {
        if (!company) return;
        
        setLoading(true);
        setError(null);
        
        try {
            const params: any = {
                company_id: company.nip_nas || company.id
            };
            
            // Add filters if provided
            if (year) {
                params.year = year;
            }
            if (month) {
                params.month = month;
            }
            
            const response = await axios.get(`/api/dashboard/individual-company-details`, {
                params
            });
            
            if (response.data.success) {
                setMonthlyData(response.data.data.monthly_data);
                setYearlyData(response.data.data.yearly_data);
                setAccountManagers(response.data.data.account_managers || []);
                setRegions(response.data.data.regions || []);
                setSummary(response.data.data.summary);
            } else {
                setError('Failed to fetch company details');
            }

            // Fetch revenue breakdown data from database (production endpoint)
            const companyIdentifier = company.nip_nas || company.id;
            const breakdownParams: any = {};
            
            // Apply same filters as company details
            if (year) {
                breakdownParams.tahun = year;
            }
            if (month) {
                breakdownParams.bulan = month;
            }
            
            const breakdownResponse = await axios.get(`/api/dashboard/revenue-breakdown/${companyIdentifier}`, {
                params: breakdownParams
            });
            
            if (breakdownResponse.data.success) {
                setRevenueBreakdown(breakdownResponse.data.data || []);
            }
        } catch (err) {
            setError('Error loading company data');
        } finally {
            setLoading(false);
        }
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
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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
                                <svg className="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                <span className="text-sm font-medium text-red-700 dark:text-red-300">Region</span>
                            </div>
                            <div className="flex flex-wrap gap-1 mt-1">
                                {!loading && regions.length > 0 ? (
                                    regions.map((region, idx) => (
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
                                    <span className="text-gray-900 dark:text-gray-100">-</span>
                                )}
                            </div>
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <User className="h-4 w-4 text-red-600 dark:text-red-400" />
                                <span className="text-sm font-medium text-red-700 dark:text-red-300">Account Manager</span>
                            </div>
                            <div className="flex flex-wrap gap-1 mt-1">
                                {!loading && accountManagers.length > 0 ? (
                                    accountManagers.map((am, idx) => (
                                        <span 
                                            key={am.nik}
                                            className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800"
                                        >
                                            {am.nama}
                                        </span>
                                    ))
                                ) : (
                                    <span className="text-gray-900 dark:text-gray-100">-</span>
                                )}
                            </div>
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
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 p-4 rounded-lg border border-blue-100 dark:border-blue-900">
                                    <div className="flex items-center gap-2 mb-2">
                                        <TrendingUp className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        <span className="text-sm font-medium text-blue-700 dark:text-blue-300">Total Revenue</span>
                                    </div>
                                    <p className="text-xl font-bold text-blue-900 dark:text-blue-100">{summary.formatted_total_revenue || 'Rp 0'}</p>
                                </div>
                                
                                <div className="bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-950/30 dark:to-violet-950/30 p-4 rounded-lg border border-purple-100 dark:border-purple-900">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Calendar className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                        <span className="text-sm font-medium text-purple-700 dark:text-purple-300">Period</span>
                                    </div>
                                    <p className="text-xl font-bold text-purple-900 dark:text-purple-100">
                                        {summary.period || 'All Time'}
                                    </p>
                                </div>
                            </div>
                        ) : (
                            <div className="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <p className="text-gray-600 dark:text-gray-400">Loading summary data...</p>
                            </div>
                        )}

                        {/* Revenue Breakdown Section */}
                        {revenueBreakdown.length > 0 && (
                            <div className="mt-6">
                                <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue Source Breakdown</h3>
                                    <RevenueBreakdownTree data={revenueBreakdown} />
                                </div>
                            </div>
                        )}
                    </>
                )}

                {!loading && !error && revenueBreakdown.length === 0 && (
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