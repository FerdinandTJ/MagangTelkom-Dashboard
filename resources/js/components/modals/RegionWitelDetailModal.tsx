import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, MapPin, TrendingUp, Building2, Target, Users } from 'lucide-react';
import axios from '@/lib/axios';
import { formatCurrency } from '@/utils/currency';

interface RegionWitelDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    regionCode: string | null;
    regionName: string | null;
    year: number;
    quartal: string;
    isYearToDate?: boolean;
}

interface WitelData {
    witel_name: string;
    t_revenue: number;
    formatted_t_revenue: string;
    r_revenue: number;
    formatted_r_revenue: string;
    am_count: number;
    achievement_percentage: number;
}

interface RegionWitelDetail {
    summary: {
        total_target_revenue: number;
        formatted_total_target_revenue: string;
        total_realisasi_revenue: number;
        formatted_total_realisasi_revenue: string;
        total_am: number;
        total_witel: number;
        achievement_percentage: number;
        period: string;
    };
    witels: WitelData[];
    region_code: string;
    region_name: string;
}

const RegionWitelDetailModal: React.FC<RegionWitelDetailModalProps> = ({
    isOpen,
    onClose,
    regionCode,
    regionName,
    year,
    quartal,
    isYearToDate = false
}) => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [data, setData] = useState<RegionWitelDetail | null>(null);

    useEffect(() => {
        if (isOpen && regionCode) {
            fetchRegionWitelDetail();
        }
    }, [isOpen, regionCode, year, quartal, isYearToDate]);

    const fetchRegionWitelDetail = async () => {
        setLoading(true);
        setError(null);

        try {
            const params = {
                region_code: regionCode,
                year: year,
                quartal: quartal,
                ytd: isYearToDate ? '1' : '0'
            };

            const response = await axios.get('/api/dashboard/region-witel-detail', { params });

            if (response.data.success) {
                setData(response.data.data);
            } else {
                setError(response.data.message || 'Failed to fetch region witel details');
            }
        } catch (err: any) {
            setError(err.response?.data?.message || 'Error loading region witel data');
        } finally {
            setLoading(false);
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-5xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl">
                        <MapPin className="h-6 w-6 text-red-600 dark:text-red-400" />
                        <span>{regionName} - Witel Details</span>
                    </DialogTitle>
                    <DialogDescription>
                        Witel performance and Account Manager distribution in this region
                    </DialogDescription>
                </DialogHeader>

                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading region witel details...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4">
                        <p className="text-red-700 dark:text-red-300">{error}</p>
                        <Button 
                            onClick={fetchRegionWitelDetail}
                            variant="outline" 
                            size="sm" 
                            className="mt-2"
                        >
                            Retry
                        </Button>
                    </div>
                )}

                {!loading && !error && data && (
                    <>
                        {/* Summary Cards */}
                        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <Target className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Target Revenue</span>
                                </div>
                                <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{data.summary.formatted_total_target_revenue}</p>
                            </div>

                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <TrendingUp className="h-4 w-4 text-green-600 dark:text-green-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Realisasi Revenue</span>
                                </div>
                                <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{data.summary.formatted_total_realisasi_revenue}</p>
                            </div>

                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <Target className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Achievement</span>
                                </div>
                                <p className={`text-xl font-bold ${data.summary.achievement_percentage >= 100 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'}`}>
                                    {data.summary.achievement_percentage.toFixed(1)}%
                                </p>
                            </div>

                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <Users className="h-4 w-4 text-red-600 dark:text-red-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Total AM</span>
                                </div>
                                <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{data.summary.total_am}</p>
                            </div>

                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <Building2 className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Witel</span>
                                </div>
                                <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{data.summary.total_witel}</p>
                            </div>
                        </div>

                        {/* Period Info */}
                        <div className="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 rounded-lg p-3 mb-6">
                            <p className="text-sm text-blue-700 dark:text-blue-300">
                                <span className="font-semibold">Period:</span> {data.summary.period}
                            </p>
                        </div>

                        {/* Witel Table */}
                        <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                            <div className="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    Witel List in {regionName}
                                </h3>
                            </div>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead className="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Witel
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Target Revenue
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Realisasi Revenue
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Achievement
                                            </th>
                                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Total AM
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                        {data.witels.length > 0 ? (
                                            data.witels.map((witel, idx) => (
                                                <tr 
                                                    key={idx}
                                                    className="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                                >
                                                    <td className="px-6 py-4">
                                                        <div className="flex items-center gap-2">
                                                            <Building2 className="h-4 w-4 text-gray-400" />
                                                            <span className="font-semibold text-gray-900 dark:text-gray-100">
                                                                {witel.witel_name}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 text-right font-semibold text-gray-900 dark:text-gray-100">
                                                        {witel.formatted_t_revenue}
                                                    </td>
                                                    <td className="px-6 py-4 text-right font-semibold text-green-600 dark:text-green-400">
                                                        {witel.formatted_r_revenue}
                                                    </td>
                                                    <td className="px-6 py-4 text-right">
                                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                            witel.achievement_percentage >= 100 
                                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                                                : 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'
                                                        }`}>
                                                            {witel.achievement_percentage.toFixed(1)}%
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className="inline-flex items-center justify-center gap-1 px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 text-sm font-medium">
                                                            <Users className="h-3.5 w-3.5" />
                                                            {witel.am_count}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan={5} className="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                    No witel data available for this region
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </>
                )}

                <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
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

export default RegionWitelDetailModal;
