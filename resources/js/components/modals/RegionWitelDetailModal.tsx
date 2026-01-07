import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, MapPin, TrendingUp, Building2, Target, Users, Calendar, ChevronDown, ChevronUp, User } from 'lucide-react';
import axios from '@/lib/axios';
import { formatCurrency } from '@/utils/currency';
import AMRevenueDetailModal from './AMRevenueDetailModal';

interface RegionWitelDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    regionCode: string | null;
    regionName: string | null;
    year: number;
    quartal: string;
    isYearToDate?: boolean;
}

interface AMDetail {
    am_nik: string;
    am_name: string;
    am_posisi: string;
    target_revenue: number;
    formatted_target_revenue: string;
    realisasi_revenue: number;
    formatted_realisasi_revenue: string;
    company_handled: number;
    achievement_percentage: number;
}

interface WitelData {
    witel_name: string;
    t_revenue: number;
    formatted_t_revenue: string;
    r_revenue: number;
    formatted_r_revenue: string;
    am_count: number;
    achievement_percentage: number;
    am_details?: AMDetail[];
    loading_am?: boolean;
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
    const [expandedWitels, setExpandedWitels] = useState<Set<string>>(new Set());
    const [showAMModal, setShowAMModal] = useState(false);
    const [selectedAmNik, setSelectedAmNik] = useState<string | null>(null);

    useEffect(() => {
        if (isOpen && regionCode) {
            fetchRegionWitelDetail();
        } else if (!isOpen) {
            // Reset expanded witels when modal is closed
            setExpandedWitels(new Set());
            setShowAMModal(false);
            setSelectedAmNik(null);
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

    const toggleWitel = async (witelName: string) => {
        const newExpanded = new Set(expandedWitels);
        
        if (newExpanded.has(witelName)) {
            // Collapse
            newExpanded.delete(witelName);
            setExpandedWitels(newExpanded);
        } else {
            // Expand - fetch AM details if not already loaded
            newExpanded.add(witelName);
            setExpandedWitels(newExpanded);

            const witelIndex = data?.witels.findIndex(w => w.witel_name === witelName);
            if (witelIndex !== undefined && witelIndex >= 0 && data?.witels[witelIndex]) {
                const witel = data.witels[witelIndex];
                
                // Only fetch if not already loaded
                if (!witel.am_details) {
                    // Set loading state for this witel
                    const updatedWitels = [...data.witels];
                    updatedWitels[witelIndex] = { ...witel, loading_am: true };
                    setData({ ...data, witels: updatedWitels });

                    try {
                        const response = await axios.get('/api/dashboard/witel-am-details', {
                            params: {
                                witel_name: witelName,
                                region_code: regionCode,
                                year: year,
                                quartal: quartal,
                                ytd: isYearToDate ? '1' : '0'
                            }
                        });

                        if (response.data.success) {
                            const updatedWitels2 = [...data.witels];
                            updatedWitels2[witelIndex] = {
                                ...witel,
                                am_details: response.data.data.am_list,
                                loading_am: false
                            };
                            setData({ ...data, witels: updatedWitels2 });
                        }
                    } catch (err) {
                        console.error('Error fetching AM details:', err);
                        const updatedWitels2 = [...data.witels];
                        updatedWitels2[witelIndex] = { ...witel, loading_am: false };
                        setData({ ...data, witels: updatedWitels2 });
                    }
                }
            }
        }
    };

    const handleAmClick = (amNik: string) => {
        setSelectedAmNik(amNik);
        setShowAMModal(true);
    };

    const handleCloseAMModal = () => {
        setShowAMModal(false);
        setSelectedAmNik(null);
    };

    return (
        <>
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[90vw] w-[90vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <MapPin className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">{regionName} - Witel Details</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
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
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                            {/* Card 1: Target Revenue */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Revenue Target</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">
                                                {data.summary.formatted_total_target_revenue}
                                            </p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-blue-50 dark:bg-blue-950 rounded-lg">
                                                <Target className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Card 2: Realisasi Revenue */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Revenue Actual</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">
                                                {data.summary.formatted_total_realisasi_revenue}
                                            </p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-green-50 dark:bg-green-950 rounded-lg">
                                                <TrendingUp className="h-5 w-5 text-green-600 dark:text-green-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Card 3: Achievement */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Achievement</p>
                                            <p className={`text-xl font-bold ${
                                                data.summary.achievement_percentage >= 100 
                                                    ? 'text-green-600 dark:text-green-400' 
                                                    : 'text-orange-600 dark:text-orange-400'
                                            }`}>
                                                {data.summary.achievement_percentage.toFixed(1)}%
                                            </p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className={`p-2 rounded-lg ${
                                                data.summary.achievement_percentage >= 100 
                                                    ? 'bg-green-50 dark:bg-green-950' 
                                                    : 'bg-orange-50 dark:bg-orange-950'
                                            }`}>
                                                <Target className={`h-5 w-5 ${
                                                    data.summary.achievement_percentage >= 100 
                                                        ? 'text-green-600 dark:text-green-400' 
                                                        : 'text-orange-600 dark:text-orange-400'
                                                }`} />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Card 4: Total AM */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total AM</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">
                                                {data.summary.total_am}
                                            </p>
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Account Managers</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <Users className="h-5 w-5 text-red-600 dark:text-red-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Card 5: Total Witel */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Witel</p>
                                            <p className="text-xl font-bold text-gray-900 dark:text-gray-100">
                                                {data.summary.total_witel}
                                            </p>
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Locations</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-orange-50 dark:bg-orange-950 rounded-lg">
                                                <Building2 className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Period Info */}
                        <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 border border-blue-200 dark:border-blue-900 rounded-lg p-4 mb-6">
                            <div className="flex items-center gap-2">
                                <Calendar className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <p className="text-sm font-semibold text-blue-700 dark:text-blue-300">
                                    Period: <span className="font-bold">{data.summary.period}</span>
                                </p>
                            </div>
                        </div>

                        {/* Witel Table */}
                        <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                            <div className="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <Building2 className="h-5 w-5 text-red-600 dark:text-red-400" />
                                    Witel List in {regionName}
                                </h3>
                            </div>
                            <div className="space-y-2 p-4">
                                {data.witels.length > 0 ? (
                                    data.witels.map((witel, idx) => {
                                        const isExpanded = expandedWitels.has(witel.witel_name);
                                        
                                        return (
                                            <div key={idx} className="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                                {/* Witel Header - Clickable */}
                                                <div 
                                                    className="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-850 p-4 cursor-pointer hover:from-gray-100 hover:to-gray-200 dark:hover:from-gray-750 dark:hover:to-gray-800 transition-all"
                                                    onClick={() => toggleWitel(witel.witel_name)}
                                                >
                                                    <div className="flex items-center justify-between">
                                                        <div className="flex items-center gap-4 flex-1">
                                                            {/* Witel Name */}
                                                            <div className="min-w-[150px]">
                                                                <div className="flex items-center gap-2">
                                                                    <div className="p-1.5 bg-gray-100 dark:bg-gray-800 rounded">
                                                                        <Building2 className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                                                    </div>
                                                                    <span className="font-bold text-gray-900 dark:text-gray-100">
                                                                        {witel.witel_name}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            {/* Stats Grid */}
                                                            <div className="grid grid-cols-4 gap-6 flex-1">
                                                                <div className="text-center">
                                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Target Revenue</p>
                                                                    <p className="font-bold text-gray-900 dark:text-gray-100 text-sm">
                                                                        {witel.formatted_t_revenue}
                                                                    </p>
                                                                </div>
                                                                <div className="text-center">
                                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Actual Revenue</p>
                                                                    <p className="font-bold text-green-600 dark:text-green-400 text-sm">
                                                                        {witel.formatted_r_revenue}
                                                                    </p>
                                                                </div>
                                                                <div className="text-center">
                                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Achievement</p>
                                                                    <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${
                                                                        witel.achievement_percentage >= 100 
                                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                                                            : 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'
                                                                    }`}>
                                                                        {witel.achievement_percentage.toFixed(1)}%
                                                                    </span>
                                                                </div>
                                                                <div className="text-center">
                                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">Total AM</p>
                                                                    <span className="inline-flex items-center justify-center gap-1.5 px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 text-sm font-bold">
                                                                        <Users className="h-3.5 w-3.5" />
                                                                        {witel.am_count}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {/* Chevron Icon */}
                                                        <div className="ml-4">
                                                            {isExpanded ? (
                                                                <ChevronUp className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                            ) : (
                                                                <ChevronDown className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>

                                                {/* Expanded Content - AM List */}
                                                {isExpanded && (
                                                    <div className="bg-white dark:bg-gray-950">
                                                        {witel.loading_am ? (
                                                            <div className="flex items-center justify-center py-8">
                                                                <Loader2 className="h-6 w-6 animate-spin text-red-600 dark:text-red-400 mr-2" />
                                                                <span className="text-gray-600 dark:text-gray-400">Loading AM details...</span>
                                                            </div>
                                                        ) : witel.am_details && witel.am_details.length > 0 ? (
                                                            <table className="w-full">
                                                                <thead className="bg-gray-100 dark:bg-gray-800">
                                                                    <tr>
                                                                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                            Account Manager
                                                                        </th>
                                                                        <th className="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                            Target Revenue
                                                                        </th>
                                                                        <th className="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                            Realisasi Revenue
                                                                        </th>
                                                                        <th className="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                            Company Handled
                                                                        </th>
                                                                        <th className="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                            Achievement
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                                    {witel.am_details.map((am, amIdx) => (
                                                                        <tr 
                                                                            key={amIdx}
                                                                            className="hover:bg-blue-50 dark:hover:bg-blue-950/20 transition-colors"
                                                                        >
                                                                            <td className="px-4 py-3">
                                                                                <div className="flex flex-col">
                                                                                    <div className="flex items-center gap-2">
                                                                                        <User className="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                                                                        <button
                                                                                            onClick={() => handleAmClick(am.am_nik)}
                                                                                            className="font-semibold text-gray-900 dark:text-gray-100 hover:text-red-600 dark:hover:text-red-400 hover:underline transition-colors text-left"
                                                                                        >
                                                                                            {am.am_name}
                                                                                        </button>
                                                                                    </div>
                                                                                    <span className="text-xs text-gray-500 dark:text-gray-400 ml-6">
                                                                                        {am.am_nik} • {am.am_posisi}
                                                                                    </span>
                                                                                </div>
                                                                            </td>
                                                                            <td className="px-4 py-3 text-right font-bold text-blue-900 dark:text-blue-100">
                                                                                {am.formatted_target_revenue}
                                                                            </td>
                                                                            <td className="px-4 py-3 text-right font-bold text-green-900 dark:text-green-100">
                                                                                {am.formatted_realisasi_revenue}
                                                                            </td>
                                                                            <td className="px-4 py-3 text-center">
                                                                                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-semibold">
                                                                                    <Building2 className="h-3 w-3" />
                                                                                    {am.company_handled}
                                                                                </span>
                                                                            </td>
                                                                            <td className="px-4 py-3 text-center">
                                                                                <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${
                                                                                    am.achievement_percentage >= 100
                                                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                                                        : 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'
                                                                                }`}>
                                                                                    {am.achievement_percentage.toFixed(1)}%
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                    ))}
                                                                </tbody>
                                                            </table>
                                                        ) : (
                                                            <div className="py-6 text-center text-gray-500 dark:text-gray-400">
                                                                No Account Managers found for this witel
                                                            </div>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="py-8 text-center">
                                        <div className="flex flex-col items-center gap-2">
                                            <Building2 className="h-12 w-12 text-gray-300 dark:text-gray-700" />
                                            <p className="text-gray-500 dark:text-gray-400 font-medium">
                                                No witel data available for this region
                                            </p>
                                        </div>
                                    </div>
                                )}
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

        {/* AM Revenue Detail Modal */}
        <AMRevenueDetailModal
            isOpen={showAMModal}
            onClose={handleCloseAMModal}
            amNik={selectedAmNik}
            year={year}
            quartal={quartal}
            isYearToDate={isYearToDate}
        />
        </>
    );
};

export default RegionWitelDetailModal;
