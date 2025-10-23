import React from 'react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import { Users, Target, TrendingUp, Award, Building2, UserCheck } from 'lucide-react';

interface PerformanceAMProps {
    amMetrics: {
        total_accounts: number;
        active_accounts: number;
        revenue_target: number;
        revenue_achieved: number;
        achievement_rate: number;
    };
    amPerformance: Array<{
        name: string;
        accounts: number;
        revenue: number;
        achievement: number;
    }>;
    accountDistribution: Array<{
        subsegment: string;
        count: number;
        percentage: number;
    }>;
    currentYear: number;
}

const COLORS = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6'];

export default function PerformanceAM({
    amMetrics,
    amPerformance,
    accountDistribution,
    currentYear
}: PerformanceAMProps) {
    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Performance AM', href: '/performance-am' }
            ]}
        >
            <div className="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">
                {/* Metrics Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <Card className="bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-center gap-2">
                                <Users className="h-5 w-5 text-blue-600" />
                                <CardTitle className="text-sm font-medium text-blue-700">Total Accounts</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-900">{amMetrics.total_accounts}</div>
                            <p className="text-xs text-blue-600 mt-1">Managed accounts</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-r from-green-50 to-emerald-50 border-green-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-center gap-2">
                                <UserCheck className="h-5 w-5 text-green-600" />
                                <CardTitle className="text-sm font-medium text-green-700">Active Accounts</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-900">{amMetrics.active_accounts}</div>
                            <p className="text-xs text-green-600 mt-1">Currently active</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-r from-purple-50 to-violet-50 border-purple-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-center gap-2">
                                <Target className="h-5 w-5 text-purple-600" />
                                <CardTitle className="text-sm font-medium text-purple-700">Revenue Target</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-purple-900">
                                Rp {(amMetrics.revenue_target / 1000000000).toFixed(1)}B
                            </div>
                            <p className="text-xs text-purple-600 mt-1">Annual target</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-r from-orange-50 to-red-50 border-orange-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-center gap-2">
                                <TrendingUp className="h-5 w-5 text-orange-600" />
                                <CardTitle className="text-sm font-medium text-orange-700">Revenue Achieved</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-900">
                                Rp {(amMetrics.revenue_achieved / 1000000000).toFixed(1)}B
                            </div>
                            <p className="text-xs text-orange-600 mt-1">Year to date</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-r from-red-50 to-pink-50 border-red-200">
                        <CardHeader className="pb-3">
                            <div className="flex items-center gap-2">
                                <Award className="h-5 w-5 text-red-600" />
                                <CardTitle className="text-sm font-medium text-red-700">Achievement Rate</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-900">{amMetrics.achievement_rate}%</div>
                            <p className="text-xs text-red-600 mt-1">Target achievement</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Charts Section */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {/* AM Performance Chart */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TrendingUp className="h-5 w-5 text-red-600" />
                                AM Performance Ranking
                            </CardTitle>
                            <CardDescription>Revenue achievement by Account Manager</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={amPerformance}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis 
                                        dataKey="name" 
                                        tick={{ fontSize: 12 }}
                                        angle={-45}
                                        textAnchor="end"
                                        height={80}
                                    />
                                    <YAxis tick={{ fontSize: 12 }} />
                                    <Tooltip 
                                        formatter={(value: any) => [
                                            `Rp ${(value / 1000000000).toFixed(1)}B`,
                                            'Revenue'
                                        ]}
                                    />
                                    <Bar dataKey="revenue" fill="#dc2626" radius={[4, 4, 0, 0]} />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* Account Distribution Chart */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Building2 className="h-5 w-5 text-red-600" />
                                Account Distribution by Subsegment
                            </CardTitle>
                            <CardDescription>Distribution of managed accounts</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        data={accountDistribution}
                                        cx="50%"
                                        cy="50%"
                                        labelLine={false}
                                        label={({ subsegment, percentage }) => `${subsegment} (${percentage}%)`}
                                        outerRadius={100}
                                        fill="#8884d8"
                                        dataKey="count"
                                    >
                                        {accountDistribution.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip formatter={(value: any) => [value, 'Accounts']} />
                                </PieChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* AM Performance Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Users className="h-5 w-5 text-red-600" />
                            Account Manager Performance Details
                        </CardTitle>
                        <CardDescription>Detailed performance metrics for each Account Manager</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200">
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Rank</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Account Manager</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Accounts</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Revenue</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Achievement</th>
                                        <th className="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {amPerformance
                                        .sort((a, b) => b.achievement - a.achievement)
                                        .map((am, index) => (
                                        <tr key={am.name} className="border-b border-gray-100 hover:bg-gray-50">
                                            <td className="py-3 px-4">
                                                <div className="flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 rounded-full text-sm font-semibold">
                                                    {index + 1}
                                                </div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="font-medium text-gray-900">{am.name}</div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="text-gray-900">{am.accounts} accounts</div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="font-semibold text-gray-900">
                                                    Rp {(am.revenue / 1000000000).toFixed(1)}B
                                                </div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="font-semibold text-gray-900">{am.achievement}%</div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <span className={`px-3 py-1 text-sm font-medium rounded-full ${
                                                    am.achievement >= 90 ? 'bg-green-100 text-green-800' :
                                                    am.achievement >= 75 ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-red-100 text-red-800'
                                                }`}>
                                                    {am.achievement >= 90 ? 'Excellent' :
                                                     am.achievement >= 75 ? 'Good' : 'Needs Improvement'}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
}
