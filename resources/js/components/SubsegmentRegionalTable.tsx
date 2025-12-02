import React, { useState, useMemo } from 'react';
import { ChevronDown, ChevronUp, TrendingUp, TrendingDown } from 'lucide-react';

interface RegionalData {
    region_code: string;
    region_name: string;
    revenue: number;
    formatted_revenue: string;
    achievement: number;
    growth_yoy: number;
    company_count: number;
    top_companies: Array<{
        nama_perusahaan: string;
        revenue: number;
        formatted_revenue: string;
        achievement: number;
        growth_yoy: number;
    }>;
}

interface SubsegmentData {
    subsegment: string;
    total_revenue: number;
    formatted_total_revenue: string;
    total_achievement: number;
    total_growth_yoy: number;
    share_percentage: number;
    total_companies: number;
    regional_breakdown: RegionalData[];
}

interface SubsegmentRegionalTableProps {
    data: SubsegmentData[];
    onSubsegmentClick?: (subsegment: string) => void;
}

const SUBSEGMENT_ICONS: { [key: string]: string } = {
    'PTN': '🎓',
    'PTS': '📚',
    'Hospital': '🏥',
    'Airport': '✈️',
    'Media': '📺',
    'Airlines': '🛫',
    'OLO': '🌐',
    'Professional Service': '💼',
    'Tourism and MICE': '🏨'
};

const SubsegmentRegionalTable: React.FC<SubsegmentRegionalTableProps> = ({
    data,
    onSubsegmentClick
}) => {
    const [expandedSubsegments, setExpandedSubsegments] = useState<Set<string>>(new Set());
    const [isDarkMode] = useState(() => document.documentElement.classList.contains('dark'));

    const toggleSubsegment = (subsegment: string) => {
        const newExpanded = new Set(expandedSubsegments);
        if (newExpanded.has(subsegment)) {
            newExpanded.delete(subsegment);
        } else {
            newExpanded.add(subsegment);
        }
        setExpandedSubsegments(newExpanded);
    };

    const expandAll = () => {
        const allSubsegments = data.map(s => s.subsegment);
        setExpandedSubsegments(new Set(allSubsegments));
    };

    const collapseAll = () => {
        setExpandedSubsegments(new Set());
    };

    const getTrendColor = (value: number) => {
        if (value > 0) return 'text-green-600 dark:text-green-400';
        if (value < 0) return 'text-red-600 dark:text-red-400';
        return 'text-gray-600 dark:text-gray-400';
    };

    const getAchievementColor = (ach: number) => {
        if (ach >= 100) return 'text-green-600 dark:text-green-400 font-bold';
        if (ach >= 80) return 'text-blue-600 dark:text-blue-400 font-semibold';
        if (ach >= 60) return 'text-yellow-600 dark:text-yellow-400 font-medium';
        return 'text-red-600 dark:text-red-400 font-medium';
    };

    return (
        <div className="px-6 pb-6">
            {/* Expand/Collapse Buttons */}
            <div className="flex items-center justify-end gap-2 mb-6">
                <button
                    onClick={expandAll}
                    className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white rounded-lg transition-colors text-sm font-medium shadow-sm"
                >
                    <ChevronDown className="h-4 w-4" />
                    Expand All
                </button>
                <button
                    onClick={collapseAll}
                    className="flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-800 text-white rounded-lg transition-colors text-sm font-medium shadow-sm"
                >
                    <ChevronUp className="h-4 w-4" />
                    Collapse All
                </button>
            </div>

                <div className="space-y-3">
                    {data.map((subsegment) => {
                        const isExpanded = expandedSubsegments.has(subsegment.subsegment);
                        
                        return (
                            <div key={subsegment.subsegment} className="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                {/* Subsegment Header */}
                                <div 
                                    className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 p-4 cursor-pointer hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-900/40 dark:hover:to-indigo-900/40 transition-all"
                                    onClick={() => toggleSubsegment(subsegment.subsegment)}
                                >
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-4 flex-1">
                                            <div className="flex items-center gap-3 min-w-[200px]">
                                                <span className="text-2xl">{SUBSEGMENT_ICONS[subsegment.subsegment] || '📊'}</span>
                                                <div>
                                                    <h4 className="font-bold text-gray-900 dark:text-gray-100">{subsegment.subsegment}</h4>
                                                    <p className="text-xs text-gray-500 dark:text-gray-400">Click for regional breakdown</p>
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-5 gap-4 flex-1">
                                                <div className="text-center">
                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Revenue</p>
                                                    <p className="font-bold text-blue-900 dark:text-blue-100">{subsegment.formatted_total_revenue}</p>
                                                </div>
                                                <div className="text-center">
                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Ach</p>
                                                    <p className={`font-bold ${getAchievementColor(subsegment.total_achievement)}`}>
                                                        {subsegment.total_achievement}%
                                                    </p>
                                                </div>
                                                <div className="text-center">
                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Gr. YoY</p>
                                                    <div className={`flex items-center justify-center gap-1 font-bold ${getTrendColor(subsegment.total_growth_yoy)}`}>
                                                        {subsegment.total_growth_yoy > 0 ? <TrendingUp className="h-4 w-4" /> : <TrendingDown className="h-4 w-4" />}
                                                        <span>{subsegment.total_growth_yoy > 0 ? '+' : ''}{subsegment.total_growth_yoy}%</span>
                                                    </div>
                                                </div>
                                                <div className="text-center">
                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Share</p>
                                                    <p className="font-bold text-purple-900 dark:text-purple-100">{subsegment.share_percentage}%</p>
                                                </div>
                                                <div className="text-center">
                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">CC</p>
                                                    <p className="font-bold text-gray-900 dark:text-gray-100">{subsegment.total_companies}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="ml-4">
                                            {isExpanded ? (
                                                <ChevronUp className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                            ) : (
                                                <ChevronDown className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Regional Breakdown Table */}
                                {isExpanded && (
                                    <div className="bg-white dark:bg-gray-950">
                                        <table className="w-full">
                                            <thead className="bg-gray-100 dark:bg-gray-800">
                                                <tr>
                                                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        TREG
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Top CC
                                                    </th>
                                                    <th className="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Rev
                                                    </th>
                                                    <th className="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Ach
                                                    </th>
                                                    <th className="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Gr YoY
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                {subsegment.regional_breakdown.map((region) => (
                                                    <tr key={region.region_code} className="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                                        <td className="px-4 py-3">
                                                            <div>
                                                                <p className="font-bold text-gray-900 dark:text-gray-100">{region.region_code}</p>
                                                                <p className="text-sm text-gray-600 dark:text-gray-400">Rev: {region.formatted_revenue} | Gr. {region.growth_yoy > 0 ? '+' : ''}{region.growth_yoy}%</p>
                                                                <p className="text-xs text-gray-500 dark:text-gray-500">CC: {region.company_count}</p>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="space-y-1">
                                                                {region.top_companies.slice(0, 3).map((company, idx) => (
                                                                    <div key={idx} className="text-sm">
                                                                        <p className="font-medium text-gray-900 dark:text-gray-100 truncate max-w-[300px]">
                                                                            {company.nama_perusahaan}
                                                                        </p>
                                                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                                                            {company.formatted_revenue}
                                                                        </p>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-right">
                                                            {region.top_companies.slice(0, 3).map((company, idx) => (
                                                                <p key={idx} className="text-sm font-semibold text-gray-900 dark:text-gray-100 py-[9px]">
                                                                    {company.formatted_revenue}
                                                                </p>
                                                            ))}
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            {region.top_companies.slice(0, 3).map((company, idx) => (
                                                                <p key={idx} className={`text-sm font-bold py-[9px] ${getAchievementColor(company.achievement)}`}>
                                                                    {company.achievement}%
                                                                </p>
                                                            ))}
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            {region.top_companies.slice(0, 3).map((company, idx) => (
                                                                <p key={idx} className={`text-sm font-bold py-[9px] ${getTrendColor(company.growth_yoy)}`}>
                                                                    {company.growth_yoy > 0 ? '+' : ''}{company.growth_yoy}%
                                                                </p>
                                                            ))}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
        </div>
    );
};

export default SubsegmentRegionalTable;
