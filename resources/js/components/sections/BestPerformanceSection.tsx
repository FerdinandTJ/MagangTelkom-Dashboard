import React from 'react';
import { Target } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface BestPerformanceAM {
    nik: string;
    am_name: string;
    region_code: string;
    revenue: number;
    formatted_revenue: string;
    growth: number;
    formatted_growth: string;
    company_count: number;
}

interface BestPerformanceSectionProps {
    bestPerformance: BestPerformanceAM[];
}

export default function BestPerformanceSection({ bestPerformance }: BestPerformanceSectionProps) {
    if (!bestPerformance || bestPerformance.length === 0) return null;

    const medals = ['🥇', '🥈', '🥉'];
    const borderColors = ['border-yellow-400', 'border-gray-400', 'border-orange-400'];
    const bgGradients = [
        'from-yellow-50 to-orange-50',
        'from-gray-50 to-gray-100',
        'from-orange-50 to-amber-50'
    ];

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Target className="h-5 w-5 text-red-600" />
                    Best Performance
                </CardTitle>
                <CardDescription>Top 3 Account Managers</CardDescription>
            </CardHeader>
            <CardContent>
                <div className="space-y-3">
                    {bestPerformance.map((am, index) => (
                        <div key={am.nik} className={`p-4 bg-gradient-to-r ${bgGradients[index]} border-2 ${borderColors[index]} rounded-xl`}>
                            {/* Header dengan Rank dan Nama */}
                            <div className="flex items-center gap-3 mb-3">
                                <div className="flex-shrink-0">
                                    <div className="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md">
                                        <span className="text-xl">{medals[index]}</span>
                                    </div>
                                </div>
                                <div className="flex-1 min-w-0">
                                    <h3 className="font-bold text-base text-gray-900 truncate">{am.am_name}</h3>
                                    <p className="text-xs text-gray-600">Region: {am.region_code}</p>
                                </div>
                            </div>
                            
                            {/* Metrics dalam satu baris */}
                            <div className="grid grid-cols-3 gap-2">
                                <div className="bg-white/70 backdrop-blur-sm rounded-lg p-2 text-center">
                                    <p className="text-xs text-gray-600 mb-1">Revenue</p>
                                    <p className="text-sm font-bold text-gray-900">{am.formatted_revenue}</p>
                                </div>
                                <div className="bg-white/70 backdrop-blur-sm rounded-lg p-2 text-center">
                                    <p className="text-xs text-gray-600 mb-1">Growth</p>
                                    <p className={`text-sm font-bold ${am.growth >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                        {am.growth >= 0 ? '↗' : '↘'} {am.formatted_growth}
                                    </p>
                                </div>
                                <div className="bg-white/70 backdrop-blur-sm rounded-lg p-2 text-center">
                                    <p className="text-xs text-gray-600 mb-1">CC</p>
                                    <p className="text-sm font-bold text-gray-900">{am.company_count}</p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
