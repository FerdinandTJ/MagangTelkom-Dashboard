import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, performanceAm } from '@/routes';
import { revenue as dataImportRevenue, performance as dataImportPerformance } from '@/routes/data-import';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder, Gauge, GaugeIcon, LayoutDashboardIcon, LayoutGrid, Monitor, MonitorCheck, TrendingUp, TvIcon, Upload } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Daily Monitoring',
        href: '/daily-monitoring',
        icon: MonitorCheck,
    },
    {
        title: 'Revenue Dashboard',
        href: dashboard(),
        icon: LayoutDashboardIcon,
    },
    {
        title: 'Performance AM',
        href: performanceAm(),
        icon: TrendingUp,
    },
    {
        title: 'Data Upload',
        href: dataImportRevenue(), // Default to first submenu
        icon: Upload,
        items: [
            {
                title: 'Revenue Dashboard',
                href: dataImportRevenue(),
            },
            {
                title: 'Performance AM',
                href: dataImportPerformance(),
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'About',
    //     href: '#',
    //     icon: Folder,
    // },
    // {
    //     title: 'Help',
    //     href: '#',
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    const { auth } = usePage().props as { auth: { user: { role: string } } };
    
    // Filter navigation items based on user role
    const filteredNavItems = mainNavItems.filter(item => {
        // Data Upload menu only for admin
        if (item.title === 'Data Upload') {
            return auth.user.role === 'admin';
        }
        return true;
    });
    
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={filteredNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
