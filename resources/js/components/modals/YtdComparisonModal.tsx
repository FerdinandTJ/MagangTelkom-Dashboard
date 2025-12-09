import React, { useState, useEffect, useMemo, useCallback } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, TrendingUp, TrendingDown, Calendar, ChevronDown, ChevronRight, Search, X } from 'lucide-react';
import axios from '@/lib/axios';
import { formatCurrency, formatCurrencyFull } from '@/utils/currency';

interface YtdComparisonModalProps {
    isOpen: boolean;
    onClose: () => void;
}

interface GroupBreakdown {
    id: string;
    name: string;
    type: 'group1' | 'group2' | 'group3' | 'group4';
    current_revenue: number;
    previous_revenue: number;
    growth_percentage: number;
    growth_amount: number;
    formatted_current: string;
    formatted_previous: string;
    formatted_growth: string;
    is_positive: boolean;
    children?: GroupBreakdown[];
}

interface YtdComparisonData {
    current_year: number;
    current_month: number;
    current_month_name: string;
    current_revenue: number;
    previous_year: number;
    previous_month: number;
    previous_month_name: string;
    previous_revenue: number;
    growth_percentage: number;
    growth_amount: number;
    formatted_current_revenue: string;
    formatted_previous_revenue: string;
    formatted_growth_amount: string;
    is_positive_growth: boolean;
    hierarchical_breakdown: GroupBreakdown[];
}

// YTD Breakdown Tree Component
interface YtdBreakdownTreeProps {
    data: GroupBreakdown[];
    currentPeriod: string;
    previousPeriod: string;
}

const YtdBreakdownTree: React.FC<YtdBreakdownTreeProps> = ({ data, currentPeriod, previousPeriod }) => {
    const [expandedItems, setExpandedItems] = useState<Set<string>>(new Set());
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [debouncedSearchQuery, setDebouncedSearchQuery] = useState<string>('');
    const [matchCount, setMatchCount] = useState<number>(0);

    // Debounce search query with 300ms delay
    useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearchQuery(searchQuery);
        }, 300);

        return () => clearTimeout(timer);
    }, [searchQuery]);

    // Filter and search logic - use debounced query
    const { filteredData, matchedIds } = useMemo(() => {
        if (!debouncedSearchQuery.trim()) {
            return { filteredData: data, matchedIds: new Set<string>() };
        }

        const query = debouncedSearchQuery.toLowerCase();
        const matched = new Set<string>();
        const ancestorIds = new Set<string>();

        // Recursive function to check if node or any descendant matches
        const checkNodeMatch = (node: GroupBreakdown, ancestors: string[]): boolean => {
            const nameMatches = node.name.toLowerCase().includes(query);
            
            if (nameMatches) {
                matched.add(node.id);
                // Add all ancestors to ensure path visibility
                ancestors.forEach(id => ancestorIds.add(id));
            }

            let hasMatchingChild = false;
            if (node.children) {
                for (const child of node.children) {
                    if (checkNodeMatch(child, [...ancestors, node.id])) {
                        hasMatchingChild = true;
                        ancestorIds.add(node.id); // Mark this node as ancestor of match
                    }
                }
            }

            return nameMatches || hasMatchingChild;
        };

        // Filter data - only include nodes that match or have matching descendants
        const filterData = (nodes: GroupBreakdown[]): GroupBreakdown[] => {
            return nodes
                .map(node => {
                    const hasMatch = checkNodeMatch(node, []);
                    
                    if (!hasMatch) return null;

                    // If node has children, filter them recursively
                    if (node.children && node.children.length > 0) {
                        const filteredChildren = filterData(node.children);
                        return {
                            ...node,
                            children: filteredChildren
                        };
                    }

                    return node;
                })
                .filter((node): node is GroupBreakdown => node !== null);
        };

        const filtered = filterData(data);
        
        return { 
            filteredData: filtered, 
            matchedIds: matched,
            ancestorIds
        };
    }, [data, debouncedSearchQuery]);

    // Auto-expand matched nodes and their ancestors when searching
    useEffect(() => {
        if (debouncedSearchQuery.trim() && matchedIds.size > 0) {
            const newExpanded = new Set(expandedItems);
            
            // Expand all ancestor nodes to show matches
            const expandAncestors = (nodes: GroupBreakdown[], path: string[] = []) => {
                nodes.forEach(node => {
                    if (matchedIds.has(node.id)) {
                        // Expand all ancestors in path
                        path.forEach(id => newExpanded.add(id));
                    }
                    if (node.children) {
                        expandAncestors(node.children, [...path, node.id]);
                    }
                });
            };

            expandAncestors(filteredData);
            setExpandedItems(newExpanded);
            setMatchCount(matchedIds.size);
        } else {
            setMatchCount(0);
        }
    }, [debouncedSearchQuery, matchedIds, filteredData]);

    const toggleExpand = (id: string) => {
        const newExpanded = new Set(expandedItems);
        if (newExpanded.has(id)) {
            newExpanded.delete(id);
        } else {
            newExpanded.add(id);
        }
        setExpandedItems(newExpanded);
    };

    const getAllNodeIds = (nodes: GroupBreakdown[]): string[] => {
        let ids: string[] = [];
        nodes.forEach(node => {
            ids.push(node.id);
            if (node.children && node.children.length > 0) {
                ids = ids.concat(getAllNodeIds(node.children));
            }
        });
        return ids;
    };

    const expandAll = () => {
        setExpandedItems(new Set(getAllNodeIds(filteredData)));
    };

    const collapseAll = () => {
        setExpandedItems(new Set());
    };

    const clearSearch = () => {
        setSearchQuery('');
        setMatchCount(0);
    };

    return (
        <div className="space-y-3">
            {/* Search Bar */}
            <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    type="text"
                    placeholder="Search by product or company name..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                />
                {searchQuery && (
                    <button
                        onClick={clearSearch}
                        className="absolute right-3 top-1/2 transform -translate-y-1/2 p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                        title="Clear search"
                    >
                        <X className="h-4 w-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" />
                    </button>
                )}
            </div>

            {/* Search Results Info */}
            {searchQuery && (
                <div className="flex items-center justify-between px-3 py-2 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <span className="text-sm text-blue-700 dark:text-blue-300">
                        {matchCount > 0 ? (
                            <>Found <span className="font-semibold">{matchCount}</span> {matchCount === 1 ? 'match' : 'matches'}</>
                        ) : (
                            <>No matches found for "<span className="font-semibold">{searchQuery}</span>"</>
                        )}
                    </span>
                    {matchCount > 0 && (
                        <button
                            onClick={clearSearch}
                            className="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium"
                        >
                            Clear
                        </button>
                    )}
                </div>
            )}

            {/* Control Buttons */}
            <div className="flex justify-end gap-2">
                <button
                    onClick={expandAll}
                    className="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors"
                >
                    <ChevronDown className="h-3 w-3" />
                    Expand All
                </button>
                <button
                    onClick={collapseAll}
                    className="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors"
                >
                    <ChevronRight className="h-3 w-3" />
                    Collapse All
                </button>
            </div>

            {/* Tree Table */}
            <div className="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <table className="w-full border-collapse">
                    <thead>
                        <tr className="bg-gray-100 dark:bg-gray-800">
                            <th className="text-left px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 text-sm">
                                Category
                            </th>
                            <th className="text-right px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 text-sm w-40">
                                {currentPeriod}
                            </th>
                            <th className="text-right px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 text-sm w-40">
                                {previousPeriod}
                            </th>
                            <th className="text-right px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 text-sm w-32">
                                Growth
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                        {filteredData.length > 0 ? (
                            filteredData.map((group) => (
                                <YtdTreeNode 
                                    key={group.id} 
                                    group={group}
                                    level={0}
                                    expandedItems={expandedItems}
                                    onToggle={toggleExpand}
                                    searchQuery={searchQuery}
                                    matchedIds={matchedIds}
                                />
                            ))
                        ) : (
                            <tr>
                                <td colSpan={4} className="p-8 text-center text-gray-500 dark:text-gray-400">
                                    No results found
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

interface YtdTreeNodeProps {
    group: GroupBreakdown;
    level: number;
    expandedItems: Set<string>;
    onToggle: (id: string) => void;
    searchQuery?: string;
    matchedIds?: Set<string>;
}

const YtdTreeNode: React.FC<YtdTreeNodeProps> = ({ 
    group, 
    level, 
    expandedItems, 
    onToggle,
    searchQuery = '',
    matchedIds = new Set()
}) => {
    const hasChildren = group.children && group.children.length > 0;
    const isExpanded = expandedItems.has(group.id);
    const isMatched = matchedIds.has(group.id);

    // Highlight matching text
    const highlightText = (text: string) => {
        if (!searchQuery || !isMatched) {
            return text;
        }

        const parts = text.split(new RegExp(`(${searchQuery})`, 'gi'));
        return parts.map((part, index) => {
            if (part.toLowerCase() === searchQuery.toLowerCase()) {
                return (
                    <mark 
                        key={index} 
                        className="bg-yellow-200 dark:bg-yellow-600 text-gray-900 dark:text-gray-100 px-0.5 rounded"
                    >
                        {part}
                    </mark>
                );
            }
            return part;
        });
    };

    const getRowBackgroundColor = () => {
        if (level === 0) return 'bg-gray-50 dark:bg-gray-800/50';
        if (level === 1) return 'bg-white dark:bg-gray-900';
        if (level === 2) return 'bg-gray-50/50 dark:bg-gray-800/30';
        return 'bg-white dark:bg-gray-900';
    };

    const getFontWeight = () => {
        if (level === 0) return 'font-bold';
        if (level === 1) return 'font-semibold';
        if (level === 2) return 'font-medium';
        return 'font-normal';
    };

    const getTextSize = () => {
        if (level === 0) return 'text-base';
        if (level === 1) return 'text-sm';
        return 'text-sm';
    };

    const getPadding = () => {
        if (level === 0) return 'py-3';
        if (level === 1) return 'py-2.5';
        return 'py-2';
    };

    return (
        <>
            <tr className={`${getRowBackgroundColor()} transition-colors`}>
                {/* Category Name */}
                <td className={`px-4 ${getPadding()}`}>
                    <div className="flex items-center gap-2" style={{ paddingLeft: `${level * 20}px` }}>
                        {hasChildren ? (
                            <button
                                onClick={() => onToggle(group.id)}
                                className="flex-shrink-0 p-0.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                            >
                                {isExpanded ? (
                                    <ChevronDown className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                ) : (
                                    <ChevronRight className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                )}
                            </button>
                        ) : (
                            <div className="w-5 flex-shrink-0" />
                        )}
                        <span className={`${getFontWeight()} ${getTextSize()} text-gray-900 dark:text-gray-100`}>
                            {highlightText(group.name)}
                        </span>
                        {hasChildren && (
                            <span className="ml-2 px-1.5 py-0.5 text-xs rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                {group.children!.length}
                            </span>
                        )}
                    </div>
                </td>

                {/* Current Revenue */}
                <td className={`px-4 ${getPadding()} text-right`}>
                    <span className={`${getFontWeight()} ${getTextSize()} text-gray-800 dark:text-gray-200`}>
                        {group.formatted_current}
                    </span>
                </td>

                {/* Previous Revenue */}
                <td className={`px-4 ${getPadding()} text-right`}>
                    <span className={`${getFontWeight()} ${getTextSize()} text-gray-600 dark:text-gray-400`}>
                        {group.formatted_previous}
                    </span>
                </td>

                {/* Growth */}
                <td className={`px-4 ${getPadding()} text-right`}>
                    <div className={`inline-flex items-center gap-1 ${getFontWeight()} ${getTextSize()} ${
                        group.is_positive 
                            ? 'text-green-600 dark:text-green-400' 
                            : 'text-red-600 dark:text-red-400'
                    }`}>
                        {group.is_positive ? (
                            <TrendingUp className="h-3.5 w-3.5" />
                        ) : (
                            <TrendingDown className="h-3.5 w-3.5" />
                        )}
                        <span>{group.growth_percentage > 0 ? '+' : ''}{group.growth_percentage}%</span>
                    </div>
                </td>
            </tr>

            {/* Render children recursively */}
            {isExpanded && hasChildren && group.children!.map((child) => (
                <YtdTreeNode
                    key={child.id}
                    group={child}
                    level={level + 1}
                    expandedItems={expandedItems}
                    onToggle={onToggle}
                    searchQuery={searchQuery}
                    matchedIds={matchedIds}
                />
            ))}
        </>
    );
};

const YtdComparisonModal: React.FC<YtdComparisonModalProps> = ({
    isOpen,
    onClose
}) => {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;

    const [currentYearInput, setCurrentYearInput] = useState<number>(currentYear);
    const [currentMonthInput, setCurrentMonthInput] = useState<number>(currentMonth);
    const [previousYearInput, setPreviousYearInput] = useState<number>(currentYear - 1);
    const [previousMonthInput, setPreviousMonthInput] = useState<number>(currentMonth);
    
    const [comparisonData, setComparisonData] = useState<YtdComparisonData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Available periods from database
    const [availableYears, setAvailableYears] = useState<number[]>([]);
    const [availableMonthsByYear, setAvailableMonthsByYear] = useState<Record<number, number[]>>({});
    const [loadingPeriods, setLoadingPeriods] = useState(true);

    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    // Fetch available periods from database
    useEffect(() => {
        const fetchAvailablePeriods = async () => {
            try {
                setLoadingPeriods(true);
                const response = await axios.get('/api/dashboard/available-periods');
                
                if (response.data.success) {
                    const { years, months_by_year } = response.data.data;
                    setAvailableYears(years);
                    setAvailableMonthsByYear(months_by_year);
                    
                    // Set default values based on available data
                    if (years.length > 0) {
                        const latestYear = years[0];
                        setCurrentYearInput(latestYear);
                        
                        // Set current month to latest available month in latest year
                        const latestYearMonths = months_by_year[latestYear] || [];
                        if (latestYearMonths.length > 0) {
                            setCurrentMonthInput(latestYearMonths[latestYearMonths.length - 1]);
                        }
                        
                        // Set previous year
                        if (years.length > 1) {
                            const previousYear = years[1];
                            setPreviousYearInput(previousYear);
                            
                            const previousYearMonths = months_by_year[previousYear] || [];
                            if (previousYearMonths.length > 0) {
                                setPreviousMonthInput(previousYearMonths[previousYearMonths.length - 1]);
                            }
                        }
                    }
                }
            } catch (err) {
                // Silent error handling - no console log in production
            } finally {
                setLoadingPeriods(false);
            }
        };

        if (isOpen) {
            fetchAvailablePeriods();
        }
    }, [isOpen]);

    // Get available months for selected year
    const getCurrentAvailableMonths = () => {
        return availableMonthsByYear[currentYearInput] || [];
    };

    const getPreviousAvailableMonths = () => {
        return availableMonthsByYear[previousYearInput] || [];
    };

    const fetchComparison = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.get('/api/dashboard/ytd-comparison-custom', {
                params: {
                    current_year: currentYearInput,
                    current_month: currentMonthInput,
                    previous_year: previousYearInput,
                    previous_month: previousMonthInput
                }
            });          
            if (response.data.success) {
                setComparisonData(response.data.data);
            } else {
                setError('Failed to fetch comparison data');
            }
        } catch (err: any) {
            setError(err.response?.data?.message || 'Error loading comparison data');
        } finally {
            setLoading(false);
        }
    };

    const handleCompare = () => {
        if (currentYearInput && currentMonthInput && previousYearInput && previousMonthInput) {
            fetchComparison();
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="medium-modal max-w-7xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl">
                        <Calendar className="h-6 w-6 text-red-600 dark:text-red-400" />
                        Custom YTD Comparison
                    </DialogTitle>
                    <DialogDescription>
                        Compare Year-to-Date revenue between different periods
                    </DialogDescription>
                </DialogHeader>

                {/* Input Section */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    {/* Current Period */}
                    <div className="space-y-4 p-5 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center gap-2">
                            <div className="p-1.5 bg-blue-50 dark:bg-blue-950 rounded">
                                <TrendingUp className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <h3 className="font-semibold text-gray-900 dark:text-gray-100">
                                Current Period (YTD)
                            </h3>
                        </div>
                        {loadingPeriods ? (
                            <div className="flex justify-center py-4">
                                <Loader2 className="h-6 w-6 animate-spin text-blue-600" />
                            </div>
                        ) : (
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Year
                                    </label>
                                    <select
                                        value={currentYearInput}
                                        onChange={(e) => {
                                            const newYear = Number(e.target.value);
                                            setCurrentYearInput(newYear);
                                            // Reset month to first available month in selected year
                                            const availableMonths = availableMonthsByYear[newYear] || [];
                                            if (availableMonths.length > 0 && !availableMonths.includes(currentMonthInput)) {
                                                setCurrentMonthInput(availableMonths[availableMonths.length - 1]);
                                            }
                                        }}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                        disabled={availableYears.length === 0}
                                    >
                                        {availableYears.map((year: number) => (
                                            <option key={year} value={year}>{year}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Up to Month
                                    </label>
                                    <select
                                        value={currentMonthInput}
                                        onChange={(e) => setCurrentMonthInput(Number(e.target.value))}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                        disabled={getCurrentAvailableMonths().length === 0}
                                    >
                                        {getCurrentAvailableMonths().map((month: number) => (
                                            <option key={month} value={month}>
                                                {monthNames[month - 1]}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Previous Period */}
                    <div className="space-y-4 p-5 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center gap-2">
                            <div className="p-1.5 bg-orange-50 dark:bg-orange-950 rounded">
                                <TrendingDown className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                            </div>
                            <h3 className="font-semibold text-gray-900 dark:text-gray-100">
                                Comparison Period (PYTD)
                            </h3>
                        </div>
                        {loadingPeriods ? (
                            <div className="flex justify-center py-4">
                                <Loader2 className="h-6 w-6 animate-spin text-orange-600" />
                            </div>
                        ) : (
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Year
                                    </label>
                                    <select
                                        value={previousYearInput}
                                        onChange={(e) => {
                                            const newYear = Number(e.target.value);
                                            setPreviousYearInput(newYear);
                                            // Reset month to first available month in selected year
                                            const availableMonths = availableMonthsByYear[newYear] || [];
                                            if (availableMonths.length > 0 && !availableMonths.includes(previousMonthInput)) {
                                                setPreviousMonthInput(availableMonths[availableMonths.length - 1]);
                                            }
                                        }}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500"
                                        disabled={availableYears.length === 0}
                                    >
                                        {availableYears.map((year: number) => (
                                            <option key={year} value={year}>{year}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Up to Month
                                    </label>
                                    <select
                                        value={previousMonthInput}
                                        onChange={(e) => setPreviousMonthInput(Number(e.target.value))}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500"
                                        disabled={getPreviousAvailableMonths().length === 0}
                                    >
                                        {getPreviousAvailableMonths().map((month: number) => (
                                            <option key={month} value={month}>
                                                {monthNames[month - 1]}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Compare Button */}
                <div className="flex justify-center mt-4">
                    <Button
                        onClick={handleCompare}
                        disabled={loading}
                        className="px-8 py-2 bg-red-600 hover:bg-red-700 text-white"
                    >
                        {loading ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                Comparing...
                            </>
                        ) : (
                            'Compare Periods'
                        )}
                    </Button>
                </div>

                {/* Error Message */}
                {error && (
                    <div className="mt-4 p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg">
                        <p className="text-red-700 dark:text-red-300 text-sm">{error}</p>
                    </div>
                )}

                {/* Results Section */}
                {comparisonData && !loading && (
                    <div className="mt-6 space-y-6">
                        {/* Comparison Summary */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {/* Current YTD */}
                            <div className="p-5 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                                <div className="flex items-center gap-2 mb-3">
                                    <div className="p-1.5 bg-blue-50 dark:bg-blue-950 rounded">
                                        <TrendingUp className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {comparisonData.current_month_name} {comparisonData.current_year} YTD
                                    </p>
                                </div>
                                <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {comparisonData.formatted_current_revenue}
                                </p>
                            </div>

                            {/* Growth */}
                            <div className="p-5 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                                <div className="flex items-center gap-2 mb-3">
                                    <div className={`p-1.5 rounded ${
                                        comparisonData.is_positive_growth
                                            ? 'bg-green-50 dark:bg-green-950'
                                            : 'bg-red-50 dark:bg-red-950'
                                    }`}>
                                        {comparisonData.is_positive_growth ? (
                                            <TrendingUp className="h-4 w-4 text-green-600 dark:text-green-400" />
                                        ) : (
                                            <TrendingDown className="h-4 w-4 text-red-600 dark:text-red-400" />
                                        )}
                                    </div>
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Growth
                                    </p>
                                </div>
                                <p className={`text-2xl font-bold ${
                                    comparisonData.is_positive_growth 
                                        ? 'text-green-600 dark:text-green-400' 
                                        : 'text-red-600 dark:text-red-400'
                                }`}>
                                    {comparisonData.growth_percentage > 0 ? '+' : ''}{comparisonData.growth_percentage}%
                                </p>
                            </div>

                            {/* Previous YTD */}
                            <div className="p-5 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                                <div className="flex items-center gap-2 mb-3">
                                    <div className="p-1.5 bg-orange-50 dark:bg-orange-950 rounded">
                                        <TrendingDown className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                    </div>
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {comparisonData.previous_month_name} {comparisonData.previous_year} YTD
                                    </p>
                                </div>
                                <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {comparisonData.formatted_previous_revenue}
                                </p>
                            </div>
                        </div>

                        {/* Detailed Breakdown */}
                        <div className="p-6 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
                            <h4 className="font-semibold text-gray-900 dark:text-gray-100 mb-4">Detailed Breakdown</h4>
                            <div className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-gray-600 dark:text-gray-400">Growth Amount:</span>
                                    <span className={`font-semibold ${
                                        comparisonData.is_positive_growth 
                                            ? 'text-green-600 dark:text-green-400' 
                                            : 'text-red-600 dark:text-red-400'
                                    }`}>
                                        {comparisonData.growth_amount > 0 ? '+' : ''}{comparisonData.formatted_growth_amount}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600 dark:text-gray-400">Comparison Period:</span>
                                    <span className="font-medium text-gray-900 dark:text-gray-100">
                                        {comparisonData.current_month_name} {comparisonData.current_year} vs {comparisonData.previous_month_name} {comparisonData.previous_year}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Group1 Breakdown Tree */}
                        <div className="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                            <div className="bg-gray-100 dark:bg-gray-800 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h4 className="font-semibold text-gray-900 dark:text-gray-100">Revenue Breakdown by Category</h4>
                            </div>
                            <div className="p-4">
                                <YtdBreakdownTree 
                                    data={comparisonData.hierarchical_breakdown}
                                    currentPeriod={`${comparisonData.current_month_name} ${comparisonData.current_year}`}
                                    previousPeriod={`${comparisonData.previous_month_name} ${comparisonData.previous_year}`}
                                />
                            </div>
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
};

export default YtdComparisonModal;
