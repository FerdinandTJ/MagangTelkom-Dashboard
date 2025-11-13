<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\Witel;

echo "\n=== TESTING COMPANY-WITEL RELATION ===\n\n";

// Test 1: Get companies with their witels
echo "1. Companies with Witel Assignments:\n";
echo str_repeat("-", 80) . "\n";

$companies = Company::with('witel.region')->get();
foreach ($companies as $company) {
    $witelInfo = $company->witel 
        ? "{$company->witel->nama_witels} ({$company->witel->region->code})"
        : "No Witel";
    
    echo sprintf("%-10s | %-35s | %s\n", 
        $company->nip_nas,
        substr($company->nama_perusahaan, 0, 35),
        $witelInfo
    );
}

// Test 2: Get witel with its companies
echo "\n\n2. Witels with Company Count:\n";
echo str_repeat("-", 80) . "\n";

$witels = Witel::withCount('companies')
    ->having('companies_count', '>', 0)
    ->with('region')
    ->orderBy('companies_count', 'desc')
    ->get();

foreach ($witels as $witel) {
    echo sprintf("%-6d | %-20s | %-15s | %d companies\n",
        $witel->idwitels,
        $witel->nama_witels,
        $witel->region->code,
        $witel->companies_count
    );
}

// Test 3: Specific company test
echo "\n\n3. Test Specific Company (NIP003 - UI):\n";
echo str_repeat("-", 80) . "\n";

$company = Company::find('NIP003');
if ($company && $company->witel) {
    echo "Company: {$company->nama_perusahaan}\n";
    echo "Witel: {$company->witel->nama_witels}\n";
    echo "Region: {$company->witel->region->code} - {$company->witel->region->name}\n";
    
    // Test traversing to region
    echo "Region AM Count: {$company->witel->region->accountManagers()->count()}\n";
}

echo "\n=== TESTS COMPLETED ===\n\n";
