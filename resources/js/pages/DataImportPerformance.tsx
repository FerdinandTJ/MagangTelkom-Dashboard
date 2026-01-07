import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Upload, FileSpreadsheet } from 'lucide-react';

export default function DataImportPerformance() {
    const handleImport = () => {
        alert('Import Performance AM feature coming soon');
    };

    return (
        <AppLayout>
            <Head title="Data Import - Performance AM" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6 bg-gray-50/30 dark:bg-gray-950/30">
                
                {/* Page Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Data Import - Performance AM</h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Upload and import Account Manager performance data
                        </p>
                    </div>
                </div>

                {/* Import Card */}
                <div className="max-w-2xl">
                    <Card className="bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800">
                        <CardHeader>
                            <div className="flex items-center gap-3">
                                <div className="p-2 bg-red-50 dark:bg-red-950 rounded-lg">
                                    <FileSpreadsheet className="h-6 w-6 text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <CardTitle>Import Performance AM Data</CardTitle>
                                    <CardDescription>Upload Excel file to import Account Manager performance data</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-8 text-center">
                                    <Upload className="h-12 w-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        Click the button below to select a file
                                    </p>
                                    <Button 
                                        onClick={handleImport}
                                        className="bg-red-600 hover:bg-red-700 text-white"
                                    >
                                        <Upload className="h-4 w-4 mr-2" />
                                        Import Performance Data
                                    </Button>
                                </div>
                                
                                <div className="text-xs text-gray-500 dark:text-gray-400">
                                    <p className="font-medium mb-1">Supported file formats:</p>
                                    <ul className="list-disc list-inside space-y-1">
                                        <li>Excel (.xlsx, .xls)</li>
                                        <li>CSV (.csv)</li>
                                    </ul>
                                    <p className="font-medium mt-3 mb-1">Data structure:</p>
                                    <ul className="list-disc list-inside space-y-1">
                                        <li>Account Manager information</li>
                                        <li>Target and achievement data</li>
                                        <li>Regional breakdown</li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
