import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/react';
import axios from '@/lib/axios';
import { 
    ChevronDown, 
    Upload, 
    FileSpreadsheet, 
    CheckCircle2, 
    AlertCircle,
    Download,
    Trash2,
    Calendar,
    Clock,
    TrendingUp
} from 'lucide-react';
import { useState, useEffect } from 'react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import AlertModal, { AlertType } from '@/components/modals/AlertModal';
import ConfirmModal from '@/components/modals/ConfirmModal';

interface ActivityLog {
    action: 'uploaded' | 'replaced' | 'deleted';
    fileName: string;
    user: string;
    timestamp: string;
    fileSize?: string;
    rowCount?: number;
}

interface QuarterData {
    quarter: number;
    name: string;
    status: 'uploaded' | 'pending' | 'error';
    uploadInfo?: {
        fileName: string;
        uploadDate: string;
        uploadedBy: string;
        fileSize: string;
        rowCount: number;
        amCount: number;
        totalTarget: string;
        totalRealisasi: string;
        regionCount: number;
    };
    activityLogs?: ActivityLog[];
}

interface DataImportPerformanceProps {
    initialQuartersData?: QuarterData[];
    selectedYear?: number;
    currentYear?: number;
}

export default function DataImportPerformance({ 
    initialQuartersData = [], 
    selectedYear: propSelectedYear, 
    currentYear: propCurrentYear 
}: DataImportPerformanceProps) {
    const currentYear = propCurrentYear || new Date().getFullYear();
    const initialYear = propSelectedYear || currentYear;
    
    const [selectedYear, setSelectedYear] = useState(initialYear);
    const [expandedQuarters, setExpandedQuarters] = useState<number[]>([]);
    const [expandedActivityLogs, setExpandedActivityLogs] = useState<number[]>([]);
    const [selectedFiles, setSelectedFiles] = useState<Record<number, File | null>>({});
    const [isUploading, setIsUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [uploadingQuarter, setUploadingQuarter] = useState<number | null>(null);
    const [quartersData, setQuartersData] = useState<QuarterData[]>(
        initialQuartersData.length > 0 ? initialQuartersData : [
            { quarter: 1, name: 'Quarter 1 (Jan - Mar)', status: 'pending' },
            { quarter: 2, name: 'Quarter 2 (Apr - Jun)', status: 'pending' },
            { quarter: 3, name: 'Quarter 3 (Jul - Sep)', status: 'pending' },
            { quarter: 4, name: 'Quarter 4 (Oct - Dec)', status: 'pending' },
        ]
    );
    const [uploadErrors, setUploadErrors] = useState<any[]>([]);
    const [showErrorModal, setShowErrorModal] = useState(false);
    
    // Alert Modal State
    const [alertModal, setAlertModal] = useState<{
        isOpen: boolean;
        type: AlertType;
        title: string;
        message: string;
        details?: string[];
    }>({
        isOpen: false,
        type: 'info',
        title: '',
        message: '',
        details: []
    });

    // Confirm Modal State
    const [confirmModal, setConfirmModal] = useState<{
        isOpen: boolean;
        type: 'danger' | 'warning' | 'info';
        title: string;
        message: string;
        details?: string[];
        requiresTyping?: boolean;
        typingConfirmation?: string;
        onConfirm: () => void;
    }>({
        isOpen: false,
        type: 'warning',
        title: '',
        message: '',
        onConfirm: () => {}
    });

    // Sync quartersData when initialQuartersData prop changes
    useEffect(() => {
        if (initialQuartersData.length > 0) {
            setQuartersData(initialQuartersData);
        }
    }, [initialQuartersData]);

    // Sync selectedYear when prop changes
    useEffect(() => {
        if (propSelectedYear) {
            setSelectedYear(propSelectedYear);
        }
    }, [propSelectedYear]);

    // Available years (current year and previous 4 years)
    const availableYears = Array.from({ length: 5 }, (_, i) => currentYear - i);

    // Handle year change
    const handleYearChange = (year: number) => {
        window.location.href = `/data-import/performance?year=${year}`;
    };

    const toggleQuarter = (quarter: number) => {
        setExpandedQuarters(prev => {
            const isCurrentlyExpanded = prev.includes(quarter);
            
            // If closing the quarter, also close its activity log
            if (isCurrentlyExpanded) {
                setExpandedActivityLogs(prevLogs => 
                    prevLogs.filter(q => q !== quarter)
                );
                return prev.filter(q => q !== quarter);
            }
            
            return [...prev, quarter];
        });
    };

    const handleFileSelect = (quarter: number, event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0] || null;
        setSelectedFiles(prev => ({ ...prev, [quarter]: file }));
    };

    const handleUpload = async (quarter: number) => {
        const file = selectedFiles[quarter];
        if (!file) {
            setAlertModal({
                isOpen: true,
                type: 'warning',
                title: 'File Tidak Dipilih',
                message: 'Silakan pilih file Excel terlebih dahulu sebelum upload.'
            });
            return;
        }

        // Validate file type
        const validExtensions = ['.xlsx', '.xls'];
        const fileExtension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
        if (!validExtensions.includes(fileExtension)) {
            setAlertModal({
                isOpen: true,
                type: 'error',
                title: 'Tipe File Tidak Valid',
                message: 'Silakan upload file Excel dengan format .xlsx atau .xls',
                details: [
                    `File yang dipilih: ${file.name}`,
                    'Format yang diterima: .xlsx, .xls'
                ]
            });
            return;
        }

        setIsUploading(true);
        setUploadingQuarter(quarter);
        setUploadProgress(0);
        setUploadErrors([]);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('quarter', quarter.toString());
        formData.append('year', selectedYear.toString());

        try {
            const response = await axios.post('/api/data-import/performance/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / (progressEvent.total || 100));
                    setUploadProgress(percentCompleted);
                },
            });

            if (response.data.success) {
                setAlertModal({
                    isOpen: true,
                    type: 'success',
                    title: 'Upload Berhasil!',
                    message: `Data Performance AM untuk Q${quarter} ${selectedYear} telah berhasil diupload.`,
                    details: response.data.summary ? [
                        `Total AM: ${response.data.summary.am_count || 0}`,
                        `Total Company: ${response.data.summary.company_count || 0}`,
                        `Total Records: ${response.data.summary.row_count || 0}`
                    ] : undefined
                });
                
                // Reload after modal closes
                setTimeout(() => window.location.reload(), 2000);
            }
        } catch (error: any) {
            console.error('Upload error:', error);
            
            if (error.response?.status === 422 && error.response?.data?.conflicts) {
                // Handle conflict errors
                setUploadErrors(error.response.data.conflicts);
                setShowErrorModal(true);
            } else if (error.response?.status === 422 && error.response?.data?.error_type === 'missing_revenue_data') {
                // Handle missing revenue data error
                const errorMessage = error.response?.data?.message || 'Upload Data Revenue Dashboard First';
                setAlertModal({
                    isOpen: true,
                    type: 'warning',
                    title: 'Data Revenue Belum Tersedia',
                    message: errorMessage,
                    details: [
                        'Silakan upload data Revenue Dashboard terlebih dahulu',
                        'Data Performance AM memerlukan data Revenue untuk validasi'
                    ]
                });
            } else {
                const errorMessage = error.response?.data?.message || 'Upload failed';
                setAlertModal({
                    isOpen: true,
                    type: 'error',
                    title: 'Upload Gagal',
                    message: errorMessage
                });
            }
        } finally {
            setIsUploading(false);
            setUploadingQuarter(null);
            setUploadProgress(0);
        }
    };

    const handleDelete = async (quarter: number) => {
        setConfirmModal({
            isOpen: true,
            type: 'danger',
            title: `Hapus Data Q${quarter} ${selectedYear}`,
            message: `Anda akan menghapus SEMUA data Performance AM untuk Q${quarter} ${selectedYear}.`,
            details: [
                'Tindakan ini TIDAK DAPAT DIBATALKAN',
                'Semua data Account Manager untuk quarter ini akan terhapus',
                'Data dashboard akan diperbarui setelah penghapusan'
            ],
            onConfirm: async () => {
                setIsUploading(true);
                setConfirmModal(prev => ({ ...prev, isOpen: false }));

                try {
                    await axios.delete(`/api/data-import/performance/delete/${selectedYear}/${quarter}`);
                    
                    setAlertModal({
                        isOpen: true,
                        type: 'success',
                        title: 'Data Berhasil Dihapus',
                        message: `Data Performance AM Q${quarter} ${selectedYear} telah berhasil dihapus.`
                    });
                    
                    setTimeout(() => window.location.reload(), 1500);
                } catch (error: any) {
                    console.error('Delete error:', error);
                    
                    const errorMessage = error.response?.data?.message || 'Delete failed';
                    setAlertModal({
                        isOpen: true,
                        type: 'error',
                        title: 'Penghapusan Gagal',
                        message: errorMessage
                    });
                    
                    setIsUploading(false);
                }
            }
        });
    };

    const handleDownloadTemplate = () => {
        window.location.href = '/api/data-import/performance/template';
    };

    const handleDeleteYear = async () => {
        setConfirmModal({
            isOpen: true,
            type: 'danger',
            title: `Hapus Data Tahun ${selectedYear}`,
            message: `Anda akan menghapus SEMUA data Performance AM tahun ${selectedYear} (4 quarters).`,
            details: [
                'Tindakan ini TIDAK DAPAT DIBATALKAN',
                'Semua data 4 quarter akan terhapus permanent',
                'Dashboard akan dikosongkan untuk tahun ini',
                `Ketik "HAPUS" untuk konfirmasi`
            ],
            requiresTyping: true,
            typingConfirmation: 'HAPUS',
            onConfirm: async () => {
                setIsUploading(true);
                setConfirmModal(prev => ({ ...prev, isOpen: false }));

                try {
                    await axios.delete(`/api/data-import/performance/delete/${selectedYear}`);
                    
                    // Success - redirect
                    const targetYear = selectedYear === currentYear ? currentYear - 1 : currentYear;
                    window.location.href = `/data-import/performance?year=${targetYear}`;
                } catch (error: any) {
                    console.error('Delete error:', error);
                    
                    const errorMessage = error.response?.data?.message || 'Delete failed';
                    setAlertModal({
                        isOpen: true,
                        type: 'error',
                        title: 'Penghapusan Gagal',
                        message: errorMessage
                    });
                    
                    setIsUploading(false);
                }
            }
        });
    };

    const getStatusBadge = (status: QuarterData['status']) => {
        switch (status) {
            case 'uploaded':
                return (
                    <div className="flex items-center gap-1.5 text-green-600 dark:text-green-400">
                        <CheckCircle2 className="w-4 h-4" />
                        <span className="text-sm font-medium">Uploaded</span>
                    </div>
                );
            case 'pending':
                return (
                    <div className="flex items-center gap-1.5 text-orange-600 dark:text-orange-400">
                        <AlertCircle className="w-4 h-4" />
                        <span className="text-sm font-medium">Pending Upload</span>
                    </div>
                );
            case 'error':
                return (
                    <div className="flex items-center gap-1.5 text-red-600 dark:text-red-400">
                        <AlertCircle className="w-4 h-4" />
                        <span className="text-sm font-medium">Error</span>
                    </div>
                );
        }
    };

    const uploadedCount = quartersData.filter(q => q.status === 'uploaded').length;
    const progressPercentage = (uploadedCount / 4) * 100;

    return (
        <AppLayout>
            <Head title="Data Upload - Performance AM" />
            
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-gradient-to-br from-red-50/70 via-white to-pink-50/70 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
                {/* Page Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl pl-1 font-bold text-gray-900 dark:text-gray-100">Data Upload - Performance AM</h1>
                        <p className="text-gray-600 pl-1 dark:text-gray-400 mt-1">Upload quarterly performance data for Account Managers</p>
                    </div>
                    
                    {/* Compact Year Filter & Delete Button */}
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <Calendar className="w-4 h-4 text-gray-500 dark:text-gray-400" />
                            <span className="text-sm text-gray-600 dark:text-gray-400">Year:</span>
                            <div className="relative">
                                <select
                                    value={selectedYear}
                                    onChange={(e) => handleYearChange(Number(e.target.value))}
                                    className="
                                        appearance-none
                                        bg-white dark:bg-gray-800 
                                        border border-gray-300 dark:border-gray-600 
                                        rounded-lg 
                                        px-4 py-2 pr-10
                                        text-sm font-semibold
                                        text-gray-900 dark:text-gray-100
                                        hover:border-gray-400 dark:hover:border-gray-500
                                        focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400
                                        transition-colors
                                        cursor-pointer
                                    "
                                >
                                    {availableYears.map(year => (
                                        <option key={year} value={year}>{year}</option>
                                    ))}
                                </select>
                                <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                            </div>
                        </div>
                        
                        {/* Download Template Button */}
                        <Button 
                            variant="outline" 
                            onClick={handleDownloadTemplate}
                            className="flex items-center gap-2"
                        >
                            <Download className="w-4 h-4" />
                            Download Template
                        </Button>

                        {/* Delete Year Button */}
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={handleDeleteYear}
                            disabled={isUploading}
                            className="gap-2 border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 hover:text-red-700 dark:hover:text-red-300"
                        >
                            <Trash2 className="w-4 h-4" />
                            Delete Year {selectedYear}
                        </Button>
                    </div>
                </div>

                {/* Quarterly Upload List */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                    <FileSpreadsheet className="w-6 h-6 text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <CardTitle>Quarterly Data Upload - {selectedYear}</CardTitle>
                                    <CardDescription>
                                        {uploadedCount}/4 quarters uploaded • {Math.round(progressPercentage)}% complete
                                    </CardDescription>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-2">
                        {quartersData.map((quarterData) => {
                            const getHeaderColors = () => {
                                if (quarterData.status === 'uploaded') {
                                    return {
                                        border: 'border-green-600 dark:border-green-500',
                                        bg: 'bg-green-50 dark:bg-green-950/30',
                                        hover: 'hover:bg-green-100 dark:hover:bg-green-900/40',
                                        icon: 'text-green-600 dark:text-green-400',
                                        chevron: 'text-green-500 dark:text-green-400'
                                    };
                                } else if (quarterData.status === 'pending') {
                                    return {
                                        border: 'border-red-600 dark:border-red-500',
                                        bg: 'bg-red-50 dark:bg-red-950/30',
                                        hover: 'hover:bg-red-100 dark:hover:bg-red-900/40',
                                        icon: 'text-red-600 dark:text-red-400',
                                        chevron: 'text-red-500 dark:text-red-400'
                                    };
                                } else {
                                    return {
                                        border: 'border-gray-200 dark:border-gray-700',
                                        bg: 'bg-white dark:bg-gray-900',
                                        hover: 'hover:bg-gray-50 dark:hover:bg-gray-800/50',
                                        icon: 'text-gray-400',
                                        chevron: 'text-gray-500 dark:text-gray-400'
                                    };
                                }
                            };

                            const colors = getHeaderColors();

                            return (
                            <Collapsible
                                key={quarterData.quarter}
                                open={expandedQuarters.includes(quarterData.quarter)}
                                onOpenChange={() => toggleQuarter(quarterData.quarter)}
                            >
                                <div className={`border ${colors.border} rounded-lg overflow-hidden`}>
                                    {/* Quarter Header */}
                                    <CollapsibleTrigger className="w-full">
                                        <div className={`flex items-center justify-between p-4 ${colors.bg} ${colors.hover} transition-colors`}>
                                            <div className="flex items-center gap-3">
                                                <FileSpreadsheet className={`w-5 h-5 ${colors.icon}`} />
                                                <span className="font-semibold text-gray-900 dark:text-gray-100">
                                                    {quarterData.name} {selectedYear}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                {getStatusBadge(quarterData.status)}
                                                <div className="p-1 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                                    {expandedQuarters.includes(quarterData.quarter) ? (
                                                        <ChevronDown className={`w-4 h-4 ${colors.chevron}`} />
                                                    ) : (
                                                        <ChevronDown className={`w-4 h-4 ${colors.chevron} -rotate-90`} />
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <div className="p-4 space-y-4">
                                            {/* Upload Section */}
                                            {quarterData.status === 'pending' && (
                                        <div className="space-y-4">
                                            <div className="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6">
                                                <div className="flex flex-col items-center justify-center space-y-3">
                                                    <Upload className="w-8 h-8 text-gray-400" />
                                                    <div className="text-center">
                                                        <label 
                                                            htmlFor={`file-upload-${quarterData.quarter}`}
                                                            className="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                                                        >
                                                            Choose a file
                                                        </label>
                                                        <input
                                                            id={`file-upload-${quarterData.quarter}`}
                                                            type="file"
                                                            accept=".xlsx,.xls"
                                                            onChange={(e) => handleFileSelect(quarterData.quarter, e)}
                                                            className="hidden"
                                                        />
                                                        <span className="text-sm text-gray-500 dark:text-gray-400"> or drag and drop</span>
                                                    </div>
                                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                                        Excel files only (.xlsx, .xls)
                                                    </p>
                                                </div>
                                            </div>

                                            {selectedFiles[quarterData.quarter] && (
                                                <div className="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                                    <div className="flex items-center gap-2">
                                                        <FileSpreadsheet className="w-4 h-4 text-red-600 dark:text-red-400" />
                                                        <span className="text-sm font-medium text-gray-900 dark:text-white">
                                                            {selectedFiles[quarterData.quarter]?.name}
                                                        </span>
                                                    </div>
                                                    <Button 
                                                        onClick={() => handleUpload(quarterData.quarter)}
                                                        disabled={isUploading}
                                                        className="flex items-center gap-2"
                                                    >
                                                        {uploadingQuarter === quarterData.quarter ? (
                                                            <>
                                                                <span className="animate-spin">⏳</span>
                                                                Uploading... {uploadProgress}%
                                                            </>
                                                        ) : (
                                                            <>
                                                                <Upload className="w-4 h-4" />
                                                                Upload
                                                            </>
                                                        )}
                                                    </Button>
                                                </div>
                                            )}

                                            {uploadingQuarter === quarterData.quarter && (
                                                <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div 
                                                        className="bg-red-600 h-2 rounded-full transition-all duration-300"
                                                        style={{ width: `${uploadProgress}%` }}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    {/* Upload Info Section */}
                                    {quarterData.status === 'uploaded' && quarterData.uploadInfo && (
                                        <div className="space-y-4">
                                            {/* Summary Stats */}
                                            <div className="grid grid-cols-4 gap-4">
                                                <div className="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                                                    <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                        {quarterData.uploadInfo.amCount}
                                                    </div>
                                                    <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        Account Managers
                                                    </div>
                                                </div>
                                                <div className="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                                                    <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                        {quarterData.uploadInfo.regionCount}
                                                    </div>
                                                    <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        Regions
                                                    </div>
                                                </div>
                                                <div className="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                                                    <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                        {quarterData.uploadInfo.totalTarget}
                                                    </div>
                                                    <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        Total Target
                                                    </div>
                                                </div>
                                                <div className="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                                                    <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                        {quarterData.uploadInfo.totalRealisasi}
                                                    </div>
                                                    <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        Total Realisasi
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Upload Details */}
                                            <div className="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-2">
                                                <div className="flex items-center justify-between text-sm">
                                                    <span className="text-gray-600 dark:text-gray-400">File Name:</span>
                                                    <span className="font-medium text-gray-900 dark:text-white">{quarterData.uploadInfo.fileName}</span>
                                                </div>
                                                <div className="flex items-center justify-between text-sm">
                                                    <span className="text-gray-600 dark:text-gray-400">Upload Date:</span>
                                                    <span className="font-medium text-gray-900 dark:text-white">{quarterData.uploadInfo.uploadDate}</span>
                                                </div>
                                                <div className="flex items-center justify-between text-sm">
                                                    <span className="text-gray-600 dark:text-gray-400">Uploaded By:</span>
                                                    <span className="font-medium text-gray-900 dark:text-white">{quarterData.uploadInfo.uploadedBy}</span>
                                                </div>
                                                <div className="flex items-center justify-between text-sm">
                                                    <span className="text-gray-600 dark:text-gray-400">File Size:</span>
                                                    <span className="font-medium text-gray-900 dark:text-white">{quarterData.uploadInfo.fileSize}</span>
                                                </div>
                                                <div className="flex items-center justify-between text-sm">
                                                    <span className="text-gray-600 dark:text-gray-400">Data Rows:</span>
                                                    <span className="font-medium text-gray-900 dark:text-white">{quarterData.uploadInfo.rowCount}</span>
                                                </div>
                                            </div>

                                            {/* Action Buttons */}
                                            <div className="flex items-center gap-3">
                                                <label 
                                                    htmlFor={`file-replace-${quarterData.quarter}`}
                                                    className="flex-1"
                                                >
                                                    <Button 
                                                        variant="outline" 
                                                        className="w-full"
                                                        onClick={() => document.getElementById(`file-replace-${quarterData.quarter}`)?.click()}
                                                    >
                                                        <Upload className="w-4 h-4 mr-2" />
                                                        Replace File
                                                    </Button>
                                                    <input
                                                        id={`file-replace-${quarterData.quarter}`}
                                                        type="file"
                                                        accept=".xlsx,.xls"
                                                        onChange={(e) => handleFileSelect(quarterData.quarter, e)}
                                                        className="hidden"
                                                    />
                                                </label>
                                                <Button 
                                                    variant="destructive"
                                                    onClick={() => handleDelete(quarterData.quarter)}
                                                    disabled={isUploading}
                                                    className="flex-1"
                                                >
                                                    <Trash2 className="w-4 h-4 mr-2" />
                                                    Delete Q{quarterData.quarter} Data
                                                </Button>
                                            </div>

                                            {selectedFiles[quarterData.quarter] && (
                                                <div className="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                                    <div className="flex items-center gap-2">
                                                        <FileSpreadsheet className="w-4 h-4 text-red-600 dark:text-red-400" />
                                                        <span className="text-sm font-medium text-gray-900 dark:text-white">
                                                            {selectedFiles[quarterData.quarter]?.name}
                                                        </span>
                                                    </div>
                                                    <Button 
                                                        onClick={() => handleUpload(quarterData.quarter)}
                                                        disabled={isUploading}
                                                        className="flex items-center gap-2"
                                                    >
                                                        {uploadingQuarter === quarterData.quarter ? (
                                                            <>
                                                                <span className="animate-spin">⏳</span>
                                                                Replacing... {uploadProgress}%
                                                            </>
                                                        ) : (
                                                            <>
                                                                <Upload className="w-4 h-4" />
                                                                Upload New File
                                                            </>
                                                        )}
                                                    </Button>
                                                </div>
                                            )}

                                            {/* Activity Logs */}
                                            {quarterData.activityLogs && quarterData.activityLogs.length > 0 && (
                                                <div className="border-t pt-4 mt-4">
                                                    <Collapsible
                                                        open={expandedActivityLogs.includes(quarterData.quarter)}
                                                        onOpenChange={() => {
                                                            setExpandedActivityLogs(prev =>
                                                                prev.includes(quarterData.quarter)
                                                                    ? prev.filter(q => q !== quarterData.quarter)
                                                                    : [...prev, quarterData.quarter]
                                                            );
                                                        }}
                                                    >
                                                        <CollapsibleTrigger asChild>
                                                            <button className="flex items-center justify-between w-full text-sm font-semibold text-gray-900 dark:text-white mb-3 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                                                <span className="flex items-center gap-2">
                                                                    <Clock className="w-4 h-4" />
                                                                    Activity Log ({quarterData.activityLogs.length})
                                                                </span>
                                                                <ChevronDown
                                                                    className={`w-4 h-4 transition-transform ${
                                                                        expandedActivityLogs.includes(quarterData.quarter) ? 'rotate-180' : ''
                                                                    }`}
                                                                />
                                                            </button>
                                                        </CollapsibleTrigger>
                                                        <CollapsibleContent className="space-y-2">
                                                            <div className="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                                <table className="w-full text-sm">
                                                                    <thead className="bg-gray-50 dark:bg-gray-800">
                                                                        <tr>
                                                                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                                Timestamp
                                                                            </th>
                                                                            <th className="px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                                Action
                                                                            </th>
                                                                            <th className="px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                                User
                                                                            </th>
                                                                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                                File Name
                                                                            </th>
                                                                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                                File Size
                                                                            </th>
                                                                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                                Rows
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody className="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                                                        {quarterData.activityLogs.map((log, idx) => (
                                                                            <tr key={idx} className="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                                                <td className="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                                                                    {log.timestamp}
                                                                                </td>
                                                                                <td className="px-4 py-3 whitespace-nowrap text-center">
                                                                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                                                        log.action === 'uploaded' || log.action === 'upload'
                                                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                                                            : log.action === 'replaced' || log.action === 'update'
                                                                                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
                                                                                            : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                                                                    }`}>
                                                                                        {log.action === 'upload' ? 'Uploaded' : log.action === 'update' ? 'Replaced' : log.action === 'delete' ? 'Deleted' : log.action.charAt(0).toUpperCase() + log.action.slice(1)}
                                                                                    </span>
                                                                                </td>
                                                                                <td className="px-4 py-3 text-gray-900 dark:text-white text-center">
                                                                                    {log.user}
                                                                                </td>
                                                                                <td className="px-4 py-3 text-gray-900 dark:text-white">
                                                                                    {log.fileName}
                                                                                </td>
                                                                                <td className="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                                                                    {log.fileSize || '-'}
                                                                                </td>
                                                                                <td className="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                                                                    {log.rowCount ? log.rowCount.toLocaleString() : '-'}
                                                                                </td>
                                                                            </tr>
                                                                        ))}
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </CollapsibleContent>
                                                    </Collapsible>
                                                </div>
                                            )}
                                        </div>
                                    )}
                                        </div>
                                    </CollapsibleContent>
                                </div>
                            </Collapsible>
                            );
                        })}
                    </CardContent>
                </Card>

            {/* Error Modal */}
            {showErrorModal && uploadErrors.length > 0 && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <Card className="max-w-2xl w-full max-h-[80vh] overflow-hidden flex flex-col">
                        <CardHeader className="border-b">
                            <CardTitle className="text-red-600 dark:text-red-400 flex items-center gap-2">
                                <AlertCircle className="w-5 h-5" />
                                Data Conflict Detected
                            </CardTitle>
                            <CardDescription>
                                The uploaded file contains data that already exists in the database. Please review the conflicts below.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="overflow-y-auto flex-1 p-6">
                            <div className="space-y-3">
                                {uploadErrors.map((error, idx) => (
                                    <div key={idx} className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                        <div className="font-medium text-gray-900 dark:text-white mb-2">
                                            Row {error.row}: {error.am_nama}
                                        </div>
                                        <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            <div>NIK: {error.am_nik}</div>
                                            <div>Company: {error.company_pembagian}</div>
                                            <div className="text-red-600 dark:text-red-400 font-medium mt-2">
                                                {error.message}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                        <div className="border-t p-4">
                            <Button 
                                onClick={() => setShowErrorModal(false)}
                                className="w-full"
                            >
                                Close
                            </Button>
                        </div>
                    </Card>
                </div>
            )}

            {/* Info Card */}
            <Card className="mt-6 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
                <CardHeader>
                    <CardTitle className="text-base flex items-center gap-2 text-blue-600 dark:text-blue-400">
                        <TrendingUp className="w-4 h-4" />
                        Excel Template Format
                    </CardTitle>
                </CardHeader>
                <CardContent className="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <p><strong>Sheet 1: region_and_witel</strong></p>
                    <ul className="list-disc list-inside pl-2 space-y-1">
                        <li>Kolom A-C: Data Regional (kode, nama, urutan_region)</li>
                        <li>Kolom D-E: Data Witel (kode, nama)</li>
                    </ul>
                    <p className="pt-2"><strong>Sheet 2: TWS {selectedYear}</strong></p>
                    <ul className="list-disc list-inside pl-2 space-y-1">
                        <li>Kolom A-H: Data Account Manager (NIK, Nama, Posisi, Regional, Witel, No GSM)</li>
                        <li>Kolom I-P: Data Company (Pembagian, NIP NAS, Proporsi)</li>
                        <li>Kolom Q-T: Target (Revenue, Sustain, Scalling, NGTMA)</li>
                        <li>Kolom U-X: Realisasi (Revenue, Sustain, Scalling, NGTMA)</li>
                    </ul>
                </CardContent>
            </Card>
            
            {/* Alert Modal */}
            <AlertModal
                isOpen={alertModal.isOpen}
                onClose={() => setAlertModal(prev => ({ ...prev, isOpen: false }))}
                type={alertModal.type}
                title={alertModal.title}
                message={alertModal.message}
                details={alertModal.details}
            />

            {/* Confirm Modal */}
            <ConfirmModal
                isOpen={confirmModal.isOpen}
                onClose={() => setConfirmModal(prev => ({ ...prev, isOpen: false }))}
                onConfirm={confirmModal.onConfirm}
                type={confirmModal.type}
                title={confirmModal.title}
                message={confirmModal.message}
                details={confirmModal.details}
                requiresTyping={confirmModal.requiresTyping}
                typingConfirmation={confirmModal.typingConfirmation}
                isLoading={isUploading}
            />
            </div>
        </AppLayout>
    );
}
