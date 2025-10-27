import React from 'react';

interface StatCardProps {
    title: string;
    value: string | number;
    subtitle?: string;
    icon?: React.ReactNode;
    trend?: {
        value: number;
        isPositive: boolean;
        label: string;
    };
    className?: string;
}

const StatCard: React.FC<StatCardProps> = ({
    title,
    value,
    subtitle,
    icon,
    trend,
    className = ''
}) => {
    return (
        <div className={`bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200 p-6 ${className}`}>
            <div className="flex items-start justify-between">
                <div className="flex-1">
                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{title}</p>
                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">{value}</p>
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
            
            {trend && (
                <div className="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div className="flex items-center">
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
                    </div>
                </div>
            )}
        </div>
    );
};

export default StatCard;