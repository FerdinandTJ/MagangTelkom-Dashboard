import React from 'react';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer
} from 'recharts';
import { formatCurrency, formatCurrencyShort } from '@/utils/currency';

interface MonthlyRevenueData {
    bulan: number;
    bulan_name: string;
    total_revenue: number;
    target_revenue: number;
    total_companies: number;
    formatted_revenue: string;
    formatted_target: string;
    achievement_percentage: number;
    comparison_revenue?: number;
    comparison_formatted_revenue?: string;
    growth_percentage?: number;
    growth_amount?: number;
}

interface RevenueBarChartProps {
    data: MonthlyRevenueData[];
    height?: number;
    onBarClick?: (data: MonthlyRevenueData) => void;
    comparisonMode?: boolean;
    selectedYear?: number;
    comparisonYear?: number;
}

const RevenueBarChart: React.FC<RevenueBarChartProps> = ({ 
    data, 
    height = 400,
    onBarClick,
    comparisonMode = false,
    selectedYear,
    comparisonYear
}) => {
    const handleBarClick = (data: any) => {
        if (onBarClick) {
            onBarClick(data);
        }
    };

    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#ffffff' : '#374151';
    const gridColor = isDarkMode ? '#374151' : '#e5e7eb';
    const tooltipBg = isDarkMode ? '#1f2937' : '#ffffff';
    const tooltipBorder = isDarkMode ? '#374151' : '#e5e7eb';

    return (
        <div className="w-full">
            {/* Compact Comparison Legend */}
            {comparisonMode && (
                <div className="flex items-center justify-between mb-3 px-2">
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <div className="w-3 h-3 bg-red-600 dark:bg-red-500 rounded"></div>
                            <span className="text-xs font-medium text-gray-700 dark:text-gray-300">{selectedYear}</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-3 h-3 bg-blue-600 dark:bg-blue-500 rounded"></div>
                            <span className="text-xs font-medium text-gray-700 dark:text-gray-300">{comparisonYear}</span>
                        </div>
                    </div>
                    <span className="text-xs text-gray-500 dark:text-gray-400">
                        Hover bars for detailed comparison
                    </span>
                </div>
            )}
            
            <ResponsiveContainer width="100%" height={height}>
                <BarChart
                    data={data}
                    margin={{
                        top: 20,
                        right: 30,
                        left: 20,
                        bottom: 5,
                    }}
                >
                    <CartesianGrid strokeDasharray="3 3" stroke={gridColor} className="opacity-30" />
                    <XAxis 
                        dataKey="bulan_name" 
                        tick={{ fontSize: 12, fill: textColor }}
                        stroke={textColor}
                        angle={-45}
                        textAnchor="end"
                        height={80}
                    />
                    <YAxis 
                        tickFormatter={formatCurrencyShort}
                        tick={{ fontSize: 12, fill: textColor }}
                        stroke={textColor}
                    />
                    <Tooltip
                        content={({ active, payload, label }) => {
                            if (active && payload && payload.length) {
                                const monthData = data.find(d => d.bulan_name === label);
                                return (
                                    <div
                                        style={{
                                            backgroundColor: tooltipBg,
                                            border: `1px solid ${tooltipBorder}`,
                                            borderRadius: '8px',
                                            boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                                            padding: '12px',
                                            color: textColor,
                                            minWidth: '200px'
                                        }}
                                    >
                                        <p style={{ fontWeight: 600, marginBottom: '8px', color: textColor }}>
                                            {label}
                                        </p>
                                        
                                        {comparisonMode && monthData ? (
                                            <>
                                                <div style={{ marginBottom: '8px', paddingBottom: '8px', borderBottom: `1px solid ${tooltipBorder}` }}>
                                                    <p style={{ fontSize: '11px', color: '#6b7280', marginBottom: '4px' }}>
                                                        {selectedYear || 'Current Year'}
                                                    </p>
                                                    <p style={{ margin: '4px 0', color: textColor }}>
                                                        <span style={{ 
                                                            display: 'inline-block',
                                                            width: '12px',
                                                            height: '12px',
                                                            backgroundColor: '#dc2626',
                                                            marginRight: '8px',
                                                            borderRadius: '2px'
                                                        }}></span>
                                                        Revenue: {formatCurrency(monthData.total_revenue, 2)}
                                                    </p>
                                                </div>
                                                
                                                {monthData.comparison_revenue !== undefined && (
                                                    <>
                                                        <div style={{ marginBottom: '8px', paddingBottom: '8px', borderBottom: `1px solid ${tooltipBorder}` }}>
                                                            <p style={{ fontSize: '11px', color: '#6b7280', marginBottom: '4px' }}>
                                                                {comparisonYear || 'Comparison Year'}
                                                            </p>
                                                            <p style={{ margin: '4px 0', color: textColor }}>
                                                                <span style={{ 
                                                                    display: 'inline-block',
                                                                    width: '12px',
                                                                    height: '12px',
                                                                    backgroundColor: '#2563eb',
                                                                    marginRight: '8px',
                                                                    borderRadius: '2px'
                                                                }}></span>
                                                                Revenue: {formatCurrency(monthData.comparison_revenue, 2)}
                                                            </p>
                                                        </div>
                                                        
                                                        {monthData.growth_percentage !== undefined && (
                                                            <div style={{ 
                                                                display: 'flex', 
                                                                alignItems: 'center', 
                                                                gap: '8px',
                                                                padding: '6px 8px',
                                                                borderRadius: '4px',
                                                                backgroundColor: monthData.growth_percentage >= 0 ? '#dcfce7' : '#fee2e2',
                                                                color: monthData.growth_percentage >= 0 ? '#166534' : '#991b1b'
                                                            }}>
                                                                <span style={{ fontWeight: 600, fontSize: '12px' }}>
                                                                    {monthData.growth_percentage >= 0 ? '▲' : '▼'} {Math.abs(monthData.growth_percentage).toFixed(1)}%
                                                                </span>
                                                                <span style={{ fontSize: '11px' }}>
                                                                    {monthData.growth_percentage >= 0 ? 'Growth' : 'Decline'}
                                                                </span>
                                                            </div>
                                                        )}
                                                    </>
                                                )}
                                            </>
                                        ) : (
                                            <>
                                                {payload.map((entry: any, index: number) => (
                                                    <p key={index} style={{ margin: '4px 0', color: textColor }}>
                                                        <span style={{ 
                                                            display: 'inline-block',
                                                            width: '12px',
                                                            height: '12px',
                                                            backgroundColor: entry.color,
                                                            marginRight: '8px',
                                                            borderRadius: '2px'
                                                        }}></span>
                                                        {entry.dataKey === 'total_revenue' ? 'Actual Revenue' : 
                                                         entry.dataKey === 'comparison_revenue' ? 'Comparison Revenue' :
                                                         'Target Revenue'}: {formatCurrency(entry.value, 2)}
                                                    </p>
                                                ))}
                                            </>
                                        )}
                                    </div>
                                );
                            }
                            return null;
                        }}
                    />
                    <Legend 
                        wrapperStyle={{ color: textColor }}
                        iconType="rect"
                    />
                    
                    {!comparisonMode ? (
                        <>
                            <Bar 
                                dataKey="target_revenue" 
                                fill="#94a3b8" 
                                name="Target Revenue"
                                radius={[4, 4, 0, 0]}
                                opacity={0.8}
                                onClick={handleBarClick}
                                className="cursor-pointer hover:opacity-90 transition-opacity"
                            />
                            <Bar 
                                dataKey="total_revenue" 
                                fill="#dc2626" 
                                name="Revenue Realisasi"
                                radius={[4, 4, 0, 0]}
                                onClick={handleBarClick}
                                className="cursor-pointer hover:opacity-80 transition-opacity"
                            />
                        </>
                    ) : (
                        <>
                            <Bar 
                                dataKey="total_revenue" 
                                fill="#dc2626" 
                                name={`${selectedYear || 'Current'} Revenue`}
                                radius={[4, 4, 0, 0]}
                                onClick={handleBarClick}
                                className="cursor-pointer hover:opacity-80 transition-opacity"
                            />
                            <Bar 
                                dataKey="comparison_revenue" 
                                fill="#2563eb" 
                                name={`${comparisonYear || 'Comparison'} Revenue`}
                                radius={[4, 4, 0, 0]}
                                onClick={handleBarClick}
                                className="cursor-pointer hover:opacity-80 transition-opacity"
                            />
                        </>
                    )}
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
};

export default RevenueBarChart;