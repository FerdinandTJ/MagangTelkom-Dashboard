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
import axios from 'axios';

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
            
            const response = await axios.get(`/api/dashboard/subsegment-details`, {
                params
            });
            
            if (response.data.success) {
                setCompanies(response.data.data.companies);
                setSummary(response.data.data.summary);
            } else {
                setError('Failed to fetch company details');
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
            'Active': 'bg-green-100 text-green-800 border-green-200',
            'Inactive': 'bg-red-100 text-red-800 border-red-200',
            'Suspended': 'bg-yellow-100 text-yellow-800 border-yellow-200'
        };
        
        return (
            <span className={`px-2 py-1 text-xs font-medium rounded-full border ${statusStyles[status as keyof typeof statusStyles] || 'bg-gray-100 text-gray-800 border-gray-200'}`}>
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
            <DialogContent className="wide-modal max-w-[98vw] w-[98vw] max-h-[95vh] overflow-y-auto p-6">
                <DialogHeader className="pb-4">
                    <DialogTitle className="flex items-center gap-2 text-lg sm:text-xl">
                        <Building2 className="h-5 w-5 sm:h-6 sm:w-6 text-red-600" />
                        <span className="truncate">{subsegment} - Company Details</span>
                    </DialogTitle>
                    <DialogDescription className="text-sm text-gray-600">
                        Daftar perusahaan dan performance revenue untuk subsegment {subsegment} periode {periodText}
                    </DialogDescription>
                </DialogHeader>

                {/* Summary Cards */}
                {summary ? (
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div className="bg-gradient-to-r from-red-50 to-pink-50 p-4 rounded-lg border border-red-100">
                            <div className="flex items-center gap-2 mb-2">
                                <TrendingUp className="h-5 w-5 text-red-600" />
                                <span className="text-sm font-medium text-red-700">Total Revenue</span>
                            </div>
                            <p className="text-xl font-bold text-red-900">{summary.formatted_total_revenue || 'Rp 0.00M'}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-100">
                            <div className="flex items-center gap-2 mb-2">
                                <Users className="h-5 w-5 text-blue-600" />
                                <span className="text-sm font-medium text-blue-700">Companies</span>
                            </div>
                            <p className="text-xl font-bold text-blue-900">{summary.total_companies || 0}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg border border-green-100">
                            <div className="flex items-center gap-2 mb-2">
                                <TrendingUp className="h-5 w-5 text-green-600" />
                                <span className="text-sm font-medium text-green-700">Avg Revenue</span>
                            </div>
                            <p className="text-xl font-bold text-green-900">{summary.formatted_avg_revenue || 'Rp 0.00M'}</p>
                        </div>
                        
                        <div className="bg-gradient-to-r from-purple-50 to-violet-50 p-4 rounded-lg border border-purple-100">
                            <div className="flex items-center gap-2 mb-2">
                                <Calendar className="h-5 w-5 text-purple-600" />
                                <span className="text-sm font-medium text-purple-700">Period</span>
                            </div>
                            <p className="text-xl font-bold text-purple-900">{periodText}</p>
                        </div>
                    </div>
                ) : (
                    <div className="mb-6 p-4 bg-gray-50 rounded-lg">
                        <p className="text-gray-600">Loading summary data...</p>
                    </div>
                )}

                {loading && (
                    <div className="flex items-center justify-center py-8">
                        <Loader2 className="h-8 w-8 animate-spin text-red-600" />
                        <span className="ml-2 text-gray-600">Loading company details...</span>
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <p className="text-red-700">{error}</p>
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
                    <div className="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <div className="overflow-hidden rounded-lg">
                            <div className="max-h-[450px] overflow-y-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Company
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                NIP-NAS
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Revenue
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {companies.map((company) => (
                                            <tr 
                                                key={company.id}
                                                className="hover:bg-gray-50 transition-colors"
                                            >
                                                <td className="px-4 py-3">
                                                    <div>
                                                        <p className="font-medium text-gray-900 text-sm leading-tight">{company.nama_perusahaan}</p>
                                                        <p className="text-xs text-gray-500">{company.subsegment}</p>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="text-sm font-mono text-gray-600">
                                                        {company.nip_nas || 'N/A'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div>
                                                        <p className="font-semibold text-gray-900 text-sm">
                                                            {company.formatted_total_revenue}
                                                        </p>
                                                        <p className="text-xs text-gray-500">
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
                                                        className="text-red-600 border-red-200 hover:bg-red-50 text-xs px-3 py-1"
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
                        <p className="text-gray-500">No companies found for {subsegment} in {periodText}</p>
                    </div>
                )}

                <div className="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200">
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