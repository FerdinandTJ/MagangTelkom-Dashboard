import React from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';
import { formatCurrency } from '@/utils/currency';
import { Calendar, TrendingUp } from 'lucide-react';

interface SubsegmentData {
    name: string;
    value: number;
    percentage: number;
    color: string;
}

interface YearlyRevenueDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    year: number | null;
    totalRevenue: number;
    subsegments: SubsegmentData[];
}

const YearlyRevenueDetailModal: React.FC<YearlyRevenueDetailModalProps> = ({
    isOpen,
    onClose,
    year,
    totalRevenue,
    subsegments
}) => {
    // Check if dark mode is active
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#ffffff' : '#374151';
    const tooltipBg = isDarkMode ? '#1f2937' : '#ffffff';
    const tooltipBorder = isDarkMode ? '#374151' : '#e5e7eb';

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="historical-modal">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl">
                        <Calendar className="w-5 h-5 text-blue-600" />
                        Revenue Breakdown - Year {year}
                    </DialogTitle>
                    <DialogDescription>
                        Detailed subsegment revenue composition for the selected year
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4 mt-4">
                    {/* Total Revenue Card - Compact */}
                    <div className="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-xs text-gray-600 dark:text-gray-400 mb-1">Total Revenue</p>
                                <p className="text-xl font-bold text-gray-900 dark:text-white">
                                    {formatCurrency(totalRevenue, 2)}
                                </p>
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {subsegments.length} subsegments
                                </p>
                            </div>
                            <div className="bg-blue-600 dark:bg-blue-500 p-3 rounded-full">
                                <TrendingUp className="w-5 h-5 text-white" />
                            </div>
                        </div>
                    </div>

                    {/* Pie Chart */}
                    <div className="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                        <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                            Subsegment Distribution
                        </h3>
                        <ResponsiveContainer width="100%" height={400}>
                            <PieChart>
                                <Pie
                                    data={subsegments}
                                    cx="50%"
                                    cy="50%"
                                    labelLine={false}
                                    label={({ name, percentage }) => `${name} (${percentage.toFixed(1)}%)`}
                                    outerRadius={120}
                                    fill="#8884d8"
                                    dataKey="value"
                                >
                                    {subsegments.map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={entry.color} />
                                    ))}
                                </Pie>
                                <Tooltip
                                    content={({ active, payload }) => {
                                        if (active && payload && payload.length > 0) {
                                            const data = payload[0].payload;
                                            return (
                                                <div
                                                    style={{
                                                        backgroundColor: tooltipBg,
                                                        border: `2px solid ${data.color}`,
                                                        borderRadius: '8px',
                                                        padding: '12px',
                                                        boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
                                                    }}
                                                >
                                                    <p style={{ fontSize: '12px', fontWeight: 600, color: textColor, marginBottom: '4px' }}>
                                                        {data.name}
                                                    </p>
                                                    <p style={{ fontSize: '16px', fontWeight: 700, color: data.color, marginBottom: '2px' }}>
                                                        {formatCurrency(data.value, 2)}
                                                    </p>
                                                    <p style={{ fontSize: '11px', color: '#6b7280' }}>
                                                        {data.percentage.toFixed(2)}% of total
                                                    </p>
                                                </div>
                                            );
                                        }
                                        return null;
                                    }}
                                />
                                <Legend
                                    verticalAlign="bottom"
                                    height={36}
                                    iconType="circle"
                                    wrapperStyle={{ fontSize: '12px', color: textColor }}
                                />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
};

export default YearlyRevenueDetailModal;
