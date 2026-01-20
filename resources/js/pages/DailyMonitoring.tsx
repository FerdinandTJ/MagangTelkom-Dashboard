import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Upload, RefreshCw } from 'lucide-react';
import { dailyMonitoring } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Daily Monitoring',
        href: dailyMonitoring().url,
    },
];

// Dummy data for metrics - individual cards
const metricsData = [
    { label: 'TARGET REVENUE', value: '27,47' },
    { label: 'SUSTAIN', value: '30,33' },
    { label: 'KEBUTUHAN SCALING', value: '-2,85' },
    { label: 'PROGRESS SCALING', value: '3,00' },
    { label: 'SODOMORO', value: '0,00' },
    { label: 'ADJUSTMENT', value: '0,00' },
    { label: 'PROGRESS REVENUE CM', value: '33,33' },
    { label: 'ACH REVENUE CM', value: '121%' },
];

// Dummy data for table
const tableData = [
    { idlop: 'S25-200003', am: 'Berlin', treg: '2 HQ', namaCC: 'RRI', project: 'Mangostar Termin 1 dan 2', scaling: '3.000.000.000', progress: 'Closed' },
    { idlop: '', am: 'Rizal', treg: '3', namaCC: 'APIDT INFRASTRUCTURE', project: 'DWDM Termin Yearly 2025', scaling: '2.925.000.000', progress: 'OGP BA' },
    { idlop: '', am: 'Syafwan', treg: '2', namaCC: 'TELKOM UNIVERSITY', project: 'BW Termin 1/12 2025', scaling: '462.532.000', progress: 'OGP BA' },
    { idlop: '', am: 'Syafwan', treg: '2', namaCC: 'TELKOM UNIVERSITY', project: 'BW Termin 2/12 2025', scaling: '462.532.000', progress: 'OGP BA' },
    { idlop: '', am: 'Santo', treg: '3', namaCC: 'KAWASAN WISATA MANDALIKA', project: 'Manage Service MGPA Event MotoGP', scaling: '2.250.000.000', progress: 'Proses KB' },
    { idlop: '', am: 'Santo', treg: '3', namaCC: 'KAWASAN WISATA MANDALIKA', project: 'Connection & Wifi MotoGP 2025', scaling: '1.000.000.000', progress: 'Proses approval Billing Account' },
    { idlop: 'S24-200260', am: 'Nurul', treg: '2 HQ', namaCC: 'GARUDA INDONESIA', project: 'Contact Center Rekon Des 2025', scaling: '1.200.000.000', progress: 'BA Rekon' },
    { idlop: '', am: 'Merisa', treg: '2 HQ', namaCC: 'Telkomsel', project: 'Assessment Telkomsel (Batch 3 dan...', scaling: '776.100.000', progress: 'BA Rekon' },
    { idlop: 'S25-200029', am: 'Taufan', treg: '2 HQ', namaCC: 'AIRNAV, API', project: 'Pekerjaan Sewa Infrastruktur', scaling: '921.901.600', progress: 'OGP BAST' },
    { idlop: 'S25-200029', am: 'Taufan', treg: '2 HQ', namaCC: 'AIRNAV, API', project: 'Pelatihan', scaling: '522.300.000', progress: 'OGP BAST' },
    { idlop: '', am: 'Nurul', treg: '2 HQ', namaCC: 'GARUDA INDONESIA', project: 'Contact Center Rekon Jan 2026', scaling: '1.200.000.000', progress: 'OGP BA' },
    { idlop: '', am: 'Nurul', treg: '2 HQ', namaCC: 'GARUDA INDONESIA', project: 'Contact Center Rekon Feb 2026', scaling: '1.200.000.000', progress: 'OGP BA' },
    { idlop: '', am: 'Syafwan', treg: '2', namaCC: 'TELKOM UNIVERSITY', project: 'BW Termin 2/12 2025', scaling: '462.532.000', progress: 'OGP BA' },
    { idlop: '', am: 'Nurul', treg: '2 HQ', namaCC: 'GMF', project: 'Wireless Network Upgrade (Wifi-6)', scaling: '166.388.889', progress: 'OGP KB' },
    { idlop: '', am: 'Tito', treg: '2 HQ', namaCC: 'TELKOM LANDMARK TOWER', project: 'Project IKG TLT Gedung baru FSTSO 9 Lan', scaling: '2.300.000.000', progress: 'OGP KB' },
    { idlop: '', am: 'Havea', treg: '3', namaCC: 'UNIVERSITAS NEGERI SURABAYA', project: 'BW Termin 1/4 2025', scaling: '529.189.189', progress: 'Proses KB' },
    { idlop: 'S25-200023', am: 'Fariz', treg: '3', namaCC: 'INSTITUT TEKNOLOGI BANDUNG', project: 'Pengganan Bandwidth Internet Termin 2', scaling: '600.000.000', progress: 'OGP BAST' },
];


export default function DailyMonitoring() {
    const { auth } = usePage().props;
    const isAdmin = auth.user.role === 'admin';


    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Daily Monitoring" />

            <div className="min-h-screen bg-gradient-to-br from-red-50/70 via-white to-pink-50/70 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900 p-4 sm:p-6 lg:p-6">
                {/* Admin Buttons - Positioned at top right */}
                {isAdmin && (
                    <div className="flex justify-end gap-3 mb-6">
                        <Button
                            variant="default"
                            className="gap-2"
                            onClick={() => {
                                // TODO: Implement update harian functionality
                                console.log('Update Harian clicked');
                            }}
                        >
                            <RefreshCw className="h-4 w-4" />
                            Update Harian
                        </Button>
                        <Button
                            variant="default"
                            className="gap-2"
                            onClick={() => {
                                // TODO: Implement upload bulanan functionality
                                console.log('Upload Data Bulanan clicked');
                            }}
                        >
                            <Upload className="h-4 w-4" />
                            Upload Data Bulanan
                        </Button>
                    </div>
                )}

                {/* Metrics Cards Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        {metricsData.map((metric, idx) => (
                            <Card
                                key={idx}
                                className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200"
                            >
                                <CardContent className="p-6">
                                    <div className="flex flex-col space-y-2">
                                        <span className="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">
                                            {metric.label}
                                        </span>
                                        <span className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                            {metric.value}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                {/* Data Table */}
                <Card className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Data Monitoring
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-800 border-b-2 border-gray-300 dark:border-gray-700">
                                            <TableHead className="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-700">
                                                IDLOP
                                            </TableHead>
                                            <TableHead className="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-700">
                                                AM
                                            </TableHead>
                                            <TableHead className="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-700">
                                                TREG
                                            </TableHead>
                                            <TableHead className="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-700">
                                                NAMA CC
                                            </TableHead>
                                            <TableHead className="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-700">
                                                Project
                                            </TableHead>
                                            <TableHead className="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs border-r border-gray-300 dark:border-gray-700 text-right">
                                                SCALING
                                            </TableHead>
                                            <TableHead className="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-xs">
                                                Progress
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {tableData.map((row, idx) => (
                                            <TableRow
                                                key={idx}
                                                className="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors duration-150"
                                            >
                                                <TableCell className="text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-800">
                                                    {row.idlop}
                                                </TableCell>
                                                <TableCell className="text-sm text-gray-700 dark:text-gray-300 font-medium border-r border-gray-200 dark:border-gray-800">
                                                    {row.am}
                                                </TableCell>
                                                <TableCell className="text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-800">
                                                    {row.treg}
                                                </TableCell>
                                                <TableCell className="text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-800">
                                                    {row.namaCC}
                                                </TableCell>
                                                <TableCell className="text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-800">
                                                    {row.project}
                                                </TableCell>
                                                <TableCell className="text-sm text-gray-900 dark:text-gray-100 font-medium border-r border-gray-200 dark:border-gray-800 text-right">
                                                    {row.scaling}
                                                </TableCell>
                                                <TableCell className="text-sm text-gray-700 dark:text-gray-300">
                                                    {row.progress}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
            </div>
        </AppSidebarLayout>
    );
}
