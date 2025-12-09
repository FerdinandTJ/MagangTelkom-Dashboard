import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import SubsegmentPieChart from '@/components/charts/SubsegmentPieChart';
import { Loader2, Calendar, TrendingUp, Building2, Target } from 'lucide-react';
import { SubsegmentData, MonthData } from '@/types/dashboard';
import axios from '@/lib/axios';

interface MonthDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    monthData: MonthData | null;
    year: number;
    onSubsegmentClick?: (subsegment: string) => void;
}

interface MonthDetailsResponse {
    subsegments: SubsegmentData[];
    month_name: string;
    month: number;
    year: number;
    total_revenue: number;
    total_target: number;
    total_companies: number;
    formatted_total_revenue: string;
    formatted_total_target: string;
    achievement_percentage: number;
}

const MonthDetailModal: React.FC<MonthDetailModalProps> = ({
    isOpen,
    onClose,
    monthData,
    year,
    onSubsegmentClick
}) => {
    const [subsegments, setSubsegments] = useState<SubsegmentData[]>([]);
    const [monthDetails, setMonthDetails] = useState<MonthDetailsResponse | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (isOpen && monthData) {
            fetchSubsegmentDetails();
        }
    }, [isOpen, monthData, year]);

    const fetchSubsegmentDetails = async () => {
        if (!monthData) return;
        
        setLoading(true);
        setError(null);
        
        try {
            const response = await axios.get(`/api/dashboard/month-details`, {
                params: {
                    year: year,
                    month: monthData.bulan
                }
            });
            
            if (response.data.success) {
                setSubsegments(response.data.data.subsegments);
                setMonthDetails(response.data.data);
            } else {
                setError('Failed to fetch subsegment details');
            }
        } catch (err) {
            setError('Error loading subsegment data');
        } finally {
            setLoading(false);
        }
    };

    const handleSubsegmentClick = (subsegmentData: SubsegmentData) => {
        if (onSubsegmentClick && monthData) {
            onSubsegmentClick(subsegmentData.subsegment);
        }
    };

    if (!monthData) return null;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <Calendar className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">Revenue Details - {monthData.bulan_name} {year}</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Breakdown revenue per subsegment untuk bulan {monthData.bulan_name} {year}
                    </DialogDescription>
                </DialogHeader>

                {/* Summary Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                        <div className="flex items-center gap-3 mb-3">
                            <div className="p-2 bg-red-50 dark:bg-red-950/30 rounded-lg">
                                <TrendingUp className="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</span>
                        </div>
                        <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {monthDetails?.formatted_total_revenue || monthData.formatted_revenue}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500 mt-1">Monthly earnings</p>
                    </div>
                    
                    <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                        <div className="flex items-center gap-3 mb-3">
                            <div className="p-2 bg-blue-50 dark:bg-blue-950/30 rounded-lg">
                                <Target className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Target</span>
                        </div>
                        <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {monthDetails?.formatted_total_target || 'Loading...'}
                        </p>
                        {monthDetails?.achievement_percentage !== undefined && (
                            <p className="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                {monthDetails.achievement_percentage}% achieved
                            </p>
                        )}
                    </div>
                    
                    <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                        <div className="flex items-center gap-3 mb-3">
                            <div className="p-2 bg-green-50 dark:bg-green-950/30 rounded-lg">
                                <Building2 className="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Companies</span>
                        </div>
                        <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {monthDetails?.total_companies || monthData.total_companies}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500 mt-1">Active customers</p>
                    </div>
                </div>

                {loading && (
                    <div className="flex items-center justify-center py-8">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading subsegment details...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4 mb-6">
                        <p className="text-red-700 dark:text-red-300">{error}</p>
                        <Button 
                            onClick={fetchSubsegmentDetails}
                            variant="outline" 
                            size="sm" 
                            className="mt-2"
                        >
                            Retry
                        </Button>
                    </div>
                )}

                {!loading && !error && subsegments.length > 0 && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Pie Chart */}
                        <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5">
                            <h3 className="font-semibold text-gray-900 dark:text-white mb-4">Revenue Distribution</h3>
                            <SubsegmentPieChart 
                                data={subsegments}
                                height={320}
                                onSegmentClick={handleSubsegmentClick}
                            />
                        </div>

                        {/* Subsegment List */}
                        <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5">
                            <h3 className="font-semibold text-gray-900 dark:text-white mb-4">Subsegment Performance</h3>
                            <div className="space-y-3 max-h-[320px] overflow-y-auto pr-2">
                                {subsegments.map((subsegment, index) => (
                                    <div 
                                        key={subsegment.subsegment}
                                        className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                                        onClick={() => handleSubsegmentClick(subsegment)}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex items-center justify-center w-8 h-8 bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-400 rounded-full text-sm font-semibold">
                                                {index + 1}
                                            </div>
                                            <div>
                                                <p className="font-medium text-gray-900 dark:text-white">{subsegment.subsegment}</p>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">{subsegment.total_companies} companies</p>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-semibold text-gray-900 dark:text-white">{subsegment.formatted_total_revenue}</p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">Avg: {subsegment.formatted_avg_revenue}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                <div className="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <Button 
                        variant="outline" 
                        onClick={onClose}
                        className="px-6 py-2"
                    >
                        Close
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
};

export default MonthDetailModal;