import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, MapPin, TrendingUp, Building2, Target } from 'lucide-react';
import axios from '@/lib/axios';
import { formatCurrency } from '@/utils/currency';

interface RegionDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    subsegment: string | null;
    regionCode: string | null;
    regionName: string | null;
    year?: number;
}

interface CompanyData {
    nip_nas: string;
    nama_perusahaan: string;
    witel: string;
    revenue: number;
    formatted_revenue: string;
    achievement: number;
    yoy_growth: number;
    target: number;
    formatted_target: string;
}

interface RegionDetail {
    summary: {
        total_revenue: number;
        total_target: number;
        achievement: number;
        yoy_growth: number;
        company_count: number;
        formatted_total_revenue: string;
        formatted_total_target: string;
    };
    companies: CompanyData[];
    subsegment: string;
    region_code: string;
    year: number;
}

const RegionDetailModal: React.FC<RegionDetailModalProps> = ({
    isOpen,
    onClose,
    subsegment,
    regionCode,
    regionName,
    year
}) => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [data, setData] = useState<RegionDetail | null>(null);

    useEffect(() => {
        if (isOpen && subsegment && regionCode) {
            fetchRegionDetail();
        }
    }, [isOpen, subsegment, regionCode, year]);

    const fetchRegionDetail = async () => {
        setLoading(true);
        setError(null);

        try {
            const params: any = {
                subsegment,
                region_code: regionCode
            };

            if (year) {
                params.year = year;
            }

            const response = await axios.get('/api/dashboard/region-detail', { params });

            console.log('Region Detail Response:', response.data);

            if (response.data.success) {
                setData(response.data.data);
            } else {
                setError('Failed to fetch region details');
            }
        } catch (err) {
            setError('Error loading region data');
            console.error('Region Detail Error:', err);
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
                        <span>{regionName} - {subsegment}</span>
                    </DialogTitle>
                    <DialogDescription>
                        Detailed performance metrics and company list for this region
                    </DialogDescription>
                </DialogHeader>

                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading region details...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4">
                        <p className="text-red-700 dark:text-red-300">{error}</p>
                        <Button 
                            onClick={fetchRegionDetail}
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
                        <div className=" grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <TrendingUp className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</span>
                                </div>
                                <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{data.summary.formatted_total_revenue}</p>
                            </div>

                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <Target className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Achievement</span>
                                </div>
                                <p className={`text-xl font-bold ${data.summary.achievement >= 100 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'}`}>
                                    {data.summary.achievement.toFixed(1)}%
                                </p>
                            </div>

                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <TrendingUp className="h-4 w-4 text-green-600 dark:text-green-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">YoY Growth</span>
                                </div>
                                <p className={`text-xl font-bold ${data.summary.yoy_growth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`}>
                                    {data.summary.yoy_growth >= 0 ? '+'  : ''}{data.summary.yoy_growth.toFixed(1)}%
                                </p>
                            </div>

                            <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                                <div className="flex items-center gap-2 mb-2">
                                    <Building2 className="h-4 w-4 text-red-600 dark:text-red-400" />
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Companies</span>
                                </div>
                                <p className="text-xl font-bold text-gray-900 dark:text-gray-100">{data.summary.company_count}</p>
                            </div>
                        </div>

                        {/* Companies Table */}
                        <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                            <div className="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                    Companies in {regionName}
                                </h3>
                            </div>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead className="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Company
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Revenue
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Target
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Achievement
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                YoY Growth
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                        {data.companies.map((company, idx) => (
                                            <tr 
                                                key={company.nip_nas}
                                                className="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                            >
                                                <td className="px-6 py-4">
                                                    <div>
                                                        <p className="font-semibold text-gray-900 dark:text-gray-100">
                                                            {company.nama_perusahaan}
                                                        </p>
                                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                                            {company.nip_nas} • {company.witel}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-right font-semibold text-gray-900 dark:text-gray-100">
                                                    {company.formatted_revenue}
                                                </td>
                                                <td className="px-6 py-4 text-right text-gray-600 dark:text-gray-400">
                                                    {company.formatted_target}
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                        company.achievement >= 100 
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                                            : 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'
                                                    }`}>
                                                        {company.achievement.toFixed(1)}%
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    <span className={`inline-flex items-center font-semibold ${
                                                        company.yoy_growth >= 0 
                                                            ? 'text-green-600 dark:text-green-400' 
                                                            : 'text-red-600 dark:text-red-400'
                                                    }`}>
                                                        {company.yoy_growth >= 0 ? '▲' : '▼'} {Math.abs(company.yoy_growth).toFixed(1)}%
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
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

export default RegionDetailModal;
