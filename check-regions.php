<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=================================\n";
echo "CHECKING ALL REGIONS\n";
echo "=================================\n\n";

// 1. All regions
$regions = DB::table('regions')
    ->orderBy('code')
    ->get();

echo "All Regions in Database:\n";
echo "------------------------------\n";
foreach ($regions as $region) {
    echo sprintf("%-15s %s\n", $region->code, $region->name);
}
echo "\n";

// 2. Regions with AM count
$regionsWithAMs = DB::table('regions')
    ->leftJoin('witels', 'regions.id', '=', 'witels.region_id')
    ->leftJoin('account_managers', 'witels.idwitels', '=', 'account_managers.idwitels')
    ->select('regions.code', 'regions.name', DB::raw('COUNT(account_managers.nik) as am_count'))
    ->groupBy('regions.id', 'regions.code', 'regions.name')
    ->orderBy('regions.code')
    ->get();

echo "Regions with AM Count:\n";
echo "------------------------------\n";
foreach ($regionsWithAMs as $region) {
    $status = $region->am_count > 0 ? '✅' : '❌';
    echo sprintf("%s %-15s %-30s %2d AMs\n", $status, $region->code, $region->name, $region->am_count);
}
echo "\n";

// 3. Check witels per region
$witelsByRegion = DB::table('regions')
    ->leftJoin('witels', 'regions.id', '=', 'witels.region_id')
    ->select('regions.code', 'regions.name', DB::raw('COUNT(witels.idwitels) as witel_count'))
    ->groupBy('regions.id', 'regions.code', 'regions.name')
    ->orderBy('regions.code')
    ->get();

echo "Witels per Region:\n";
echo "------------------------------\n";
foreach ($witelsByRegion as $region) {
    echo sprintf("%-15s %-30s %2d Witels\n", $region->code, $region->name, $region->witel_count);
}
echo "\n";

echo "✅ CHECK COMPLETE!\n";
