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

interface MonthlyRevenueData {
    bulan: number;
    bulan_name: string;
    total_revenue: number;
    target_revenue: number;
    total_companies: number;
    formatted_revenue: string;
    formatted_target: string;
    achievement_percentage: number;
}

interface RevenueBarChartProps {
    data: MonthlyRevenueData[];
    height?: number;
    onBarClick?: (data: MonthlyRevenueData) => void;
}

const RevenueBarChart: React.FC<RevenueBarChartProps> = ({ 
    data, 
    height = 400,
    onBarClick 
}) => {
    const formatCurrency = (value: number) => {
        return `Rp ${(value / 1000000000).toFixed(1)}M`;
    };

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
                        tickFormatter={formatCurrency}
                        tick={{ fontSize: 12, fill: textColor }}
                        stroke={textColor}
                    />
                    <Tooltip
                        formatter={(value: number, name: string) => [
                            formatCurrency(value),
                            name === 'total_revenue' ? 'Actual Revenue' : 'Target Revenue'
                        ]}
                        labelFormatter={(label) => `Bulan: ${label}`}
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
                        iconType="rect"
                    />
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
                        name="Actual Revenue"
                        radius={[4, 4, 0, 0]}
                        onClick={handleBarClick}
                        className="cursor-pointer hover:opacity-80 transition-opacity"
                    />
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
};

export default RevenueBarChart;