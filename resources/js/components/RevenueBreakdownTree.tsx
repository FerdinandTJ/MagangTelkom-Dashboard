import React, { useState } from 'react';
import { ChevronDown, ChevronRight, Folder, FolderOpen, FileText, TrendingUp, Target } from 'lucide-react';
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

const RevenueBreakdownTree: React.FC<RevenueBreakdownTreeProps> = ({ data, level = 0 }) => {
    return (
        <div className={level === 0 ? 'space-y-2' : 'space-y-1'}>
            {data.map((node) => (
                <TreeNode key={node.id} node={node} level={level} />
            ))}
        </div>
    );
};

const TreeNode: React.FC<{ node: RevenueNode; level: number }> = ({ node, level }) => {
    const [isExpanded, setIsExpanded] = useState(level < 2); // Auto-expand first 2 levels
    const hasChildren = node.children && node.children.length > 0;

    const getIcon = () => {
        if (node.type === 'project') {
            return <FileText className="h-4 w-4 text-blue-500 dark:text-blue-400" />;
        }
        return isExpanded ? (
            <FolderOpen className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
        ) : (
            <Folder className="h-4 w-4 text-yellow-700 dark:text-yellow-500" />
        );
    };

    const getBackgroundColor = () => {
        switch (level) {
            case 0: return 'bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-950/30 dark:to-pink-950/30 hover:from-red-100 hover:to-pink-100 dark:hover:from-red-900/40 dark:hover:to-pink-900/40';
            case 1: return 'bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/40';
            case 2: return 'bg-green-50 dark:bg-green-950/30 hover:bg-green-100 dark:hover:bg-green-900/40';
            case 3: return 'bg-purple-50 dark:bg-purple-950/30 hover:bg-purple-100 dark:hover:bg-purple-900/40';
            default: return 'bg-gray-50 dark:bg-gray-900/30 hover:bg-gray-100 dark:hover:bg-gray-800/40';
        }
    };

    const getBorderColor = () => {
        switch (level) {
            case 0: return 'border-red-200 dark:border-red-800';
            case 1: return 'border-blue-200 dark:border-blue-800';
            case 2: return 'border-green-200 dark:border-green-800';
            case 3: return 'border-purple-200 dark:border-purple-800';
            default: return 'border-gray-200 dark:border-gray-700';
        }
    };

    const getAchievementColor = () => {
        if (!node.achievement_percentage) return 'text-gray-500 dark:text-gray-400';
        if (node.achievement_percentage >= 100) return 'text-green-600 dark:text-green-400';
        if (node.achievement_percentage >= 80) return 'text-yellow-600 dark:text-yellow-400';
        return 'text-red-600 dark:text-red-400';
    };

    const paddingClass = level === 0 ? 'p-4' : level === 1 ? 'p-3' : 'p-2';

    return (
        <div style={{ marginLeft: `${level * 24}px` }}>
            <div
                className={`
                    ${getBackgroundColor()}
                    ${getBorderColor()}
                    ${paddingClass}
                    border rounded-lg
                    transition-all duration-200
                    cursor-pointer
                `}
                onClick={() => hasChildren && setIsExpanded(!isExpanded)}
            >
                <div className="flex items-center justify-between gap-4">
                    {/* Left Section: Icon + Name */}
                    <div className="flex items-center gap-2 min-w-0 flex-1">
                        {hasChildren && (
                            <button
                                className="flex-shrink-0 p-1 hover:bg-white/50 dark:hover:bg-black/20 rounded"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    setIsExpanded(!isExpanded);
                                }}
                            >
                                {isExpanded ? (
                                    <ChevronDown className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                ) : (
                                    <ChevronRight className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                )}
                            </button>
                        )}
                        {!hasChildren && <div className="w-6" />}
                        
                        <div className="flex-shrink-0">{getIcon()}</div>
                        
                        <div className="min-w-0 flex-1">
                            <h4 className={`font-semibold truncate ${
                                level === 0 ? 'text-base' : 
                                level === 1 ? 'text-sm' : 
                                'text-xs'
                            } text-gray-900 dark:text-gray-100`}>
                                {node.name}
                            </h4>
                            {node.metadata && (
                                <div className="flex items-center gap-2 mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {node.metadata.period && (
                                        <span>📅 {node.metadata.period}</span>
                                    )}
                                    {node.metadata.status && (
                                        <span className={`px-2 py-0.5 rounded ${
                                            node.metadata.status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                            node.metadata.status === 'completed' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                            'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
                                        }`}>
                                            {node.metadata.status}
                                        </span>
                                    )}
                                    {node.metadata.am_name && (
                                        <span>👤 {node.metadata.am_name}</span>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right Section: Revenue Metrics */}
                    <div className="flex items-center gap-4 flex-shrink-0">
                        {/* Revenue */}
                        <div className="text-right">
                            <div className="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400 mb-0.5">
                                <TrendingUp className="h-3 w-3" />
                                <span>Revenue</span>
                            </div>
                            <p className={`font-bold ${
                                level === 0 ? 'text-lg' : 
                                level === 1 ? 'text-base' : 
                                'text-sm'
                            } text-blue-600 dark:text-blue-400`}>
                                {formatCurrency(node.revenue, 2)}
                            </p>
                        </div>

                        {/* Target & Achievement */}
                        {node.target !== undefined && (
                            <div className="text-right">
                                <div className="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400 mb-0.5">
                                    <Target className="h-3 w-3" />
                                    <span>Target</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <p className="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {formatCurrency(node.target, 2)}
                                    </p>
                                    {node.achievement_percentage !== undefined && (
                                        <span className={`text-xs font-bold ${getAchievementColor()}`}>
                                            ({node.achievement_percentage.toFixed(1)}%)
                                        </span>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Child Count Badge */}
                        {hasChildren && (
                            <div className="bg-white dark:bg-gray-800 px-2 py-1 rounded text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                {node.children!.length} items
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Children */}
            {isExpanded && hasChildren && (
                <div className="mt-2">
                    <RevenueBreakdownTree data={node.children!} level={level + 1} />
                </div>
            )}
        </div>
    );
};

export default RevenueBreakdownTree;
