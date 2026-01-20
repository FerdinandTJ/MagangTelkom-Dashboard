import React, { useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { AlertTriangle, X } from 'lucide-react';

interface ConfirmModalProps {
    isOpen: boolean;
    onClose: () => void;
    onConfirm: () => void;
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    type?: 'danger' | 'warning' | 'info';
    requiresTyping?: boolean;
    typingConfirmation?: string;
    details?: string[];
    isLoading?: boolean;
}

const ConfirmModal: React.FC<ConfirmModalProps> = ({
    isOpen,
    onClose,
    onConfirm,
    title,
    message,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    type = 'warning',
    requiresTyping = false,
    typingConfirmation = 'HAPUS',
    details,
    isLoading = false
}) => {
    const [typedValue, setTypedValue] = useState('');
    const [error, setError] = useState('');

    const handleConfirm = () => {
        if (requiresTyping && typedValue !== typingConfirmation) {
            setError(`Ketik "${typingConfirmation}" untuk konfirmasi`);
            return;
        }
        setError('');
        setTypedValue('');
        onConfirm();
    };

    const handleClose = () => {
        setTypedValue('');
        setError('');
        onClose();
    };

    const getColors = () => {
        switch (type) {
            case 'danger':
                return {
                    iconColor: 'text-red-600 dark:text-red-400',
                    bgColor: 'bg-red-50 dark:bg-red-950/30',
                    borderColor: 'border-red-200 dark:border-red-800',
                    titleColor: 'text-red-900 dark:text-red-100',
                    buttonColor: 'bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700'
                };
            case 'warning':
                return {
                    iconColor: 'text-orange-600 dark:text-orange-400',
                    bgColor: 'bg-orange-50 dark:bg-orange-950/30',
                    borderColor: 'border-orange-200 dark:border-orange-800',
                    titleColor: 'text-orange-900 dark:text-orange-100',
                    buttonColor: 'bg-orange-600 hover:bg-orange-700 dark:bg-orange-600 dark:hover:bg-orange-700'
                };
            case 'info':
                return {
                    iconColor: 'text-blue-600 dark:text-blue-400',
                    bgColor: 'bg-blue-50 dark:bg-blue-950/30',
                    borderColor: 'border-blue-200 dark:border-blue-800',
                    titleColor: 'text-blue-900 dark:text-blue-100',
                    buttonColor: 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700'
                };
        }
    };

    const { iconColor, bgColor, borderColor, titleColor, buttonColor } = getColors();

    return (
        <Dialog open={isOpen} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[500px] p-0">
                {/* Header */}
                <div className={`${bgColor} ${borderColor} border-b px-6 py-4`}>
                    <div className="flex items-start gap-4">
                        <div className="flex-shrink-0 mt-1">
                            <AlertTriangle className={`w-12 h-12 ${iconColor}`} />
                        </div>
                        <div className="flex-1 min-w-0">
                            <DialogTitle className={`text-xl font-bold ${titleColor} mb-2`}>
                                {title}
                            </DialogTitle>
                            <DialogDescription className="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                {message}
                            </DialogDescription>
                        </div>
                    </div>
                </div>

                {/* Content */}
                <div className="px-6 py-4 space-y-4">
                    {/* Details */}
                    {details && details.length > 0 && (
                        <div className="space-y-2">
                            {details.map((detail, index) => (
                                <div key={index} className="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span className="text-gray-400 dark:text-gray-500 mt-0.5">•</span>
                                    <span className="flex-1">{detail}</span>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Typing confirmation */}
                    {requiresTyping && (
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ketik <span className="font-bold text-red-600 dark:text-red-400">"{typingConfirmation}"</span> untuk konfirmasi:
                            </label>
                            <Input
                                value={typedValue}
                                onChange={(e) => {
                                    setTypedValue(e.target.value);
                                    setError('');
                                }}
                                placeholder={typingConfirmation}
                                className={error ? 'border-red-500 dark:border-red-500' : ''}
                                disabled={isLoading}
                            />
                            {error && (
                                <p className="text-sm text-red-600 dark:text-red-400">{error}</p>
                            )}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <DialogFooter className="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 gap-2">
                    <Button 
                        variant="outline" 
                        onClick={handleClose}
                        disabled={isLoading}
                        className="flex-1"
                    >
                        {cancelText}
                    </Button>
                    <Button 
                        onClick={handleConfirm}
                        disabled={isLoading || (requiresTyping && typedValue !== typingConfirmation)}
                        className={`flex-1 ${buttonColor} text-white`}
                    >
                        {isLoading ? 'Processing...' : confirmText}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
};

export default ConfirmModal;
