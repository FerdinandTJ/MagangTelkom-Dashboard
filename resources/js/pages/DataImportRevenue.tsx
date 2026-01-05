import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Head, router, usePage } from '@inertiajs/react';
import axios from '@/lib/axios';
import { revenue } from '@/routes/data-import';
import { upload, template } from '@/routes/data-import/revenue';
import { 
    ChevronDown, 
    Upload, 
    FileSpreadsheet, 
    CheckCircle2, 
    AlertCircle,
    Eye,
    Download,
    Trash2,
    Calendar,
    Clock
} from 'lucide-react';
import { useState, useEffect } from 'react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';

interface ActivityLog {
    action: 'uploaded' | 'replaced' | 'deleted';
    fileName: string;
    user: string;
    timestamp: string;
}

interface MonthData {
    month: number;
    name: string;
    status: 'uploaded' | 'pending' | 'error';
    uploadInfo?: {
        fileName: string;
        uploadDate: string;
        uploadedBy: string;
        fileSize: string;
        rowCount: number;
        dateRange: string;
        totalRevenue: string;
        subsegmentCount: number;
    };
    activityLogs?: ActivityLog[];
}

interface DataImportRevenueProps {
    initialMonthsData?: MonthData[];
    selectedYear?: number;
    currentYear?: number;
}

export default function DataImportRevenue({ initialMonthsData = [], selectedYear: propSelectedYear, currentYear: propCurrentYear }: DataImportRevenueProps) {
    const currentYear = propCurrentYear || new Date().getFullYear();
    const initialYear = propSelectedYear || currentYear;
    
    const [selectedYear, setSelectedYear] = useState(initialYear);
    const [expandedMonths, setExpandedMonths] = useState<number[]>([]);
    const [selectedFiles, setSelectedFiles] = useState<Record<number, File | null>>({});
    const [isUploading, setIsUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [uploadingMonth, setUploadingMonth] = useState<number | null>(null);
    const [monthsData, setMonthsData] = useState<MonthData[]>(initialMonthsData);
    const [fileInputKey, setFileInputKey] = useState(Date.now()); // Key to force input re-render

    // Sync monthsData when initialMonthsData prop changes
    useEffect(() => {
        setMonthsData(initialMonthsData);
    }, [initialMonthsData]);

    // Sync selectedYear when prop changes
    useEffect(() => {
        if (propSelectedYear) {
            setSelectedYear(propSelectedYear);
        }
    }, [propSelectedYear]);

    // Available years (current year and previous 4 years)
    const availableYears = Array.from({ length: 5 }, (_, i) => currentYear - i);

    // Handle year change - full page reload to ensure fresh data
    const handleYearChange = (year: number) => {
        window.location.href = revenue.definition.url + '?year=' + year;
    };

    const toggleMonth = (month: number) => {
        setExpandedMonths(prev => 
            prev.includes(month) 
                ? prev.filter(m => m !== month)
                : [...prev, month]
        );
    };

    const handleFileSelect = (month: number, event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0] || null;
        setSelectedFiles(prev => ({ ...prev, [month]: file }));
    };

    const handleGeneralUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (!file) return;

        setIsUploading(true);
        setUploadProgress(0);
        setFileInputKey(Date.now()); // Reset input immediately

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post(upload.url(), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    if (progressEvent.total) {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        setUploadProgress(percentCompleted);
                    }
                },
            });

            // Success - reload data
            setIsUploading(false);
            setUploadProgress(0);
            
            // Check if years_imported is available in response
            const importStats = response.data?.import_stats;
            const yearsImported = importStats?.years_imported || [];
            
            // If years were imported, redirect to the latest year imported with full reload
            if (yearsImported.length > 0) {
                // Get the most recent year from imported years
                const targetYear = Math.max(...yearsImported.map((y: any) => parseInt(y)));
                // Use window.location for full page reload to ensure fresh data
                window.location.href = revenue.definition.url + '?year=' + targetYear;
            } else {
                // Fallback: reload current page
                window.location.reload();
            }
        } catch (error) {
            console.error('Upload error:', error);
            setIsUploading(false);
            setUploadProgress(0);
        }
    };

    const handleMonthUpload = async (month: number) => {
        const file = selectedFiles[month];
        if (!file) return;

        setIsUploading(true);
        setUploadingMonth(month);
        setUploadProgress(0);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('year', selectedYear.toString());
        formData.append('month', month.toString());

        try {
            await axios.post(upload.url(), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    if (progressEvent.total) {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        setUploadProgress(percentCompleted);
                    }
                },
            });

            // Success - reload data with full page reload
            setIsUploading(false);
            setUploadingMonth(null);
            setUploadProgress(0);
            setSelectedFiles(prev => ({ ...prev, [month]: null }));
            
            // Use window.location for full page reload to ensure fresh data
            window.location.href = revenue.url({ query: { year: selectedYear } });
        } catch (error) {
            console.error('Upload error:', error);
            setIsUploading(false);
            setUploadingMonth(null);
            setUploadProgress(0);
        }
    };

    // Handler untuk Replace/Update
    const handleReplaceClick = (month: number) => {
        const fileInput = document.getElementById(`replace-file-${month}`) as HTMLInputElement;
        fileInput?.click();
    };

    const handleReplaceFileSelect = async (month: number, event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (!file) return;

        const monthData = monthsData.find(m => m.month === month);
        const confirmed = window.confirm(
            `Apakah Anda yakin ingin mengganti data ${monthData?.name} ${selectedYear}?\n\n` +
            `File lama: ${monthData?.uploadInfo?.fileName}\n` +
            `File baru: ${file.name}\n\n` +
            `Data yang sudah ada akan ditimpa dengan data baru.`
        );
        
        if (!confirmed) {
            event.target.value = ''; // Reset input
            return;
        }

        setIsUploading(true);
        setUploadingMonth(month);
        setUploadProgress(0);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('year', selectedYear.toString());
        formData.append('month', month.toString());

        try {
            await axios.post(upload.url(), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    if (progressEvent.total) {
                        const percentCompleted = Math.round(
                            (progressEvent.loaded * 100) / progressEvent.total
                        );
                        setUploadProgress(percentCompleted);
                    }
                },
            });

            // Success - reload data with full page reload
            window.location.href = revenue.url({ query: { year: selectedYear } });
        } catch (error: any) {
            console.error('Replace error:', error);
            
            // Tampilkan error message
            const errorMessage = error.response?.data?.message || 'Terjadi kesalahan saat mengganti file';
            alert(`Gagal mengganti file:\n${errorMessage}`);
            
            setIsUploading(false);
            setUploadingMonth(null);
            setUploadProgress(0);
            event.target.value = ''; // Reset input
        }
    };

    // Handler untuk Download
    const handleDownload = (month: number) => {
        window.location.href = `/data-import/revenue/download/${selectedYear}/${month}`;
    };

    // Handler untuk Delete Year
    const handleDeleteYear = async () => {
        const confirmed = window.confirm(
            `⚠️ PERINGATAN: Hapus Data Tahun ${selectedYear}\n\n` +
            `Anda akan menghapus SEMUA data revenue tahun ${selectedYear}:\n` +
            `• ${uploadedCount} bulan data\n` +
            `• File Excel yang tersimpan\n` +
            `• Data revenue dan target\n\n` +
            `Tindakan ini TIDAK DAPAT DIBATALKAN!\n\n` +
            `Ketik "HAPUS" untuk konfirmasi:`
        );
        
        if (!confirmed) return;
        
        const confirmation = window.prompt(`Ketik "HAPUS" untuk mengkonfirmasi penghapusan data tahun ${selectedYear}:`);
        
        if (confirmation !== 'HAPUS') {
            alert('Konfirmasi tidak sesuai. Penghapusan dibatalkan.');
            return;
        }

        setIsUploading(true);

        try {
            await axios.delete(`/data-import/revenue/delete/${selectedYear}`);
            
            // Success - redirect ke current year atau tahun sebelumnya
            const targetYear = selectedYear === currentYear ? currentYear - 1 : currentYear;
            window.location.href = revenue.url({ query: { year: targetYear } });
        } catch (error: any) {
            console.error('Delete error:', error);
            
            const errorMessage = error.response?.data?.message || 'Terjadi kesalahan saat menghapus data';
            alert(`Gagal menghapus data:\n${errorMessage}`);
            
            setIsUploading(false);
        }
    };

    const getStatusBadge = (status: MonthData['status']) => {
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

    const uploadedCount = monthsData.filter(m => m.status === 'uploaded').length;
    const progressPercentage = (uploadedCount / 12) * 100;

    return (
        <AppLayout>
            <Head title="Data Upload - Revenue Dashboard" />
            
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-gradient-to-br from-red-50/70 via-white to-pink-50/70 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
                {/* Page Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl pl-1 font-bold text-gray-900 dark:text-gray-100">Data Upload - Revenue Dashboard</h1>
                        <p className="text-gray-600 pl-1 dark:text-gray-400 mt-1">Upload revenue data per month or use quick upload (auto-detect month from file)</p>
                    </div>
                    
                    {/* Compact Year Filter & Upload Button */}
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <Calendar className="w-4 h-4 text-gray-500 dark:text-gray-400" />
                            <span className="text-sm text-gray-600 dark:text-gray-400">Year:</span>
                            <div className="relative">
                                <select
                                    value={selectedYear}
                                    onChange={(e) => handleYearChange(Number(e.target.value))}
                                    className="appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-1.5 pr-8 text-sm font-medium text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                >
                                    {availableYears.map(year => (
                                        <option key={year} value={year}>{year}</option>
                                    ))}
                                </select>
                                <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                            </div>
                        </div>

                        {/* General Upload Button */}
                        <div>
                            <input
                                type="file"
                                id="general-upload"
                                className="hidden"
                                accept=".xlsx,.xls,.csv"
                                onChange={handleGeneralUpload}
                                key={fileInputKey}
                                disabled={isUploading}
                            />
                            <label htmlFor="general-upload">
                                <Button 
                                    size="sm" 
                                    className="cursor-pointer" 
                                    disabled={isUploading && uploadingMonth === null}
                                    asChild={!isUploading || uploadingMonth !== null}
                                >
                                    {isUploading && uploadingMonth === null ? (
                                        <div className="flex items-center">
                                            <span className="mr-2">Uploading...</span>
                                            <span>{uploadProgress}%</span>
                                        </div>
                                    ) : (
                                        <span>
                                            <Upload className="w-4 h-4 mr-2" />
                                            Quick Upload
                                        </span>
                                    )}
                                </Button>
                            </label>
                        </div>
                    </div>
                </div>

                {/* Monthly Upload List */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                    <FileSpreadsheet className="w-6 h-6 text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <CardTitle>Monthly Data Upload - {selectedYear}</CardTitle>
                                    <CardDescription>
                                        {uploadedCount}/12 months uploaded • {progressPercentage.toFixed(0)}% complete
                                    </CardDescription>
                                </div>
                            </div>
                            
                            {/* Yearly Action Buttons */}
                            {uploadedCount > 0 && (
                                <div className="flex items-center gap-2">
                                    <Button 
                                        size="sm" 
                                        variant="outline"
                                        className="text-red-600 hover:text-red-700 hover:border-red-600 dark:text-red-400 dark:hover:text-red-300"
                                        onClick={handleDeleteYear}
                                        disabled={isUploading}
                                    >
                                        <Trash2 className="w-4 h-4 mr-2" />
                                        Delete {selectedYear}
                                    </Button>
                                </div>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-2">
                        {monthsData.map((monthData) => (
                            <Collapsible
                                key={monthData.month}
                                open={expandedMonths.includes(monthData.month)}
                                onOpenChange={() => toggleMonth(monthData.month)}
                            >
                                <div className="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    {/* Month Header */}
                                    <CollapsibleTrigger className="w-full">
                                        <div className="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <div className="flex items-center gap-3">
                                                <Calendar className="w-5 h-5 text-gray-400" />
                                                <span className="font-semibold text-gray-900 dark:text-gray-100">
                                                    {monthData.name} {selectedYear}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                {getStatusBadge(monthData.status)}
                                                <div className="p-1 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                                    {expandedMonths.includes(monthData.month) ? (
                                                        <ChevronDown className="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                                    ) : (
                                                        <ChevronDown className="w-4 h-4 text-gray-500 dark:text-gray-400 -rotate-90" />
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </CollapsibleTrigger>

                                    {/* Month Content */}
                                    <CollapsibleContent>
                                        <div className="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800/30">
                                            {monthData.status === 'uploaded' && monthData.uploadInfo ? (
                                                // Uploaded Data View
                                                <div className="space-y-4">
                                                    {/* File Info */}
                                                    <div className="flex items-start justify-between">
                                                        <div className="flex items-start gap-3">
                                                            <div className="p-2 bg-green-50 dark:bg-green-950/30 rounded-lg">
                                                                <FileSpreadsheet className="w-5 h-5 text-green-600 dark:text-green-400" />
                                                            </div>
                                                            <div>
                                                                <p className="font-medium text-gray-900 dark:text-gray-100">{monthData.uploadInfo.fileName}</p>
                                                                <p className="text-sm text-gray-500 dark:text-gray-500 mt-0.5">
                                                                    Uploaded by <span className="font-medium">{monthData.uploadInfo.uploadedBy}</span>
                                                                </p>
                                                                {/* File Details */}
                                                                <div className="flex items-center gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                                    <span>{monthData.uploadInfo.fileSize}</span>
                                                                    <span>•</span>
                                                                    <span>{monthData.uploadInfo.rowCount.toLocaleString()} rows</span>
                                                                    <span>•</span>
                                                                    <span>{monthData.uploadInfo.dateRange}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <span className="text-sm text-gray-500 dark:text-gray-500">{monthData.uploadInfo.uploadDate}</span>
                                                    </div>

                                                    {/* Activity Log */}
                                                    {monthData.activityLogs && monthData.activityLogs.length > 0 && (
                                                        <div className="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                                            <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Activity Log</h4>
                                                            <div className="space-y-2">
                                                                {monthData.activityLogs.map((log, index) => (
                                                                    <div key={index} className="flex items-start gap-3 text-sm">
                                                                        <div className="mt-0.5">
                                                                            {log.action === 'uploaded' && (
                                                                                <div className="p-1.5 bg-blue-50 dark:bg-blue-950/30 rounded">
                                                                                    <Upload className="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                                                                                </div>
                                                                            )}
                                                                            {log.action === 'replaced' && (
                                                                                <div className="p-1.5 bg-orange-50 dark:bg-orange-950/30 rounded">
                                                                                    <Upload className="w-3.5 h-3.5 text-orange-600 dark:text-orange-400" />
                                                                                </div>
                                                                            )}
                                                                            {log.action === 'deleted' && (
                                                                                <div className="p-1.5 bg-red-50 dark:bg-red-950/30 rounded">
                                                                                    <Trash2 className="w-3.5 h-3.5 text-red-600 dark:text-red-400" />
                                                                                </div>
                                                                            )}
                                                                        </div>
                                                                        <div className="flex-1 min-w-0">
                                                                            <p className="text-gray-900 dark:text-gray-100">
                                                                                <span className="font-medium capitalize">{log.action}</span> by <span className="font-medium">{log.user}</span>
                                                                            </p>
                                                                            <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                                                {log.fileName}
                                                                            </p>
                                                                        </div>
                                                                        <div className="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                                            <Clock className="w-3 h-3" />
                                                                            <span>{log.timestamp}</span>
                                                                        </div>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            ) : (
                                                // Upload Form View
                                                <div className="space-y-4">
                                                    <div className="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                                        <Upload className="w-10 h-10 text-gray-400 dark:text-gray-500 mx-auto mb-3" />
                                                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                                            Drag & drop file here or
                                                        </p>
                                                        <input
                                                            type="file"
                                                            id={`file-${monthData.month}`}
                                                            accept=".xlsx,.xls,.csv"
                                                            onChange={(e) => handleFileSelect(monthData.month, e)}
                                                            className="hidden"
                                                        />
                                                        <label htmlFor={`file-${monthData.month}`}>
                                                            <Button size="sm" variant="outline" className="cursor-pointer" asChild>
                                                                <span>
                                                                    <FileSpreadsheet className="w-4 h-4 mr-2" />
                                                                    Browse Files
                                                                </span>
                                                            </Button>
                                                        </label>
                                                        {selectedFiles[monthData.month] && (
                                                            <div className="mt-4 space-y-3">
                                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                                    Selected: <span className="font-medium">{selectedFiles[monthData.month]?.name}</span>
                                                                </p>
                                                                <Button 
                                                                    size="sm" 
                                                                    onClick={() => handleMonthUpload(monthData.month)}
                                                                    disabled={isUploading}
                                                                    className="w-full"
                                                                >
                                                                    {isUploading && uploadingMonth === monthData.month ? (
                                                                        <>
                                                                            <span className="mr-2">Uploading...</span>
                                                                            <span>{uploadProgress}%</span>
                                                                        </>
                                                                    ) : (
                                                                        <>
                                                                            <Upload className="w-4 h-4 mr-2" />
                                                                            Upload Data
                                                                        </>
                                                                    )}
                                                                </Button>
                                                            </div>
                                                        )}
                                                    </div>
                                                    
                                                    <div className="grid gap-3 md:grid-cols-2 text-sm">
                                                        <div className="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                            <h5 className="font-medium text-blue-900 dark:text-blue-300 mb-2">Accepted Formats</h5>
                                                            <ul className="text-blue-700 dark:text-blue-400 space-y-1">
                                                                <li>• Excel (.xlsx, .xls)</li>
                                                                <li>• CSV (.csv)</li>
                                                                <li>• Max size: 10 MB</li>
                                                            </ul>
                                                        </div>
                                                        <div className="p-3 bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800 rounded-lg">
                                                            <h5 className="font-medium text-purple-900 dark:text-purple-300 mb-2">Required Columns</h5>
                                                            <ul className="text-purple-700 dark:text-purple-400 space-y-1">
                                                                <li>• NIP_NAS (Company ID)</li>
                                                                <li>• STANDARD_NAME</li>
                                                                <li>• GROUP1 to GROUP4</li>
                                                            </ul>
                                                        </div>
                                                    </div>


                                                </div>
                                            )}
                                        </div>
                                    </CollapsibleContent>
                                </div>
                            </Collapsible>
                        ))}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
