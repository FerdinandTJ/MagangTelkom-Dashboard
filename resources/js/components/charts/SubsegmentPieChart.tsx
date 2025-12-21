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
import { formatCurrency } from '@/utils/currency';

interface SubsegmentPieChartProps {
    data: SubsegmentData[];
    height?: number;
    onSegmentClick?: (data: SubsegmentData) => void;
}

// Generate consistent color based on string hash
const getColorFromString = (str: string): string => {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }
    
    // Generate vibrant, distinguishable colors using HSL
    const hue = Math.abs(hash % 360);
    const saturation = 70 + (Math.abs(hash) % 20); // 70-90% for vibrant colors
    const lightness = 40 + (Math.abs(hash >> 8) % 12); // 40-52% for darker, readable colors
    
    return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
};

const SubsegmentPieChart: React.FC<SubsegmentPieChartProps> = ({ 
    data, 
    height = 400,
    onSegmentClick 
}) => {
    const isClickable = Boolean(onSegmentClick);

    const handleSegmentClick = (data: any) => {
        if (onSegmentClick) {
            onSegmentClick(data);
        }
    };

    // Check if dark mode is active
    const isDarkMode = document.documentElement.classList.contains('dark');
    const tooltipBg = isDarkMode ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)';
    const tooltipBorder = isDarkMode ? '#374151' : '#e5e7eb';
    const textColor = isDarkMode ? '#e5e7eb' : '#374151';

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
                        onClick={isClickable ? handleSegmentClick : undefined}
                        className={isClickable ? 'cursor-pointer' : undefined}
                    >
                        {data.map((entry, index) => (
                            <Cell 
                                key={`cell-${index}`} 
                                fill={getColorFromString(entry.subsegment)}
                                className="hover:opacity-80 transition-opacity"
                            />
                        ))}
                    </Pie>
                    <Tooltip
                        formatter={(value: number, name: string, props: any) => [
                            formatCurrency(value, 2),
                            props.payload.subsegment
                        ]}
                        labelFormatter={() => ''}
                        contentStyle={{
                            backgroundColor: tooltipBg,
                            border: `1px solid ${tooltipBorder}`,
                            borderRadius: '8px',
                            boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                            color: textColor
                        }}
                        itemStyle={{
                            color: textColor
                        }}
                        labelStyle={{ 
                            color: textColor 
                        }}
                    />
                    <Legend 
                        verticalAlign="bottom" 
                        height={36}
                        formatter={(value, entry: any) => (
                            <span style={{ color: textColor }}>
                                {entry.payload.subsegment}
                            </span>
                        )}
                        wrapperStyle={{ color: textColor }}
                    />
                </PieChart>
            </ResponsiveContainer>
        </div>
    );
};

export default SubsegmentPieChart;