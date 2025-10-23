import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="sticky top-0 z-50 flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/50 bg-white/95 backdrop-blur-md px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                {/* Enhanced Sidebar Toggle Button */}
                <div className="flex items-center gap-1">
                    <SidebarTrigger className="h-9 w-9 border border-gray-200 bg-white hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition-all duration-200 shadow-sm" />
                    <div className="hidden md:block text-xs text-gray-500 font-medium">
                        Ctrl+B
                    </div>
                </div>
                <div className="h-6 w-px bg-gray-200 mx-2"></div>
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            
            {/* Optional: Add dashboard title */}
            <div className="ml-auto hidden md:flex items-center gap-2">
                <div className="text-sm font-semibold text-gray-700">TWS Revenue Dashboard</div>
                <div className="p-1.5 bg-red-50 rounded-lg">
                    <svg className="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </header>
    );
}
