import React from 'react';

interface RegionRevenue {
    region_code: string;
    region_name: string;
    target_revenue: number;
    realisasi_revenue: number;
    achievement_percentage: number;
    variance_percentage: number;
    formatted_target: string;
    formatted_realisasi: string;
}

interface TargetRevenueRegionChartProps {
    regionRevenueData: RegionRevenue[];
    tooltipData: RegionRevenue | null;
    setTooltipData: (data: RegionRevenue | null) => void;
    tooltipPosition: { x: number; y: number };
    setTooltipPosition: (position: { x: number; y: number }) => void;
    onRegionClick: (regionCode: string) => void;
}

export default function TargetRevenueRegionChart({
    regionRevenueData,
    tooltipData,
    setTooltipData,
    tooltipPosition,
    setTooltipPosition,
    onRegionClick,
}: TargetRevenueRegionChartProps) {
    return (
        <div className="w-full">
            {/* Legend - At Top Right */}
            <div className="flex justify-end mb-3">
                <div className="flex flex-row gap-4">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded" style={{ backgroundColor: '#94a3b8', opacity: 0.8 }}></div>
                        <span className="text-xs font-medium text-gray-700 dark:text-gray-300">To Target</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-red-600 dark:bg-red-500 rounded"></div>
                        <span className="text-xs font-medium text-gray-700 dark:text-gray-300">Below Target</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-green-600 dark:bg-green-500 rounded"></div>
                        <span className="text-xs font-medium text-gray-700 dark:text-gray-300">Above Target</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="w-4 h-1 bg-black rounded-full"></div>
                        <span className="text-xs font-medium text-gray-700 dark:text-gray-300">Target Value</span>
                    </div>
                </div>
            </div>
            
            {/* Bullet Chart with Axis */}
            <div className="flex gap-0">
                    {/* Y-Axis */}
                    <div className="flex flex-col justify-between py-3 relative" style={{ height: '450px' }}>
                        {(() => {
                            const maxValue = Math.max(...regionRevenueData.map(r => Math.max(r.target_revenue, r.realisasi_revenue)));
                            const yAxisSteps = 10;
                            const stepValue = maxValue / yAxisSteps;
                            
                            return Array.from({ length: yAxisSteps + 1 }, (_, i) => {
                                const value = maxValue - (i * stepValue);
                                const formatted = value >= 1000000000000 
                                    ? `${(value / 1000000000000).toFixed(1)}T`
                                    : `${(value / 1000000000).toFixed(0)}M`;
                                
                                return (
                                    <div key={i} className="flex items-center justify-end gap-0.5">
                                        <span className="text-xs text-gray-600 dark:text-gray-400" style={{ fontSize: '12px' }}>
                                            {formatted}
                                        </span>
                                        <div className="w-1.5 h-px bg-gray-400"></div>
                                    </div>
                                );
                            });
                        })()}
                    </div>
                
                    {/* Chart Area */}
                    <div className="flex-1 relative">
                        <div className="chart-container flex items-end justify-around gap-6 h-[450px] border-l-2 border-b-2 border-gray-400 pl-6 relative">
                            {/* Global Tooltip */}
                            {tooltipData && (
                                <div 
                                    className="absolute pointer-events-none z-50"
                                    style={{ 
                                        left: `${tooltipPosition.x + 15}px`,
                                        top: `${tooltipPosition.y - 75}px`,
                                        transform: 'translateY(-50%)'
                                    }}
                                >
                                    <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-3 min-w-[200px]" style={{ boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }}>
                                        <p className="font-semibold text-gray-900 dark:text-gray-100 mb-2" style={{ fontSize: '14px' }}>
                                            {tooltipData.region_name}
                                        </p>
                                        
                                        <div style={{ marginBottom: '8px', paddingBottom: '8px', borderBottom: '1px solid #e5e7eb' }} className="dark:border-gray-700">
                                            <p style={{ fontSize: '11px', color: '#6b7280', marginBottom: '4px' }}>Target Revenue</p>
                                            <p style={{ margin: '4px 0', fontSize: '14px' }} className="text-gray-900 dark:text-gray-100">
                                                <span style={{ 
                                                    display: 'inline-block',
                                                    width: '12px',
                                                    height: '12px',
                                                    backgroundColor: '#94a3b8',
                                                    marginRight: '8px',
                                                    borderRadius: '2px',
                                                    opacity: 0.8
                                                }}></span>
                                                {tooltipData.formatted_target}
                                            </p>
                                        </div>
                                        
                                        <div style={{ marginBottom: '8px', paddingBottom: '8px', borderBottom: '1px solid #e5e7eb' }} className="dark:border-gray-700">
                                            <p style={{ fontSize: '11px', color: '#6b7280', marginBottom: '4px' }}>Actual Revenue</p>
                                            <p style={{ margin: '4px 0', fontSize: '14px' }} className="text-gray-900 dark:text-gray-100">
                                                <span style={{ 
                                                    display: 'inline-block',
                                                    width: '12px',
                                                    height: '12px',
                                                    backgroundColor: tooltipData.realisasi_revenue > tooltipData.target_revenue ? '#16a34a' : '#dc2626',
                                                    marginRight: '8px',
                                                    borderRadius: '2px'
                                                }}></span>
                                                {tooltipData.formatted_realisasi}
                                            </p>
                                        </div>
                                        
                                        <div style={{ 
                                            display: 'flex', 
                                            alignItems: 'center', 
                                            gap: '8px',
                                            padding: '6px 8px',
                                            borderRadius: '4px',
                                            backgroundColor: (tooltipData.realisasi_revenue - tooltipData.target_revenue) >= 0 ? '#dcfce7' : '#fee2e2',
                                            color: (tooltipData.realisasi_revenue - tooltipData.target_revenue) >= 0 ? '#166534' : '#991b1b'
                                        }}>
                                            <span style={{ fontWeight: 600, fontSize: '12px' }}>
                                                {(tooltipData.realisasi_revenue - tooltipData.target_revenue) >= 0 ? '▲' : '▼'} {(() => {
                                                    const absVariance = Math.abs(tooltipData.realisasi_revenue - tooltipData.target_revenue);
                                                    if (absVariance >= 1000000000000) {
                                                        return `${(absVariance / 1000000000000).toFixed(2)}T`;
                                                    } else if (absVariance >= 1000000000) {
                                                        return `${(absVariance / 1000000000).toFixed(2)}M`;
                                                    } else {
                                                        return `${(absVariance / 1000000).toFixed(2)}Jt`;
                                                    }
                                                })()}
                                            </span>
                                            <span style={{ fontSize: '11px' }}>
                                                {(tooltipData.realisasi_revenue - tooltipData.target_revenue) >= 0 ? 'Above Target' : 'Below Target'}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {regionRevenueData.map((region, idx) => {
                            const maxValue = Math.max(...regionRevenueData.map(r => Math.max(r.target_revenue, r.realisasi_revenue)));
                            const targetHeight = (region.target_revenue / maxValue) * 100;
                            const realisasiHeight = (region.realisasi_revenue / maxValue) * 100;
                            const isAboveTarget = region.realisasi_revenue > region.target_revenue;
                            const variance = region.realisasi_revenue - region.target_revenue;
                            const varianceHeight = (Math.abs(variance) / maxValue) * 100;
                            
                            return (
                                <div 
                                    key={region.region_code} 
                                    className="flex flex-col items-center flex-1 max-w-[90px] group cursor-pointer"
                                    onClick={() => onRegionClick(region.region_code)}
                                    onMouseEnter={() => setTooltipData(region)}
                                    onMouseLeave={() => setTooltipData(null)}
                                    onMouseMove={(e) => {
                                        const chartContainer = e.currentTarget.closest('.chart-container');
                                        if (chartContainer) {
                                            const rect = chartContainer.getBoundingClientRect();
                                            setTooltipPosition({
                                                x: e.clientX - rect.left,
                                                y: e.clientY - rect.top
                                            });
                                        }
                                    }}
                                >
                                    {/* Stacked Bar Chart */}
                                    <div 
                                        className="relative w-full flex flex-col-reverse" 
                                        style={{ height: '420px' }}
                                    >
                                        {/* Percentage Label - follows chart height */}
                                        {variance !== 0 && (
                                            <div 
                                                className={`absolute left-1/2 transform -translate-x-1/2 text-xs font-semibold flex items-center gap-0.5 whitespace-nowrap ${
                                                    isAboveTarget ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500'
                                                }`}
                                                style={{ 
                                                    bottom: `${isAboveTarget ? targetHeight + varianceHeight : Math.max(targetHeight, realisasiHeight)}%`,
                                                    marginBottom: '4px'
                                                }}
                                            >
                                                <span>{isAboveTarget ? `${(100 + Math.abs(region.variance_percentage)).toFixed(1)}%` : `${(100 - Math.abs(region.variance_percentage)).toFixed(1)}%`}</span>
                                            </div>
                                        )}
                                        
                                        {/* Gray base bar (Target) */}
                                        <div 
                                            className="w-full relative shadow-md transition-all duration-200 hover:opacity-90"
                                            style={{ height: `${targetHeight}%`, backgroundColor: '#94a3b8', opacity: 0.8 }}
                                        >
                                            {/* Target marker line */}
                                            <div className="absolute -top-[2px] left-0 right-0 h-[2px] bg-black z-10 shadow-md"></div>
                                            
                                            {/* Variance bar on top of gray */}
                                            {variance !== 0 && (
                                                <div 
                                                    className={`absolute left-0 right-0 rounded-t-xl shadow-lg transition-all duration-200 group-hover:opacity-90 ${
                                                        isAboveTarget 
                                                            ? 'bg-gradient-to-t from-green-500 to-green-600' 
                                                            : 'bg-gradient-to-t from-red-500 to-red-600'
                                                    }`}
                                                    style={{ 
                                                        height: `${(varianceHeight / targetHeight) * 100}%`,
                                                        bottom: isAboveTarget ? '100%' : 'auto',
                                                        top: isAboveTarget ? 'auto' : '0'
                                                    }}
                                                />
                                            )}
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                    
                        {/* X-Axis with Label */}
                        <div className="relative mt-0">
                            {/* Tick Marks and Labels */}
                            <div className="flex items-start justify-around gap-6 pl-6">
                                {regionRevenueData.map((region) => (
                                    <div key={`label-${region.region_code}`} className="flex-1 max-w-[90px] flex flex-col items-center justify-center">
                                        <div className="w-px h-1.5 bg-gray-400 mb-0.5"></div>
                                        <div className="text-xs text-gray-600 text-center">
                                            {region.region_code}
                                        </div>
                                    </div>
                                ))}
                            </div>
                            
                            {/* Axis Label */}
                            <div className="text-center mt-0.5 mb-0.5">
                                <span className="text-xs text-gray-600" style={{ fontSize: '11px' }}>
                                    Region
                                </span>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    );
}
