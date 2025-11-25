<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RevenueBreakdownDummyController;
use App\Http\Controllers\RevenueBreakdownController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard routes
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('performance-am', [DashboardController::class, 'performanceAM'])->name('performance-am');
    
    // API routes for dashboard analytics
    Route::prefix('api/dashboard')->group(function () {
        Route::get('monthly-data', [DashboardController::class, 'getMonthlyData'])->name('api.dashboard.monthly');
        Route::get('month-details', [DashboardController::class, 'getMonthDetails'])->name('api.dashboard.month-details');
        Route::get('company-details', [DashboardController::class, 'getCompanyDetails'])->name('api.dashboard.company-details');
        Route::get('subsegment-details', [DashboardController::class, 'getSubsegmentDetails'])->name('api.dashboard.subsegment-details');
        Route::get('individual-company-details', [DashboardController::class, 'getIndividualCompanyDetails'])->name('api.dashboard.individual-company-details');
        Route::get('subsegment-trend', [DashboardController::class, 'getSubsegmentTrend'])->name('api.dashboard.subsegment-trend');
        Route::get('yearly-comparison', [DashboardController::class, 'getYearlyComparison'])->name('api.dashboard.yearly-comparison');
        Route::get('analytics-summary', [DashboardController::class, 'getAnalyticsSummary'])->name('api.dashboard.analytics-summary');
        Route::get('available-periods', [DashboardController::class, 'getAvailablePeriods'])->name('api.dashboard.available-periods');
        Route::get('ytd-comparison-custom', [DashboardController::class, 'getCustomYtdComparison'])->name('api.dashboard.ytd-comparison-custom');
        Route::get('am-revenue-details', [DashboardController::class, 'getAMRevenueDetails'])->name('api.dashboard.am-revenue-details');
        Route::get('region-nki/{regionId}', [\App\Http\Controllers\RegionNkiController::class, 'getRegionNkiData'])->name('api.dashboard.region-nki');
        
        // DUMMY ENDPOINT - For testing Revenue Breakdown Tree component
        // Route::get('revenue-breakdown-dummy', [RevenueBreakdownDummyController::class, 'getDummyRevenueBreakdown'])->name('api.dashboard.revenue-breakdown-dummy');
        
        // PRODUCTION ENDPOINT - Revenue Breakdown from database
        Route::get('revenue-breakdown/{companyId}', [RevenueBreakdownController::class, 'getBreakdown'])->name('api.dashboard.revenue-breakdown');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
