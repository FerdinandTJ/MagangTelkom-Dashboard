import React from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertCircle, CheckCircle2, Info, AlertTriangle, X } from 'lucide-react';

export type AlertType = 'success' | 'error' | 'warning' | 'info';

interface AlertModalProps {
    isOpen: boolean;
    onClose: () => void;
    type: AlertType;
    title: string;
    message: string;
    details?: string[];
}

const AlertModal: React.FC<AlertModalProps> = ({
    isOpen,
    onClose,
    type,
    title,
    message,
    details
}) => {
    const getIconAndColors = () => {
        switch (type) {
            case 'success':
                return {
                    icon: <CheckCircle2 className="w-12 h-12 text-green-600 dark:text-green-400" />,
                    titleColor: 'text-green-900 dark:text-green-100',
                    bgColor: 'bg-green-50 dark:bg-green-950/30',
                    borderColor: 'border-green-200 dark:border-green-800',
                    buttonColor: 'bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700'
                };
            case 'error':
                return {
                    icon: <AlertCircle className="w-12 h-12 text-red-600 dark:text-red-400" />,
                    titleColor: 'text-red-900 dark:text-red-100',
                    bgColor: 'bg-red-50 dark:bg-red-950/30',
                    borderColor: 'border-red-200 dark:border-red-800',
                    buttonColor: 'bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700'
                };
            case 'warning':
                return {
                    icon: <AlertTriangle className="w-12 h-12 text-orange-600 dark:text-orange-400" />,
                    titleColor: 'text-orange-900 dark:text-orange-100',
                    bgColor: 'bg-orange-50 dark:bg-orange-950/30',
                    borderColor: 'border-orange-200 dark:border-orange-800',
                    buttonColor: 'bg-orange-600 hover:bg-orange-700 dark:bg-orange-600 dark:hover:bg-orange-700'
                };
            case 'info':
                return {
                    icon: <Info className="w-12 h-12 text-blue-600 dark:text-blue-400" />,
                    titleColor: 'text-blue-900 dark:text-blue-100',
                    bgColor: 'bg-blue-50 dark:bg-blue-950/30',
                    borderColor: 'border-blue-200 dark:border-blue-800',
                    buttonColor: 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700'
                };
        }
    };

    const { icon, titleColor, bgColor, borderColor, buttonColor } = getIconAndColors();

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[500px] p-0">
                {/* Header with colored background */}
                <div className={`${bgColor} ${borderColor} border-b px-6 py-4`}>
                    <div className="flex items-start gap-4">
                        <div className="flex-shrink-0 mt-1">
                            {icon}
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

                {/* Details section if provided */}
                {details && details.length > 0 && (
                    <div className="px-6 py-4 max-h-[300px] overflow-y-auto">
                        <ul className="space-y-2">
                            {details.map((detail, index) => (
                                <li key={index} className="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span className="text-gray-400 dark:text-gray-500 mt-0.5">•</span>
                                    <span className="flex-1">{detail}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                {/* Footer */}
                <DialogFooter className="px-6 py-4 bg-gray-50 dark:bg-gray-900/50">
                    <Button 
                        onClick={onClose}
                        className={`w-full ${buttonColor} text-white`}
                    >
                        OK
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
};

export default AlertModal;
