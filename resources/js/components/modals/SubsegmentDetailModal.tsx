import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2, Building2, TrendingUp, Users, Calendar } from 'lucide-react';
import { CompanyData } from '@/types/dashboard';
import axios from '@/lib/axios';

interface SubsegmentDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    subsegment: string | null;
    year?: number;
    month?: number;
    onCompanyClick?: (company: CompanyData) => void;
}

const SubsegmentDetailModal: React.FC<SubsegmentDetailModalProps> = ({
    isOpen,
    onClose,
    subsegment,
    year = new Date().getFullYear(),
    month,
    onCompanyClick
}) => {
    const [companies, setCompanies] = useState<CompanyData[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [summary, setSummary] = useState<any>(null);

    useEffect(() => {
        if (isOpen && subsegment) {
            fetchCompanyDetails();
        }
    }, [isOpen, subsegment, year, month]);

    const fetchCompanyDetails = async () => {
        if (!subsegment) return;
        
        console.log('Fetching subsegment details for:', { subsegment, year, month });
        
        setLoading(true);
        setError(null);
        
        try {
            const params: any = {
                subsegment: subsegment,
                year: year
            };
            
            if (month) {
                params.month = month;
            }
            
            console.log('API request params:', params);
            
            const response = await axios.get(`/api/dashboard/subsegment-details`, {
                params
            });
            
            console.log('API response:', response.data);
            
            if (response.data.success) {
                setCompanies(response.data.data.companies);
                setSummary(response.data.data.summary);
                console.log('Companies loaded:', response.data.data.companies.length);
            } else {
                setError('Failed to fetch company details');
                console.error('API response not successful:', response.data);
            }
        } catch (err) {
            setError('Error loading company data');
            console.error('Error fetching company details:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleCompanyClick = (company: CompanyData) => {
        if (onCompanyClick) {
            onCompanyClick(company);
        }
    };

    const getStatusBadge = (status: string) => {
        const statusStyles = {
            'Active': 'bg-green-100 dark:bg-green-950 text-green-800 dark:text-green-300 border-green-200 dark:border-green-900',
            'Inactive': 'bg-red-100 dark:bg-red-950 text-red-800 dark:text-red-300 border-red-200 dark:border-red-900',
            'Suspended': 'bg-yellow-100 dark:bg-yellow-950 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-900'
        };
        
        return (
            <span className={`px-2 py-1 text-xs font-medium rounded-full border ${statusStyles[status as keyof typeof statusStyles] || 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700'}`}>
                {status}
            </span>
        );
    };

    if (!subsegment) return null;

    const periodText = month 
        ? `${new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(new Date(year, month - 1))} ${year}`
        : `${year}`;

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6 bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl text-gray-900 dark:text-white">
                        <Building2 className="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" />
                        <span className="truncate">{subsegment} - Company Details</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600 dark:text-gray-400">
                        Daftar perusahaan dan performance revenue untuk subsegment {subsegment} periode {periodText}
                    </DialogDescription>
                </DialogHeader>

                {/* Summary Cards */}
                {summary ? (
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div className="bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-950/30 dark:to-pink-950/30 p-4 rounded-lg border border-red-100 dark:border-red-900">
                            <div className="flex items-center gap-2 mb-2">
                                <TrendingUp className="h-5 w-5 text-red-600 dark:text-red-400" />
                                <span className="text-sm font-medium text-red-700 dark:text-red-300">Total Revenue</span>
                            </div>
                            <p className="text-xl font-bold text-red-900 dark:text-red-100">{summary.formatted_total_revenue || 'Rp 0.00M'}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 p-4 rounded-lg border border-blue-100 dark:border-blue-900">
                            <div className="flex items-center gap-2 mb-2">
                                <Users className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <span className="text-sm font-medium text-blue-700 dark:text-blue-300">Companies</span>
                            </div>
                            <p className="text-xl font-bold text-blue-900 dark:text-blue-100">{summary.total_companies || 0}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 p-4 rounded-lg border border-green-100 dark:border-green-900">
                            <div className="flex items-center gap-2 mb-2">
                                <TrendingUp className="h-5 w-5 text-green-600 dark:text-green-400" />
                                <span className="text-sm font-medium text-green-700 dark:text-green-300">Avg Revenue</span>
                            </div>
                            <p className="text-xl font-bold text-green-900 dark:text-green-100">{summary.formatted_avg_revenue || 'Rp 0.00M'}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-950/30 dark:to-violet-950/30 p-4 rounded-lg border border-purple-100 dark:border-purple-900">
                            <div className="flex items-center gap-2 mb-2">
                                <Calendar className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                <span className="text-sm font-medium text-purple-700 dark:text-purple-300">Period</span>
                            </div>
                            <p className="text-xl font-bold text-purple-900 dark:text-purple-100">{periodText}</p>
                        </div>
                    </div>
                ) : (
                    <div className="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <p className="text-gray-600 dark:text-gray-400">Loading summary data...</p>
                    </div>
                )}

                {loading && (
                    <div className="flex items-center justify-center py-8">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600 dark:text-red-400" />
                        <span className="ml-2 text-gray-600 dark:text-gray-400">Loading company details...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4 mb-6">
                        <p className="text-red-700 dark:text-red-300">{error}</p>
                        <Button 
                            onClick={fetchCompanyDetails}
                            variant="outline" 
                            size="sm" 
                            className="mt-2"
                        >
                            Retry
                        </Button>
                    </div>
                )}

                {!loading && !error && companies.length > 0 && (
                    <div className="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div className="overflow-hidden rounded-lg">
                            <div className="max-h-[450px] overflow-y-auto">
                                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead className="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Company
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                NIP-NAS
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Revenue
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white dark:bg-gray-950 divide-y divide-gray-200 dark:divide-gray-800">
                                        {companies.map((company) => (
                                            <tr 
                                                key={company.id}
                                                className="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors"
                                            >
                                                <td className="px-4 py-3">
                                                    <div>
                                                        <p className="font-medium text-gray-900 dark:text-white text-sm leading-tight">{company.nama_perusahaan}</p>
                                                        <p className="text-xs text-gray-500 dark:text-gray-400">{company.subsegment}</p>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="text-sm font-mono text-gray-600 dark:text-gray-400">
                                                        {company.nip_nas || 'N/A'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div>
                                                        <p className="font-semibold text-gray-900 dark:text-white text-sm">
                                                            {company.formatted_total_revenue}
                                                        </p>
                                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                                            {periodText}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {getStatusBadge(company.status)}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleCompanyClick(company)}
                                                        className="text-red-600 dark:text-red-400 border-red-200 dark:border-red-900 hover:bg-red-50 dark:hover:bg-red-950/30 text-xs px-3 py-1"
                                                    >
                                                        View Details
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}

                {!loading && !error && companies.length === 0 && (
                    <div className="text-center py-8">
                        <Building2 className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                        <p className="text-gray-500 dark:text-gray-400">No companies found for {subsegment} in {periodText}</p>
                    </div>
                )}

                <div className="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200 dark:border-gray-800">
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

export default SubsegmentDetailModal;