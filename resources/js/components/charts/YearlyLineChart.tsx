import React from 'react';
import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer
} from 'recharts';

interface YearlyRevenueData {
    tahun: number;
    total_revenue: number;
    total_companies: number;
    formatted_revenue: string;
    [key: string]: any; // Add index signature for Recharts compatibility
}

interface YearlyLineChartProps {
    data: YearlyRevenueData[];
    height?: number;
    onPointClick?: (data: YearlyRevenueData) => void;
}

const YearlyLineChart: React.FC<YearlyLineChartProps> = ({ 
    data, 
    height = 300,
    onPointClick 
}) => {
    const formatCurrency = (value: number) => {
        return `Rp ${(value / 1000000000).toFixed(1)}M`;
    };

    const handlePointClick = (data: any) => {
        if (onPointClick) {
            onPointClick(data);
        }
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
                <LineChart
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
                        tickFormatter={formatCurrency}
                        tick={{ fontSize: 12, fill: textColor }}
                        stroke={textColor}
                    />
                    <Tooltip
                        formatter={(value: number, name: string) => [
                            formatCurrency(value),
                            'Revenue'
                        ]}
                        labelFormatter={(label) => `Tahun: ${label}`}
                        contentStyle={{
                            backgroundColor: tooltipBg,
                            border: `1px solid ${tooltipBorder}`,
                            borderRadius: '8px',
                            boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                            color: textColor
                        }}
                        labelStyle={{ color: textColor }}
                    />
                    <Legend 
                        wrapperStyle={{ color: textColor }}
                        iconType="line"
                    />
                    <Line 
                        type="monotone" 
                        dataKey="total_revenue" 
                        stroke="#dc2626" 
                        strokeWidth={3}
                        dot={{ fill: '#dc2626', strokeWidth: 2, r: 5 }}
                        activeDot={{ r: 7, stroke: '#dc2626', strokeWidth: 2 }}
                        name="Revenue"
                        onClick={handlePointClick}
                        className="cursor-pointer"
                    />
                </LineChart>
            </ResponsiveContainer>
        </div>
    );
};

export default YearlyLineChart;