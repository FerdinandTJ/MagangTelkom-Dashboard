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
import { Link } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, TrendingUp, Upload } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Revenue Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
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
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
