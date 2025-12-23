import React from 'react';
import { PieChart, Pie, Cell, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface RegionDistribution {
    region_id: number;
    region_code: string;
    region_name: string;
    am_count: number;
    percentage: number;
}

interface RegionDistributionChartProps {
    data: RegionDistribution[];
    colors: string[];
    onPieClick: (data: any, index: number) => void;
}

export default function RegionDistributionChart({ data, colors, onPieClick }: RegionDistributionChartProps) {
    return (
        <ResponsiveContainer width="100%" height={300}>
            <PieChart>
                <Pie
                    data={data as any}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={({ cx, cy, midAngle, innerRadius, outerRadius, percent }: any) => {
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
                    }}
                    outerRadius={100}
                    fill="#8884d8"
                    dataKey="am_count"
                    className="cursor-pointer"
                    onClick={onPieClick}
                >
                    {data.map((entry, index) => (
                        <Cell 
                            key={`cell-${index}`} 
                            fill={colors[index % colors.length]}
                            className="hover:opacity-80 transition-opacity"
                        />
                    ))}
                </Pie>
                <Tooltip 
                    formatter={(value: any, name: any, props: any) => [
                        `${value} Account Manager`,
                        props.payload.region_code
                    ]}
                    labelFormatter={() => ''}
                    contentStyle={{
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        border: '1px solid #e5e7eb',
                        borderRadius: '8px',
                        boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)'
                    }}
                />
                <Legend 
                    verticalAlign="bottom"
                    height={36}
                    formatter={(value, entry: any) => entry.payload.region_code}
                    wrapperStyle={{ fontSize: '12px' }}
                />
            </PieChart>
        </ResponsiveContainer>
    );
}
