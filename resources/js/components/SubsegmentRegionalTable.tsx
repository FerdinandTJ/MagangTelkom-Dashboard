import React, { useState, useMemo } from 'react';
import { ChevronDown, ChevronUp, TrendingUp, TrendingDown } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface RegionalData {
    region_code: string;
    region_name: string;
    revenue: number;
    formatted_revenue: string;
    achievement: number;
    growth_yoy: number | null;
    has_previous_year_data?: boolean;
    company_count: number;
    top_companies: Array<{
        nama_perusahaan: string;
        revenue: number;
        formatted_revenue: string;
        achievement: number;
        growth_yoy: number | null;
    }>;
}

interface SubsegmentData {
    subsegment: string;
    total_revenue: number;
    formatted_total_revenue: string;
    total_achievement: number;
    total_growth_yoy: number | null;
    has_previous_year_data?: boolean;
    share_percentage: number;
    total_companies: number;
    regional_breakdown: RegionalData[];
}

interface SubsegmentRegionalTableProps {
    data: SubsegmentData[];
    onSubsegmentClick?: (subsegment: string) => void;
    onRegionClick?: (subsegment: string, regionCode: string, regionName: string) => void;
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
    onSubsegmentClick,
    onRegionClick
}) => {
    const [expandedSubsegments, setExpandedSubsegments] = useState<Set<string>>(new Set());
    const [isDarkMode] = useState(() => document.documentElement.classList.contains('dark'));
    const [expandCollapseState, setExpandCollapseState] = useState<'collapsed' | 'expanded'>('collapsed');

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
        setExpandCollapseState('expanded');
    };

    const collapseAll = () => {
        setExpandedSubsegments(new Set());
        setExpandCollapseState('collapsed');
    };

    const getTrendColor = (value: number | null) => {
        if (value === null) return 'text-gray-400 dark:text-gray-500';
        if (value > 0) return 'text-green-600 dark:text-green-400';
        if (value < 0) return 'text-red-600 dark:text-red-400';
        return 'text-gray-600 dark:text-gray-400';
    };

    const renderYoYGrowth = (value: number | null, hasPrevData?: boolean) => {
        if (value === null || hasPrevData === false) {
            return (
                <div className="flex items-center justify-center gap-1">
                    <span 
                        className="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium cursor-help" 
                        title="No previous year data available"
                    >
                        N/A
                    </span>
                </div>
            );
        }
        return (
            <div className={`flex items-center justify-center gap-1 font-bold ${getTrendColor(value)}`}>
                {value > 0 ? <TrendingUp className="h-4 w-4" /> : <TrendingDown className="h-4 w-4" />}
                <span>{value > 0 ? '+' : ''}{value}%</span>
            </div>
        );
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
            <div className="flex items-center justify-end gap-1.5 mb-3">
                <Button
                    onClick={expandAll}
                    variant={expandCollapseState === 'expanded' ? 'default' : 'outline'}
                    size="sm"
                    className={`h-7 px-2 gap-1 text-xs ${expandCollapseState === 'expanded' ? 'bg-red-600 hover:bg-red-700' : ''}`}
                >
                    <ChevronDown className="h-3 w-3" />
                    Expand
                </Button>
                <Button
                    onClick={collapseAll}
                    variant={expandCollapseState === 'collapsed' ? 'default' : 'outline'}
                    size="sm"
                    className={`h-7 px-2 gap-1 text-xs ${expandCollapseState === 'collapsed' ? 'bg-red-600 hover:bg-red-700' : ''}`}
                >
                    <ChevronUp className="h-3 w-3" />
                    Collapse
                </Button>
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
                                    <div className="flex items-center">
                                        <div className="flex items-center gap-3 w-[260px] flex-shrink-0 pl-4">
                                            <span className="text-2xl">{SUBSEGMENT_ICONS[subsegment.subsegment] || '📊'}</span>
                                            <div>
                                                <h4 className="font-bold text-gray-900 dark:text-gray-100">{subsegment.subsegment}</h4>
                                                <p className="text-xs text-gray-500 dark:text-gray-400">Click for regional breakdown</p>
                                            </div>
                                        </div>

                                        <div className="text-center w-[160px] flex-shrink-0">
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Revenue</p>
                                            <p className="font-bold text-blue-900 dark:text-blue-100">{subsegment.formatted_total_revenue}</p>
                                        </div>
                                        <div className="text-center w-[150px] flex-shrink-0">
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Ach</p>
                                            <p className={`font-bold ${getAchievementColor(subsegment.total_achievement)}`}>
                                                {subsegment.total_achievement}%
                                            </p>
                                        </div>
                                        <div className="text-center w-[140px] flex-shrink-0">
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Gr. YoY</p>
                                            {renderYoYGrowth(subsegment.total_growth_yoy, subsegment.has_previous_year_data)}
                                        </div>
                                        <div className="text-center w-[120px] flex-shrink-0">
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Share</p>
                                            <p className="font-bold text-purple-900 dark:text-purple-100">{subsegment.share_percentage}%</p>
                                        </div>
                                        <div className="text-center w-[100px] flex-shrink-0">
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">CC</p>
                                            <p className="font-bold text-gray-900 dark:text-gray-100">{subsegment.total_companies}</p>
                                        </div>

                                        <div className="ml-auto pr-4">
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
                                        <table className="w-full table-fixed">
                                            <colgroup>
                                                <col className="w-[260px]" />
                                                <col className="w-[160px]" />
                                                <col className="w-[150px]" />
                                                <col className="w-[140px]" />
                                                <col className="w-[120px]" />
                                                <col />
                                            </colgroup>
                                            <thead className="bg-gray-100 dark:bg-gray-800">
                                                <tr>
                                                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Region
                                                    </th>
                                                    <th className="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Rev
                                                    </th>
                                                    <th className="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Ach
                                                    </th>
                                                    <th className="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Gr. YoY
                                                    </th>
                                                    <th className="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Share
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Top CC
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                {subsegment.regional_breakdown.map((region) => (
                                                    <tr 
                                                        key={region.region_code} 
                                                        className="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors cursor-pointer"
                                                        onClick={() => onRegionClick?.(subsegment.subsegment, region.region_code, region.region_name)}
                                                    >
                                                        <td className="px-4 py-3">
                                                            <div>
                                                                <p className="font-bold text-gray-900 dark:text-gray-100">{region.region_code}</p>
                                                                <p className="text-xs text-gray-500 dark:text-gray-500">{region.region_name}</p>
                                                                <p className="text-xs text-gray-400 dark:text-gray-600 mt-0.5">CC: {region.company_count}</p>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            <p className="font-bold text-blue-900 dark:text-blue-100">{region.formatted_revenue}</p>
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            <p className={`font-bold ${getAchievementColor(region.achievement)}`}>
                                                                {region.achievement}%
                                                            </p>
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            {renderYoYGrowth(region.growth_yoy, region.has_previous_year_data)}
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            <p className="font-bold text-purple-900 dark:text-purple-100">
                                                                {((region.revenue / subsegment.total_revenue) * 100).toFixed(1)}%
                                                            </p>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="space-y-1.5">
                                                                {region.top_companies.slice(0, 3).map((company, idx) => (
                                                                    <div key={idx} className="text-sm">
                                                                        <p className="font-medium text-gray-900 dark:text-gray-100 truncate max-w-[300px]">
                                                                            {company.nama_perusahaan}
                                                                        </p>
                                                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                                                            {company.formatted_revenue} • Ach: {company.achievement}% • Gr: {company.growth_yoy !== null ? (company.growth_yoy > 0 ? '+' : '') + company.growth_yoy + '%' : 'N/A'}
                                                                        </p>
                                                                    </div>
                                                                ))}
                                                            </div>
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
