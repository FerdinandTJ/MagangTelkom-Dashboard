import React, { useMemo, useState } from 'react';
import {
    ComposedChart,
    Bar,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer
} from 'recharts';
import { formatCurrency, formatCurrencyShort } from '@/utils/currency';
import YearlyRevenueDetailModal from '@/components/modals/YearlyRevenueDetailModal';

interface YearlyRevenueData {
    tahun: number;
    total_revenue: number;
    formatted_revenue: string;
    [key: string]: any; // For subsegment data (Airport, Hospital, PTN, etc.)
}

interface YearlyLineChartProps {
    data: YearlyRevenueData[];
    height?: number;
    onPointClick?: (data: YearlyRevenueData) => void;
}

// Predefined highly contrasting colors - bold and saturated
export const DISTINCT_COLORS = [
    '#DC2626', // Bold Red
    '#2563EB', // Bold Blue
    '#EA580C', // Bold Orange
    '#16A34A', // Bold Green
    '#9333EA', // Bold Purple
    '#CA8A04', // Bold Yellow
    '#0891B2', // Bold Cyan
    '#DB2777', // Bold Pink
    '#65A30D', // Bold Lime
    '#7C3AED', // Bold Violet
    '#0D9488', // Bold Teal
    '#C026D3', // Bold Fuchsia
    '#4F46E5', // Bold Indigo
    '#0EA5E9', // Bold Sky
    '#F97316', // Bold Deep Orange
    '#84CC16', // Bold Light Green
    '#8B5CF6', // Bold Purple Light
];

// Generate consistent color based on index using predefined palette
export const getColorFromString = (str: string, index: number): string => {
    // Use index directly for consistent color assignment
    return DISTINCT_COLORS[index % DISTINCT_COLORS.length];
};

const YearlyLineChart: React.FC<YearlyLineChartProps> = ({ 
    data, 
    height = 300,
    onPointClick 
}) => {
    // State for modal
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [selectedYear, setSelectedYear] = useState<number | null>(null);
    const [selectedYearData, setSelectedYearData] = useState<{
        totalRevenue: number;
        subsegments: Array<{ name: string; value: number; percentage: number; color: string; }>;
    }>({ totalRevenue: 0, subsegments: [] });

    // Extract subsegment keys dynamically from data
    const subsegments = useMemo(() => {
        if (!data || data.length === 0) return [];
        
        const keys = new Set<string>();
        data.forEach(yearData => {
            Object.keys(yearData).forEach(key => {
                if (key !== 'tahun' && key !== 'total_revenue' && key !== 'formatted_revenue' && typeof yearData[key] === 'number') {
                    keys.add(key);
                }
            });
        });
        
        return Array.from(keys).sort();
    }, [data]);

    // Handle bar click to open modal - works with individual bar clicks
    const handleBarClick = (barData: any) => {
        if (!barData || !barData.tahun) return;
        
        const year = barData.tahun;
        const yearData = data.find(d => d.tahun === year);
        if (!yearData) return;
        
        const totalRevenue = yearData.total_revenue;
        
        // Prepare subsegment data for modal
        const subsegmentData = subsegments.map((subsegment, index) => {
            const value = yearData[subsegment] || 0;
            const percentage = totalRevenue > 0 ? (value / totalRevenue * 100) : 0;
            const color = getColorFromString(subsegment, index);
            
            return {
                name: subsegment,
                value,
                percentage,
                color
            };
        }).filter(item => item.value > 0) // Only show subsegments with value
          .sort((a, b) => b.value - a.value); // Sort by value descending
        
        setSelectedYear(year);
        setSelectedYearData({
            totalRevenue,
            subsegments: subsegmentData
        });
        setIsModalOpen(true);
    };

    // Check if dark mode is active
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#ffffff' : '#374151';
    const gridColor = isDarkMode ? '#374151' : '#e5e7eb';
    const tooltipBg = isDarkMode ? '#1f2937' : '#ffffff';
    const tooltipBorder = isDarkMode ? '#374151' : '#e5e7eb';

    return (
        <div className="w-full">
            <ResponsiveContainer width="100%" height={height}>
                <ComposedChart
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
                        dataKey="tahun" 
                        tick={{ fontSize: 12, fill: textColor }}
                        stroke={textColor}
                    />
                    <YAxis 
                        tickFormatter={formatCurrencyShort}
                        tick={{ fontSize: 12, fill: textColor }}
                        stroke={textColor}
                    />
                    <Tooltip
                        cursor={{ fill: 'rgba(128, 128, 128, 0.1)' }}
                        content={({ active, payload, label }) => {
                            if (active && payload && payload.length > 0) {
                                // Get total revenue from the data
                                const yearData = data.find(d => d.tahun === label);
                                const totalRevenue = yearData?.total_revenue || 0;
                                
                                // Check if we have only line or also bars
                                const lineEntry = payload.find(p => p.dataKey === 'total_revenue');
                                const barEntries = payload.filter(p => p.dataKey !== 'total_revenue' && p.value && p.value > 0);
                                
                                // If only line entry (hovering on line dot/area), show total revenue
                                if (lineEntry && barEntries.length === 0) {
                                    return (
                                        <div
                                            style={{
                                                backgroundColor: tooltipBg,
                                                border: `2px solid #2563eb`,
                                                borderRadius: '8px',
                                                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                                                padding: '12px 16px',
                                                color: textColor,
                                                minWidth: '200px'
                                            }}
                                        >
                                            <p style={{ fontSize: '12px', color: '#6b7280', marginBottom: '4px' }}>
                                                Year {label}
                                            </p>
                                            <p style={{ fontSize: '11px', color: '#6b7280', marginBottom: '2px', fontWeight: 500 }}>
                                                Total Revenue (All Subsegments)
                                            </p>
                                            <p style={{ fontSize: '18px', fontWeight: 700, color: '#2563eb' }}>
                                                {formatCurrency(totalRevenue, 2)}
                                            </p>
                                        </div>
                                    );
                                } else if (barEntries.length > 0) {
                                    // Hovering on bars - show only total with subsegment count
                                    return (
                                        <div
                                            style={{
                                                backgroundColor: tooltipBg,
                                                border: `1px solid ${tooltipBorder}`,
                                                borderRadius: '6px',
                                                boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                                                padding: '8px 12px',
                                                color: textColor,
                                                minWidth: '180px'
                                            }}
                                        >
                                            <p style={{ fontSize: '10px', color: '#6b7280', marginBottom: '3px' }}>
                                                Year {label}
                                            </p>
                                            <p style={{ fontSize: '16px', fontWeight: 700, color: '#2563eb', marginBottom: '4px' }}>
                                                {formatCurrency(totalRevenue, 2)}
                                            </p>
                                            <p style={{ fontSize: '9px', color: '#6b7280' }}>
                                                {barEntries.length} subsegments
                                            </p>
                                        </div>
                                    );
                                } else {
                                    return null;
                                }
                            }
                            return null;
                        }}
                    />
                    <Legend 
                        wrapperStyle={{ color: textColor, fontSize: '11px' }}
                        iconType="rect"
                    />
                    
                    {/* Render stacked bars for each subsegment */}
                    {subsegments.map((subsegment, index) => {
                        const color = getColorFromString(subsegment, index);
                        return (
                            <Bar 
                                key={subsegment}
                                dataKey={subsegment} 
                                stackId="subsegments"
                                fill={color}
                                name={subsegment}
                                radius={subsegment === subsegments[subsegments.length - 1] ? [4, 4, 0, 0] : undefined}
                                isAnimationActive={false}
                                cursor="pointer"
                                onClick={handleBarClick}
                            />
                        );
                    })}
                    
                    {/* Line for total revenue */}
                    <Line 
                        type="monotone" 
                        dataKey="total_revenue" 
                        stroke="#2563eb"
                        strokeWidth={3}
                        dot={{ r: 5, fill: '#2563eb', strokeWidth: 2, stroke: '#fff' }}
                        activeDot={{ r: 7 }}
                        name="Total Revenue"
                        connectNulls
                    />
                </ComposedChart>
            </ResponsiveContainer>

            {/* Modal for yearly revenue details */}
            <YearlyRevenueDetailModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                year={selectedYear}
                totalRevenue={selectedYearData.totalRevenue}
                subsegments={selectedYearData.subsegments}
            />
        </div>
    );
};

export default YearlyLineChart;