import { X, CheckCircle, AlertCircle, AlertTriangle, Info } from 'lucide-react';
import { useEffect, useState } from 'react';

interface ToastProps {
    show: boolean;
    type: 'success' | 'error' | 'warning' | 'info';
    title: string;
    message: string;
    onClose: () => void;
}

export function Toast({ show, type, title, message, onClose }: ToastProps) {
    useEffect(() => {
        if (show) {
            const timer = setTimeout(() => onClose(), 5000);
            return () => clearTimeout(timer);
        }
    }, [show, onClose]);

    if (!show) return null;

    const styles = {
        success: { 
            bg: 'bg-green-50 dark:bg-green-950/30 border-green-200 dark:border-green-800', 
            icon: <CheckCircle className="w-6 h-6 text-green-600 dark:text-green-400" />, 
            text: 'text-green-900 dark:text-green-100' 
        },
        error: { 
            bg: 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800', 
            icon: <AlertCircle className="w-6 h-6 text-red-600 dark:text-red-400" />, 
            text: 'text-red-900 dark:text-red-100' 
        },
        warning: { 
            bg: 'bg-orange-50 dark:bg-orange-950/30 border-orange-200 dark:border-orange-800', 
            icon: <AlertTriangle className="w-6 h-6 text-orange-600 dark:text-orange-400" />, 
            text: 'text-orange-900 dark:text-orange-100' 
        },
        info: { 
            bg: 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800', 
            icon: <Info className="w-6 h-6 text-blue-600 dark:text-blue-400" />, 
            text: 'text-blue-900 dark:text-blue-100' 
        }
    };

    const style = styles[type];

    return (
        <div className="fixed top-4 right-4 z-50 animate-in slide-in-from-top-5">
            <div className={`max-w-md w-full ${style.bg} border-2 rounded-lg shadow-lg p-4`}>
                <div className="flex items-start gap-3">
                    <div className="flex-shrink-0 mt-0.5">
                        {style.icon}
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className={`text-sm font-semibold ${style.text}`}>
                            {title}
                        </p>
                        <p className={`text-sm mt-1 ${style.text} opacity-90`}>
                            {message}
                        </p>
                    </div>
                    <button
                        onClick={onClose}
                        className={`flex-shrink-0 ${style.text} hover:opacity-70 transition-opacity`}
                    >
                        <X className="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    );
}

interface ConfirmDialogProps {
    show: boolean;
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    type?: 'danger' | 'warning' | 'info';
    onConfirm: () => void;
    onCancel: () => void;
    requireTyping?: boolean;
    typingConfirmation?: string;
}

export function ConfirmDialog({
    show,
    title,
    message,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    type = 'warning',
    onConfirm,
    onCancel,
    requireTyping = false,
    typingConfirmation = 'HAPUS'
}: ConfirmDialogProps) {
    const [typedText, setTypedText] = useState('');

    if (!show) return null;

    const handleConfirm = () => {
        if (requireTyping && typedText !== typingConfirmation) {
            return;
        }
        onConfirm();
        setTypedText('');
    };

    const buttonColors = {
        danger: 'bg-red-600 hover:bg-red-700 text-white',
        warning: 'bg-orange-600 hover:bg-orange-700 text-white',
        info: 'bg-blue-600 hover:bg-blue-700 text-white'
    };

    const iconColors = {
        danger: { bg: 'bg-red-100 dark:bg-red-950/30', icon: <AlertCircle className="w-6 h-6 text-red-600 dark:text-red-400" /> },
        warning: { bg: 'bg-orange-100 dark:bg-orange-950/30', icon: <AlertTriangle className="w-6 h-6 text-orange-600 dark:text-orange-400" /> },
        info: { bg: 'bg-blue-100 dark:bg-blue-950/30', icon: <Info className="w-6 h-6 text-blue-600 dark:text-blue-400" /> }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm animate-in fade-in">
            <div className="bg-white dark:bg-gray-900 rounded-lg shadow-2xl max-w-md w-full mx-4 border border-gray-200 dark:border-gray-700 animate-in zoom-in-95">
                <div className="p-6">
                    <div className="flex items-start gap-4">
                        <div className="flex-shrink-0">
                            <div className={`w-12 h-12 rounded-full ${iconColors[type].bg} flex items-center justify-center`}>
                                {iconColors[type].icon}
                            </div>
                        </div>
                        <div className="flex-1">
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {title}
                            </h3>
                            <p className="mt-2 text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                {message}
                            </p>
                            
                            {requireTyping && (
                                <div className="mt-4">
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Ketik "{typingConfirmation}" untuk konfirmasi:
                                    </label>
                                    <input
                                        type="text"
                                        value={typedText}
                                        onChange={(e) => setTypedText(e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder={typingConfirmation}
                                        autoFocus
                                    />
                                </div>
                            )}
                        </div>
                    </div>
                </div>
                
                <div className="bg-gray-50 dark:bg-gray-800 px-6 py-4 flex gap-3 justify-end rounded-b-lg border-t border-gray-200 dark:border-gray-700">
                    <button
                        onClick={() => {
                            setTypedText('');
                            onCancel();
                        }}
                        className="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                    >
                        {cancelText}
                    </button>
                    <button
                        onClick={handleConfirm}
                        disabled={requireTyping && typedText !== typingConfirmation}
                        className={`px-4 py-2 text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${buttonColors[type]}`}
                    >
                        {confirmText}
                    </button>
                </div>
            </div>
        </div>
    );
}
