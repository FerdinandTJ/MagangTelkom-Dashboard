import React from 'react';

interface StatCardProps {
    title: string;
    value: string | number;
    subtitle?: string;
    icon?: React.ReactNode;
    trend?: {
        value: number | null;
        isPositive: boolean | null;
        label: string;
        hasPreviousData?: boolean;
    };
    className?: string;
    tooltip?: string;
}

const StatCard: React.FC<StatCardProps> = ({
    title,
    value,
    subtitle,
    icon,
    trend,
    className = '',
    tooltip
}) => {
    return (
        <div className={`bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 ${className}`}>
            <div className="pb-1 pt-4 px-4">
                <div className="flex items-start justify-between">
                    <div className="flex-1">
                        <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{title}</p>
                        <p 
                            className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1 group relative cursor-help"
                            title={tooltip}
                        >
                            {value}
                            {tooltip && (
                                <span className="invisible group-hover:visible absolute left-0 top-full mt-2 w-max max-w-xs bg-gray-900 dark:bg-gray-700 text-white text-xs px-2 py-1.5 rounded shadow-lg z-10 whitespace-nowrap">
                                    {tooltip}
                                </span>
                            )}
                        </p>
                        {subtitle && (
                            <p className="text-sm text-gray-500 dark:text-gray-400">{subtitle}</p>
                        )}
                    </div>
                    {icon && (
                        <div className="flex-shrink-0 ml-4">
                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                {icon}
                            </div>
                        </div>
                    )}
                </div>
            </div>
            
            {trend && (
                <div className="px-4 pb-1 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <div className="flex items-center">
                        {trend.value !== null && trend.isPositive !== null ? (
                            <>
                                <span className={`inline-flex items-center text-sm font-medium ${
                                    trend.isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                                }`}>
                                    {trend.isPositive ? (
                                        <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                        </svg>
                                    ) : (
                                        <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    )}
                                    {Math.abs(trend.value)}%
                                </span>
                                <span className="text-sm text-gray-500 dark:text-gray-400 ml-1">{trend.label}</span>
                            </>
                        ) : (
                            <span className="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium" title="No previous year data available">
                                N/A
                            </span>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default StatCard;