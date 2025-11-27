import React, { useEffect, useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, MapPin, Target, Calendar, Users } from 'lucide-react';
import axios from 'axios';

interface RegionNkiModalProps {
    isOpen: boolean;
    onClose: () => void;
    regionId: number;
    regionName: string;
    quarter: number;
    year: number;
}

interface SegmentStat {
    segment: string;
    result: {
        ach: number;
        not_ach: number;
    };
    proses: {
        ach: number;
        not_ach: number;
    };
    nki: {
        above_100: number;
        below_100: number;
    };
    highest_nki: number;
    lowest_nki: number;
    avg_nki: number;
    total_am: number;
    parameters_to_improve: string;
}

interface ParameterStat {
    parameter: string;
    bobot: number;
    ach: number;
    not_ach: number;
}

interface RegionNkiData {
    region: {
        id: number;
        name: string;
    };
    period: {
        quarter: number;
        year: number;
    };
    summary: {
        target_revenue: number;
        formatted_target_revenue: string;
        realisasi_revenue: number;
        formatted_realisasi_revenue: string;
        total_am: number;
    };
    segment_stats: SegmentStat[];
    parameter_result: {
        percentage_result: number;
        parameters: ParameterStat[];
    };
    parameter_proses: {
        percentage_proses: number;
        parameters: ParameterStat[];
    };
}

export default function RegionNkiModal({ isOpen, onClose, regionId, regionName, quarter, year }: RegionNkiModalProps) {
    const [data, setData] = useState<RegionNkiData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [activeParameterTab, setActiveParameterTab] = useState<'result' | 'proses'>('result');

    useEffect(() => {
        if (isOpen && regionId) {
            fetchData();
        }
    }, [isOpen, regionId, quarter, year]);

    const fetchData = async () => {
        setLoading(true);
        setError(null);
        try {
            console.log('Fetching region NKI data:', { regionId, quarter, year });
            const response = await axios.get(`/api/dashboard/region-nki/${regionId}`, {
                params: { quarter, year }
            });
            console.log('Response data:', response.data);
            console.log('Formatted target:', response.data.summary?.formatted_target_revenue);
            console.log('Formatted realisasi:', response.data.summary?.formatted_realisasi_revenue);
            
            // Ensure NKI values are numbers
            const processedData = {
                ...response.data,
                segment_stats: response.data.segment_stats?.map((stat: any) => ({
                    ...stat,
                    highest_nki: Number(stat.highest_nki) || 0,
                    lowest_nki: Number(stat.lowest_nki) || 0,
                    avg_nki: Number(stat.avg_nki) || 0
                })) || []
            };
            
            console.log('Processed data:', processedData);
            setData(processedData);
        } catch (error: any) {
            console.error('Error fetching region NKI data:', error);
            console.error('Error response:', error.response?.data);
            setError(error.response?.data?.message || 'Failed to load data');
        } finally {
            setLoading(false);
        }
    };

    const getQuarterText = (q: number) => {
        return `Q${q}`;
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <MapPin className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">
                            NKI AM DSS {regionName}
                        </span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Regional performance indicators and segment achievement analysis
                    </DialogDescription>
                </DialogHeader>

                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading region NKI data...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4 mb-6">
                        <p className="text-red-700 dark:text-red-300">{error}</p>
                        <Button 
                            onClick={fetchData}
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
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            {/* Card 1: Revenue Target */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Revenue Target</p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                {data.summary.formatted_target_revenue}
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
                                                {data.summary.formatted_realisasi_revenue}
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
                                                {data.summary.total_am}
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
                                                {getQuarterText(data.period.quarter)} {data.period.year}
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

                        {/* Content: Table and Reserved Section */}
                        <div className="grid grid-cols-1 lg:grid-cols-20 gap-6">
                            {/* Left: Table - 65% (13 columns) */}
                            <div className="lg:col-span-13 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                                <div className="bg-white dark:bg-gray-900 px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg className="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        Summary NKI AM {getQuarterText(data.period.quarter)} {data.period.year}
                                    </h3>
                                </div>
                                
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="bg-gray-100 dark:bg-gray-800 border-b-2 border-gray-300 dark:border-gray-700">
                                                <th rowSpan={2} className="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700 sticky left-0 bg-gray-100 dark:bg-gray-800 z-10">
                                                    Segments
                                                </th>
                                                <th colSpan={2} className="px-4 py-2 text-center text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700">
                                                    Result
                                                </th>
                                                <th colSpan={2} className="px-4 py-2 text-center text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700">
                                                    Proses
                                                </th>
                                                <th colSpan={2} className="px-4 py-2 text-center text-xs font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700">
                                                    NKI
                                                </th>
                                                <th colSpan={3} className="px-4 py-2 text-center text-xs font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700">
                                                    NKI Statistics
                                                </th>
                                                <th rowSpan={2} className="px-4 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Parameter To Be Improve
                                                </th>
                                            </tr>
                                            <tr className="bg-gray-50 dark:bg-gray-850 border-b border-gray-300 dark:border-gray-700">
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">
                                                    Ach
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400 border-r border-gray-300 dark:border-gray-700">
                                                    Not Ach
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">
                                                    Ach
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400 border-r border-gray-300 dark:border-gray-700">
                                                    Not Ach
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">
                                                    &gt;100%
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400 border-r border-gray-300 dark:border-gray-700">
                                                    &lt;100%
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                                                    Highest
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                                                    Lowest
                                                </th>
                                                <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-700">
                                                    Average
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                            {data.segment_stats.length > 0 ? (
                                                data.segment_stats.map((stat, index) => (
                                                    <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-850 transition-colors">
                                                        <td className="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-800 sticky left-0 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-850">
                                                            <div className="flex items-center gap-2">
                                                                <div className="w-2 h-2 rounded-full bg-red-500"></div>
                                                                {stat.segment || 'N/A'}
                                                            </div>
                                                        </td>
                                                        <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                            <span className="text-green-700 dark:text-green-400 font-bold">
                                                                {stat.result.ach}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                            <span className="text-red-700 dark:text-red-400 font-bold">
                                                                {stat.result.not_ach}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                            <span className="text-green-700 dark:text-green-400 font-bold">
                                                                {stat.proses.ach}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                            <span className="text-red-700 dark:text-red-400 font-bold">
                                                                {stat.proses.not_ach}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3 text-center font-medium border-r border-gray-200 dark:border-gray-800">
                                                            <span className="text-green-700 dark:text-green-400 font-bold">
                                                                {stat.nki.above_100}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3 text-center font-medium border-r border-gray-300 dark:border-gray-700">
                                                            <span className="text-red-700 dark:text-red-400 font-bold">
                                                                {stat.nki.below_100}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3 text-center border-r border-gray-200 dark:border-gray-800">
                                                            <div className="flex flex-col items-center">
                                                                <span className="text-sm font-bold text-green-600 dark:text-green-400">
                                                                    {typeof stat.highest_nki === 'number' ? stat.highest_nki.toFixed(2) : '0.00'}%
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="px-3 py-3 text-center border-r border-gray-200 dark:border-gray-800">
                                                            <div className="flex flex-col items-center">
                                                                <span className="text-sm font-bold text-red-600 dark:text-red-400">
                                                                    {typeof stat.lowest_nki === 'number' ? stat.lowest_nki.toFixed(2) : '0.00'}%
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="px-3 py-3 text-center border-r border-gray-300 dark:border-gray-700">
                                                            <div className="flex flex-col items-center">
                                                                <span className="text-sm font-bold text-blue-600 dark:text-blue-400">
                                                                    {typeof stat.avg_nki === 'number' ? stat.avg_nki.toFixed(2) : '0.00'}%
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                            {stat.parameters_to_improve || '-'}
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan={11} className="px-4 py-8 text-center">
                                                        <div className="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                                            <svg className="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                            </svg>
                                                            <p className="text-sm font-medium">No segment data available for this region</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Right: Parameter Section - 35% (7 columns) */}
                            <div className="lg:col-span-7 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                                <div className="bg-white dark:bg-gray-900 px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Parameter</h3>
                                    
                                    {/* Tab Navigation */}
                                    <div className="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                                        <button
                                            onClick={() => setActiveParameterTab('result')}
                                            className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                                                activeParameterTab === 'result'
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                                            }`}
                                        >
                                            Aspek Result
                                            {activeParameterTab === 'result' && (
                                                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-red-600 dark:bg-red-400"></div>
                                            )}
                                        </button>
                                        <button
                                            onClick={() => setActiveParameterTab('proses')}
                                            className={`px-4 py-2 text-sm font-medium transition-colors relative ${
                                                activeParameterTab === 'proses'
                                                    ? 'text-red-600 dark:text-red-400'
                                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                                            }`}
                                        >
                                            Aspek Process
                                            {activeParameterTab === 'proses' && (
                                                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-red-600 dark:bg-red-400"></div>
                                            )}
                                        </button>
                                    </div>
                                </div>
                                
                                <div className="p-5 max-h-[600px] overflow-y-auto">
                                    {/* Aspek Result */}
                                    {activeParameterTab === 'result' && (
                                        <div>
                                            <div className="mb-3">
                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                    Bobot: {data.parameter_result?.percentage_result || 0}%
                                                </p>
                                            </div>
                                            
                                            <div className="overflow-x-auto">
                                                <table className="w-full text-sm">
                                                    <thead>
                                                        <tr className="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                                            <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">No</th>
                                                            <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Parameter</th>
                                                            <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">Bobot (%)</th>
                                                            <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400">Ach</th>
                                                            <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400">Not Ach</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                        {data.parameter_result?.parameters && data.parameter_result.parameters.length > 0 ? (
                                                            data.parameter_result.parameters.map((param, index) => (
                                                                <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-850">
                                                                    <td className="px-3 py-2 text-gray-900 dark:text-gray-100">{index + 1}</td>
                                                                    <td className="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{param.parameter}</td>
                                                                    <td className="px-3 py-2 text-center text-gray-900 dark:text-gray-100">{param.bobot}%</td>
                                                                    <td className="px-3 py-2 text-center font-bold text-green-600 dark:text-green-400">{param.ach}</td>
                                                                    <td className="px-3 py-2 text-center font-bold text-red-600 dark:text-red-400">{param.not_ach}</td>
                                                                </tr>
                                                            ))
                                                        ) : (
                                                            <tr>
                                                                <td colSpan={5} className="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                                    No parameter data available
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    )}

                                    {/* Aspek Process */}
                                    {activeParameterTab === 'proses' && (
                                        <div>
                                            <div className="mb-3">
                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                    Bobot: {data.parameter_proses?.percentage_proses || 0}%
                                                </p>
                                            </div>
                                            
                                            <div className="overflow-x-auto">
                                                <table className="w-full text-sm">
                                                    <thead>
                                                        <tr className="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                                            <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">No</th>
                                                            <th className="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Parameter</th>
                                                            <th className="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">Bobot (%)</th>
                                                            <th className="px-3 py-2 text-center text-xs font-semibold text-green-600 dark:text-green-400">Ach</th>
                                                            <th className="px-3 py-2 text-center text-xs font-semibold text-red-600 dark:text-red-400">Not Ach</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                        {data.parameter_proses?.parameters && data.parameter_proses.parameters.length > 0 ? (
                                                            data.parameter_proses.parameters.map((param, index) => (
                                                                <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-850">
                                                                    <td className="px-3 py-2 text-gray-900 dark:text-gray-100">{index + 1}</td>
                                                                    <td className="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{param.parameter}</td>
                                                                    <td className="px-3 py-2 text-center text-gray-900 dark:text-gray-100">{param.bobot}%</td>
                                                                    <td className="px-3 py-2 text-center font-bold text-green-600 dark:text-green-400">{param.ach}</td>
                                                                    <td className="px-3 py-2 text-center font-bold text-red-600 dark:text-red-400">{param.not_ach}</td>
                                                                </tr>
                                                            ))
                                                        ) : (
                                                            <tr>
                                                                <td colSpan={5} className="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                                    No parameter data available
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </>
                )}

                {!loading && !error && !data && (
                    <div className="text-center py-12 text-gray-500 dark:text-gray-400">
                        No data available
                    </div>
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
}
