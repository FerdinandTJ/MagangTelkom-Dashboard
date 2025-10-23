import { HTMLAttributes } from 'react';

export default function AppLogoIcon(props: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={`inline-flex items-center justify-center p-1 bg-white rounded shadow-sm border border-gray-200 ${props.className || ''}`}>
            <img 
                src="/telkom-logo.svg" 
                alt="Telkom Logo" 
                className="w-full h-full object-contain"
            />
        </div>
    );
}
