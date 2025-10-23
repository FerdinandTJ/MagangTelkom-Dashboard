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
                    <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
                    <XAxis 
                        dataKey="tahun" 
                        tick={{ fontSize: 12 }}
                    />
                    <YAxis 
                        tickFormatter={formatCurrency}
                        tick={{ fontSize: 12 }}
                    />
                    <Tooltip
                        formatter={(value: number, name: string) => [
                            formatCurrency(value),
                            'Revenue'
                        ]}
                        labelFormatter={(label) => `Tahun: ${label}`}
                        contentStyle={{
                            backgroundColor: '#ffffff',
                            border: '1px solid #e5e7eb',
                            borderRadius: '8px',
                            boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)'
                        }}
                    />
                    <Legend />
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