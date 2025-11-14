import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, TrendingUp, TrendingDown, Calendar } from 'lucide-react';
import axios from '@/lib/axios';
import { formatCurrency, formatCurrencyFull } from '@/utils/currency';

interface YtdComparisonModalProps {
    isOpen: boolean;
    onClose: () => void;
}

interface YtdComparisonData {
    current_year: number;
    current_month: number;
    current_ytd: number;
    previous_year: number;
    previous_month: number;
    previous_ytd: number;
    growth_percentage: number;
    growth_amount: number;
    formatted_current_ytd: string;
    formatted_previous_ytd: string;
    formatted_growth_amount: string;
    is_positive_growth: boolean;
    current_month_name: string;
    previous_month_name: string;
}

const YtdComparisonModal: React.FC<YtdComparisonModalProps> = ({
    isOpen,
    onClose
}) => {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;

    const [currentYearInput, setCurrentYearInput] = useState<number>(currentYear);
    const [currentMonthInput, setCurrentMonthInput] = useState<number>(currentMonth);
    const [previousYearInput, setPreviousYearInput] = useState<number>(currentYear - 1);
    const [previousMonthInput, setPreviousMonthInput] = useState<number>(currentMonth);
    
    const [comparisonData, setComparisonData] = useState<YtdComparisonData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Available periods from database
    const [availableYears, setAvailableYears] = useState<number[]>([]);
    const [availableMonthsByYear, setAvailableMonthsByYear] = useState<Record<number, number[]>>({});
    const [loadingPeriods, setLoadingPeriods] = useState(true);

    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    // Fetch available periods from database
    useEffect(() => {
        const fetchAvailablePeriods = async () => {
            try {
                setLoadingPeriods(true);
                const response = await axios.get('/api/dashboard/available-periods');
                
                if (response.data.success) {
                    const { years, months_by_year } = response.data.data;
                    setAvailableYears(years);
                    setAvailableMonthsByYear(months_by_year);
                    
                    // Set default values based on available data
                    if (years.length > 0) {
                        const latestYear = years[0];
                        setCurrentYearInput(latestYear);
                        
                        // Set current month to latest available month in latest year
                        const latestYearMonths = months_by_year[latestYear] || [];
                        if (latestYearMonths.length > 0) {
                            setCurrentMonthInput(latestYearMonths[latestYearMonths.length - 1]);
                        }
                        
                        // Set previous year
                        if (years.length > 1) {
                            const previousYear = years[1];
                            setPreviousYearInput(previousYear);
                            
                            const previousYearMonths = months_by_year[previousYear] || [];
                            if (previousYearMonths.length > 0) {
                                setPreviousMonthInput(previousYearMonths[previousYearMonths.length - 1]);
                            }
                        }
                    }
                }
            } catch (err) {
                console.error('Error fetching available periods:', err);
            } finally {
                setLoadingPeriods(false);
            }
        };

        if (isOpen) {
            fetchAvailablePeriods();
        }
    }, [isOpen]);

    // Get available months for selected year
    const getCurrentAvailableMonths = () => {
        return availableMonthsByYear[currentYearInput] || [];
    };

    const getPreviousAvailableMonths = () => {
        return availableMonthsByYear[previousYearInput] || [];
    };

    const fetchComparison = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.get('/api/dashboard/ytd-comparison-custom', {
                params: {
                    current_year: currentYearInput,
                    current_month: currentMonthInput,
                    previous_year: previousYearInput,
                    previous_month: previousMonthInput
                }
            });

            if (response.data.success) {
                setComparisonData(response.data.data);
            } else {
                setError('Failed to fetch comparison data');
            }
        } catch (err: any) {
            setError(err.response?.data?.message || 'Error loading comparison data');
        } finally {
            setLoading(false);
        }
    };

    const handleCompare = () => {
        if (currentYearInput && currentMonthInput && previousYearInput && previousMonthInput) {
            fetchComparison();
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="medium-modal max-w-7xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl">
                        <Calendar className="h-6 w-6 text-red-600 dark:text-red-400" />
                        Custom YTD Comparison
                    </DialogTitle>
                    <DialogDescription>
                        Compare Year-to-Date revenue between different periods
                    </DialogDescription>
                </DialogHeader>

                {/* Input Section */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    {/* Current Period */}
                    <div className="space-y-4 p-4 bg-blue-50 dark:bg-blue-950/30 rounded-lg border border-blue-200 dark:border-blue-900">
                        <h3 className="font-semibold text-blue-900 dark:text-blue-100 flex items-center gap-2">
                            <TrendingUp className="h-5 w-5" />
                            Current Period (YTD)
                        </h3>
                        {loadingPeriods ? (
                            <div className="flex justify-center py-4">
                                <Loader2 className="h-6 w-6 animate-spin text-blue-600" />
                            </div>
                        ) : (
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Year
                                    </label>
                                    <select
                                        value={currentYearInput}
                                        onChange={(e) => {
                                            const newYear = Number(e.target.value);
                                            setCurrentYearInput(newYear);
                                            // Reset month to first available month in selected year
                                            const availableMonths = availableMonthsByYear[newYear] || [];
                                            if (availableMonths.length > 0 && !availableMonths.includes(currentMonthInput)) {
                                                setCurrentMonthInput(availableMonths[availableMonths.length - 1]);
                                            }
                                        }}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                        disabled={availableYears.length === 0}
                                    >
                                        {availableYears.map((year: number) => (
                                            <option key={year} value={year}>{year}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Up to Month
                                    </label>
                                    <select
                                        value={currentMonthInput}
                                        onChange={(e) => setCurrentMonthInput(Number(e.target.value))}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                        disabled={getCurrentAvailableMonths().length === 0}
                                    >
                                        {getCurrentAvailableMonths().map((month: number) => (
                                            <option key={month} value={month}>
                                                {monthNames[month - 1]}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Previous Period */}
                    <div className="space-y-4 p-4 bg-orange-50 dark:bg-orange-950/30 rounded-lg border border-orange-200 dark:border-orange-900">
                        <h3 className="font-semibold text-orange-900 dark:text-orange-100 flex items-center gap-2">
                            <TrendingDown className="h-5 w-5" />
                            Comparison Period (PYTD)
                        </h3>
                        {loadingPeriods ? (
                            <div className="flex justify-center py-4">
                                <Loader2 className="h-6 w-6 animate-spin text-orange-600" />
                            </div>
                        ) : (
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Year
                                    </label>
                                    <select
                                        value={previousYearInput}
                                        onChange={(e) => {
                                            const newYear = Number(e.target.value);
                                            setPreviousYearInput(newYear);
                                            // Reset month to first available month in selected year
                                            const availableMonths = availableMonthsByYear[newYear] || [];
                                            if (availableMonths.length > 0 && !availableMonths.includes(previousMonthInput)) {
                                                setPreviousMonthInput(availableMonths[availableMonths.length - 1]);
                                            }
                                        }}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500"
                                        disabled={availableYears.length === 0}
                                    >
                                        {availableYears.map((year: number) => (
                                            <option key={year} value={year}>{year}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Up to Month
                                    </label>
                                    <select
                                        value={previousMonthInput}
                                        onChange={(e) => setPreviousMonthInput(Number(e.target.value))}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500"
                                        disabled={getPreviousAvailableMonths().length === 0}
                                    >
                                        {getPreviousAvailableMonths().map((month: number) => (
                                            <option key={month} value={month}>
                                                {monthNames[month - 1]}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Compare Button */}
                <div className="flex justify-center mt-4">
                    <Button
                        onClick={handleCompare}
                        disabled={loading}
                        className="px-8 py-2 bg-red-600 hover:bg-red-700 text-white"
                    >
                        {loading ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                Comparing...
                            </>
                        ) : (
                            'Compare Periods'
                        )}
                    </Button>
                </div>

                {/* Error Message */}
                {error && (
                    <div className="mt-4 p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg">
                        <p className="text-red-700 dark:text-red-300 text-sm">{error}</p>
                    </div>
                )}

                {/* Results Section */}
                {comparisonData && !loading && (
                    <div className="mt-6 space-y-6">
                        {/* Comparison Summary */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {/* Current YTD */}
                            <div className="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950/30 dark:to-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800">
                                <p className="text-sm text-blue-700 dark:text-blue-300 mb-1">
                                    {comparisonData.current_month_name} {comparisonData.current_year} YTD
                                </p>
                                <p className="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                    {comparisonData.formatted_current_ytd}
                                </p>
                            </div>

                            {/* Growth */}
                            <div className={`p-4 rounded-lg border ${
                                comparisonData.is_positive_growth
                                    ? 'bg-gradient-to-br from-green-50 to-green-100 dark:from-green-950/30 dark:to-green-900/30 border-green-200 dark:border-green-800'
                                    : 'bg-gradient-to-br from-red-50 to-red-100 dark:from-red-950/30 dark:to-red-900/30 border-red-200 dark:border-red-800'
                            }`}>
                                <p className={`text-sm mb-1 ${
                                    comparisonData.is_positive_growth 
                                        ? 'text-green-700 dark:text-green-300' 
                                        : 'text-red-700 dark:text-red-300'
                                }`}>
                                    Growth
                                </p>
                                <div className="flex items-center gap-2">
                                    {comparisonData.is_positive_growth ? (
                                        <TrendingUp className="h-6 w-6 text-green-600 dark:text-green-400" />
                                    ) : (
                                        <TrendingDown className="h-6 w-6 text-red-600 dark:text-red-400" />
                                    )}
                                    <p className={`text-2xl font-bold ${
                                        comparisonData.is_positive_growth 
                                            ? 'text-green-900 dark:text-green-100' 
                                            : 'text-red-900 dark:text-red-100'
                                    }`}>
                                        {comparisonData.growth_percentage > 0 ? '+' : ''}{comparisonData.growth_percentage}%
                                    </p>
                                </div>
                            </div>

                            {/* Previous YTD */}
                            <div className="p-4 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-950/30 dark:to-orange-900/30 rounded-lg border border-orange-200 dark:border-orange-800">
                                <p className="text-sm text-orange-700 dark:text-orange-300 mb-1">
                                    {comparisonData.previous_month_name} {comparisonData.previous_year} YTD
                                </p>
                                <p className="text-2xl font-bold text-orange-900 dark:text-orange-100">
                                    {comparisonData.formatted_previous_ytd}
                                </p>
                            </div>
                        </div>

                        {/* Detailed Breakdown */}
                        <div className="p-6 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
                            <h4 className="font-semibold text-gray-900 dark:text-gray-100 mb-4">Detailed Breakdown</h4>
                            <div className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-gray-600 dark:text-gray-400">Growth Amount:</span>
                                    <span className={`font-semibold ${
                                        comparisonData.is_positive_growth 
                                            ? 'text-green-600 dark:text-green-400' 
                                            : 'text-red-600 dark:text-red-400'
                                    }`}>
                                        {comparisonData.growth_amount > 0 ? '+' : ''}{comparisonData.formatted_growth_amount}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600 dark:text-gray-400">Comparison Period:</span>
                                    <span className="font-medium text-gray-900 dark:text-gray-100">
                                        {comparisonData.current_month_name} {comparisonData.current_year} vs {comparisonData.previous_month_name} {comparisonData.previous_year}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
};

export default YtdComparisonModal;
