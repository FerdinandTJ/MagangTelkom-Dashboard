import React, { useState, createContext, useContext } from 'react';
import { ChevronDown, ChevronRight, ChevronsDown, ChevronsUp } from 'lucide-react';
import { formatCurrency } from '@/utils/currency';

interface RevenueNode {
    id: string;
    name: string;
    type: 'category' | 'subcategory' | 'product' | 'project';
    revenue: number;
    target?: number;
    achievement_percentage?: number;
    children?: RevenueNode[];
    metadata?: {
        period?: string;
        status?: 'active' | 'completed' | 'cancelled';
        am_name?: string;
    };
}

interface RevenueBreakdownTreeProps {
    data: RevenueNode[];
    level?: number;
}

const ExpandContext = createContext<{
    expandAll: boolean | null;
    setExpandAll: (value: boolean | null) => void;
}>({
    expandAll: null,
    setExpandAll: () => {}
});

const RevenueBreakdownTree: React.FC<RevenueBreakdownTreeProps> = ({ data, level = 0 }) => {
    const [expandAll, setExpandAll] = useState<boolean | null>(null);

    if (level === 0) {
        // Root level - render table wrapper with controls
        return (
            <ExpandContext.Provider value={{ expandAll, setExpandAll }}>
                <div className="space-y-3">
                    {/* Control Buttons */}
                    <div className="flex justify-end gap-2">
                        <button
                            onClick={() => setExpandAll(true)}
                            className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors"
                        >
                            <ChevronsDown className="h-4 w-4" />
                            Expand All
                        </button>
                        <button
                            onClick={() => setExpandAll(false)}
                            className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors"
                        >
                            <ChevronsUp className="h-4 w-4" />
                            Collapse All
                        </button>
                    </div>

                    {/* Table */}
                    <div className="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table className="w-full border-collapse">
                            <thead>
                                <tr className="bg-gradient-to-r from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-850 border-b-2 border-gray-300 dark:border-gray-600">
                                    <th className="text-left p-4 font-bold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-wider">
                                        Source Name
                                    </th>
                                    <th className="text-right p-4 font-bold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-wider w-64">
                                        Revenue
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.map((node) => (
                                    <TreeNode key={node.id} node={node} level={level} />
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </ExpandContext.Provider>
        );
    }

    // Nested levels - just render rows
    return (
        <>
            {data.map((node) => (
                <TreeNode key={node.id} node={node} level={level} />
            ))}
        </>
    );
};


const TreeNode: React.FC<{ node: RevenueNode; level: number }> = ({ node, level }) => {
    const { expandAll } = useContext(ExpandContext);
    const [isExpanded, setIsExpanded] = useState(level < 2); // Auto-expand first 2 levels
    const hasChildren = node.children && node.children.length > 0;

    // React to expandAll changes
    React.useEffect(() => {
        if (expandAll !== null) {
            setIsExpanded(expandAll);
        }
    }, [expandAll]);

    const getRowBackgroundColor = () => {
        if (level === 0) return 'bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-900/30';
        if (level === 1) return 'bg-blue-50 dark:bg-blue-950/20 hover:bg-blue-100 dark:hover:bg-blue-900/30';
        if (level === 2) return 'bg-green-50 dark:bg-green-950/20 hover:bg-green-100 dark:hover:bg-green-900/30';
        if (level === 3) return 'bg-purple-50 dark:bg-purple-950/20 hover:bg-purple-100 dark:hover:bg-purple-900/30';
        return 'hover:bg-gray-50 dark:hover:bg-gray-900/20';
    };

    return (
        <>
            <tr className={`border-b border-gray-200 dark:border-gray-700 ${getRowBackgroundColor()} transition-colors`}>
                {/* Source Name with expand/collapse */}
                <td className="p-4 border-r border-gray-200 dark:border-gray-700">
                    <div className="flex items-center gap-2" style={{ paddingLeft: `${level * 24}px` }}>
                        {hasChildren ? (
                            <button
                                onClick={() => setIsExpanded(!isExpanded)}
                                className="flex-shrink-0 p-1 hover:bg-white/50 dark:hover:bg-black/30 rounded transition-colors"
                            >
                                {isExpanded ? (
                                    <ChevronDown className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                ) : (
                                    <ChevronRight className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                )}
                            </button>
                        ) : (
                            <div className="w-6 flex-shrink-0" />
                        )}
                        <span className={`font-semibold text-gray-900 dark:text-gray-100 ${
                            level === 0 ? 'text-base' : level === 1 ? 'text-sm' : 'text-xs'
                        }`}>
                            {node.name}
                        </span>
                        {hasChildren && (
                            <span className="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-medium">
                                {node.children!.length}
                            </span>
                        )}
                    </div>
                </td>

                {/* Revenue */}
                <td className="p-4 text-right bg-blue-50/50 dark:bg-blue-950/20">
                    <div className="flex flex-col items-end">
                        <span className="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Revenue</span>
                        <span className="font-bold text-blue-700 dark:text-blue-400 text-base">
                            {formatCurrency(node.revenue, 2)}
                        </span>
                    </div>
                </td>
            </tr>

            {/* Children Rows */}
            {isExpanded && hasChildren && (
                <RevenueBreakdownTree data={node.children!} level={level + 1} />
            )}
        </>
    );
};

export default RevenueBreakdownTree;
