import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { X } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface UpdateHarianModalProps {
    isOpen: boolean;
    onClose: () => void;
    currentDate: number;
    currentMonth: number;
    currentYear: number;
}

export default function UpdateHarianModal({
    isOpen,
    onClose,
    currentDate,
    currentMonth,
    currentYear,
}: UpdateHarianModalProps) {
    const [sodomoro, setSodomoro] = useState<string>('');
    const [sodomoroSign, setSodomoroSign] = useState<'positive' | 'negative'>('positive');
    const [adjustment, setAdjustment] = useState<string>('');
    const [adjustmentSign, setAdjustmentSign] = useState<'positive' | 'negative'>('positive');
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Format number to Indonesian Rupiah format (1.000.000)
    const formatRupiah = (value: string): string => {
        // Remove all non-digit characters
        const numbers = value.replace(/\D/g, '');
        
        if (!numbers) return '';
        
        // Add thousand separators
        return numbers.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    // Parse formatted rupiah to number
    const parseRupiah = (value: string): number => {
        const numbers = value.replace(/\./g, '');
        return parseFloat(numbers) || 0;
    };

    // Handle input change with auto-format
    const handleSodomoroChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const formatted = formatRupiah(e.target.value);
        setSodomoro(formatted);
    };

    const handleAdjustmentChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const formatted = formatRupiah(e.target.value);
        setAdjustment(formatted);
    };

    // Handle form submit
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        const sodomoroValue = parseRupiah(sodomoro) * (sodomoroSign === 'negative' ? -1 : 1);
        const adjustmentValue = parseRupiah(adjustment) * (adjustmentSign === 'negative' ? -1 : 1);

        const data = {
            date: currentDate,
            month: currentMonth,
            year: currentYear,
            sodomoro: sodomoroValue,
            adjustment: adjustmentValue,
        };

        router.post('/daily-monitoring/update-harian', data, {
            onSuccess: () => {
                alert('Data harian berhasil diupdate!');
                onClose();
                // Reset form
                setSodomoro('');
                setAdjustment('');
                setSodomoroSign('positive');
                setAdjustmentSign('positive');
            },
            onError: (errors) => {
                console.error('Update error:', errors);
                alert('Gagal update data: ' + JSON.stringify(errors));
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    // Reset form when modal closes
    useEffect(() => {
        if (!isOpen) {
            setSodomoro('');
            setAdjustment('');
            setSodomoroSign('positive');
            setAdjustmentSign('positive');
        }
    }, [isOpen]);

    if (!isOpen) return null;

    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div className="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <h2 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                            Update Data Harian
                        </h2>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {currentDate} {monthNames[currentMonth - 1]} {currentYear}
                        </p>
                    </div>
                    <button
                        onClick={onClose}
                        className="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                        disabled={isSubmitting}
                    >
                        <X className="h-5 w-5 text-gray-500" />
                    </button>
                </div>

                {/* Form Content */}
                <form onSubmit={handleSubmit} className="p-6 space-y-6">
                    {/* Sodomoro Input */}
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Sodomoro
                        </label>
                        
                        {/* Positive/Negative Toggle */}
                        <div className="flex gap-2 mb-3">
                            <button
                                type="button"
                                onClick={() => setSodomoroSign('positive')}
                                className={`flex-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                                    sodomoroSign === 'positive'
                                        ? 'bg-green-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                                disabled={isSubmitting}
                            >
                                + Positif
                            </button>
                            <button
                                type="button"
                                onClick={() => setSodomoroSign('negative')}
                                className={`flex-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                                    sodomoroSign === 'negative'
                                        ? 'bg-red-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                                disabled={isSubmitting}
                            >
                                - Negatif
                            </button>
                        </div>
                        
                        <div className="relative">
                            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">
                                Rp
                            </span>
                            <input
                                type="text"
                                value={sodomoro}
                                onChange={handleSodomoroChange}
                                placeholder="0"
                                className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 text-right font-mono"
                                disabled={isSubmitting}
                            />
                        </div>
                        <p className="text-xs text-gray-500 mt-1">
                            {sodomoro ? `${sodomoroSign === 'negative' ? '-' : '+'}Rp ${sodomoro}` : 'Masukkan nilai sodomoro'}
                        </p>
                    </div>

                    {/* Adjustment Input */}
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Adjustment
                        </label>
                        
                        {/* Positive/Negative Toggle */}
                        <div className="flex gap-2 mb-3">
                            <button
                                type="button"
                                onClick={() => setAdjustmentSign('positive')}
                                className={`flex-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                                    adjustmentSign === 'positive'
                                        ? 'bg-green-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                                disabled={isSubmitting}
                            >
                                + Positif
                            </button>
                            <button
                                type="button"
                                onClick={() => setAdjustmentSign('negative')}
                                className={`flex-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                                    adjustmentSign === 'negative'
                                        ? 'bg-red-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                                disabled={isSubmitting}
                            >
                                - Negatif
                            </button>
                        </div>
                        
                        <div className="relative">
                            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">
                                Rp
                            </span>
                            <input
                                type="text"
                                value={adjustment}
                                onChange={handleAdjustmentChange}
                                placeholder="0"
                                className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 text-right font-mono"
                                disabled={isSubmitting}
                            />
                        </div>
                        <p className="text-xs text-gray-500 mt-1">
                            {adjustment ? `${adjustmentSign === 'negative' ? '-' : '+'}Rp ${adjustment}` : 'Masukkan nilai adjustment'}
                        </p>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex gap-3 pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            className="flex-1"
                            onClick={onClose}
                            disabled={isSubmitting}
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            className="flex-1 bg-red-600 hover:bg-red-700"
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? 'Menyimpan...' : 'Simpan'}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}
