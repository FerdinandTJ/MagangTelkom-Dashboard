import React from 'react';
import {
    PieChart,
    Pie,
    Cell,
    ResponsiveContainer,
    Tooltip,
    Legend
} from 'recharts';
import { SubsegmentData } from '@/types/dashboard';

interface SubsegmentPieChartProps {
    data: SubsegmentData[];
    height?: number;
    onSegmentClick?: (data: SubsegmentData) => void;
}

const COLORS = {
    'Airport': '#dc2626',              // Red
    'Hospital': '#ea580c',             // Orange
    'PTN': '#d97706',                  // Amber
    'PTS': '#ca8a04',                  // Yellow
    'Media': '#65a30d',                // Green
    'Airlines': '#0891b2',             // Cyan
    'OLO': '#4f46e5',                  // Indigo
    'Professional Service': '#7c3aed', // Violet
    'Tourism and MICE': '#db2777'      // Pink
};

const SubsegmentPieChart: React.FC<SubsegmentPieChartProps> = ({ 
    data, 
    height = 400,
    onSegmentClick 
}) => {
    const formatCurrency = (value: number) => {
        return `Rp ${(value / 1000000000).toFixed(1)}M`;
    };

    const handleSegmentClick = (data: any) => {
        if (onSegmentClick) {
            onSegmentClick(data);
        }
    };

    const renderCustomizedLabel = ({
        cx, cy, midAngle, innerRadius, outerRadius, percent
    }: any) => {
        if (percent < 0.05) return null;
        
        const RADIAN = Math.PI / 180;
        const radius = innerRadius + (outerRadius - innerRadius) * 0.5;
        const x = cx + radius * Math.cos(-midAngle * RADIAN);
        const y = cy + radius * Math.sin(-midAngle * RADIAN);

        return (
            <text 
                x={x} 
                y={y} 
                fill="white" 
                textAnchor={x > cx ? 'start' : 'end'} 
                dominantBaseline="central"
                fontSize={12}
                fontWeight="bold"
            >
                {`${(percent * 100).toFixed(0)}%`}
            </text>
        );
    };

    return (
        <div className="w-full">
            <ResponsiveContainer width="100%" height={height}>
                <PieChart>
                    <Pie
                        data={data}
                        cx="50%"
                        cy="50%"
                        labelLine={false}
                        label={renderCustomizedLabel}
                        outerRadius={120}
                        fill="#8884d8"
                        dataKey="total_revenue"
                        onClick={handleSegmentClick}
                        className="cursor-pointer"
                    >
                        {data.map((entry, index) => (
                            <Cell 
                                key={`cell-${index}`} 
                                fill={COLORS[entry.subsegment as keyof typeof COLORS] || '#6b7280'}
                                className="hover:opacity-80 transition-opacity"
                            />
                        ))}
                    </Pie>
                    <Tooltip
                        formatter={(value: number, name: string, props: any) => [
                            formatCurrency(value),
                            props.payload.subsegment
                        ]}
                        labelFormatter={() => ''}
                        contentStyle={{
                            backgroundColor: '#ffffff',
                            border: '1px solid #e5e7eb',
                            borderRadius: '8px',
                            boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)'
                        }}
                    />
                    <Legend 
                        verticalAlign="bottom" 
                        height={36}
                        formatter={(value, entry: any) => (
                            <span style={{ color: entry.color }}>
                                {entry.payload.subsegment}
                            </span>
                        )}
                    />
                </PieChart>
            </ResponsiveContainer>
        </div>
    );
};

export default SubsegmentPieChart;