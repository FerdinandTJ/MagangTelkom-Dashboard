import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const [mounted, setMounted] = useState(false);
    const [showForm, setShowForm] = useState(false);
    const [typingText, setTypingText] = useState('');
    const [showCursor, setShowCursor] = useState(true);
    const [showWave, setShowWave] = useState(false);
    const [typingDirection, setTypingDirection] = useState<'typing' | 'deleting' | 'pausing'>('typing');
    const [showPassword, setShowPassword] = useState(false);

    useEffect(() => {
        setMounted(true);
        const timer = setTimeout(() => setShowForm(true), 300);
        
        const welcomeText = "Welcome Back! ";
        let i = 0;
        let currentDirection: 'typing' | 'deleting' | 'pausing' = 'typing';
        
        const typingLoop = () => {
            const typingTimer = setInterval(() => {
                if (currentDirection === 'typing') {
                    if (i <= welcomeText.length) {
                        setTypingText(welcomeText.slice(0, i));
                        setTypingDirection('typing');
                        i++;
                        
                        if (i > welcomeText.length) {
                            currentDirection = 'pausing';
                            clearInterval(typingTimer);
                            setShowWave(true);
                            setTimeout(() => {
                                setShowWave(false);
                                currentDirection = 'deleting';
                                typingLoop();
                            }, 3500); 
                        }
                    }
                } else if (currentDirection === 'deleting') {
                    if (i > 0) {
                        i--;
                        setTypingText(welcomeText.slice(0, i));
                        setTypingDirection('deleting');
                    } else {
                        currentDirection = 'typing';
                        clearInterval(typingTimer);
                        setTimeout(() => {
                            typingLoop();
                        }, 500); 
                    }
                }
            }, currentDirection === 'typing' ? 100 : 50); 
        };
        
        typingLoop();

        const cursorTimer = setInterval(() => {
            setShowCursor(prev => !prev);
        }, 500);

        return () => {
            clearTimeout(timer);
            clearInterval(cursorTimer);
        };
    }, []);

    return (
        <AuthLayout
            title="Log in to your account"
            description="Enter your email and password below to access Dashboard"
        >
            <Head title="Login" />

            <div className="fixed inset-0 overflow-hidden pointer-events-none">
                <div className={`absolute top-20 right-20 w-16 h-16 border-2 border-red-200 dark:border-red-800 transition-all duration-2000 ${mounted ? 'opacity-30 rotate-45' : 'opacity-0 rotate-0'}`}
                     style={{animation: 'float 6s ease-in-out infinite, morphShape 4s ease-in-out infinite'}}>
                </div>
                
                <div className={`absolute top-1/3 right-1/3 w-2 h-2 transition-all duration-1500 ${mounted ? 'opacity-40' : 'opacity-0'}`}
                     style={{animation: 'gentleFloat 8s ease-in-out infinite'}}>
                    <div className="w-full h-full bg-red-300 dark:bg-red-700 rounded-full"></div>
                </div>
                
                <div className={`absolute top-2/3 left-1/5 w-1 h-1 transition-all duration-2000 ${mounted ? 'opacity-60' : 'opacity-0'}`}
                     style={{animation: 'gentleFloat 6s ease-in-out infinite 1s'}}>
                    <div className="w-full h-full bg-pink-300 dark:bg-pink-700 rounded-full"></div>
                </div>
                
                <div className={`absolute top-1/4 left-1/4 w-4 h-4 transition-all duration-2500 ${mounted ? 'opacity-50' : 'opacity-0'}`}
                     style={{animation: 'orbit 15s linear infinite'}}>
                    <div className="w-full h-full bg-gradient-to-r from-red-400 to-pink-400 dark:from-red-600 dark:to-pink-600 rounded-full"></div>
                </div>
                
                <div className={`absolute bottom-1/3 right-1/4 w-3 h-3 transition-all duration-3000 ${mounted ? 'opacity-40' : 'opacity-0'}`}
                     style={{animation: 'orbit 12s linear infinite reverse'}}>
                    <div className="w-full h-full bg-gradient-to-r from-pink-400 to-red-400 dark:from-pink-600 dark:to-red-600 rounded-full"></div>
                </div>
                
                <div className={`absolute top-1/2 right-1/6 w-3 h-3 transition-all duration-2200 ${mounted ? 'opacity-30 rotate-45' : 'opacity-0 rotate-0'}`}
                     style={{animation: 'gentleFloat 10s ease-in-out infinite, wiggle 4s ease-in-out infinite'}}>
                    <div className="w-full h-full bg-gradient-to-br from-red-300 to-pink-300 dark:from-red-700 dark:to-pink-700 transform rotate-45"></div>
                </div>

                <div className={`absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-red-400 dark:via-red-600 to-transparent transition-all duration-1000 ${mounted ? 'opacity-60' : 'opacity-0'}`}
                     style={{animation: 'progressLine 8s ease-in-out infinite'}}>
                </div>

                <div className={`absolute top-32 left-20 w-32 h-0.5 bg-gradient-to-r from-red-200 dark:from-red-800 to-transparent transition-all duration-2000 ${mounted ? 'opacity-30 rotate-12' : 'opacity-0 rotate-0'}`}
                     style={{animation: 'lineGrow 6s ease-in-out infinite'}}>
                </div>
                
                <div className={`absolute bottom-1/4 right-20 w-6 h-6 border border-red-300 dark:border-red-700 rounded transition-all duration-2200 ${mounted ? 'opacity-30' : 'opacity-0'}`}
                     style={{animation: 'pulse 3s ease-in-out infinite'}}>
                </div>
                
                <div className={`absolute top-16 left-16 w-24 h-24 transition-all duration-1800 ${mounted ? 'opacity-10' : 'opacity-0'}`}
                     style={{animation: 'breathe 8s ease-in-out infinite'}}>
                    <div className="w-full h-full border border-red-200 dark:border-red-800 rounded-lg transform rotate-12"></div>
                </div>
            </div>

            <div className={`mb-8 transition-all duration-1000 overflow-visible ${mounted ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-8'}`}>
                <div className="flex items-center gap-2 mb-2 min-h-[2.5rem] relative overflow-visible">
                    <h1 className="text-3xl font-bold bg-gradient-to-r from-red-600 via-red-700 to-red-800 dark:from-red-400 dark:via-red-500 dark:to-red-600 bg-clip-text text-transparent">
                        <span>{typingText}</span>
                        {!showWave && (
                            <span className={`${showCursor ? 'opacity-100' : 'opacity-0'} transition-opacity duration-100`}>|</span>
                        )}
                    </h1>
                    {showWave && (
                        <span 
                            className="text-4xl transition-all duration-500 inline-block relative z-50"
                            style={{
                                animation: 'wave 1.5s ease-in-out infinite',
                                transformOrigin: '70% 70%',
                                display: 'inline-block',
                                fontSize: '2.5rem',
                                filter: 'hue-rotate(310deg) saturate(1.5) brightness(1.1)'
                            }}
                        >
                            👋
                        </span>
                    )}
                </div>
                <div className="flex items-center gap-2">
                    <p className="text-gray-600 dark:text-gray-400">Let's get you signed in</p>
                </div>
            </div>

            {status && (
                <div className={`mb-6 p-4 text-center text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg transition-all duration-500 ${mounted ? 'opacity-100 scale-100' : 'opacity-0 scale-95'}`}>
                    {status}
                </div>
            )}

            <div className={`transition-all duration-700 delay-200 ${showForm ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'}`}>
                <div className="relative">
                    {/* Subtle form breathing animation */}
                    <div className="absolute -inset-4 bg-gradient-to-r from-red-50/30 via-pink-50/20 to-red-50/30 dark:from-red-900/20 dark:via-pink-900/10 dark:to-red-900/20 rounded-2xl opacity-0 animate-pulse"
                         style={{animation: 'breathe 6s ease-in-out infinite'}}></div>
                    
                    <Form
                        {...store.form()}
                        resetOnSuccess={['password']}
                        className="flex flex-col gap-6 relative z-10 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 border border-white/20 dark:border-gray-700/20 shadow-sm"
                        style={{animation: 'gentleFloat 8s ease-in-out infinite'}}
                    >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-6">
                                <div className={`grid gap-3 transition-all duration-500 delay-300 ${showForm ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-8'}`}>
                                    <Label htmlFor="email" className="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2 group cursor-pointer hover:text-red-600 dark:hover:text-red-400 transition-colors duration-300">
                                        <span className="group-hover:translate-x-1 transition-transform duration-300">Email or Username</span>
                                        <svg className="w-4 h-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </Label>
                                    <div className="relative group">

                                        <div className="absolute -inset-0.5 bg-gradient-to-r from-red-600 via-pink-600 to-red-600 dark:from-red-500 dark:via-pink-500 dark:to-red-500 rounded-lg opacity-0 group-hover:opacity-75 group-focus-within:opacity-75 transition-all duration-300 animate-pulse"></div>
                                        
                                        <div className="absolute -inset-1 bg-gradient-to-r from-red-400/20 to-pink-400/20 dark:from-red-600/20 dark:to-pink-600/20 rounded-lg opacity-0 group-focus-within:opacity-100 transition-all duration-500 blur-md"></div>
                                        
                                        <Input
                                            id="email"
                                            type="text"
                                            name="email"
                                            required
                                            autoFocus
                                            tabIndex={1}
                                            autoComplete="username"
                                            placeholder="Enter your email or username"
                                            className="relative h-12 px-4 text-base text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 focus:border-red-500 dark:focus:border-red-400 focus:ring-0 rounded-lg placeholder:text-gray-400 dark:placeholder:text-gray-500 transition-all duration-300 hover:border-red-300 dark:hover:border-red-600 transform hover:scale-[1.02] focus:scale-[1.02] group-focus-within:shadow-lg"
                                        />
                                        <div className="absolute right-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-all duration-300">
                                            <div className="w-5 h-5 border-2 border-red-400 dark:border-red-500 rounded-full animate-spin group-focus-within:border-red-500 dark:group-focus-within:border-red-400"></div>
                                        </div>
                                    </div>
                                    <InputError message={errors.email} />
                                </div>

                                <div className={`grid gap-3 transition-all duration-500 delay-400 ${showForm ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-8'}`}>
                                    <div className="flex items-center">
                                        <Label htmlFor="password" className="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2 group cursor-pointer hover:text-red-600 dark:hover:text-red-400 transition-colors duration-300">
                                            <span className="group-hover:translate-x-1 transition-transform duration-300">Password</span>
                                            <svg className="w-4 h-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </Label>
                                        {canResetPassword && (
                                            <TextLink
                                                href={request()}
                                                className="ml-auto text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-500 font-medium transition-colors duration-200 hover:underline"
                                                tabIndex={5}
                                            >
                                                Forgot password?
                                            </TextLink>
                                        )}
                                    </div>
                                    <div className="relative group">
                                        {/* Animated border */}
                                        <div className="absolute -inset-0.5 bg-gradient-to-r from-red-600 via-pink-600 to-red-600 dark:from-red-500 dark:via-pink-500 dark:to-red-500 rounded-lg opacity-0 group-hover:opacity-75 group-focus-within:opacity-75 transition-all duration-300 animate-pulse"></div>
                                        
                                        {/* Focus glow effect */}
                                        <div className="absolute -inset-1 bg-gradient-to-r from-red-400/20 to-pink-400/20 dark:from-red-600/20 dark:to-pink-600/20 rounded-lg opacity-0 group-focus-within:opacity-100 transition-all duration-500 blur-md"></div>
                                        
                                        <Input
                                            id="password"
                                            type={showPassword ? "text" : "password"}
                                            name="password"
                                            required
                                            tabIndex={2}
                                            autoComplete="current-password"
                                            placeholder="Enter your password"
                                            className="relative h-12 px-4 pr-12 text-base text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 focus:border-red-500 dark:focus:border-red-400 focus:ring-0 rounded-lg placeholder:text-gray-400 dark:placeholder:text-gray-500 transition-all duration-300 hover:border-red-300 dark:hover:border-red-600 transform hover:scale-[1.02] focus:scale-[1.02] group-focus-within:shadow-lg"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword(!showPassword)}
                                            tabIndex={-1}
                                            className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors duration-200 focus:outline-none z-10"
                                        >
                                            {showPassword ? (
                                                <EyeOff className="h-5 w-5" />
                                            ) : (
                                                <Eye className="h-5 w-5" />
                                            )}
                                        </button>
                                        {/* Lock icon animation */}
                                        <div className="absolute right-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-all duration-300">
                                            <svg className="w-5 h-5 text-red-400 dark:text-red-500 group-focus-within:text-red-500 dark:group-focus-within:text-red-400 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                 style={{animation: 'wiggle 2s ease-in-out infinite'}}>
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <InputError message={errors.password} />
                                </div>

                                <div className={`flex items-center space-x-3 py-2 transition-all duration-500 delay-500 ${showForm ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                                    <div className="relative group">
                                        {/* Checkbox glow effect */}
                                        <div className="absolute -inset-1 bg-red-400/20 dark:bg-red-600/20 rounded opacity-0 group-hover:opacity-100 transition-all duration-300 blur-sm"></div>
                                        <Checkbox
                                            id="remember"
                                            name="remember"
                                            tabIndex={3}
                                            className="text-red-600 dark:text-red-400 focus:ring-red-500 dark:focus:ring-red-400 transition-all duration-200 hover:scale-110 relative z-10"
                                        />
                                    </div>
                                    <Label htmlFor="remember" className="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200 cursor-pointer select-none hover:tracking-wide">
                                        Remember me for 30 days
                                    </Label>
                                </div>

                                <div className={`transition-all duration-500 delay-600 ${showForm ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'}`}>
                                    <Button
                                        type="submit"
                                        className="mt-6 w-full h-12 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold text-base rounded-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] relative overflow-hidden group shadow-lg hover:shadow-xl"
                                        tabIndex={4}
                                        disabled={processing}
                                        data-test="login-button"
                                    >
                                        {/* Subtle glow effect */}
                                        <div className="absolute -inset-1 bg-gradient-to-r from-red-400 to-red-600 rounded-lg opacity-0 group-hover:opacity-30 transition-all duration-500 blur-sm"></div>
                                        
                                        {/* Shimmer effect */}
                                        <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out"></div>
                                        
                                        {processing ? (
                                            <div className="flex items-center justify-center gap-2 relative z-10">
                                                <Spinner className="h-5 w-5 animate-spin" />
                                                <span>Signing you in</span>
                                                <div className="flex gap-0.5">
                                                    <span className="animate-bounce" style={{animationDelay: '0s'}}>.</span>
                                                    <span className="animate-bounce" style={{animationDelay: '0.2s'}}>.</span>
                                                    <span className="animate-bounce" style={{animationDelay: '0.4s'}}>.</span>
                                                </div>
                                            </div>
                                        ) : (
                                            <span className="flex items-center justify-center gap-2 relative z-10 group-hover:text-white transition-colors duration-300">
                                                <span className="group-hover:tracking-wide transition-all duration-300">Sign In</span>
                                                <svg className="w-5 h-5 transition-all duration-300 group-hover:translate-x-1 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                </svg>
                                            </span>
                                        )}
                                    </Button>
                                </div>
                            </div>

                            <div className={`text-center text-sm text-gray-500 transition-all duration-500 delay-700 ${showForm ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                                Don't have an account?{' '}
                                <TextLink href={register()} tabIndex={5} className="text-red-600 hover:text-red-700 font-medium transition-colors duration-200 hover:underline">
                                    Create account
                                </TextLink>
                            </div>
                        </>
                    )}
                    </Form>
                </div>
            </div>
        </AuthLayout>
    );
}
