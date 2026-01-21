import React, { useMemo, useCallback } from 'react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, Legend, ResponsiveContainer, Cell } from 'recharts';
import { formatCurrency } from '@/utils/currency';

interface ChartDataItem {
    parameter: string;
    target: number;
    realisasi: number;
    bobot: number;
    ach: number;
    ach_current?: number;
    ach_compare?: number;
    target_compare?: number;
    realisasi_compare?: number;
    bobot_compare?: number;
}

interface ParameterPerformanceBalanceChartProps {
    chartData: ChartDataItem[];
    activeChartTab: 'result' | 'proses';
    setActiveChartTab: (tab: 'result' | 'proses') => void;
    quarter: number;
    year: number;
    compareMode: boolean;
    compareQuarter: number;
    compareYear: number;
    resultParamNames: string[];
    prosesParamNames: string[];
}

const CustomTooltip = React.memo(({ active, payload, compareMode, hasCompareData, compareQuarter, compareYear, quarter, year }: any) => {
    if (!active || !payload || !payload.length) return null;

    const data = payload[0].payload;
    const isCompareMode = compareMode && hasCompareData;

    // Format value helper - memoized
    const formatValue = useCallback((value: number, parameter: string) => {
        // Check if parameter is Revenue, Scaling, or Kecukupan LOP - use formatCurrency
        const paramLower = parameter.toLowerCase();
        if (paramLower.includes('revenue') || paramLower.includes('scaling') || paramLower.includes('kecukupan lop')) {
            return formatCurrency(value, 2);
        } else {
            // For other parameters, use simple number formatting
            return value.toLocaleString('id-ID', { maximumFractionDigits: 2 });
        }
    }, []);

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
                    const tolerance = 0.01;
                    const isAchieved = (currentAch + tolerance) >= bobot;
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
                            const tolerance = 0.01;
                            const isAchievedCompare = (compareAch + tolerance) >= bobotCompare;
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
});

CustomTooltip.displayName = 'CustomTooltip';

export default function ParameterPerformanceBalanceChart({
    chartData,
    activeChartTab,
    setActiveChartTab,
    quarter,
    year,
    compareMode,
    compareQuarter,
    compareYear,
    resultParamNames,
    prosesParamNames,
}: ParameterPerformanceBalanceChartProps) {
    // Memoize filtered data to prevent recalculation on every render
    const filteredData = useMemo(() => {
        if (activeChartTab === 'result') {
            return chartData.filter((item) => resultParamNames.includes(item.parameter));
        } else {
            return chartData.filter((item) => prosesParamNames.includes(item.parameter));
        }
    }, [chartData, activeChartTab, resultParamNames, prosesParamNames]);
    
    // Memoize hasCompareData check
    const hasCompareData = useMemo(() => {
        return compareMode && filteredData.length > 0 && 
            filteredData[0] && typeof filteredData[0].ach_compare === 'number' && filteredData[0].ach_compare > 0;
    }, [compareMode, filteredData]);

    // Memoize chart height calculation
    const chartHeight = useMemo(() => Math.max(filteredData.length * 60, 400), [filteredData.length]);

    // Memoize max value for X-axis domain
    const maxAxisValue = useMemo(() => {
        const maxValue = Math.max(...filteredData.map((d) => 
            Math.max(d.ach_current || 0, d.ach_compare || 0)
        ));
        return Math.ceil(maxValue) + 10;
    }, [filteredData]);

    // Memoize bar cells to prevent recreation
    const barCells = useMemo(() => {
        return filteredData.map((entry, index) => {
            const currentAch = Number(entry.ach_current || entry.ach || 0);
            const bobot = Number(entry.bobot) || 0;
            
            // Use tolerance for floating point comparison (0.01 = 0.01%)
            const tolerance = 0.01;
            const isAchieved = (currentAch + tolerance) >= bobot;
            const achievementColor = isAchieved ? '#10b981' : '#ef4444';
            
            return <Cell key={`cell-${index}`} fill={achievementColor} />;
        });
    }, [filteredData]);

    // Memoize label component to reduce re-renders
    const renderLabel = useCallback((props: any) => {
        const { x, y, width, height, index } = props;
        if (!filteredData || !filteredData[index]) return null;
        
        const entry = filteredData[index];
        const currentAch = Number(entry.ach_current || entry.ach || 0);
        const bobot = Number(entry.bobot) || 0;
        
        // Use same tolerance for consistency
        const tolerance = 0.01;
        const isAchieved = (currentAch + tolerance) >= bobot;
        const achievementColor = isAchieved ? '#10b981' : '#ef4444';
        
        return (
            <text 
                x={x + width + 5} 
                y={y + (height / 2)} 
                dominantBaseline="middle"
                fill={achievementColor}
                fontSize={12}
                fontWeight="600"
            >
                {currentAch.toFixed(1)}%
            </text>
        );
    }, [filteredData]);

    // Memoize compare label component
    const renderCompareLabel = useCallback((props: any) => {
        const { x, y, width, height, index } = props;
        if (!filteredData || !filteredData[index]) return null;
        
        const entry = filteredData[index];
        const currentAch = Number(entry.ach_current || 0);
        const compareAch = Number(entry.ach_compare || 0);
        const difference = currentAch - compareAch;
        
        // Determine color and arrow
        let color, arrow, displayText;
        if (Math.abs(difference) < 0.05) {
            color = '#6B7280';
            arrow = '';
            displayText = '0.0%';
        } else {
            const isPositive = difference > 0;
            color = isPositive ? '#10b981' : '#ef4444';
            arrow = isPositive ? '▲ ' : '▼ ';
            displayText = `${arrow}${Math.abs(difference).toFixed(1)}%`;
        }
        
        const midX = Number(x) + Number(width) + 15;
        
        return (
            <g>
                <text 
                    x={midX} 
                    y={y + (height / 2)} 
                    dominantBaseline="middle"
                    fill={color}
                    fontSize={11}
                    fontWeight="700"
                    textAnchor="start"
                >
                    {displayText}
                </text>
            </g>
        );
    }, [filteredData]);
    
    // Return early if no data
    if (filteredData.length === 0) {
        return (
            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                <div className="bg-white dark:bg-gray-900 px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                        <svg className="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Parameter Performance Balance
                    </h3>
                    
                    {/* Tab Navigation for Chart */}
                    <div className="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                        <button
                            onClick={() => setActiveChartTab('result')}
                            className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                                activeChartTab === 'result'
                                    ? 'text-purple-600 dark:text-purple-400'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                            }`}
                        >
                            Aspek Result
                            {activeChartTab === 'result' && (
                                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-600 dark:bg-purple-400"></div>
                            )}
                        </button>
                        <button
                            onClick={() => setActiveChartTab('proses')}
                            className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                                activeChartTab === 'proses'
                                    ? 'text-purple-600 dark:text-purple-400'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                            }`}
                        >
                            Aspek Proses
                            {activeChartTab === 'proses' && (
                                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-600 dark:bg-purple-400"></div>
                            )}
                        </button>
                    </div>
                </div>
                <div className="flex items-center justify-center h-[400px]">
                    <p className="text-gray-500 dark:text-gray-400">No parameter data available</p>
                </div>
            </div>
        );
    }
    
    return (
        <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div className="bg-white dark:bg-gray-900 px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                    <svg className="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Parameter Performance Balance
                </h3>
                
                {/* Tab Navigation for Chart */}
                <div className="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                    <button
                        onClick={() => setActiveChartTab('result')}
                        className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                            activeChartTab === 'result'
                                ? 'text-purple-600 dark:text-purple-400'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                        }`}
                    >
                        Aspek Result
                        {activeChartTab === 'result' && (
                            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-600 dark:bg-purple-400"></div>
                        )}
                    </button>
                    <button
                        onClick={() => setActiveChartTab('proses')}
                        className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                            activeChartTab === 'proses'
                                ? 'text-purple-600 dark:text-purple-400'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                        }`}
                    >
                        Aspek Proses
                        {activeChartTab === 'proses' && (
                            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-600 dark:bg-purple-400"></div>
                        )}
                    </button>
                </div>
            </div>
            <div className="pl-5 pr-5 pb-5 pt-5">
                <div className="overflow-x-auto">
                    <div style={{ width: '100%', minHeight: `${chartHeight}px` }}>
                        <ResponsiveContainer width="100%" height={chartHeight}>
                            <BarChart 
                                data={filteredData} 
                                layout="vertical"
                                margin={{ top: 40, right: 80, bottom: 20, left: -20 }}
                            >
                                <XAxis 
                                    type="number"
                                    tick={{ fontSize: 13 }}
                                    stroke="#6B7280"
                                    label={{ value: 'Achievement (%)', position: 'insideBottom', offset: -10, style: { fontSize: 13 } }}
                                    domain={[0, maxAxisValue]}
                                />
                                <YAxis 
                                    type="category"
                                    dataKey="parameter"
                                    tick={{ fontSize: 13 }}
                                    stroke="#6B7280"
                                    width={140}
                                />
                                <Tooltip 
                                    content={(props) => (
                                        <CustomTooltip 
                                            {...props} 
                                            compareMode={compareMode}
                                            hasCompareData={hasCompareData}
                                            compareQuarter={compareQuarter}
                                            compareYear={compareYear}
                                            quarter={quarter}
                                            year={year}
                                        />
                                    )}
                                    animationDuration={200}
                                    isAnimationActive={false}
                                />
                                {hasCompareData ? (
                                    <>
                                        {/* Chart Title for Compare Mode */}
                                        <text 
                                            x="120" 
                                            y="20" 
                                            fill="#374151" 
                                            fontSize="18" 
                                            fontWeight="600"
                                        >
                                            Achievement Parameter Q{quarter} {year} VS Q{compareQuarter} {compareYear}
                                        </text>
                                        <Legend 
                                            align="left"
                                            verticalAlign="top"
                                            wrapperStyle={{ paddingBottom: '10px', paddingLeft: '140px', paddingTop: '1px' }}
                                        />
                                        <Bar 
                                            dataKey="ach_current" 
                                            fill="#3B82F6"
                                            name={`Q${quarter} ${year}`}
                                            radius={[0, 4, 4, 0]}
                                            isAnimationActive={false}
                                        />
                                        <Bar 
                                            dataKey="ach_compare" 
                                            fill="#10b981"
                                            name={`Q${compareQuarter} ${compareYear}`}
                                            radius={[0, 4, 4, 0]}
                                            label={renderCompareLabel}
                                            isAnimationActive={false}
                                        />
                                    </>
                                ) : (
                                    <>
                                        {/* Chart Title for Non-Compare Mode */}
                                        <text 
                                            x="120" 
                                            y="20" 
                                            fill="#374151" 
                                            fontSize="18" 
                                            fontWeight="600"
                                        >
                                            Achievement Parameter Q{quarter} {year}
                                        </text>
                                        <Legend 
                                            align="left"
                                            verticalAlign="top"
                                            wrapperStyle={{ paddingBottom: '10px', paddingLeft: '140px', paddingTop: '1px' }}
                                            content={() => (
                                                <div style={{ 
                                                    display: 'flex',
                                                    justifyContent: 'flex-start',
                                                    gap: '20px',
                                                    paddingBottom: '10px'
                                                }}>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                                        <div style={{ width: '16px', height: '16px', backgroundColor: '#10b981', borderRadius: '2px' }}></div>
                                                        <span style={{ fontSize: '12px', color: '#374151', fontWeight: 500 }}>Reach Target</span>
                                                    </div>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                                        <div style={{ width: '16px', height: '16px', backgroundColor: '#ef4444', borderRadius: '2px' }}></div>
                                                        <span style={{ fontSize: '12px', color: '#374151', fontWeight: 500 }}>Not Reach Target</span>
                                                    </div>
                                                </div>
                                            )}
                                        />
                                        <Bar 
                                            dataKey="ach_current" 
                                            name="Achievement %"
                                            radius={[0, 4, 4, 0]}
                                            label={renderLabel}
                                            isAnimationActive={false}
                                        >
                                            {barCells}
                                        </Bar>
                                    </>
                                )}
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            </div>
        </div>
    );
}
