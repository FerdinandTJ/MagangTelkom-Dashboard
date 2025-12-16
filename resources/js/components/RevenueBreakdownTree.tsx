import React, { useState, createContext, useContext } from 'react';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { formatCurrencyFull } from '@/utils/currency';

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
    selectedCategory?: string | null;
    onCategoryCleared?: () => void;
}

const ExpandContext = createContext<{
    expandAll: boolean | null;
    setExpandAll: (value: boolean | null) => void;
}>({
    expandAll: null,
    setExpandAll: () => {}
});

const RevenueBreakdownTree: React.FC<RevenueBreakdownTreeProps> = ({ data, selectedCategory, onCategoryCleared }) => {
    const [expandAll, setExpandAll] = useState<boolean | null>(null);

    // Reset expandAll when category is selected from pie chart
    React.useEffect(() => {
        if (selectedCategory) {
            setExpandAll(null);
        }
    }, [selectedCategory]);

    return (
        <ExpandContext.Provider value={{ expandAll, setExpandAll }}>
            <div className="space-y-3">
                {/* Expand/Collapse Buttons */}
                <div className="flex justify-end gap-2">
                    <button
                        onClick={() => setExpandAll(true)}
                        className="px-3 py-1.5 text-sm font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors"
                    >
                        Expand All
                    </button>
                    <button
                        onClick={() => setExpandAll(false)}
                        className="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors"
                    >
                        Collapse All
                    </button>
                </div>

                {/* Table */}
                <div className="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table className="w-full border-collapse">
                        <thead>
                            <tr className="bg-gray-100 dark:bg-gray-800">
                                <th className="text-left px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 text-sm">
                                    Revenue Source
                                </th>
                                <th className="text-right px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 text-sm w-48">
                                    Revenue
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                            {data.map((node) => (
                                <TreeNode 
                                    key={node.id} 
                                    node={node} 
                                    level={0}
                                    selectedCategory={selectedCategory}
                                    onCategoryCleared={onCategoryCleared}
                                />
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </ExpandContext.Provider>
    );
};

interface TreeNodeProps {
    node: RevenueNode;
    level: number;
    selectedCategory?: string | null;
    onCategoryCleared?: () => void;
    expandFromCategory?: boolean;
}

const TreeNode: React.FC<TreeNodeProps> = ({ node, level, selectedCategory, onCategoryCleared, expandFromCategory = false }) => {
    const { expandAll } = useContext(ExpandContext);
    const [isExpanded, setIsExpanded] = useState(false);
    const hasChildren = node.children && node.children.length > 0;
    const nodeRef = React.useRef<HTMLTableRowElement>(null);

    // Determine if this node should be expanded from category selection
    const isSelectedCategory = selectedCategory && level === 0 && 
        node.name.toUpperCase() === selectedCategory.toUpperCase();
    const shouldAutoExpand = isSelectedCategory || (expandFromCategory && hasChildren);

    // React to expandAll changes - this takes priority over category expansion
    React.useEffect(() => {
        if (expandAll !== null) {
            setIsExpanded(expandAll);
        }
    }, [expandAll]);

    // Auto-expand if this node matches the selected category or is descendant of selected category
    React.useEffect(() => {
        if (shouldAutoExpand && expandAll === null) {
            setIsExpanded(true);
            
            // Only scroll for the top-level category node
            if (isSelectedCategory) {
                setTimeout(() => {
                    nodeRef.current?.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    
                    // Clear selection after scrolling
                    if (onCategoryCleared) {
                        setTimeout(() => {
                            onCategoryCleared();
                        }, 1000);
                    }
                }, 100);
            }
        }
    }, [shouldAutoExpand, isSelectedCategory, onCategoryCleared, expandAll]);

    const getRowStyle = () => {
        // Level 0: Bold, larger text, darker background
        if (level === 0) return {
            bg: 'bg-gray-50 dark:bg-gray-800/50',
            textWeight: 'font-bold',
            textSize: 'text-base',
            textColor: 'text-gray-900 dark:text-gray-100',
            revenueWeight: 'font-bold',
            revenueSize: 'text-base',
            revenueColor: 'text-gray-900 dark:text-gray-100',
            py: 'py-3'
        };
        // Level 1: Semi-bold, medium background
        if (level === 1) return {
            bg: 'bg-white dark:bg-gray-900',
            textWeight: 'font-semibold',
            textSize: 'text-sm',
            textColor: 'text-gray-800 dark:text-gray-200',
            revenueWeight: 'font-semibold',
            revenueSize: 'text-sm',
            revenueColor: 'text-gray-800 dark:text-gray-200',
            py: 'py-2.5'
        };
        // Level 2: Medium weight
        if (level === 2) return {
            bg: 'bg-gray-50/50 dark:bg-gray-800/30',
            textWeight: 'font-medium',
            textSize: 'text-sm',
            textColor: 'text-gray-700 dark:text-gray-300',
            revenueWeight: 'font-medium',
            revenueSize: 'text-sm',
            revenueColor: 'text-gray-700 dark:text-gray-300',
            py: 'py-2'
        };
        // Level 3+: Normal weight
        return {
            bg: 'bg-white dark:bg-gray-900',
            textWeight: 'font-normal',
            textSize: 'text-sm',
            textColor: 'text-gray-600 dark:text-gray-400',
            revenueWeight: 'font-normal',
            revenueSize: 'text-sm',
            revenueColor: 'text-gray-600 dark:text-gray-400',
            py: 'py-2'
        };
    };

    const style = getRowStyle();

    return (
        <>
            <tr 
                ref={nodeRef}
                className={`${style.bg} hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border-b border-gray-200 dark:border-gray-700`}
            >
                <td className={`px-4 ${style.py}`}>
                    <div className="flex items-center gap-2" style={{ paddingLeft: `${level * 24}px` }}>
                        {hasChildren ? (
                            <button
                                onClick={() => setIsExpanded(!isExpanded)}
                                className="flex-shrink-0 p-0.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded"
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
                        <span className={`${style.textWeight} ${style.textSize} ${style.textColor}`}>
                            {node.name}
                        </span>
                        {hasChildren && (
                            <span className="ml-1 text-xs text-gray-500 dark:text-gray-500">
                                ({node.children!.length})
                            </span>
                        )}
                    </div>
                </td>
                <td className={`px-4 ${style.py} text-right`}>
                    <span className={`${style.revenueWeight} ${style.revenueSize} ${style.revenueColor}`}>
                        {formatCurrencyFull(node.revenue)}
                    </span>
                </td>
            </tr>

            {isExpanded && hasChildren && node.children!.map((child) => (
                <TreeNode 
                    key={child.id} 
                    node={child} 
                    level={level + 1}
                    selectedCategory={selectedCategory}
                    onCategoryCleared={onCategoryCleared}
                    expandFromCategory={shouldAutoExpand}
                />
            ))}
        </>
    );
};

export default RevenueBreakdownTree;
