import React, { useState, useEffect, useMemo } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, MapPin, Users, Target, Calendar, TrendingUp, TrendingDown, ArrowUp, ArrowDown } from 'lucide-react';
import axios from '@/lib/axios';
import AmPerformanceDetailModal from './AmPerformanceDetailModal';

interface AMDetailData {
    nik_am: string;
    nama_am: string;
    t_revenue: number;
    r_revenue: number;
    ach_revenue_plan: number;
    t_scaling: number;
    r_scaling: number;
    ach_scaling: number;
    t_sales_datin: number;
    r_sales_datin: number;
    ach_sales_datin: number;
    t_hsi: number;
    r_hsi: number;
    ach_hsi: number;
    t_wireline: number;
    r_wireline: number;
    ach_wireline: number;
    t_wifi: number;
    r_wifi: number;
    ach_wifi: number;
    t_cyc: number;
    r_cyc: number;
    ach_cyc: number;
    t_cr: number;
    r_cr: number;
    ach_cr: number;
    t_profit: number;
    r_profit: number;
    ach_profit: number;
    t_nps: number;
    r_nps: number;
    ach_nps: number;
    t_maps: number;
    r_maps: number;
    ach_maps: number;
    t_lop: number;
    r_lop: number;
    ach_lop: number;
    t_capability: number;
    r_capability: number;
    ach_capability: number;
    t_cc: number;
    r_cc: number;
    ach_cc: number;
    ach_result: number;
    ach_proses: number;
    nki_adjustment: number;
    formatted_t_revenue: string;
    formatted_r_revenue: string;
    formatted_t_scaling?: string;
    formatted_r_scaling?: string;
    formatted_t_lop?: string;
    formatted_r_lop?: string;
}

interface ParameterData {
    parameter: string;
    bobot: number;
    ach_count: number;
    not_ach_count: number;
    avg_achievement: number;
}

interface WitelNkiDetailData {
    witel_info: {
        witel_id: number;
        witel_name: string;
        region_code: string;
        segment: string;
    };
    period: {
        quarter: number;
        year: number;
        period_display: string;
    };
    summary: {
        total_am: number;
        total_target_revenue: number;
        formatted_total_target_revenue: string;
        total_realisasi_revenue: number;
        formatted_total_realisasi_revenue: string;
        avg_nki: number;
    };
    am_list: AMDetailData[];
    parameter_result: {
        percentage_result: number;
        parameters: ParameterData[];
    };
    parameter_proses: {
        percentage_proses: number;
        parameters: ParameterData[];
    };
}

interface WitelNkiDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    regionId: number;
    quarter: number;
    year: number;
    segment: string;
    witelId?: number;
}

const WitelNkiDetailModal: React.FC<WitelNkiDetailModalProps> = ({
    isOpen,
    onClose,
    regionId,
    quarter,
    year,
    segment,
    witelId
}) => {
    const [data, setData] = useState<WitelNkiDetailData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [selectedAM, setSelectedAM] = useState<string | null>(null);
    const [isAMModalOpen, setIsAMModalOpen] = useState(false);

    const handleAMClick = (nikAm: string) => {
        console.log('AM clicked:', nikAm);
        setSelectedAM(nikAm);
        setIsAMModalOpen(true);
        console.log('Modal state set:', { selectedAM: nikAm, isAMModalOpen: true });
    };

    const closeAMModal = () => {
        console.log('Closing AM modal');
        setIsAMModalOpen(false);
        setSelectedAM(null);
    };

    // Reset data when modal closes
    useEffect(() => {
        if (!isOpen) {
            setData(null);
            setError(null);
        }
    }, [isOpen]);

    useEffect(() => {
        if (isOpen) {
            // Reset data before fetching to ensure clean state
            setData(null);
            setError(null);
            fetchWitelDetails();
            // Close AM modal when period changes
            if (isAMModalOpen) {
                closeAMModal();
            }
        }
    }, [isOpen, regionId, quarter, year, segment, witelId]);
    
    // Close AM modal when parent modal closes
    useEffect(() => {
        if (!isOpen && isAMModalOpen) {
            closeAMModal();
        }
    }, [isOpen]);

    const fetchWitelDetails = async () => {
        setLoading(true);
        setError(null);
        
        try {
            const response = await axios.get('/api/dashboard/witel-nki-detail', {
                params: {
                    region_id: regionId,
                    quarter: quarter,
                    year: year,
                    segment: segment,
                    witel_id: witelId
                }
            });
            
            console.log('Witel NKI Detail Response:', response.data);
            console.log('AM List:', response.data?.data?.am_list);
            console.log('AM List length:', response.data?.data?.am_list?.length);
            
            if (response.data.success) {
                setData(response.data.data);
                console.log('Data set:', response.data.data);
                console.log('Data.am_list:', response.data.data.am_list);
            } else {
                setError('Failed to fetch witel details');
            }
        } catch (err) {
            console.error('Fetch error:', err);
            setError('Error loading witel data');
        } finally {
            setLoading(false);
        }
    };

    const formatNumber = (value: number | string, decimals: number = 2) => {
        const num = typeof value === 'string' ? parseFloat(value) : value;
        if (isNaN(num)) return '0.00';
        return num.toFixed(decimals);
    };

    const getAchievementColor = (ach: number | string) => {
        const num = typeof ach === 'string' ? parseFloat(ach) : ach;
        if (isNaN(num)) return 'text-gray-600 dark:text-gray-400';
        if (num >= 100) return 'text-green-600 dark:text-green-400';
        return 'text-red-600 dark:text-red-400';
    };

    // Create parameter bobot mapping from data
    const parameterBobotMap = useMemo(() => {
        if (!data) return new Map<string, number>();
        
        const map = new Map<string, number>();
        
        // Map Result parameters
        data.parameter_result.parameters.forEach(param => {
            const paramName = param.parameter.toLowerCase();
            if (paramName === 'revenue') map.set('revenue_plan', param.bobot);
            else if (paramName === 'scaling') map.set('scaling', param.bobot);
            else if (paramName === 'sales datin') map.set('sales_datin', param.bobot);
            else if (paramName === 'hsi') map.set('hsi', param.bobot);
            else if (paramName === 'wireline') map.set('wireline', param.bobot);
            else if (paramName === 'wifi') map.set('wifi', param.bobot);
            else if (paramName === 'cyc') map.set('cyc', param.bobot);
            else if (paramName === 'cr') map.set('cr', param.bobot);
            else if (paramName === 'profit') map.set('profit', param.bobot);
            else if (paramName === 'nps') map.set('nps', param.bobot);
        });
        
        // Map Process parameters
        data.parameter_proses.parameters.forEach(param => {
            const paramName = param.parameter.toLowerCase();
            if (paramName === 'maps') map.set('maps', param.bobot);
            else if (paramName === 'lop') map.set('lop', param.bobot);
            else if (paramName === 'capability') map.set('capability', param.bobot);
            else if (paramName === 'cc') map.set('cc', param.bobot);
        });
        
        return map;
    }, [data]);

    // Helper function to render achievement cell with indicator
    const renderAchCell = (achValue: number, paramKey: string) => {
        const bobot = parameterBobotMap.get(paramKey) || 100;
        const isAboveBobot = achValue >= bobot;
        
        return (
            <td className={`text-right p-2 text-sm font-bold border-r border-gray-300 dark:border-gray-600 bg-green-50 dark:bg-green-950 ${
                isAboveBobot 
                    ? 'text-green-600 dark:text-green-400' 
                    : 'text-red-600 dark:text-red-400'
            }`}>
                <div className="flex items-center justify-end gap-1 whitespace-nowrap">
                    {isAboveBobot ? (
                        <ArrowUp className="h-3 w-3 flex-shrink-0" />
                    ) : (
                        <ArrowDown className="h-3 w-3 flex-shrink-0" />
                    )}
                    <span>{formatNumber(achValue)}%</span>
                </div>
            </td>
        );
    };

    return (
        <>
            <Dialog open={isOpen} onOpenChange={onClose}>
                <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <MapPin className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">
                            Detail NKI AM
                        </span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Performance breakdown per Account Manager in selected segment/witel
                    </DialogDescription>
                </DialogHeader>

                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading details...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4 mb-6">
                        <p className="text-red-700 dark:text-red-300">{error}</p>
                        <Button 
                            onClick={fetchWitelDetails}
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
                        {console.log('Rendering with data:', data)}
                        {console.log('Rendering am_list:', data.am_list)}
                        {/* Summary Cards */}
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            {/* Card 1: Segment/Witel */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Segment/Witel</p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                {data.witel_info.segment}
                                            </p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                                {data.witel_info.witel_name || data.witel_info.region_code}
                                            </p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <MapPin className="h-6 w-6 text-red-600 dark:text-red-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Card 2: Total AM */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Account Manager</p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                {data.summary.total_am}
                                            </p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">Active AMs</p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <Users className="h-6 w-6 text-red-600 dark:text-red-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Card 3: Target Revenue */}
                            <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Target Revenue</p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                {data.summary.formatted_total_target_revenue}
                                            </p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                                Actual: {data.summary.formatted_total_realisasi_revenue}
                                            </p>
                                        </div>
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                                <Target className="h-6 w-6 text-red-600 dark:text-red-400" />
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
                                                {data.period.period_display}
                                            </p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                                Avg NKI: {formatNumber(data.summary.avg_nki)}%
                                            </p>
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

                        {/* Main Content: Full Width Table with Grid Constraint */}
                        <div className="grid grid-cols-1 lg:grid-cols-20 gap-6">
                            {/* AM Detail Table - Full 20 columns */}
                            <div className="lg:col-span-20">
                                <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                        <Users className="h-5 w-5 text-red-600 dark:text-red-400" />
                                        Account Manager Performance Details
                                    </h3>
                                    
                                    <div className="overflow-x-auto">
                                        <style>{`
                                            .sticky-nik { 
                                                min-width: 100px; 
                                                max-width: 100px; 
                                                width: 100px;
                                                position: sticky !important;
                                                left: 0 !important;
                                            }
                                            .sticky-nik::after {
                                                content: '';
                                                position: absolute;
                                                right: 0;
                                                top: 0;
                                                bottom: 0;
                                                width: 1px;
                                                background-color: rgb(209 213 219);
                                                z-index: 1;
                                            }
                                            .dark .sticky-nik::after {
                                                background-color: rgb(75 85 99);
                                            }
                                            .sticky-nama { 
                                                min-width: 180px; 
                                                max-width: 180px; 
                                                width: 180px;
                                                position: sticky !important;
                                                left: 100px !important;
                                            }
                                            .sticky-nama::after {
                                                content: '';
                                                position: absolute;
                                                right: 0;
                                                top: 0;
                                                bottom: 0;
                                                width: 2px;
                                                background-color: rgb(156 163 175);
                                                z-index: 1;
                                            }
                                            .dark .sticky-nama::after {
                                                background-color: rgb(107 114 128);
                                            }
                                            .sticky-shadow-right { 
                                                box-shadow: 3px 0 8px -2px rgba(0, 0, 0, 0.15); 
                                            }
                                            .dark .sticky-shadow-right { 
                                                box-shadow: 3px 0 8px -2px rgba(0, 0, 0, 0.5); 
                                            }
                                        `}</style>
                                        <table className="w-full text-sm border-collapse">
                                            <thead>
                                                <tr className="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-850 border-b-2 border-gray-300 dark:border-gray-600">
                                                    <th className="sticky-nik z-20 bg-gray-50 dark:bg-gray-800 text-left p-3 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs" rowSpan={2}>NIK AM</th>
                                                    <th className="sticky-nama z-20 bg-gray-50 dark:bg-gray-800 text-left p-3 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs sticky-shadow-right" rowSpan={2}>Nama AM</th>
                                                    {/* Aspek Result - Blue Background */}
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Revenue</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Scaling</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Sales Datin</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>HSI</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Wireline</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>WiFi</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>CYC</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>CR</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>Profit</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900" colSpan={3}>NPS</th>
                                                    {/* Aspek Process - Green Background */}
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-green-100 dark:bg-green-900" colSpan={3}>MAPS</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-green-100 dark:bg-green-900" colSpan={3}>LOP</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-green-100 dark:bg-green-900" colSpan={3}>Capability</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600 bg-green-100 dark:bg-green-900" colSpan={3}>CC</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600" rowSpan={2}>Ach Result</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-600" rowSpan={2}>Ach Process</th>
                                                    <th className="text-center p-2 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs" rowSpan={2}>NKI</th>
                                                </tr>
                                                <tr className="bg-gray-100 dark:bg-gray-800 border-b border-gray-300 dark:border-gray-600">
                                                    {/* Revenue */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600 min-w-[90px]">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* Scaling */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600 min-w-[90px]">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* Sales Datin */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* HSI */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* Wireline */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* WiFi */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* CYC */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* CR */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* Profit */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* NPS */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* MAPS */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* LOP */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600 min-w-[90px]">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* Capability */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                    {/* CC */}
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Target</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Real</th>
                                                    <th className="text-center p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">Ach%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.am_list && data.am_list.length > 0 ? (
                                                    data.am_list.map((am, idx) => (
                                                        <tr 
                                                            key={am.nik_am} 
                                                            className={`border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group ${
                                                                idx % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-850'
                                                            }`}
                                                        >
                                                            <td 
                                                                className={`sticky-nik z-10 p-3 font-mono text-sm cursor-pointer hover:text-red-600 dark:hover:text-red-400 hover:underline ${
                                                                    idx % 2 === 0 ? 'bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800' : 'bg-gray-50 dark:bg-gray-850 group-hover:bg-gray-100 dark:group-hover:bg-gray-800'
                                                                }`}
                                                                onClick={() => handleAMClick(am.nik_am)}
                                                            >
                                                                {am.nik_am}
                                                            </td>
                                                            <td 
                                                                className={`sticky-nama z-10 p-3 font-medium text-sm sticky-shadow-right cursor-pointer hover:text-red-600 dark:hover:text-red-400 hover:underline ${
                                                                    idx % 2 === 0 ? 'bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800' : 'bg-gray-50 dark:bg-gray-850 group-hover:bg-gray-100 dark:group-hover:bg-gray-800'
                                                                }`}
                                                                onClick={() => handleAMClick(am.nik_am)}
                                                            >
                                                                {am.nama_am}
                                                            </td>
                                                            {/* Revenue */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[90px] whitespace-nowrap">{am.formatted_t_revenue || formatNumber(am.t_revenue, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[90px] whitespace-nowrap">{am.formatted_r_revenue || formatNumber(am.r_revenue, 0)}</td>
                                                            {renderAchCell(am.ach_revenue_plan, 'revenue_plan')}
                                                            {/* Scaling */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[90px] whitespace-nowrap">{am.formatted_t_scaling || formatNumber(am.t_scaling, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[90px] whitespace-nowrap">{am.formatted_r_scaling || formatNumber(am.r_scaling, 0)}</td>
                                                            {renderAchCell(am.ach_scaling, 'scaling')}
                                                            {/* Sales Datin */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_sales_datin, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_sales_datin, 0)}</td>
                                                            {renderAchCell(am.ach_sales_datin, 'sales_datin')}
                                                            {/* HSI */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_hsi, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_hsi, 0)}</td>
                                                            {renderAchCell(am.ach_hsi, 'hsi')}
                                                            {/* Wireline */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_wireline, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_wireline, 0)}</td>
                                                            {renderAchCell(am.ach_wireline, 'wireline')}
                                                            {/* WiFi */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_wifi, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_wifi, 0)}</td>
                                                            {renderAchCell(am.ach_wifi, 'wifi')}
                                                            {/* CYC */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_cyc, 2)}%</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_cyc, 2)}%</td>
                                                            {renderAchCell(am.ach_cyc, 'cyc')}
                                                            {/* CR */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_cr, 2)}%</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_cr, 2)}%</td>
                                                            {renderAchCell(am.ach_cr, 'cr')}
                                                            {/* Profit */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_profit, 2)}%</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_profit, 2)}%</td>
                                                            {renderAchCell(am.ach_profit, 'profit')}
                                                            {/* NPS */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_nps, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_nps, 0)}</td>
                                                            {renderAchCell(am.ach_nps, 'nps')}
                                                            {/* MAPS */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_maps, 2)}%</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_maps, 2)}%</td>
                                                            {renderAchCell(am.ach_maps, 'maps')}
                                                            {/* LOP */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[90px] whitespace-nowrap">{am.formatted_t_lop || formatNumber(am.t_lop, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600 min-w-[90px] whitespace-nowrap">{am.formatted_r_lop || formatNumber(am.r_lop, 0)}</td>
                                                            {renderAchCell(am.ach_lop, 'lop')}
                                                            {/* Capability */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_capability, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_capability, 0)}</td>
                                                            {renderAchCell(am.ach_capability, 'capability')}
                                                            {/* CC */}
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.t_cc, 0)}</td>
                                                            <td className="text-right p-2 text-sm border-r border-gray-300 dark:border-gray-600">{formatNumber(am.r_cc, 0)}</td>
                                                            {renderAchCell(am.ach_cc, 'cc')}
                                                            {/* Summary */}
                                                            <td className="text-right p-2 text-sm font-bold border-r border-gray-300 dark:border-gray-600 bg-green-50 dark:bg-green-950 text-gray-900 dark:text-gray-100">
                                                                {formatNumber(am.ach_result)}%
                                                            </td>
                                                            <td className="text-right p-2 text-sm font-bold border-r border-gray-300 dark:border-gray-600 bg-green-50 dark:bg-green-950 text-gray-900 dark:text-gray-100">
                                                                {formatNumber(am.ach_proses)}%
                                                            </td>
                                                            <td className={`text-right p-2 text-sm font-bold ${getAchievementColor(am.nki_adjustment)}`}>
                                                                {formatNumber(am.nki_adjustment)}%
                                                            </td>
                                                    </tr>
                                                    ))
                                                ) : (
                                                    <tr>
                                                        <td colSpan={14} className="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                            No Account Manager data available for this segment
                                                        </td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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

            {/* AM Performance Detail Modal - Outside main dialog to avoid nesting issues */}
            {console.log('Rendering AM Modal?', { selectedAM, isAMModalOpen })}
            <AmPerformanceDetailModal
                isOpen={isAMModalOpen && selectedAM !== null}
                onClose={closeAMModal}
                nikAm={selectedAM || ''}
                quarter={quarter}
                year={year}
                segment={segment}
            />
        </>
    );
};

export default WitelNkiDetailModal;
