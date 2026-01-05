<?php

// Test script untuk debugging AM Performance Detail API
// Jalankan dengan: php artisan tinker
// Atau buat file test khusus

use App\Models\AccountManager;
use App\Models\AccountManagerCompany;
use Illuminate\Support\Facades\DB;

// 1. Cek apakah ada data Account Manager
echo "=== CHECKING ACCOUNT MANAGERS ===\n";
$amCount = AccountManager::count();
echo "Total Account Managers: {$amCount}\n\n";

if ($amCount > 0) {
    $firstAM = AccountManager::with(['witel.region'])->first();
    echo "First AM Details:\n";
    echo "NIK: {$firstAM->nik}\n";
    echo "Nama: {$firstAM->nama}\n";
    echo "Posisi: {$firstAM->posisi}\n";
    echo "Witel: " . ($firstAM->witel->nama_witels ?? 'NULL') . "\n";
    echo "Region: " . ($firstAM->witel->region->name ?? 'NULL') . "\n\n";
    
    // 2. Cek apakah AM ini punya data performance
    echo "=== CHECKING PERFORMANCE DATA ===\n";
    $performanceData = AccountManagerCompany::where('nik_am', $firstAM->nik)
        ->orderBy('year', 'desc')
        ->orderBy('quarter', 'desc')
        ->first();
    
    if ($performanceData) {
        echo "Latest Performance Record:\n";
        echo "Quarter: {$performanceData->quarter}\n";
        echo "Year: {$performanceData->year}\n";
        echo "Company NIP: {$performanceData->nip_nas}\n";
        
        // Get company details
        $company = \App\Models\Company::where('nip_nas', $performanceData->nip_nas)->first();
        if ($company) {
            echo "Company Segment: {$company->segment}\n";
        }
        echo "\n";
        
        // 3. Test API call simulation
        echo "=== TEST API PARAMETERS ===\n";
        echo "Use these parameters to test the API:\n";
        echo "nik_am: {$firstAM->nik}\n";
        echo "quarter: {$performanceData->quarter}\n";
        echo "year: {$performanceData->year}\n";
        if ($company) {
            echo "segment: {$company->segment}\n";
        }
        echo "\n";
        
        // 4. Generate test URL
        if ($company) {
            $testUrl = "http://" . $_SERVER['HTTP_HOST'] . "/api/dashboard/am-performance-detail";
            $testUrl .= "?nik_am={$firstAM->nik}";
            $testUrl .= "&quarter={$performanceData->quarter}";
            $testUrl .= "&year={$performanceData->year}";
            $testUrl .= "&segment={$company->segment}";
            
            echo "=== TEST URL ===\n";
            echo "Copy this URL to test in browser:\n";
            echo $testUrl . "\n\n";
        }
    } else {
        echo "No performance data found for this AM\n";
        echo "Please check account_manager_companies table\n\n";
    }
} else {
    echo "No Account Managers found in database!\n";
    echo "Please run seeders first:\n";
    echo "php artisan db:seed --class=AccountManagerSeeder\n\n";
}

// 5. Check data availability summary
echo "=== DATA AVAILABILITY SUMMARY ===\n";
echo "Account Managers: " . AccountManager::count() . "\n";
echo "Witels: " . \App\Models\Witel::count() . "\n";
echo "Regions: " . \App\Models\Region::count() . "\n";
echo "Companies: " . \App\Models\Company::count() . "\n";
echo "AM Performance Records: " . AccountManagerCompany::count() . "\n";
echo "\n";

// 6. Check for AMs without witel
$amsWithoutWitel = AccountManager::whereNull('idwitels')->orWhereNotExists(function($query) {
    $query->select(DB::raw(1))
          ->from('witels')
          ->whereRaw('witels.idwitels = account_managers.idwitels');
})->count();

if ($amsWithoutWitel > 0) {
    echo "⚠️  Warning: {$amsWithoutWitel} Account Managers without valid Witel assignment\n";
}

// 7. Check for witels without region
$witelsWithoutRegion = \App\Models\Witel::whereNull('region_id')->orWhereNotExists(function($query) {
    $query->select(DB::raw(1))
          ->from('regions')
          ->whereRaw('regions.id = witels.region_id');
})->count();

if ($witelsWithoutRegion > 0) {
    echo "⚠️  Warning: {$witelsWithoutRegion} Witels without valid Region assignment\n";
}

echo "\n=== END OF TEST ===\n";
