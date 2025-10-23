import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import { Link } from '@inertiajs/react';
import { type PropsWithChildren, useEffect, useState } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    return (
        <div className="flex min-h-svh bg-gradient-to-br from-gray-50 via-white to-gray-50 relative overflow-hidden">
            {/* Animated background particles */}
            <div className="absolute inset-0 pointer-events-none">
                <div className={`absolute top-10 left-10 w-4 h-4 bg-red-400/30 rounded-full transition-all duration-1000 ${mounted ? 'animate-bounce delay-100' : 'opacity-0'}`}></div>
                <div className={`absolute top-32 right-20 w-3 h-3 bg-blue-400/30 rounded-full transition-all duration-1000 ${mounted ? 'animate-bounce delay-300' : 'opacity-0'}`}></div>
                <div className={`absolute bottom-20 left-20 w-2 h-2 bg-green-400/30 rounded-full transition-all duration-1000 ${mounted ? 'animate-bounce delay-500' : 'opacity-0'}`}></div>
                <div className={`absolute top-1/2 right-10 w-5 h-5 bg-yellow-400/20 rounded-full transition-all duration-1000 ${mounted ? 'animate-pulse delay-700' : 'opacity-0'}`}></div>
            </div>

            {/* Left side - Enhanced branding area */}
            <div className={`hidden lg:flex lg:flex-1 lg:flex-col lg:justify-center lg:items-center lg:bg-gradient-to-br lg:from-red-50 lg:via-white lg:to-pink-50 lg:px-12 border-r border-gray-200 relative overflow-hidden transition-all duration-1000 min-h-screen ${mounted ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-full'}`}>
                
                {/* Enhanced background animations */}
                <div className="absolute inset-0 pointer-events-none">
                    {/* Floating geometric shapes */}
                    <div className={`absolute top-20 left-16 w-20 h-20 border-2 border-red-200/50 rounded-lg transition-all duration-2000 ${mounted ? 'opacity-40 rotate-45' : 'opacity-0 rotate-0'}`}
                         style={{animation: 'gentleFloat 8s ease-in-out infinite, morphShape 6s ease-in-out infinite'}}></div>
                    
                    <div className={`absolute bottom-32 right-16 w-16 h-16 bg-gradient-to-br from-red-100/50 to-pink-100/50 rounded-full transition-all duration-2500 ${mounted ? 'opacity-50' : 'opacity-0'}`}
                         style={{animation: 'orbit 20s linear infinite'}}></div>
                    
                    <div className={`absolute top-1/3 left-8 w-12 h-12 border border-pink-200/60 rounded transition-all duration-2200 ${mounted ? 'opacity-30 rotate-12' : 'opacity-0 rotate-0'}`}
                         style={{animation: 'pulse 4s ease-in-out infinite, wiggle 3s ease-in-out infinite'}}></div>
                    
                    {/* Connecting lines */}
                    <div className={`absolute top-40 left-32 w-40 h-0.5 bg-gradient-to-r from-red-200/60 to-transparent transition-all duration-2000 ${mounted ? 'opacity-40 rotate-12' : 'opacity-0 rotate-0'}`}
                         style={{animation: 'lineGrow 8s ease-in-out infinite'}}></div>
                    
                    {/* Floating particles */}
                    <div className={`absolute top-1/4 right-20 w-3 h-3 bg-red-300/40 rounded-full transition-all duration-1800 ${mounted ? 'opacity-60' : 'opacity-0'}`}
                         style={{animation: 'gentleFloat 6s ease-in-out infinite 1s'}}></div>
                    
                    <div className={`absolute bottom-1/4 left-20 w-2 h-2 bg-pink-300/40 rounded-full transition-all duration-2100 ${mounted ? 'opacity-50' : 'opacity-0'}`}
                         style={{animation: 'gentleFloat 7s ease-in-out infinite 2s'}}></div>
                    
                    {/* Dynamic grid pattern */}
                    <div className={`absolute top-16 right-24 w-32 h-32 transition-all duration-1800 ${mounted ? 'opacity-10' : 'opacity-0'}`}
                         style={{animation: 'breathe 10s ease-in-out infinite'}}>
                        <div className="w-full h-full border border-red-200/30 rounded-xl transform rotate-12"></div>
                    </div>
                </div>

                {/* Main content */}
                <div className="flex flex-col items-center justify-center text-center relative z-10 w-full max-w-lg">
                    {/* Enhanced logo section */}
                    <div className={`mb-12 relative flex justify-center transition-all duration-700 delay-200 ${mounted ? 'opacity-100 scale-100 rotate-0' : 'opacity-0 scale-0 rotate-180'}`}>
                        {/* Glow effect behind logo */}
                        <div className="absolute -inset-4 bg-gradient-to-r from-red-400/20 via-pink-400/20 to-red-400/20 rounded-3xl blur-xl opacity-0 animate-pulse"
                             style={{animation: 'breathe 6s ease-in-out infinite'}}></div>
                        
                        <div className="relative p-8 bg-white rounded-3xl shadow-2xl border border-gray-200/50 backdrop-blur-sm hover:scale-110 hover:shadow-3xl transition-all duration-500 group">
                            <img 
                                src="/telkom-logo.svg" 
                                alt="Telkom Logo" 
                                className="h-32 w-32 transition-transform duration-500 group-hover:rotate-12 group-hover:scale-105"
                            />
                            {/* Shimmer effect */}
                            <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 rounded-3xl"></div>
                        </div>
                    </div>
                    
                    {/* Enhanced title section */}
                    <div className={`mb-8 transition-all duration-700 delay-400 ${mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'}`}>
                        <h1 className="text-5xl font-bold bg-gradient-to-r from-red-600 via-red-700 to-red-800 bg-clip-text text-transparent mb-4 tracking-tight">
                            Telkom TWS
                        </h1>
                        <div className="flex items-center justify-center gap-4 mb-6">
                            <div className="h-1 w-16 bg-gradient-to-r from-transparent via-red-500 to-transparent rounded-full"
                                 style={{animation: 'progressLine 4s ease-in-out infinite'}}></div>
                            <div className="h-1 w-16 bg-gradient-to-r from-transparent via-red-500 to-transparent rounded-full"
                                 style={{animation: 'progressLine 4s ease-in-out infinite 2s'}}></div>
                        </div>
                        <h2 className="text-2xl font-semibold text-gray-700 mb-4">Dashboard</h2>
                    </div>
                    
                    {/* Enhanced description */}
                    {/* <div className={`mb-8 transition-all duration-700 delay-600 ${mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                        <p className="text-gray-600 text-lg max-w-md mx-auto leading-relaxed"
                           style={{animation: 'breathe 8s ease-in-out infinite 2s'}}>
                            Your Gateway to Comprehensive<br/>
                            <span className="font-semibold text-red-600">Business Intelligence</span>
                        </p>
                    </div>
                     */}
                    {/* Simple welcome message */}
                    <div className={`mb-8 transition-all duration-1000 delay-800 ${mounted ? 'opacity-100 scale-100' : 'opacity-0 scale-95'}`}>
                        <div className="max-w-sm mx-auto text-center">
                            <div className="p-4 bg-white/70 backdrop-blur-sm rounded-xl border border-gray-200/50 hover:shadow-lg transition-all duration-300 hover:scale-105"
                                 style={{animation: 'breathe 8s ease-in-out infinite 3s'}}>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    Welcome to 
                                    <span className="font-semibold text-red-600 mx-1">TWS Dashboard</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    {/* Enhanced animated dots */}
                    <div className={`flex justify-center gap-3 transition-all duration-1000 delay-1000 ${mounted ? 'opacity-60' : 'opacity-0'}`}>
                        <div className="w-3 h-3 bg-gradient-to-r from-red-500 to-red-600 rounded-full" style={{animation: 'bounce 2s infinite, breathe 4s ease-in-out infinite'}}></div>
                        <div className="w-3 h-3 bg-gradient-to-r from-red-600 to-pink-600 rounded-full" style={{animation: 'bounce 2s infinite 0.2s, breathe 4s ease-in-out infinite 1s'}}></div>
                        <div className="w-3 h-3 bg-gradient-to-r from-pink-600 to-red-600 rounded-full" style={{animation: 'bounce 2s infinite 0.4s, breathe 4s ease-in-out infinite 2s'}}></div>
                        <div className="w-3 h-3 bg-gradient-to-r from-red-600 to-red-500 rounded-full" style={{animation: 'bounce 2s infinite 0.6s, breathe 4s ease-in-out infinite 3s'}}></div>
                    </div>
                </div>
            </div>

            {/* Right side - Login form */}
            <div className="flex-1 flex items-center justify-center p-6 md:p-10">
                <div className={`w-full max-w-md transition-all duration-1000 ${mounted ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-full'}`}>
                    <div className="bg-white rounded-2xl p-8 border border-gray-200 transition-all duration-500 hover:scale-[1.02] hover:border-red-200">
                        <div className="flex flex-col gap-8">
                            {children}
                        </div>
                    </div>
                    
                    {/* Footer */}
                    <div className={`text-center mt-6 text-sm text-gray-500 flex items-center justify-center gap-2 transition-all duration-1000 delay-800 ${mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                        © 2025
                        <div className="p-1 bg-white rounded shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 hover:scale-110">
                            <img 
                                src="/telkom-logo.svg" 
                                alt="Telkom Logo" 
                                className="h-3 w-3 hover:rotate-12 transition-transform duration-300"
                            />
                        </div>
                        Dashboard - by Telkom TWS
                    </div>
                </div>
            </div>
        </div>
    );
}
