import React, { useEffect, useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, MapPin } from 'lucide-react';
import axios from 'axios';
import WitelNkiDetailModal from './modals/WitelNkiDetailModal';
import ParameterPerformanceBalanceChart from './charts/ParameterPerformanceBalanceChart';
import SummaryCardsSection from './sections/SummaryCardsSection';
import { formatCurrency } from '@/utils/currency';

interface RegionNkiModalProps {
    isOpen: boolean;
    onClose: () => void;
    regionId: number;
    regionName: string;
    quarter: number;
    year: number;
}

interface SegmentStat {
    segment: string;
    result: {
        ach: number;
        not_ach: number;
    };
    proses: {
        ach: number;
        not_ach: number;
    };
    nki: {
        above_100: number;
        below_100: number;
    };
    highest_nki: number;
    lowest_nki: number;
    avg_nki: number;
    total_am: number;
    parameters_to_improve: string;
}

interface ParameterStat {
    parameter: string;
    bobot: number;
    ach: number;
    not_ach: number;
}

interface RegionNkiData {
    region: {
        id: number;
        name: string;
    };
    period?: {
        quarter: number;
        year: number;
    };
    summary?: {
        target_revenue: number;
        formatted_target_revenue: string;
        realisasi_revenue: number;
        formatted_realisasi_revenue: string;
        total_am: number;
    };
    segment_stats?: SegmentStat[];
    parameter_result?: {
        percentage_result: number;
        parameters: ParameterStat[];
    };
    parameter_proses?: {
        percentage_proses: number;
        parameters: ParameterStat[];
    };
    current_period?: {
        quarter: number;
        year: number;
        label: string;
        data: {
            summary: {
                target_revenue: number;
                formatted_target_revenue: string;
                realisasi_revenue: number;
                formatted_realisasi_revenue: string;
                total_am: number;
            };
            segment_stats: SegmentStat[];
            parameter_result: {
                percentage_result: number;
                parameters: ParameterStat[];
            };
            parameter_proses: {
                percentage_proses: number;
                parameters: ParameterStat[];
            };
        };
    };
    comparison_period?: {
        quarter: number;
        year: number;
        label: string;
        data: {
            summary: {
                target_revenue: number;
                formatted_target_revenue: string;
                realisasi_revenue: number;
                formatted_realisasi_revenue: string;
                total_am: number;
            };
            segment_stats: SegmentStat[];
            parameter_result: {
                percentage_result: number;
                parameters: ParameterStat[];
            };
            parameter_proses: {
                percentage_proses: number;
                parameters: ParameterStat[];
            };
        };
    };
    compare_enabled?: boolean;
}

export default function RegionNkiModal({ isOpen, onClose, regionId, regionName, quarter, year }: RegionNkiModalProps) {
    const [data, setData] = useState<RegionNkiData | null>(null);
    const [loading, setLoading] = useState(false);
    const [isTransitioning, setIsTransitioning] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [compareMode, setCompareMode] = useState(false);
    const [compareQuarter, setCompareQuarter] = useState(1);
    const [compareYear, setCompareYear] = useState(2025);
    const [hasInitializedCompare, setHasInitializedCompare] = useState(false);
    const [activeParameterTab, setActiveParameterTab] = useState<'result' | 'proses'>('result');
    const [activeChartTab, setActiveChartTab] = useState<'result' | 'proses'>('result');
    const [availableYears, setAvailableYears] = useState<number[]>([]);

    // Handler untuk sinkronisasi tab Parameter dan Chart
    const handleTabChange = (tab: 'result' | 'proses') => {
        setActiveParameterTab(tab);
        setActiveChartTab(tab);
    };
    const [availableQuarters, setAvailableQuarters] = useState<number[]>([1, 2, 3, 4]);
    const [quartersByYear, setQuartersByYear] = useState<Record<number, number[]>>({});
    const [chartData, setChartData] = useState<any[]>([]);
    const [showDetailModal, setShowDetailModal] = useState(false);
    const [selectedSegment, setSelectedSegment] = useState<string>('');
    const [selectedWitelId, setSelectedWitelId] = useState<number | undefined>(undefined);
    const [showFutureErrorModal, setShowFutureErrorModal] = useState(false);

    // Fetch available periods from API
    useEffect(() => {
        const fetchAvailablePeriods = async () => {
            try {
                const response = await axios.get('/api/dashboard/region-nki-periods');
                if (response.data.success) {
                    const years = response.data.data.years;
                    const qByYear = response.data.data.quarters_by_year;
                    
                    setAvailableYears(years);
                    setQuartersByYear(qByYear);
                    
                    // Initialize compare period with valid values from available data
                    if (years.length > 0 && !hasInitializedCompare) {
                        let validYear = year;
                        let validQuarter: number;
                        
                        // First, try to use previous quarter in the same year
                        if (quarter > 1) {
                            validQuarter = quarter - 1;
                        } else {
                            // Current quarter is Q1, check if previous year exists
                            const previousYear = year - 1;
                            if (years.includes(previousYear) && qByYear[previousYear]) {
                                // Previous year exists, use Q4 of previous year
                                validYear = previousYear;
                                const quartersInPrevYear = qByYear[previousYear];
                                validQuarter = Math.max(...quartersInPrevYear); // Get highest quarter available
                            } else {
                                // Previous year doesn't exist, stay in current year
                                // But we can't use current quarter, so find the highest available quarter < current
                                const quartersInCurrentYear = qByYear[year] || [1, 2, 3, 4];
                                const lowerQuarters = quartersInCurrentYear.filter(q => q < quarter);
                                if (lowerQuarters.length > 0) {
                                    validQuarter = Math.max(...lowerQuarters);
                                } else {
                                    // No lower quarter available, use the last available quarter
                                    validQuarter = Math.max(...quartersInCurrentYear.filter(q => q !== quarter));
                                    if (!validQuarter) {
                                        validQuarter = quartersInCurrentYear[0];
                                    }
                                }
                            }
                        }
                        
                        // Ensure the selected year exists in available years
                        if (!years.includes(validYear)) {
                            validYear = years.find(y => y <= year) || years[0];
                        }
                        
                        // Ensure quarter is valid for selected year
                        const quartersForYear = qByYear[validYear] || [1, 2, 3, 4];
                        if (!quartersForYear.includes(validQuarter)) {
                            validQuarter = quartersForYear[quartersForYear.length - 1] || 1;
                        }
                        
                        setCompareYear(validYear);
                        setCompareQuarter(validQuarter);
                        setAvailableQuarters(quartersForYear);
                        setHasInitializedCompare(true);
                    } else if (qByYear[compareYear]) {
                        // Update quarters for current compareYear
                        setAvailableQuarters(qByYear[compareYear]);
                    }
                }
            } catch (err) {
                // Fallback to default values (include current and future years for testing)
                setAvailableYears([year + 2, year + 1, year, year - 1, year - 2, year - 3, year - 4, year - 5]);
                setAvailableQuarters([1, 2, 3, 4]);
            }
        };

        if (isOpen) {
            fetchAvailablePeriods();
        }
    }, [isOpen, year]);

    // Update available quarters when compareYear changes
    useEffect(() => {
        if (quartersByYear[compareYear]) {
            setAvailableQuarters(quartersByYear[compareYear]);
            
            // Auto-adjust compareQuarter if not available in new year
            if (!quartersByYear[compareYear].includes(compareQuarter)) {
                const firstAvailable = quartersByYear[compareYear][0];
                if (firstAvailable) {
                    setCompareQuarter(firstAvailable);
                }
            }
        }
    }, [compareYear, quartersByYear]);

    useEffect(() => {
        if (isOpen && regionId) {
            setCompareMode(false);
            fetchData();
            fetchChartData();
            // Close detail modal when period changes
            if (showDetailModal) {
                setShowDetailModal(false);
                setSelectedSegment('');
                setSelectedWitelId(undefined);
            }
        } else if (!isOpen) {
            // Reset flag when modal closes so compare period gets reinitialized on next open
            setHasInitializedCompare(false);
        }
    }, [isOpen, regionId, quarter, year]);
    
    // Close detail modal when parent modal closes
    useEffect(() => {
        if (!isOpen && showDetailModal) {
            setShowDetailModal(false);
            setSelectedSegment('');
            setSelectedWitelId(undefined);
        }
    }, [isOpen]);

    // Refetch chart data when compare mode or compare period changes
    useEffect(() => {
        if (isOpen && regionId) {
            fetchChartData();
        }
    }, [compareMode, compareQuarter, compareYear]);

    const fetchData = async (forceDisableCompare: boolean = false) => {
        setLoading(true);
        setError(null);
        try {
            const params: any = { quarter, year };
            
            // Only add compare params if compareMode is true AND not forcing disable
            if (compareMode && !forceDisableCompare) {
                params.compare = true;
                params.compare_quarter = compareQuarter;
                params.compare_year = compareYear;
            }
            
            const response = await axios.get(`/api/dashboard/region-nki/${regionId}`, { params });
            
            setData(response.data);
        } catch (error: any) {
            setError(error.response?.data?.error || error.response?.data?.message || 'Failed to load data');
        } finally {
            setLoading(false);
        }
    };

    const fetchChartData = async () => {
        try {
            // Fetch current period data
            const response = await axios.get(`/api/dashboard/region-nki-chart/${regionId}`, {
                params: { quarter, year }
            });
            
            if (response.data.success) {
                const currentData = response.data.data.parameters;
                
                // If compare mode is enabled, fetch comparison period data
                if (compareMode && !(compareQuarter === quarter && compareYear === year)) {
                    try {
                        const compareResponse = await axios.get(`/api/dashboard/region-nki-chart/${regionId}`, {
                            params: { quarter: compareQuarter, year: compareYear }
                        });
                        
                        if (compareResponse.data.success) {
                            const compareData = compareResponse.data.data.parameters;
                            
                            // Merge data: add comparison achievement to each parameter
                            const mergedData = currentData.map((curr: any) => {
                                const comp = compareData.find((c: any) => c.parameter === curr.parameter);
                                return {
                                    parameter: curr.parameter,
                                    target: curr.target,
                                    realisasi: curr.realisasi,
                                    bobot: curr.bobot,
                                    ach: curr.ach,
                                    ach_current: curr.ach,
                                    avg_ach_per_am: curr.avg_ach_per_am,  // NEW: Include avg_ach_per_am
                                    ach_compare: comp ? comp.ach : 0,
                                    avg_ach_per_am_compare: comp ? comp.avg_ach_per_am : 0,  // NEW: Include compare value
                                    target_compare: comp ? comp.target : 0,
                                    realisasi_compare: comp ? comp.realisasi : 0,
                                    bobot_compare: comp ? comp.bobot : 0
                                };
                            });
                            
                            setChartData(mergedData);
                            return;
                        }
                    } catch (error) {
                        // Silent error handling
                    }
                }
                
                // No compare mode or error: show only current data (clean data without compare fields)
                const dataWithCurrent = currentData.map((item: any) => ({
                    parameter: item.parameter,
                    target: item.target,
                    realisasi: item.realisasi,
                    bobot: item.bobot,
                    ach: item.ach,
                    ach_current: item.ach,
                    avg_ach_per_am: item.avg_ach_per_am  // NEW: Include avg_ach_per_am
                    // Explicitly not including ach_compare, target_compare, etc.
                }));
                setChartData(dataWithCurrent);
            }
        } catch (error) {
            // Silent error handling
        }
    };

    const handleCompareToggle = async () => {
        const newMode = !compareMode;
        
        if (!newMode) {
            // Disabling compare mode
            setIsTransitioning(true);
            setCompareMode(newMode);
            setError(null);
            
            await Promise.all([
                fetchData(true), // forceDisableCompare = true
                fetchChartData()
            ]);
            
            setIsTransitioning(false);
        } else {
            // Enabling compare mode
            setCompareMode(newMode);
        }
    };

    const handleCloseFutureErrorModal = () => {
        setShowFutureErrorModal(false);
        setCompareMode(false);
        
        // Reset to a valid previous period that exists in database
        let validYear = year;
        let validQuarter: number;
        
        // Try previous quarter in same year first
        if (quarter > 1) {
            validQuarter = quarter - 1;
        } else {
            // Current is Q1, check if previous year exists
            const previousYear = year - 1;
            if (availableYears.includes(previousYear) && quartersByYear[previousYear]) {
                // Previous year exists, use highest quarter from that year
                validYear = previousYear;
                const quartersInPrevYear = quartersByYear[previousYear];
                validQuarter = Math.max(...quartersInPrevYear);
            } else {
                // Previous year doesn't exist, stay in current year
                // Find any quarter lower than current quarter
                const quartersInCurrentYear = quartersByYear[year] || [1, 2, 3, 4];
                const lowerQuarters = quartersInCurrentYear.filter(q => q < quarter);
                if (lowerQuarters.length > 0) {
                    validQuarter = Math.max(...lowerQuarters);
                } else {
                    // No valid compare period available, just use current quarter (will show warning)
                    validQuarter = quarter;
                    validYear = year;
                }
            }
        }
        
        setCompareQuarter(validQuarter);
        setCompareYear(validYear);
    };

    const handleApplyCompare = async () => {
        // Validate: Cannot compare with future periods
        const isFuturePeriod = compareYear > year || (compareYear === year && compareQuarter > quarter);
        
        if (isFuturePeriod) {
            setShowFutureErrorModal(true);
            return;
        }
        
        // Validate: Cannot compare with the same period
        if (compareQuarter === quarter && compareYear === year) {
            // Inline warning will be shown, no need to set error
            return;
        }
        
        setIsTransitioning(true);
        setError(null);
        
        await Promise.all([
            fetchData(),
            fetchChartData()
        ]);
        
        setIsTransitioning(false);
    };

    const getCurrentSummary = () => {
        if (data?.compare_enabled && data?.current_period) {
            return data.current_period.data.summary;
        }
        return data?.summary;
    };

    const getCurrentSegmentStats = () => {
        if (data?.compare_enabled && data?.current_period) {
            return data.current_period.data.segment_stats || [];
        }
        return data?.segment_stats || [];
    };

    const getCurrentParameterResult = () => {
        if (data?.compare_enabled && data?.current_period) {
            return data.current_period.data.parameter_result;
        }
        return data?.parameter_result;
    };

    const getCurrentParameterProses = () => {
        if (data?.compare_enabled && data?.current_period) {
            return data.current_period.data.parameter_proses;
        }
        return data?.parameter_proses;
    };

    const handleSegmentRowClick = (segment: string) => {
        if (!data?.compare_enabled) {
            setSelectedSegment(segment);
            setShowDetailModal(true);
        }
    };

    const getCurrentPeriodLabel = () => {
        if (data?.compare_enabled && data?.current_period) {
            return data.current_period.label;
        }
        if (data?.period) {
            return `${getQuarterText(data.period.quarter)} ${data.period.year}`;
        }
        return '';
    };

    const getQuarterText = (q: number) => {
        return `Q${q}`;
    };

    const TrendIndicator = ({ current, previous }: { current: number; previous: number | null }) => {
        if (previous === null || previous === undefined) {
            return <span className="font-bold text-gray-900 dark:text-gray-100">{current}</span>;
        }
        
        if (current > previous) {
            return (
                <span className="flex items-center justify-center gap-1 font-bold text-green-600 dark:text-green-400">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    {current}
                </span>
            );
        } else if (current < previous) {
            return (
                <span className="flex items-center justify-center gap-1 font-bold text-red-600 dark:text-red-400">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                    {current}
                </span>
            );
        }
        
        return <span className="font-bold text-gray-900 dark:text-gray-100">{current}</span>;
    };

    const TrendIndicatorNKI = ({ current, previous }: { current: number; previous: number | null }) => {
        if (previous === null || previous === undefined) {
            return <span className="text-sm font-bold">{current.toFixed(2)}%</span>;
        }
        
        if (current > previous) {
            return (
                <span className="flex items-center justify-center gap-1 text-sm font-bold text-green-600 dark:text-green-400">
                    <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    {current.toFixed(2)}%
                </span>
            );
        } else if (current < previous) {
            return (
                <span className="flex items-center justify-center gap-1 text-sm font-bold text-red-600 dark:text-red-400">
                    <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                    {current.toFixed(2)}%
                </span>
            );
        }
        
        return <span className="text-sm font-bold">{current.toFixed(2)}%</span>;
    };

    // Custom tooltip formatter for chart
    const CustomTooltip = ({ active, payload }: any) => {
        if (active && payload && payload.length) {
            const data = payload[0].payload;
            // Use compareMode state instead of checking data property
            const isCompareMode = compareMode && data.ach_compare !== undefined;
            
            // Format value based on parameter type
            const formatValue = (value: number, parameter: string) => {
                // Check if value is in Billion or Million range
                if (value >= 1000000000) {
                    // Billion (Miliar) - divide by 1,000,000,000
                    const billionValue = value / 1000000000;
                    if (parameter === 'Revenue' || parameter.toLowerCase().includes('revenue')) {
                        return `Rp ${billionValue.toFixed(2)}B`;
                    } else {
                        return `${billionValue.toFixed(2)}B`;
                    }
                } else if (value >= 1000000) {
                    // Million (Juta) - divide by 1,000,000
                    const millionValue = value / 1000000;
                    if (parameter === 'Revenue' || parameter.toLowerCase().includes('revenue')) {
                        return `Rp ${millionValue.toFixed(2)}M`;
                    } else {
                        return `${millionValue.toFixed(2)}M`;
                    }
                } else if (value >= 1000) {
                    // Thousand (Ribu) - divide by 1,000
                    const thousandValue = value / 1000;
                    if (parameter === 'Revenue' || parameter.toLowerCase().includes('revenue')) {
                        return `Rp ${thousandValue.toFixed(2)}K`;
                    } else {
                        return `${thousandValue.toFixed(2)}K`;
                    }
                } else {
                    // Less than thousand - show as is
                    if (parameter === 'Revenue' || parameter.toLowerCase().includes('revenue')) {
                        return `Rp ${value.toLocaleString('id-ID', { maximumFractionDigits: 2 })}`;
                    } else {
                        return value.toLocaleString('id-ID', { maximumFractionDigits: 2 });
                    }
                }
            };
            
            return (
                <div
                    style={{
                        backgroundColor: '#ffffff',
                        border: '1px solid #e5e7eb',
                        borderRadius: '8px',
                        boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                        padding: '12px',
                        color: '#374151',
                        minWidth: '200px'
                    }}
                >
                    <p style={{ fontWeight: 600, marginBottom: '8px', color: '#374151', borderBottom: '1px solid #e5e7eb', paddingBottom: '4px' }}>
                        {data.parameter}
                    </p>
                    
                    {/* Current Period Data */}
                    <div style={{ marginBottom: isCompareMode ? '8px' : '4px' }}>
                        {isCompareMode && (
                            <p style={{ margin: '0 0 4px 0', fontSize: '11px', fontWeight: 600, color: '#6B7280' }}>
                                Q{quarter} {year}
                            </p>
                        )}
                        <p style={{ margin: '4px 0', color: '#000000', fontSize: '12px', fontWeight: 700 }}>
                            Target: {formatValue(data.target || 0, data.parameter)}
                        </p>
                        {(() => {
                            const currentAch = Number(data.ach_current) || 0;
                            const bobot = Number(data.bobot) || 0;
                            const isAchieved = currentAch >= bobot;
                            const realisasiColor = isAchieved ? '#10b981' : '#ef4444';
                            
                            return (
                                <p style={{ margin: '4px 0', color: realisasiColor, fontSize: '12px', fontWeight: 700 }}>
                                    Realisasi: {formatValue(data.realisasi || 0, data.parameter)}
                                </p>
                            );
                        })()}
                    </div>
                    
                    {/* Comparison Period Data (if enabled) */}
                    {isCompareMode && (
                        <>
                            <div style={{ borderTop: '1px solid #e5e7eb', marginTop: '8px', paddingTop: '8px' }}>
                                <p style={{ margin: '0 0 4px 0', fontSize: '11px', fontWeight: 600, color: '#6B7280' }}>
                                    Q{compareQuarter} {compareYear}
                                </p>
                                <p style={{ margin: '4px 0', color: '#000000', fontSize: '12px', fontWeight: 700 }}>
                                    Target: {formatValue(data.target_compare || 0, data.parameter)}
                                </p>
                                {(() => {
                                    const compareAch = Number(data.ach_compare) || 0;
                                    const bobotCompare = Number(data.bobot_compare) || Number(data.bobot) || 0;
                                    const isAchievedCompare = compareAch >= bobotCompare;
                                    const realisasiColor = isAchievedCompare ? '#10b981' : '#ef4444';
                                    
                                    return (
                                        <p style={{ margin: '4px 0', color: realisasiColor, fontSize: '12px', fontWeight: 700 }}>
                                            Realisasi: {formatValue(data.realisasi_compare || 0, data.parameter)}
                                        </p>
                                    );
                                })()}
                            </div>
                        </>
                    )}
                </div>
            );
        }
        return null;
    };


    return (
        <>
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                {/* Future Period Error Overlay */}
                {showFutureErrorModal && (
                    <div 
                        className="fixed inset-0 bg-black/60 z-[9999] flex items-center justify-center"
                        style={{ margin: 0 }}
                    >
                        <div className="bg-white dark:bg-gray-900 rounded-lg shadow-2xl max-w-md w-full mx-4 p-6 border-2 border-red-500 animate-in fade-in zoom-in duration-200">
                            <div className="flex items-center gap-2 mb-4 text-red-600 dark:text-red-400">
                                <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                </svg>
                                <h3 className="text-lg font-semibold">Periode Perbandingan Tidak Valid</h3>
                            </div>
                            
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Tidak dapat membandingkan dengan periode masa depan
                            </p>
                            
                            <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                                <p className="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    <strong>Periode Saat Ini:</strong> Q{quarter} {year}
                                </p>
                                <p className="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                    <strong>Periode yang Dipilih:</strong> Q{compareQuarter} {compareYear}
                                </p>
                                <div className="flex items-start gap-2 mt-3 pt-3 border-t border-red-200 dark:border-red-800">
                                    <svg className="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                                    </svg>
                                    <p className="text-sm text-red-700 dark:text-red-300">
                                        Perbandingan data hanya dapat dilakukan dengan periode waktu sebelumnya. Silakan pilih periode yang lebih awal dari Q{quarter} {year}.
                                    </p>
                                </div>
                            </div>
                            
                            <div className="flex justify-end">
                                <Button 
                                    onClick={handleCloseFutureErrorModal}
                                    className="px-6 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800"
                                >
                                    Mengerti
                                </Button>
                            </div>
                        </div>
                    </div>
                )}
                
                {/* Transitioning Overlay */}
                {isTransitioning && (
                    <div className="absolute inset-0 bg-white/70 dark:bg-gray-900/70 backdrop-blur-sm z-50 flex items-center justify-center rounded-lg">
                        <div className="flex flex-col items-center gap-3">
                            <div className="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                            <p className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Updating data...
                            </p>
                        </div>
                    </div>
                )}
                
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <MapPin className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">
                            NKI AM DSS {regionName}
                        </span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Regional performance indicators and segment achievement analysis
                    </DialogDescription>
                </DialogHeader>

                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading region NKI data...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4 mb-6">
                        <p className="text-red-700 dark:text-red-300">{error}</p>
                        <Button 
                            onClick={() => fetchData()}
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
                        {/* Compare Button */}
                        <div className="mb-4 flex items-center gap-3">
                            <Button
                                onClick={handleCompareToggle}
                                variant={compareMode ? "default" : "outline"}
                                size="sm"
                                className="flex items-center gap-2"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                {compareMode ? 'Disable Compare' : 'Enable Compare'}
                            </Button>

                            {compareMode && (
                                <>
                                    <div className="flex items-center gap-2 flex-wrap">
                                        <span className="text-sm text-gray-600 dark:text-gray-400">Compare with:</span>
                                        <select
                                            value={compareQuarter}
                                            onChange={(e) => setCompareQuarter(Number(e.target.value))}
                                            className="px-3 py-1 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                                        >
                                            {availableQuarters.map((q) => (
                                                <option key={q} value={q}>
                                                    Q{q}{compareYear === year && quarter === q ? ' (Current)' : ''}
                                                </option>
                                            ))}
                                        </select>
                                        <select
                                            value={compareYear}
                                            onChange={(e) => setCompareYear(Number(e.target.value))}
                                            className="px-3 py-1 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                                        >
                                            {availableYears.map((y) => (
                                                <option key={y} value={y}>{y}{y === year ? ' (Current Year)' : ''}</option>
                                            ))}
                                        </select>
                                        <Button
                                            onClick={(e) => {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                
                                                const currentQ = Number(quarter);
                                                const currentY = Number(year);
                                                const selectedQ = Number(compareQuarter);
                                                const selectedY = Number(compareYear);
                                                
                                                // Check for future period
                                                const isFuture = selectedY > currentY || (selectedY === currentY && selectedQ > currentQ);
                                                
                                                if (isFuture) {
                                                    setShowFutureErrorModal(true);
                                                    return;
                                                }
                                                
                                                // Check for same period
                                                if (selectedQ === currentQ && selectedY === currentY) {
                                                    return;
                                                }
                                                
                                                // Valid past period - proceed
                                                handleApplyCompare();
                                            }}
                                            size="sm"
                                            className="ml-2"
                                        >
                                            Apply
                                        </Button>
                                    </div>
                                    {compareQuarter === quarter && compareYear === year && (
                                        <div className="flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md">
                                            <svg className="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                            </svg>
                                            <span className="text-sm text-amber-700 dark:text-amber-300">Please select a different period to compare</span>
                                        </div>
                                    )}
                                </>
                            )}
                        </div>

                        {/* Summary Cards */}
                        <SummaryCardsSection 
                            currentSummary={getCurrentSummary()}
                            periodLabel={getCurrentPeriodLabel()}
                        />

                        {/* Content: Table, Chart, and Parameter Section */}
                        <div className="grid grid-cols-1 lg:grid-cols-20 gap-6 auto-rows-auto">
                            {/* Left Column: Summary Table + Chart - 65% (13 columns) */}
                            <div className="lg:col-span-13 space-y-6">
                                {/* Summary NKI AM Table */}
                                <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                                    <div className="bg-white dark:bg-gray-900 px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            <svg className="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Summary NKI AM {getCurrentPeriodLabel()}
                                        </h3>
                                    </div>
                                
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="bg-gray-100 dark:bg-gray-800 border-b-2 border-gray-300 dark:border-gray-700">
                                                <th rowSpan={2} className="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700 sticky left-0 bg-gray-100 dark:bg-gray-800 z-10">
                                                    {data.compare_enabled ? 'Triwulan' : 'Segments'}
                                                </th>
                                                <th colSpan={2} className="px-4 py-2 text-center text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700">
                                                    Result
                                                </th>
                                                <th colSpan={2} className="px-4 py-2 text-center text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700">
                                                    Proses
                                                </th>
                                                <th colSpan={2} className="px-4 py-2 text-center text-xs font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700">
                                                    NKI
                                                </th>
                                                <th colSpan={3} className="px-4 py-2 text-center text-xs font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700">
                                                    NKI Statistics
                                                </th>
                                                <th rowSpan={2} className="px-4 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Parameter To Be Improve
                                                </th>
                                            </tr>
                                            <tr className="bg-gray-50 dark:bg-gray-850 border-b border-gray-300 dark:border-gray-700">
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">
                                                    Ach
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400 border-r border-gray-300 dark:border-gray-700">
                                                    Not Ach
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">
                                                    Ach
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400 border-r border-gray-300 dark:border-gray-700">
                                                    Not Ach
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">
                                                    &gt;100%
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400 border-r border-gray-300 dark:border-gray-700">
                                                    &lt;100%
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                                                    Highest
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                                                    Lowest
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-700">
                                                    Average
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                            {getCurrentSegmentStats().length > 0 ? (
                                                data.compare_enabled ? (
                                                    <>
                                                        {/* Compare Mode: Show current and comparison period */}
                                                        {data.current_period && data.current_period.data.segment_stats.map((stat, index) => (
                                                            <tr key={`current-${index}`} className="hover:bg-gray-50 dark:hover:bg-gray-850 transition-colors">
                                                                <td className="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-800 sticky left-0 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-850">
                                                                    <div className="flex items-center gap-2">
                                                                        <div className="w-2 h-2 rounded-full bg-red-500"></div>
                                                                        {data.current_period?.label}
                                                                    </div>
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                                    <TrendIndicator 
                                                                        current={stat.result.ach} 
                                                                        previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.result.ach : null}
                                                                    />
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                                    <TrendIndicator 
                                                                        current={stat.result.not_ach} 
                                                                        previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.result.not_ach : null}
                                                                    />
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                                    <TrendIndicator 
                                                                        current={stat.proses.ach} 
                                                                        previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.proses.ach : null}
                                                                    />
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                                    <TrendIndicator 
                                                                        current={stat.proses.not_ach} 
                                                                        previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.proses.not_ach : null}
                                                                    />
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                                    <TrendIndicator 
                                                                        current={stat.nki.above_100} 
                                                                        previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.nki.above_100 : null}
                                                                    />
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                                    <TrendIndicator 
                                                                        current={stat.nki.below_100} 
                                                                        previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.nki.below_100 : null}
                                                                    />
                                                                </td>
                                                                <td className="px-3 py-3 text-center border-r border-gray-200 dark:border-gray-800">
                                                                    <div className="flex flex-col items-center">
                                                                        <TrendIndicatorNKI 
                                                                            current={stat.highest_nki} 
                                                                            previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.highest_nki : null}
                                                                        />
                                                                    </div>
                                                                </td>
                                                                <td className="px-3 py-3 text-center border-r border-gray-200 dark:border-gray-800">
                                                                    <div className="flex flex-col items-center">
                                                                        <TrendIndicatorNKI 
                                                                            current={stat.lowest_nki} 
                                                                            previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.lowest_nki : null}
                                                                        />
                                                                    </div>
                                                                </td>
                                                                <td className="px-3 py-3 text-center border-r border-gray-300 dark:border-gray-700">
                                                                    <div className="flex flex-col items-center">
                                                                        <TrendIndicatorNKI 
                                                                            current={stat.avg_nki} 
                                                                            previous={data.comparison_period ? data.comparison_period.data.segment_stats[index]?.avg_nki : null}
                                                                        />
                                                                    </div>
                                                                </td>
                                                                <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                                    {stat.parameters_to_improve || '-'}
                                                                </td>
                                                            </tr>
                                                        ))}

                                                        {/* Comparison Period Row */}
                                                        {data.comparison_period && data.comparison_period.data.segment_stats.map((stat, index) => (
                                                            <tr key={`comparison-${index}`} className="bg-gray-50 dark:bg-gray-850 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                                                <td className="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800 sticky left-0 bg-gray-50 dark:bg-gray-850 hover:bg-gray-100 dark:hover:bg-gray-800">
                                                                    <div className="flex items-center gap-2">
                                                                        <div className="w-2 h-2 rounded-full bg-gray-400"></div>
                                                                        {data.comparison_period?.label}
                                                                    </div>
                                                                </td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800">{stat.result.ach}</td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700">{stat.result.not_ach}</td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800">{stat.proses.ach}</td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700">{stat.proses.not_ach}</td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800">{stat.nki.above_100}</td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700">{stat.nki.below_100}</td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800">{stat.highest_nki.toFixed(2)}%</td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800">{stat.lowest_nki.toFixed(2)}%</td>
                                                                <td className="px-3 py-3 text-center text-gray-600 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700">{stat.avg_nki.toFixed(2)}%</td>
                                                                <td className="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{stat.parameters_to_improve || '-'}</td>
                                                            </tr>
                                                        ))}
                                                    </>
                                                ) : (
                                                    <>
                                                        {/* Default Mode: Show segments only */}
                                                        {getCurrentSegmentStats().map((stat, index) => (
                                                            <tr 
                                                                key={index} 
                                                                onClick={() => handleSegmentRowClick(stat.segment)}
                                                                className="hover:bg-gray-50 dark:hover:bg-gray-850 transition-colors cursor-pointer"
                                                            >
                                                                <td className="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-800 sticky left-0 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-850">
                                                                    <div className="flex items-center gap-2">
                                                                        <div className="w-2 h-2 rounded-full bg-red-500"></div>
                                                                        {stat.segment || 'N/A'}
                                                                    </div>
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                                    <span className="text-green-700 dark:text-green-400 font-bold">{stat.result.ach}</span>
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                                    <span className="text-red-700 dark:text-red-400 font-bold">{stat.result.not_ach}</span>
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                                    <span className="text-green-700 dark:text-green-400 font-bold">{stat.proses.ach}</span>
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                                    <span className="text-red-700 dark:text-red-400 font-bold">{stat.proses.not_ach}</span>
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                                    <span className="text-green-700 dark:text-green-400 font-bold">{stat.nki.above_100}</span>
                                                                </td>
                                                                <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                                    <span className="text-red-700 dark:text-red-400 font-bold">{stat.nki.below_100}</span>
                                                                </td>
                                                                <td className="px-3 py-3 text-center border-r border-gray-200 dark:border-gray-800">
                                                                    <span className="text-sm font-bold text-green-600 dark:text-green-400">
                                                                        {typeof stat.highest_nki === 'number' ? stat.highest_nki.toFixed(2) : '0.00'}%
                                                                    </span>
                                                                </td>
                                                                <td className="px-3 py-3 text-center border-r border-gray-200 dark:border-gray-800">
                                                                    <span className="text-sm font-bold text-red-600 dark:text-red-400">
                                                                        {typeof stat.lowest_nki === 'number' ? stat.lowest_nki.toFixed(2) : '0.00'}%
                                                                    </span>
                                                                </td>
                                                                <td className="px-3 py-3 text-center border-r border-gray-300 dark:border-gray-700">
                                                                    <span className="text-sm font-bold text-blue-600 dark:text-blue-400">
                                                                        {typeof stat.avg_nki === 'number' ? stat.avg_nki.toFixed(2) : '0.00'}%
                                                                    </span>
                                                                </td>
                                                                <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                                    {stat.parameters_to_improve || '-'}
                                                                </td>
                                                            </tr>
                                                        ))}
                                                    </>
                                                )
                                            ) : (
                                                <tr>
                                                    <td colSpan={11} className="px-4 py-8 text-center">
                                                        <div className="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                                            <svg className="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                            </svg>
                                                            <p className="text-sm font-medium">No segment data available for this region</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                                </div>

                                {/* Parameter Performance Balance Chart */}
                                {chartData.length > 0 && (
                                    <ParameterPerformanceBalanceChart
                                        chartData={chartData}
                                        activeChartTab={activeChartTab}
                                        setActiveChartTab={handleTabChange}
                                        quarter={quarter}
                                        year={year}
                                        compareMode={compareMode}
                                        compareQuarter={compareQuarter}
                                        compareYear={compareYear}
                                        resultParamNames={data?.compare_enabled && data?.current_period 
                                            ? data.current_period.data.parameter_result.parameters.map((p: any) => p.parameter)
                                            : data?.parameter_result?.parameters.map((p: any) => p.parameter) || []}
                                        prosesParamNames={data?.compare_enabled && data?.current_period
                                            ? data.current_period.data.parameter_proses.parameters.map((p: any) => p.parameter)
                                            : data?.parameter_proses?.parameters.map((p: any) => p.parameter) || []}
                                    />
                                )}
                            </div>

                            {/* Right: Parameter Section & Top Parameter - 35% (7 columns) */}
                            <div className="lg:col-span-7 flex flex-col gap-6">
                                {/* Top Parameter Section */}
                                <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                                    <div className="bg-white dark:bg-gray-900 px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                            <svg className="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                            </svg>
                                            Top Parameter
                                        </h3>
                                        
                                        {/* Tab Navigation */}
                                        <div className="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                                            <button
                                                onClick={() => handleTabChange('result')}
                                                className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                                                    activeParameterTab === 'result'
                                                        ? 'text-amber-600 dark:text-amber-400'
                                                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                                                }`}
                                            >
                                                Aspek Result
                                                {activeParameterTab === 'result' && (
                                                    <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-600 dark:bg-amber-400"></div>
                                                )}
                                            </button>
                                            <button
                                                onClick={() => handleTabChange('proses')}
                                                className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                                                    activeParameterTab === 'proses'
                                                        ? 'text-amber-600 dark:text-amber-400'
                                                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                                                }`}
                                            >
                                                Aspek Process
                                                {activeParameterTab === 'proses' && (
                                                    <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-600 dark:bg-amber-400"></div>
                                                )}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div className="p-5">
                                        {activeParameterTab === 'result' && (() => {
                                            // Get result parameters from chartData (filtered by resultParamNames)
                                            const resultParamNames = data?.compare_enabled && data?.current_period 
                                                ? data.current_period.data.parameter_result.parameters.map((p: any) => p.parameter)
                                                : data?.parameter_result?.parameters.map((p: any) => p.parameter) || [];
                                            
                                            const resultChartData = chartData.filter((item) => resultParamNames.includes(item.parameter));
                                            
                                            const topParam = resultChartData.length > 0 
                                                ? resultChartData.reduce((max, param) => {
                                                    // Use ach (achievement percentage) as the metric for top parameter
                                                    const maxAch = Number(max.ach_current || max.ach || 0);
                                                    const paramAch = Number(param.ach_current || param.ach || 0);
                                                    return paramAch > maxAch ? param : max;
                                                })
                                                : null;
                                            
                                            if (!topParam) {
                                                return (
                                                    <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                                                        No parameter data available
                                                    </div>
                                                );
                                            }
                                            
                                            // Use avg_ach_per_am if available (in decimal format, needs * 100)
                                            // Otherwise use ach_current/ach which is already in percentage format
                                            const achievementPercentage = topParam.avg_ach_per_am 
                                                ? Number(topParam.avg_ach_per_am) * 100 
                                                : Number(topParam.ach_current || topParam.ach || 0);
                                            
                                            // Format realisasi based on parameter type
                                            const formatRealisasi = (value: number, parameter: string) => {
                                                const paramLower = parameter.toLowerCase();
                                                if (paramLower.includes('revenue') || paramLower.includes('scaling') || paramLower.includes('kecukupan lop')) {
                                                    return formatCurrency(value, 2);
                                                } else {
                                                    return value.toLocaleString('id-ID', { maximumFractionDigits: 2 });
                                                }
                                            };
                                            
                                            return (
                                                <div className="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-lg p-6 border-2 border-amber-200 dark:border-amber-700">
                                                    <div className="flex items-start justify-between mb-4">
                                                        <div className="flex items-center gap-2">
                                                            <div className="w-10 h-10 bg-amber-500 dark:bg-amber-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                                1
                                                            </div>
                                                            <div>
                                                                <h4 className="font-bold text-lg text-gray-900 dark:text-white">{topParam.parameter}</h4>
                                                                <p className="text-xs text-gray-600 dark:text-gray-400">Best Performance</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-4 mt-4">
                                                        <div className="bg-white dark:bg-gray-800 rounded-lg p-3">
                                                            <p className="text-xs text-gray-600 dark:text-gray-400 mb-1">Realisasi</p>
                                                            <p className="text-lg font-bold text-gray-900 dark:text-white">
                                                                {formatRealisasi(Number(topParam.realisasi || 0), topParam.parameter)}
                                                            </p>
                                                        </div>
                                                        <div className="bg-white dark:bg-gray-800 rounded-lg p-3">
                                                            <p className="text-xs text-gray-600 dark:text-gray-400 mb-1">Persentase</p>
                                                            <p className="text-lg font-bold text-green-600 dark:text-green-400">
                                                                {achievementPercentage.toFixed(1)}%
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })()}
                                        
                                        {activeParameterTab === 'proses' && (() => {
                                            // Get proses parameters from chartData (filtered by prosesParamNames)
                                            const prosesParamNames = data?.compare_enabled && data?.current_period
                                                ? data.current_period.data.parameter_proses.parameters.map((p: any) => p.parameter)
                                                : data?.parameter_proses?.parameters.map((p: any) => p.parameter) || [];
                                            
                                            const prosesChartData = chartData.filter((item) => prosesParamNames.includes(item.parameter));
                                            
                                            const topParam = prosesChartData.length > 0 
                                                ? prosesChartData.reduce((max, param) => {
                                                    // Use ach (achievement percentage) as the metric for top parameter
                                                    const maxAch = Number(max.ach_current || max.ach || 0);
                                                    const paramAch = Number(param.ach_current || param.ach || 0);
                                                    return paramAch > maxAch ? param : max;
                                                })
                                                : null;
                                            
                                            if (!topParam) {
                                                return (
                                                    <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                                                        No parameter data available
                                                    </div>
                                                );
                                            }
                                            
                                            // Use avg_ach_per_am if available (in decimal format, needs * 100)
                                            // Otherwise use ach_current/ach which is already in percentage format
                                            const achievementPercentage = topParam.avg_ach_per_am 
                                                ? Number(topParam.avg_ach_per_am) * 100 
                                                : Number(topParam.ach_current || topParam.ach || 0);
                                            
                                            // Format realisasi based on parameter type
                                            const formatRealisasi = (value: number, parameter: string) => {
                                                const paramLower = parameter.toLowerCase();
                                                if (paramLower.includes('revenue') || paramLower.includes('scaling') || paramLower.includes('kecukupan lop')) {
                                                    return formatCurrency(value, 2);
                                                } else {
                                                    return value.toLocaleString('id-ID', { maximumFractionDigits: 2 });
                                                }
                                            };
                                            
                                            return (
                                                <div className="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-lg p-6 border-2 border-amber-200 dark:border-amber-700">
                                                    <div className="flex items-start justify-between mb-4">
                                                        <div className="flex items-center gap-2">
                                                            <div className="w-10 h-10 bg-amber-500 dark:bg-amber-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                                1
                                                            </div>
                                                            <div>
                                                                <h4 className="font-bold text-lg text-gray-900 dark:text-white">{topParam.parameter}</h4>
                                                                <p className="text-xs text-gray-600 dark:text-gray-400">Best Performance</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-4 mt-4">
                                                        <div className="bg-white dark:bg-gray-800 rounded-lg p-3">
                                                            <p className="text-xs text-gray-600 dark:text-gray-400 mb-1">Realisasi</p>
                                                            <p className="text-lg font-bold text-gray-900 dark:text-white">
                                                                {formatRealisasi(Number(topParam.realisasi || 0), topParam.parameter)}
                                                            </p>
                                                        </div>
                                                        <div className="bg-white dark:bg-gray-800 rounded-lg p-3">
                                                            <p className="text-xs text-gray-600 dark:text-gray-400 mb-1">Persentase</p>
                                                            <p className="text-lg font-bold text-green-600 dark:text-green-400">
                                                                {achievementPercentage.toFixed(1)}%
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })()}
                                    </div>
                                </div>

                                {/* Parameter Section */}
                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden flex-1">
                                <div className="bg-white dark:bg-gray-900 px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                        <svg className="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        Parameter
                                    </h3>
                                    
                                    {/* Tab Navigation */}
                                    <div className="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                                        <button
                                            onClick={() => handleTabChange('result')}
                                            className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                                                activeParameterTab === 'result'
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                                            }`}
                                        >
                                            Aspek Result
                                            {activeParameterTab === 'result' && (
                                                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-red-600 dark:bg-red-400"></div>
                                            )}
                                        </button>
                                        <button
                                            onClick={() => handleTabChange('proses')}
                                            className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                                                activeParameterTab === 'proses'
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                                            }`}
                                        >
                                            Aspek Process
                                            {activeParameterTab === 'proses' && (
                                                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-red-600 dark:bg-red-400"></div>
                                            )}
                                        </button>
                                    </div>
                                </div>
                                
                                <div className="p-5 max-h-[600px] overflow-y-auto">
                                    {/* Aspek Result */}
                                    {activeParameterTab === 'result' && (
                                        <div>
                                            <div className="mb-3">
                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                    Bobot: {parseFloat(String(getCurrentParameterResult()?.percentage_result || 0)).toFixed(2)}%
                                                </p>
                                            </div>
                                            
                                            <div className="overflow-x-auto">
                                                <table className="w-full text-sm">
                                                    <thead>
                                                        {data.compare_enabled ? (
                                                            <>
                                                                <tr className="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                                                    <th rowSpan={2} className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">No</th>
                                                                    <th rowSpan={2} className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Parameter</th>
                                                                    <th rowSpan={2} className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Bobot (%)</th>
                                                                    <th colSpan={2} className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">{data.current_period?.label || `Q${quarter} ${year}`}</th>
                                                                    <th colSpan={2} className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">{data.comparison_period?.label || `Q${compareQuarter} ${compareYear}`}</th>
                                                                </tr>
                                                                <tr className="bg-gray-50 dark:bg-gray-800 border-b-2 border-gray-300 dark:border-gray-600">
                                                                    <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">Ach</th>
                                                                    <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400 border-r border-gray-300 dark:border-gray-600">Not Ach</th>
                                                                    <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">Ach</th>
                                                                    <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400">Not Ach</th>
                                                                </tr>
                                                            </>
                                                        ) : (
                                                            <tr className="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                                                <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">No</th>
                                                                <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Parameter</th>
                                                                <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">Bobot (%)</th>
                                                                <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400">Ach</th>
                                                                <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400">Not Ach</th>
                                                            </tr>
                                                        )}
                                                    </thead>
                                                    <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                        {getCurrentParameterResult()?.parameters && getCurrentParameterResult()!.parameters.length > 0 ? (
                                                            data.compare_enabled && data.current_period && data.comparison_period ? (
                                                                <>
                                                                    {/* Compare Mode - One row per parameter with 4 data columns */}
                                                                    {data.current_period.data.parameter_result.parameters.map((param, index) => {
                                                                        const compParam = data.comparison_period?.data.parameter_result?.parameters[index];
                                                                        return (
                                                                            <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-850">
                                                                                <td className="px-3 py-2 text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-800">{index + 1}</td>
                                                                                <td className="px-3 py-2 font-medium text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-800">{param.parameter}</td>
                                                                                <td className="px-3 py-2 text-center text-gray-900 dark:text-gray-100 border-r border-gray-300 dark:border-gray-700">{parseFloat(String(param.bobot)).toFixed(2)}%</td>
                                                                                {/* Periode 1 (Current) */}
                                                                                <td className="px-3 py-2 text-center font-bold border-r border-gray-200 dark:border-gray-800">
                                                                                    <TrendIndicator 
                                                                                        current={param.ach} 
                                                                                        previous={compParam?.ach ?? null}
                                                                                    />
                                                                                </td>
                                                                                <td className="px-3 py-2 text-center font-bold border-r border-gray-300 dark:border-gray-700">
                                                                                    <TrendIndicator 
                                                                                        current={param.not_ach} 
                                                                                        previous={compParam?.not_ach ?? null}
                                                                                    />
                                                                                </td>
                                                                                {/* Periode 2 (Comparison) */}
                                                                                <td className="px-3 py-2 text-center font-bold text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800">{compParam?.ach || 0}</td>
                                                                                <td className="px-3 py-2 text-center font-bold text-gray-600 dark:text-gray-400">{compParam?.not_ach || 0}</td>
                                                                            </tr>
                                                                        );
                                                                    })}
                                                                </>
                                                            ) : (
                                                                <>
                                                                    {/* Default Mode */}
                                                                    {getCurrentParameterResult()!.parameters.map((param, index) => (
                                                                        <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-850">
                                                                            <td className="px-3 py-2 text-gray-900 dark:text-gray-100">{index + 1}</td>
                                                                            <td className="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{param.parameter}</td>
                                                                            <td className="px-3 py-2 text-center text-gray-900 dark:text-gray-100">{parseFloat(String(param.bobot)).toFixed(2)}%</td>
                                                                            <td className="px-3 py-2 text-center font-bold text-green-600 dark:text-green-400">{param.ach}</td>
                                                                            <td className="px-3 py-2 text-center font-bold text-red-600 dark:text-red-400">{param.not_ach}</td>
                                                                        </tr>
                                                                    ))}
                                                                </>
                                                            )
                                                        ) : (
                                                            <tr>
                                                                <td colSpan={data.compare_enabled ? 7 : 5} className="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                                    No parameter data available
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    )}

                                    {/* Aspek Process */}
                                    {activeParameterTab === 'proses' && (
                                        <div>
                                            <div className="mb-3">
                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                    Bobot: {parseFloat(String(getCurrentParameterProses()?.percentage_proses || 0)).toFixed(2)}%
                                                </p>
                                            </div>
                                            
                                            <div className="overflow-x-auto">
                                                <table className="w-full text-sm">
                                                    <thead>
                                                        {data.compare_enabled ? (
                                                            <>
                                                                <tr className="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                                                    <th rowSpan={2} className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">No</th>
                                                                    <th rowSpan={2} className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Parameter</th>
                                                                    <th rowSpan={2} className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Bobot (%)</th>
                                                                    <th colSpan={2} className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">{data.current_period?.label || `Q${quarter} ${year}`}</th>
                                                                    <th colSpan={2} className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">{data.comparison_period?.label || `Q${compareQuarter} ${compareYear}`}</th>
                                                                </tr>
                                                                <tr className="bg-gray-50 dark:bg-gray-800 border-b-2 border-gray-300 dark:border-gray-600">
                                                                    <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">Ach</th>
                                                                    <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400 border-r border-gray-300 dark:border-gray-600">Not Ach</th>
                                                                    <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">Ach</th>
                                                                    <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400">Not Ach</th>
                                                                </tr>
                                                            </>
                                                        ) : (
                                                            <tr className="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                                                <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">No</th>
                                                                <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Parameter</th>
                                                                <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">Bobot (%)</th>
                                                                <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400">Ach</th>
                                                                <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400">Not Ach</th>
                                                            </tr>
                                                        )}
                                                    </thead>
                                                    <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                        {getCurrentParameterProses()?.parameters && getCurrentParameterProses()!.parameters.length > 0 ? (
                                                            data.compare_enabled && data.current_period && data.comparison_period ? (
                                                                <>
                                                                    {/* Compare Mode - One row per parameter with 4 data columns */}
                                                                    {data.current_period.data.parameter_proses.parameters.map((param, index) => {
                                                                        const compParam = data.comparison_period?.data.parameter_proses?.parameters[index];
                                                                        return (
                                                                            <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-850">
                                                                                <td className="px-3 py-2 text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-800">{index + 1}</td>
                                                                                <td className="px-3 py-2 font-medium text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-800">{param.parameter}</td>
                                                                                <td className="px-3 py-2 text-center text-gray-900 dark:text-gray-100 border-r border-gray-300 dark:border-gray-700">{parseFloat(String(param.bobot)).toFixed(2)}%</td>
                                                                                {/* Periode 1 (Current) */}
                                                                                <td className="px-3 py-2 text-center font-bold border-r border-gray-200 dark:border-gray-800">
                                                                                    <TrendIndicator 
                                                                                        current={param.ach} 
                                                                                        previous={compParam?.ach ?? null}
                                                                                    />
                                                                                </td>
                                                                                <td className="px-3 py-2 text-center font-bold border-r border-gray-300 dark:border-gray-700">
                                                                                    <TrendIndicator 
                                                                                        current={param.not_ach} 
                                                                                        previous={compParam?.not_ach ?? null}
                                                                                    />
                                                                                </td>
                                                                                {/* Periode 2 (Comparison) */}
                                                                                <td className="px-3 py-2 text-center font-bold text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800">{compParam?.ach || 0}</td>
                                                                                <td className="px-3 py-2 text-center font-bold text-gray-600 dark:text-gray-400">{compParam?.not_ach || 0}</td>
                                                                            </tr>
                                                                        );
                                                                    })}
                                                                </>
                                                            ) : (
                                                                <>
                                                                    {/* Default Mode */}
                                                                    {getCurrentParameterProses()!.parameters.map((param, index) => (
                                                                        <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-850">
                                                                            <td className="px-3 py-2 text-gray-900 dark:text-gray-100">{index + 1}</td>
                                                                            <td className="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{param.parameter}</td>
                                                                            <td className="px-3 py-2 text-center text-gray-900 dark:text-gray-100">{parseFloat(String(param.bobot)).toFixed(2)}%</td>
                                                                            <td className="px-3 py-2 text-center font-bold text-green-600 dark:text-green-400">{param.ach}</td>
                                                                            <td className="px-3 py-2 text-center font-bold text-red-600 dark:text-red-400">{param.not_ach}</td>
                                                                        </tr>
                                                                    ))}
                                                                </>
                                                            )
                                                        ) : (
                                                            <tr>
                                                                <td colSpan={data.compare_enabled ? 7 : 5} className="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                                    No parameter data available
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                            </div>
                        </div>
                    </>
                )}

                {!loading && !error && !data && (
                    <div className="text-center py-12 text-gray-500 dark:text-gray-400">
                        No data available
                    </div>
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

        {/* Witel NKI Detail Modal */}
        <WitelNkiDetailModal
            isOpen={showDetailModal}
            onClose={() => setShowDetailModal(false)}
            regionId={regionId}
            quarter={quarter}
            year={year}
            segment={selectedSegment}
            witelId={selectedWitelId}
        />
    </>
    );
}
