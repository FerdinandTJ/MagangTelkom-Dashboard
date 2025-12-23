import React from 'react';
import { Target, Users, Calendar } from 'lucide-react';

interface Summary {
    formatted_target_revenue: string;
    formatted_realisasi_revenue: string;
    total_am: number;
}

interface SummaryCardsSectionProps {
    currentSummary: Summary | undefined;
    periodLabel: string;
}

export default function SummaryCardsSection({ currentSummary, periodLabel }: SummaryCardsSectionProps) {
    return (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {/* Card 1: Revenue Target */}
            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div className="p-4">
                    <div className="flex items-start justify-between">
                        <div className="flex-1">
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Revenue Target</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {currentSummary?.formatted_target_revenue}
                            </p>
                            <p className="text-sm text-gray-500 dark:text-gray-400">Total target regional</p>
                        </div>
                        <div className="flex-shrink-0 ml-4">
                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                <Target className="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Card 2: Revenue Actual */}
            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div className="p-4">
                    <div className="flex items-start justify-between">
                        <div className="flex-1">
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Revenue Actual</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {currentSummary?.formatted_realisasi_revenue}
                            </p>
                            <p className="text-sm text-gray-500 dark:text-gray-400">Total realisasi regional</p>
                        </div>
                        <div className="flex-shrink-0 ml-4">
                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                <Target className="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Card 3: Jumlah AM */}
            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div className="p-4">
                    <div className="flex items-start justify-between">
                        <div className="flex-1">
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Jumlah AM</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {currentSummary?.total_am}
                            </p>
                            <p className="text-sm text-gray-500 dark:text-gray-400">Account Managers</p>
                        </div>
                        <div className="flex-shrink-0 ml-4">
                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                <Users className="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Card 4: Period */}
            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div className="p-4">
                    <div className="flex items-start justify-between">
                        <div className="flex-1">
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Period</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {periodLabel}
                            </p>
                            <p className="text-sm text-gray-500 dark:text-gray-400">Periode aktif</p>
                        </div>
                        <div className="flex-shrink-0 ml-4">
                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                <Calendar className="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
