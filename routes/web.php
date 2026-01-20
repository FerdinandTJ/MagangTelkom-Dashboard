<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RevenueBreakdownController;
use App\Http\Controllers\RevenueImportController;
use App\Http\Controllers\DataImportPerformanceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard routes - accessible by all authenticated users
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('performance-am', [DashboardController::class, 'performanceAM'])->name('performance-am');
    Route::get('daily-monitoring', [DashboardController::class, 'dailymonitoring'])->name('daily-monitoring');

    // Data Import routes - only accessible by admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('data-import/revenue', [DashboardController::class, 'dataImportRevenue'])->name('data-import.revenue');
        Route::get('data-import/performance', [DataImportPerformanceController::class, 'index'])->name('data-import.performance');
        
        // Data Import - Upload & Template Download
        Route::post('data-import/revenue/upload', [RevenueImportController::class, 'store'])->name('data-import.revenue.upload');
        Route::get('data-import/revenue/download-template/{year}', [RevenueImportController::class, 'downloadTemplate'])->name('data-import.revenue.download-template');
        Route::get('data-import/revenue/download/{year}/{month}', [RevenueImportController::class, 'downloadFile'])->name('data-import.revenue.download');
        Route::get('data-import/revenue/download-year/{year}', [RevenueImportController::class, 'downloadYear'])->name('data-import.revenue.download-year');
        Route::delete('data-import/revenue/delete/{year}/{month}', [RevenueImportController::class, 'deleteMonth'])->name('data-import.revenue.delete-month');
        Route::delete('data-import/revenue/delete/{year}', [RevenueImportController::class, 'deleteYear'])->name('data-import.revenue.delete');
        
        // Data Import - Performance AM (admin only)
        Route::post('/api/data-import/performance/upload', [DataImportPerformanceController::class, 'upload'])->name('data-import.performance.upload');
        Route::get('/api/data-import/performance/template', [DataImportPerformanceController::class, 'downloadTemplate'])->name('data-import.performance.template');
        Route::delete('/api/data-import/performance/delete/{year}/{quarter?}', [DataImportPerformanceController::class, 'delete'])->name('data-import.performance.delete');
    });
    
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
        Route::get('region-detail', [DashboardController::class, 'getRegionDetail'])->name('api.dashboard.region-detail');
        Route::get('region-nki/{regionId}', [\App\Http\Controllers\RegionNkiController::class, 'getRegionNkiData'])->name('api.dashboard.region-nki');
        Route::get('region-nki-periods', [\App\Http\Controllers\RegionNkiController::class, 'getAvailablePeriods'])->name('api.dashboard.region-nki-periods');
        Route::get('region-nki-chart/{regionId}', [\App\Http\Controllers\RegionNkiController::class, 'getParameterChartData'])->name('api.dashboard.region-nki-chart');
        Route::get('witel-nki-detail', [\App\Http\Controllers\RegionNkiController::class, 'getWitelNkiDetail'])->name('api.dashboard.witel-nki-detail');
        Route::get('witel-am-details', [\App\Http\Controllers\RegionNkiController::class, 'getWitelAMDetails'])->name('api.dashboard.witel-am-details');
        Route::get('region-revenue', [\App\Http\Controllers\RegionRevenueController::class, 'getRegionRevenue'])->name('api.dashboard.region-revenue');
        Route::get('region-witel-detail', [DashboardController::class, 'getRegionWitelDetail'])->name('api.dashboard.region-witel-detail');
        Route::get('am-performance-detail', [\App\Http\Controllers\AmPerformanceDetailController::class, 'getAmPerformanceDetail'])->name('api.dashboard.am-performance-detail');
        
        // Revenue Breakdown
        Route::get('revenue-breakdown/{companyId}', [RevenueBreakdownController::class, 'getBreakdown'])->name('api.dashboard.revenue-breakdown');
        
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
