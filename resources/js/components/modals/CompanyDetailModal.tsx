import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, Building2, TrendingUp, Calendar, BarChart3, Info, User, ChevronDown } from 'lucide-react';
import { CompanyData, MonthlyRevenue, YearlyRevenue } from '@/types/dashboard';
import axios from '@/lib/axios';
import { formatCurrency, formatCurrencyShort } from '@/utils/currency';
import RevenueBreakdownTree from '@/components/RevenueBreakdownTree';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    PieChart,
    Pie,
    Cell,
    Legend
} from 'recharts';

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
    defaultYear?: number;
}

const CompanyDetailModal: React.FC<CompanyDetailModalProps> = ({
    isOpen,
    onClose,
    company,
    currentMonth,
    currentYear,
    defaultYear
}) => {
    const [monthlyData, setMonthlyData] = useState<MonthlyRevenue[]>([]);
    const [yearlyData, setYearlyData] = useState<YearlyRevenue[]>([]);
    const [revenueBreakdown, setRevenueBreakdown] = useState<any[]>([]);
    const [accountManagers, setAccountManagers] = useState<AccountManagerData[]>([]);
    const [regions, setRegions] = useState<RegionData[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [summary, setSummary] = useState<any>(null);
    
    // Filter states
    const [selectedMonth, setSelectedMonth] = useState<number>(new Date().getMonth() + 1);
    const [selectedYear, setSelectedYear] = useState<number>(defaultYear || currentYear || new Date().getFullYear());
    const [availableYears, setAvailableYears] = useState<number[]>([]);
    const [availableMonths, setAvailableMonths] = useState<number[]>([]);
    
    // Selected category from pie chart click
    const [selectedCategory, setSelectedCategory] = useState<string | null>(null);
    
    const [isDarkMode, setIsDarkMode] = useState(false);

    const monthLabels: { [key: number]: string } = {
        1: 'Januari',
        2: 'Februari',
        3: 'Maret',
        4: 'April',
        5: 'Mei',
        6: 'Juni',
        7: 'Juli',
        8: 'Agustus',
        9: 'September',
        10: 'Oktober',
        11: 'November',
        12: 'Desember'
    };

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

    // Reset filter to defaultYear when modal opens or defaultYear changes
    useEffect(() => {
        if (isOpen && defaultYear) {
            setSelectedYear(defaultYear);
        }
    }, [isOpen, defaultYear]);

    useEffect(() => {
        if (isOpen && company) {
            fetchCompanyDetails();
        }
    }, [isOpen, company, selectedYear, selectedMonth]);

    // Separate effect to update available months when year changes OR modal opens
    useEffect(() => {
        if (isOpen && company && selectedYear) {
            fetchAvailableMonths();
        }
    }, [selectedYear, isOpen]);

    const fetchAvailableMonths = async () => {
        if (!company) return;
        
        try {
            const monthsParams: any = {
                company_id: company.nip_nas || company.id,
                year: selectedYear
            };
            
            const monthsResponse = await axios.get(`/api/dashboard/individual-company-details`, {
                params: monthsParams
            });
            
            if (monthsResponse.data.success) {
                const monthly = monthsResponse.data.data.monthly_data || [];
                const months = ([...new Set(monthly.map((m: any) => m.bulan))] as number[]).sort((a: number, b: number) => a - b);
                setAvailableMonths(months);
                
                // Set initial month if not available in selected year
                if (months.length > 0 && !months.includes(selectedMonth)) {
                    setSelectedMonth(months[0]);
                }
            }
        } catch (err) {
            console.error('Error fetching available months:', err);
        }
    };

    const fetchCompanyDetails = async () => {
        if (!company) return;
        
        setLoading(true);
        setError(null);
        
        try {
            const params: any = {
                company_id: company.nip_nas || company.id
            };
            
            // Add filters if provided
            if (selectedYear) {
                params.year = selectedYear;
            }
            if (selectedMonth) {
                params.month = selectedMonth;
            }
            
            const response = await axios.get(`/api/dashboard/individual-company-details`, {
                params
            });
            
            if (response.data.success) {
                setMonthlyData(response.data.data.monthly_data);
                setAccountManagers(response.data.data.account_managers || []);
                setRegions(response.data.data.regions || []);
                setSummary(response.data.data.summary);
            } else {
                setError('Failed to fetch company details');
            }

            // Fetch yearly data separately WITHOUT filters for historical chart
            const yearlyParams: any = {
                company_id: company.nip_nas || company.id
            };
            // No year or month filters - we want ALL yearly data
            
            const yearlyResponse = await axios.get(`/api/dashboard/individual-company-details`, {
                params: yearlyParams
            });
            
            if (yearlyResponse.data.success) {
                const yearly = yearlyResponse.data.data.yearly_data;
                setYearlyData(yearly);
                
                // Extract available years from yearly data
                const years = yearly.map((y: any) => y.tahun).sort((a: number, b: number) => b - a);
                setAvailableYears(years);
            }

            // Fetch revenue breakdown data from database (production endpoint)
            const companyIdentifier = company.nip_nas || company.id;
            const breakdownParams: any = {};
            
            // Apply same filters as company details
            if (selectedYear) {
                breakdownParams.tahun = selectedYear;
            }
            if (selectedMonth) {
                breakdownParams.bulan = selectedMonth;
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

    // Handle pie chart slice click to expand tree
    const handlePieSliceClick = (data: any) => {
        setSelectedCategory(data.name);
    };

    if (!company) return null;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-2">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <Building2 className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">{company.nama_perusahaan}</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Detailed revenue analysis and performance metrics
                    </DialogDescription>
                </DialogHeader>

                {/* Filter Section */}
                <div className="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-3 mb-3">
                    <div className="flex items-center gap-4 flex-wrap">
                        <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Filter Periode:</span>
                        
                        {/* Year Dropdown */}
                        <div className="relative">
                            <select
                                value={selectedYear}
                                onChange={(e) => setSelectedYear(Number(e.target.value))}
                                className="appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 pr-8 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                disabled={availableYears.length === 0}
                            >
                                {availableYears.length > 0 ? (
                                    availableYears.map(year => (
                                        <option key={year} value={year}>
                                            {year}
                                        </option>
                                    ))
                                ) : (
                                    <option value="">No data available</option>
                                )}
                            </select>
                            <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                        </div>

                        {/* Month Dropdown */}
                        <div className="relative">
                            <select
                                value={selectedMonth}
                                onChange={(e) => setSelectedMonth(Number(e.target.value))}
                                className="appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 pr-8 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                disabled={availableMonths.length === 0}
                            >
                                {availableMonths.length > 0 ? (
                                    availableMonths.map(month => (
                                        <option key={month} value={month}>
                                            {monthLabels[month]}
                                        </option>
                                    ))
                                ) : (
                                    <option value="">No data available</option>
                                )}
                            </select>
                            <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                        </div>
                    </div>
                </div>

                {/* Company Info Card */}
                <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 mb-3 shadow-sm">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <Info className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                <span className="text-sm font-medium text-gray-600 dark:text-gray-400">NIP-NAS</span>
                            </div>
                            <p className="font-mono text-gray-900 dark:text-gray-100">{company.nip_nas || 'N/A'}</p>
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <Building2 className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Subsegment</span>
                            </div>
                            <p className="font-semibold text-gray-900 dark:text-gray-100">{company.subsegment}</p>
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <svg className="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Region</span>
                            </div>
                            <div className="flex flex-wrap gap-1 mt-1">
                                {!loading && regions.length > 0 ? (
                                    regions.map((region, idx) => (
                                        <span 
                                            key={idx}
                                            title={`${region.region_name}${region.witel_name ? ` - ${region.witel_name}` : ''}`}
                                            className={`inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium ${
                                                region.is_primary 
                                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800' 
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
                                <User className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Account Manager</span>
                            </div>
                            <div className="flex flex-wrap gap-1 mt-1">
                                {!loading && accountManagers.length > 0 ? (
                                    accountManagers.map((am, idx) => (
                                        <span 
                                            key={am.nik}
                                            className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800"
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
                                <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Data Source</span>
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
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">{/* Total Revenue Card */}
                                <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                    <div className="flex items-center gap-3 mb-3">
                                        <div className="p-2 bg-blue-50 dark:bg-blue-950/30 rounded-lg">
                                            <TrendingUp className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</span>
                                    </div>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">{summary.formatted_total_revenue || 'Rp 0'}</p>
                                    <p className="text-xs text-gray-500 dark:text-gray-500 mt-1">Accumulated earnings</p>
                                </div>
                                
                                {/* Period Card */}
                                <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                    <div className="flex items-center gap-3 mb-3">
                                        <div className="p-2 bg-purple-50 dark:bg-purple-950/30 rounded-lg">
                                            <Calendar className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                        </div>
                                        <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Reporting Period</span>
                                    </div>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {summary.period || 'All Time'}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-500 mt-1">Analysis timeframe</p>
                                </div>
                            </div>
                        ) : (
                            <div className="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <p className="text-gray-600 dark:text-gray-400">Loading summary data...</p>
                            </div>
                        )}

                        {/* Revenue Breakdown Section */}
                        {revenueBreakdown.length > 0 && (
                            <div className="mt-1">
                                <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                                    <div className="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            <BarChart3 className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            Revenue Source Breakdown
                                        </h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">Detailed breakdown by category and product</p>
                                    </div>
                                    <div className="p-5">
                                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                            {/* Tree View - Takes 2 columns with scroll */}
                                            <div className="lg:col-span-2 max-h-[600px] overflow-y-auto pr-2">
                                                <RevenueBreakdownTree 
                                                    data={revenueBreakdown}
                                                    selectedCategory={selectedCategory}
                                                    onCategoryCleared={() => setSelectedCategory(null)}
                                                />
                                            </div>
                                            
                                            {/* Pie Chart - Takes 1 column */}
                                            <div className="flex flex-col items-center justify-center">
                                                <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Category Distribution</h4>
                                                <ResponsiveContainer width="100%" height={300}>
                                                    <PieChart>
                                                        <Pie
                                                            data={(() => {
                                                                // Calculate total and percentage for each category
                                                                const categoryTotals: { [key: string]: number } = {};
                                                                let grandTotal = 0;
                                                                
                                                                // Check structure and aggregate by category name
                                                                revenueBreakdown.forEach(item => {
                                                                    // Try different possible field names for category
                                                                    const category = item.name || item.group1_name || item.category || 'Unknown';
                                                                    const revenue = parseFloat(item.revenue || item.total_revenue || 0);
                                                                    
                                                                    categoryTotals[category] = (categoryTotals[category] || 0) + revenue;
                                                                    grandTotal += revenue;
                                                                });
                                                                
                                                                // Define colors for each category
                                                                const categoryColors: { [key: string]: string } = {
                                                                    'CONNECTIVITY': '#60a5fa', // Light blue
                                                                    'LEGACY': '#1e3a8a', // Dark blue
                                                                    'PLATFORM': '#3b82f6', // Blue
                                                                    'SERVICE': '#f59e0b', // Orange/Amber
                                                                };
                                                                
                                                                const result = Object.entries(categoryTotals)
                                                                    .filter(([_, value]) => value > 0)
                                                                    .map(([name, value]) => ({
                                                                        name,
                                                                        value,
                                                                        percentage: grandTotal > 0 ? ((value / grandTotal) * 100).toFixed(1) : '0',
                                                                        fill: categoryColors[name.toUpperCase()] || '#94a3b8'
                                                                    }));
                                                                
                                                                return result;
                                                            })()}
                                                            cx="50%"
                                                            cy="50%"
                                                            labelLine={false}
                                                            label={(entry: any) => `${entry.percentage}%`}
                                                            outerRadius={80}
                                                            dataKey="value"
                                                            onClick={handlePieSliceClick}
                                                            cursor="pointer"
                                                        />
                                                        <Tooltip
                                                            content={({ active, payload }) => {
                                                                if (active && payload && payload.length) {
                                                                    const data = payload[0].payload;
                                                                    return (
                                                                        <div
                                                                            style={{
                                                                                backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                                                                                border: `1px solid ${isDarkMode ? '#374151' : '#e5e7eb'}`,
                                                                                borderRadius: '8px',
                                                                                boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                                                                                padding: '12px',
                                                                                minWidth: '200px'
                                                                            }}
                                                                        >
                                                                            <p style={{ fontWeight: 600, marginBottom: '4px', color: isDarkMode ? '#ffffff' : '#374151' }}>
                                                                                {data.name}
                                                                            </p>
                                                                            <p style={{ margin: '4px 0', color: isDarkMode ? '#ffffff' : '#374151', fontSize: '14px' }}>
                                                                                Rp {data.value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                                            </p>
                                                                            <p style={{ margin: '4px 0', fontSize: '12px', color: '#6b7280' }}>
                                                                                {data.percentage}% of total
                                                                            </p>
                                                                        </div>
                                                                    );
                                                                }
                                                                return null;
                                                            }}
                                                        />
                                                        <Legend 
                                                            verticalAlign="bottom" 
                                                            height={36}
                                                            formatter={(value, entry: any) => {
                                                                return (
                                                                    <span style={{ 
                                                                        color: isDarkMode ? '#d1d5db' : '#374151',
                                                                        fontSize: '12px'
                                                                    }}>
                                                                        {value} ({entry.payload.percentage}%)
                                                                    </span>
                                                                );
                                                            }}
                                                        />
                                                    </PieChart>
                                                </ResponsiveContainer>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Historical Revenue Chart - Yearly Data */}
                        {yearlyData.length > 0 && (
                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm mt-6">
                                <div className="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <TrendingUp className="h-5 w-5 text-green-600 dark:text-green-400" />
                                        Historical Revenue (Yearly)
                                    </h3>
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">Total revenue per year</p>
                                </div>
                                <div className="p-5">
                                    <ResponsiveContainer width="100%" height={250}>
                                        <BarChart data={yearlyData}>
                                            <CartesianGrid 
                                                strokeDasharray="3 3" 
                                                stroke={isDarkMode ? '#374151' : '#e5e7eb'} 
                                                className="opacity-30" 
                                            />
                                            <XAxis 
                                                dataKey="tahun" 
                                                tick={{ fontSize: 12, fill: isDarkMode ? '#ffffff' : '#374151' }}
                                                stroke={isDarkMode ? '#ffffff' : '#374151'}
                                            />
                                            <YAxis 
                                                tickFormatter={formatCurrencyShort}
                                                tick={{ fontSize: 12, fill: isDarkMode ? '#ffffff' : '#374151' }}
                                                stroke={isDarkMode ? '#ffffff' : '#374151'}
                                            />
                                            <Tooltip
                                                content={({ active, payload }) => {
                                                    if (active && payload && payload.length) {
                                                        const data = payload[0].payload;
                                                        return (
                                                            <div
                                                                style={{
                                                                    backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                                                                    border: `1px solid ${isDarkMode ? '#374151' : '#e5e7eb'}`,
                                                                    borderRadius: '8px',
                                                                    boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                                                                    padding: '12px',
                                                                    minWidth: '200px'
                                                                }}
                                                            >
                                                                <p style={{ fontWeight: 600, marginBottom: '8px', color: isDarkMode ? '#ffffff' : '#374151' }}>
                                                                    Year {data.tahun}
                                                                </p>
                                                                <p style={{ margin: '4px 0', color: isDarkMode ? '#ffffff' : '#374151' }}>
                                                                    Revenue: {formatCurrency(data.total_revenue, 2)}
                                                                </p>
                                                                <p style={{ margin: '4px 0', fontSize: '12px', color: '#6b7280' }}>
                                                                    {data.months_count} month{data.months_count > 1 ? 's' : ''}
                                                                </p>
                                                            </div>
                                                        );
                                                    }
                                                    return null;
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