import RevenueBarChart from '@/components/charts/RevenueBarChart';
import SubsegmentPieChart from '@/components/charts/SubsegmentPieChart';
import YearlyLineChart from '@/components/charts/YearlyLineChart';
import StatCard from '@/components/StatCard';
import MonthDetailModal from '@/components/modals/MonthDetailModal';
import SubsegmentDetailModal from '@/components/modals/SubsegmentDetailModal';
import CompanyDetailModal from '@/components/modals/CompanyDetailModal';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import { ChevronDown } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardProps {
    dashboardSummary: {
        total_revenue: number;
        total_companies: number;
        active_subsegments: number;
        current_month_revenue: number;
        formatted_total_revenue: string;
        formatted_current_month_revenue: string;
        avg_revenue_per_company: number;
    };
    yearlyRevenue: Array<{
        tahun: number;
        total_revenue: number;
        total_companies: number;
        formatted_revenue: string;
    }>;
    monthlyRevenue: Array<{
        bulan: number;
        bulan_name: string;
        total_revenue: number;
        target_revenue: number;
        total_companies: number;
        formatted_revenue: string;
        formatted_target: string;
        achievement_percentage: number;
    }>;
    ytdComparison: {
        current_year: number;
        previous_year: number;
        current_ytd: number;
        previous_ytd: number;
        growth_percentage: number;
        growth_amount: number;
        formatted_current_ytd: string;
        formatted_previous_ytd: string;
        formatted_growth_amount: string;
        is_positive_growth: boolean;
    };
    subsegmentRevenue: Array<{
        subsegment: string;
        total_revenue: number;
        total_companies: number;
        avg_revenue: number;
        formatted_total_revenue: string;
        formatted_avg_revenue: string;
    }>;
    topCompanies: Array<{
        id: number;
        nip_nas: string;
        nama_perusahaan: string;
        subsegment: string;
        source_data: string;
        total_revenue: number;
        formatted_total_revenue: string;
    }>;
    currentYear: number;
}

export default function Dashboard({
    dashboardSummary,
    yearlyRevenue,
    monthlyRevenue,
    ytdComparison,
    subsegmentRevenue,
    topCompanies,
    currentYear
}: DashboardProps) {
    const [selectedMonth, setSelectedMonth] = useState<any>(null);
    const [selectedSubsegment, setSelectedSubsegment] = useState<string | null>(null);
    const [selectedCompany, setSelectedCompany] = useState<any>(null);
    const [monthModalOpen, setMonthModalOpen] = useState(false);
    const [subsegmentModalOpen, setSubsegmentModalOpen] = useState(false);
    const [companyModalOpen, setCompanyModalOpen] = useState(false);
    
    // Filter states
    const [monthlySortOrder, setMonthlySortOrder] = useState<'chronological' | 'asc' | 'desc'>('chronological');
    const [selectedYear, setSelectedYear] = useState<number>(currentYear);
    
    // Get available years from yearlyRevenue data
    const availableYears = useMemo(() => {
        return yearlyRevenue.map(y => y.tahun).sort((a, b) => b - a);
    }, [yearlyRevenue]);

    // Filtered and sorted data
    const sortedMonthlyRevenue = useMemo(() => {
        if (monthlySortOrder === 'chronological') {
            return monthlyRevenue; // Keep original order
        }
        return [...monthlyRevenue].sort((a, b) => {
            if (monthlySortOrder === 'asc') {
                return a.total_revenue - b.total_revenue;
            } else {
                return b.total_revenue - a.total_revenue;
            }
        });
    }, [monthlyRevenue, monthlySortOrder]);

    const handleMonthClick = (monthData: any) => {
        setSelectedMonth(monthData);
        setMonthModalOpen(true);
    };

    const handleYearChange = (year: number) => {
        setSelectedYear(year);
        // Reload data dengan tahun yang dipilih
        router.get(dashboard().url, { year }, { 
            preserveState: true,
            preserveScroll: true 
        });
    };

    const handleSubsegmentClick = (subsegmentData: any) => {
        setSelectedSubsegment(subsegmentData.subsegment);
        setSubsegmentModalOpen(true);
    };

    const handleSubsegmentClickFromMonth = (subsegment: string) => {
        setSelectedSubsegment(subsegment);
        setMonthModalOpen(false);
        setSubsegmentModalOpen(true);
    };

    const handleCompanyClick = (company: any) => {
        setSelectedCompany(company);
        setCompanyModalOpen(true);
    };

    const handleTopCompanyClick = (company: any) => {
        setSelectedCompany(company);
        setCompanyModalOpen(true);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Revenue Analytics" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6 bg-gray-50/30 dark:bg-gray-950/30">
                
                {/* Summary Cards */}
                {/* <div className="grid auto-rows-min gap-6 md:grid-cols-4">
                    <StatCard
                        title="Total Revenue YTD"
                        value={dashboardSummary.formatted_total_revenue}
                        subtitle={`${currentYear} Year-to-Date`}
                        trend={{
                            value: ytdComparison.growth_percentage,
                            isPositive: ytdComparison.is_positive_growth,
                            label: `vs ${ytdComparison.previous_year}`
                        }}
                        icon={
                            <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        }
                    />
                    
                    <StatCard
                        title="Active Companies"
                        value={dashboardSummary.total_companies}
                        subtitle={`${dashboardSummary.active_subsegments} Subsegments`}
                        icon={
                            <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        }
                    />
                    
                    <StatCard
                        title="Current Month"
                        value={dashboardSummary.formatted_current_month_revenue}
                        subtitle="Oktober 2025"
                        icon={
                            <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        }
                    />
                    
                    <StatCard
                        title="Avg per Company"
                        value={`Rp ${(dashboardSummary.avg_revenue_per_company / 1000000000).toFixed(1)}M`}
                        subtitle="Annual Average"
                        icon={
                            <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        }
                    />
                </div> */}

                {/* Charts Section */}
                <div className="grid auto-rows-min gap-6 lg:grid-cols-2">
                    {/* Monthly Revenue Chart */}
                    <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                        <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-gray-900 dark:text-gray-100">Monthly Revenue Trend</h3>
                                        <p className="text-sm text-gray-500 dark:text-gray-400">Click bars to view subsegment details</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    {/* Year Filter */}
                                    <div className="relative">
                                        <select
                                            value={selectedYear}
                                            onChange={(e) => handleYearChange(Number(e.target.value))}
                                            className="appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 pr-8 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                        >
                                            {availableYears.map(year => (
                                                <option key={year} value={year}>{year}</option>
                                            ))}
                                        </select>
                                        <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                                    </div>
                                    
                                    {/* Sort Order Filter */}
                                    <div className="relative">
                                        <select
                                            value={monthlySortOrder}
                                            onChange={(e) => setMonthlySortOrder(e.target.value as 'chronological' | 'asc' | 'desc')}
                                            className="appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 pr-8 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                        >
                                            <option value="chronological">Original</option>
                                            <option value="desc">Revenue: High to Low</option>
                                            <option value="asc">Revenue: Low to High</option>
                                        </select>
                                        <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                                    </div>
                                </div>
                            </div>
                            <RevenueBarChart 
                                data={sortedMonthlyRevenue} 
                                height={350}
                                onBarClick={handleMonthClick}
                            />
                        </div>
                    </div>

                    {/* Subsegment Breakdown */}
                    <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <div className="p-6">
                            <div className="flex items-center gap-3 mb-6">
                                <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                    <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 className="font-semibold text-gray-900 dark:text-gray-100">Revenue by Subsegment</h3>
                                    {/* <p className="text-sm text-gray-500 dark:text-gray-400">YTD {currentYear}</p> */}
                                </div>
                            </div>
                            <SubsegmentPieChart 
                                data={subsegmentRevenue} 
                                height={350}
                                onSegmentClick={handleSubsegmentClick}
                            />
                        </div>
                    </div>
                </div>

                {/* Yearly Trend and Top Companies */}
                <div className="grid auto-rows-min gap-6 lg:grid-cols-5">
                    {/* Yearly Trend */}
                    <div className="lg:col-span-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <div className="p-6">
                            <div className="flex items-center gap-3 mb-6">
                                <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                    <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 className="font-semibold text-gray-900 dark:text-gray-100">5-Year Revenue Trend</h3>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">Historical performance</p>
                                </div>
                            </div>
                            <YearlyLineChart data={yearlyRevenue} height={300} />
                        </div>
                    </div>

                    {/* Top & Lowest Performers */}
                    <div className="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <div className="p-6">
                            <div className="space-y-6">
                                {/* Top Performer */}
                                <div>
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 bg-green-50 dark:bg-green-950 rounded-lg">
                                            <svg className="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-gray-900 dark:text-gray-100">Top Performer</h3>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">{currentYear} YTD</p>
                                        </div>
                                    </div>
                                    {topCompanies.length > 0 && (
                                        <div 
                                            className="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/50 dark:to-emerald-950/50 border-2 border-green-200 dark:border-green-800 rounded-lg hover:shadow-md transition-all cursor-pointer"
                                            onClick={() => handleTopCompanyClick(topCompanies[0])}
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="flex items-center justify-center w-10 h-10 bg-green-600 dark:bg-green-700 text-white rounded-full text-base font-bold">
                                                    1
                                                </div>
                                                <div>
                                                    <p className="font-semibold text-gray-900 dark:text-gray-100 text-base leading-tight">{topCompanies[0].nama_perusahaan}</p>
                                                    <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">{topCompanies[0].subsegment}</p>
                                                    <p className="text-xs text-gray-500 dark:text-gray-500">{topCompanies[0].nip_nas}</p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="font-bold text-green-700 dark:text-green-400 text-base">{topCompanies[0].formatted_total_revenue}</p>
                                                <p className="text-xs text-gray-500 dark:text-gray-500 mt-1">Highest Revenue</p>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* Divider */}
                                <div className="border-t border-gray-200 dark:border-gray-700"></div>

                                {/* Lowest Performer */}
                                <div>
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 bg-orange-50 dark:bg-orange-950 rounded-lg">
                                            <svg className="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-gray-900 dark:text-gray-100">Lowest Performer</h3>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">{currentYear} YTD</p>
                                        </div>
                                    </div>
                                    {topCompanies.length > 0 && (
                                        <div 
                                            className="flex items-center justify-between p-4 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-950/50 dark:to-amber-950/50 border-2 border-orange-200 dark:border-orange-800 rounded-lg hover:shadow-md transition-all cursor-pointer"
                                            onClick={() => handleTopCompanyClick(topCompanies[topCompanies.length - 1])}
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="flex items-center justify-center w-10 h-10 bg-orange-600 dark:bg-orange-700 text-white rounded-full text-base font-bold">
                                                    {topCompanies.length}
                                                </div>
                                                <div>
                                                    <p className="font-semibold text-gray-900 dark:text-gray-100 text-base leading-tight">{topCompanies[topCompanies.length - 1].nama_perusahaan}</p>
                                                    <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">{topCompanies[topCompanies.length - 1].subsegment}</p>
                                                    <p className="text-xs text-gray-500 dark:text-gray-500">{topCompanies[topCompanies.length - 1].nip_nas}</p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="font-bold text-orange-700 dark:text-orange-400 text-base">{topCompanies[topCompanies.length - 1].formatted_total_revenue}</p>
                                                <p className="text-xs text-gray-500 dark:text-gray-500 mt-1">Lowest Revenue</p>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Drill-down Modals */}
                <MonthDetailModal
                    isOpen={monthModalOpen}
                    onClose={() => setMonthModalOpen(false)}
                    monthData={selectedMonth}
                    year={currentYear}
                    onSubsegmentClick={handleSubsegmentClickFromMonth}
                />

                <SubsegmentDetailModal
                    isOpen={subsegmentModalOpen}
                    onClose={() => setSubsegmentModalOpen(false)}
                    subsegment={selectedSubsegment}
                    year={currentYear}
                    month={selectedMonth?.bulan}
                    onCompanyClick={handleCompanyClick}
                />

                <CompanyDetailModal
                    isOpen={companyModalOpen}
                    onClose={() => setCompanyModalOpen(false)}
                    company={selectedCompany}
                />
            </div>
        </AppLayout>
    );
}
