import React from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface PeriodData {
    period_display: string;
    quarter: number;
    year: number;
    ach_revenue_plan: number;
    ach_scaling: number;
    ach_sales_datin: number;
    ach_hsi: number;
    ach_wireline: number;
    ach_wifi: number;
    ach_cyc: number;
    ach_cr: number;
    ach_profit: number;
    ach_nps: number;
    ach_maps: number;
    ach_lop: number;
    ach_capability: number;
    ach_cc: number;
    nki_adjustment: number;
}

interface AmPerformanceTrendChartProps {
    historicalData: PeriodData[];
    mode: 'year' | 'quarter';
    currentYear: number;
}

const AmPerformanceTrendChart: React.FC<AmPerformanceTrendChartProps> = ({ 
    historicalData, 
    mode,
    currentYear 
}) => {
    // Prepare chart data based on mode
    const prepareChartData = () => {
        if (!historicalData || historicalData.length === 0) return [];

        if (mode === 'year') {
            // Yearly mode: Compare current year with 2 previous years (total 3 years)
            const targetYears = [currentYear, currentYear - 1, currentYear - 2];
            const yearlyData: { [key: number]: any } = {};
            
            // Initialize data structure for target years only
            targetYears.forEach(yr => {
                yearlyData[yr] = {
                    year: yr,
                    Q1: null,
                    Q2: null,
                    Q3: null,
                    Q4: null
                };
            });
            
            // Fill in data from historical_data
            historicalData.forEach((period) => {
                const periodYear = period.year;
                const periodQuarter = period.quarter;
                
                // Only include data for target years
                if (targetYears.includes(periodYear)) {
                    yearlyData[periodYear][`Q${periodQuarter}`] = period.nki_adjustment;
                }
            });

            // Convert to array and create data points for each quarter
            const chartData: any[] = [];
            ['Q1', 'Q2', 'Q3', 'Q4'].forEach(quarter => {
                const dataPoint: any = { quarter };
                targetYears.forEach(yr => {
                    const value = yearlyData[yr][quarter];
                    dataPoint[`year${yr}`] = value;
                });
                chartData.push(dataPoint);
            });

            return chartData;
        } else {
            // Quarterly mode: Show all parameters (Revenue to CC) for last 3 quarters
            const last3Quarters = historicalData.slice(0, 3);
            
            const parameters = [
                { key: 'ach_revenue_plan', label: 'Revenue' },
                { key: 'ach_scaling', label: 'Scaling' },
                { key: 'ach_sales_datin', label: 'Sales Datin' },
                { key: 'ach_hsi', label: 'HSI' },
                { key: 'ach_wireline', label: 'Wireline' },
                { key: 'ach_wifi', label: 'WiFi' },
                { key: 'ach_cyc', label: 'CYC' },
                { key: 'ach_cr', label: 'CR' },
                { key: 'ach_profit', label: 'Profit' },
                { key: 'ach_nps', label: 'NPS' },
                { key: 'ach_maps', label: 'MAPS' },
                { key: 'ach_lop', label: 'LOP' },
                { key: 'ach_capability', label: 'Capability' },
                { key: 'ach_cc', label: 'CC' }
            ];

            return parameters.map(param => ({
                parameter: param.label,
                ...Object.fromEntries(
                    last3Quarters.map((period) => [
                        `Q${period.quarter} ${period.year}`,
                        period[param.key as keyof PeriodData]
                    ])
                )
            }));
        }
    };

    // Get max value from quarterly data for dynamic Y-axis
    const getMaxValueFromQuarterlyData = () => {
        if (!historicalData || historicalData.length === 0) return 120;
        
        const last3Quarters = historicalData.slice(0, 3);
        const paramKeys = [
            'ach_revenue_plan', 'ach_scaling', 'ach_sales_datin', 'ach_hsi', 
            'ach_wireline', 'ach_wifi', 'ach_cyc', 'ach_cr', 'ach_profit', 
            'ach_nps', 'ach_maps', 'ach_lop', 'ach_capability', 'ach_cc'
        ];

        let maxValue = 0;
        last3Quarters.forEach(period => {
            paramKeys.forEach(key => {
                const value = period[key as keyof PeriodData] as number;
                if (value && value > maxValue) {
                    maxValue = value;
                }
            });
        });

        // Round up to nearest 10 for cleaner display
        return Math.ceil(maxValue / 10) * 10;
    };

    // Generate dynamic ticks for quarterly mode (10 iterations)
    const getQuarterlyTicks = () => {
        const maxValue = getMaxValueFromQuarterlyData();
        const step = maxValue / 10;
        return Array.from({ length: 11 }, (_, i) => Math.round(step * i * 100) / 100);
    };

    // Get years for legend
    const getYearsFromData = () => {
        return [currentYear, currentYear - 1, currentYear - 2];
    };

    // Get quarters for legend (quarterly mode)
    const getQuartersFromData = () => {
        if (!historicalData || historicalData.length === 0) return [];
        const last3 = historicalData.slice(0, 3);
        return last3.map(p => `Q${p.quarter} ${p.year}`);
    };

    const chartData = prepareChartData();

    if (chartData.length === 0) {
        return (
            <div className="flex items-center justify-center h-full text-gray-500">
                No data available for {mode === 'year' ? 'yearly' : 'quarterly'} chart
            </div>
        );
    }

    return (
        <div className="h-[400px] w-full">
            {mode === 'year' ? (
                <ResponsiveContainer width="100%" height={400} minWidth={300}>
                    <LineChart data={chartData} margin={{ top: 20, right: 30, left: 20, bottom: 20 }}>
                        <XAxis 
                            dataKey="quarter" 
                            label={{ value: 'Quartal', position: 'insideBottom', offset: -10 }}
                            className="text-gray-700 dark:text-gray-300"
                        />
                        <YAxis 
                            label={{ value: 'NKI (%)', angle: -90, position: 'insideLeft' }}
                            className="text-gray-700 dark:text-gray-300"
                            ticks={[0, 15, 30, 45, 60, 75, 90, 105, 120]}
                            domain={[0, 120]}
                        />
                        <Tooltip 
                            contentStyle={{ 
                                backgroundColor: 'rgba(255, 255, 255, 0.95)', 
                                border: '1px solid #ccc',
                                borderRadius: '8px'
                            }}
                            formatter={(value: any) => `${value?.toFixed(2)}%`}
                        />
                        <Legend layout="horizontal" verticalAlign="bottom" align="left" wrapperStyle={{ paddingLeft: '50px' }} />
                        {getYearsFromData().map((yearItem) => {
                            const hasData = chartData.some(point => point[`year${yearItem}`] != null);
                            const isCurrentYear = yearItem === currentYear;
                            
                            return (
                                <Line
                                    key={yearItem}
                                    type="monotone"
                                    dataKey={`year${yearItem}`}
                                    name={`${yearItem}`}
                                    stroke={isCurrentYear ? '#3b82f6' : '#10b981'}
                                    strokeWidth={2}
                                    dot={hasData ? { fill: isCurrentYear ? '#3b82f6' : '#10b981', r: 4 } : false}
                                    activeDot={{ r: 6 }}
                                    connectNulls={false}
                                    strokeDasharray={hasData ? "0" : "5 5"}
                                />
                            );
                        })}
                    </LineChart>
                </ResponsiveContainer>
            ) : (
                <ResponsiveContainer width="100%" height={400} minWidth={300}>
                    <LineChart data={chartData} margin={{ top: 20, right: 30, left: 20, bottom: 20 }}>
                        <XAxis 
                            dataKey="parameter" 
                            label={{ value: 'Parameter', position: 'insideBottom', offset: -10 }}
                            className="text-gray-700 dark:text-gray-300"
                        />
                        <YAxis 
                            label={{ value: 'Achievement (%)', angle: -90, position: 'insideLeft' }}
                            className="text-gray-700 dark:text-gray-300"
                            ticks={getQuarterlyTicks()}
                            domain={[0, getMaxValueFromQuarterlyData()]}
                        />
                        <Tooltip 
                            contentStyle={{ 
                                backgroundColor: 'rgba(255, 255, 255, 0.95)', 
                                border: '1px solid #ccc',
                                borderRadius: '8px'
                            }}
                            formatter={(value: any) => `${value?.toFixed(2)}%`}
                        />
                        <Legend layout="horizontal" verticalAlign="bottom" align="left" wrapperStyle={{ paddingLeft: '50px' }} />
                        {getQuartersFromData().map((quarterLabel, index) => {
                            const isCurrentQuarter = index === 0;
                            return (
                                <Line
                                    key={quarterLabel}
                                    type="monotone"
                                    dataKey={quarterLabel}
                                    name={quarterLabel}
                                    stroke={isCurrentQuarter ? '#10b981' : '#000000'}
                                    strokeWidth={2}
                                    dot={{ fill: isCurrentQuarter ? '#10b981' : '#000000', r: 4 }}
                                    activeDot={{ r: 6 }}
                                />
                            );
                        })}
                    </LineChart>
                </ResponsiveContainer>
            )}
        </div>
    );
};

export default AmPerformanceTrendChart;
