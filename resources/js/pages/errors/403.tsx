import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';

export default function Error403() {
    return (
        <>
            <Head title="403 - Access Denied" />
            <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-950 px-4">
                <div className="max-w-md w-full text-center">
                    <div className="mb-8">
                        <div className="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-100 dark:bg-red-900/30 mb-6">
                            <ShieldAlert className="h-12 w-12 text-red-600 dark:text-red-400" />
                        </div>
                        <h1 className="text-6xl font-bold text-gray-900 dark:text-white mb-2">
                            403
                        </h1>
                        <h2 className="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
                            Access Denied
                        </h2>
                        <p className="text-gray-600 dark:text-gray-400 mb-8">
                            You do not have permission to access this resource. 
                            Please contact your administrator if you believe this is an error.
                        </p>
                    </div>
                    
                    <div className="flex flex-col sm:flex-row gap-3 justify-center">
                        <Button asChild variant="default">
                            <Link href="/dashboard">
                                Go to Dashboard
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
