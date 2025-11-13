<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=================================\n";
echo "REGIONAL COVERAGE VERIFICATION\n";
echo "=================================\n\n";

// 1. Count AMs per region
$amByRegion = DB::table('account_managers')
    ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
    ->join('regions', 'witels.region_id', '=', 'regions.id')
    ->select('regions.code', 'regions.name', DB::raw('COUNT(account_managers.nik) as am_count'))
    ->groupBy('regions.id', 'regions.code', 'regions.name')
    ->orderBy('regions.code')
    ->get();

echo "Account Managers per Region:\n";
echo "------------------------------\n";
$totalAMs = 0;
foreach ($amByRegion as $row) {
    $percentage = round(($row->am_count / 40) * 100, 2);
    echo sprintf("%-15s %-30s %2d AMs (%5.2f%%)\n", $row->code, $row->name, $row->am_count, $percentage);
    $totalAMs += $row->am_count;
}
echo "------------------------------\n";
echo "TOTAL: $totalAMs AMs\n\n";

// 2. Count targets
$targetCount = DB::table('target_account_m')->count();
$liniWaktuCount = DB::table('lini_waktu')->count();
$pivotCount = DB::table('lini_waktu_target')->count();

echo "Data Records:\n";
echo "------------------------------\n";
echo "Lini Waktu: $liniWaktuCount records\n";
echo "Target Account M: $targetCount records\n";
echo "Lini Waktu-Target Pivot: $pivotCount records\n\n";

// 3. Sample targets for first 3 AMs across quarters
echo "Sample Target Revenue (First 3 AMs - 2024):\n";
echo "------------------------------\n";

$sampleTargets = DB::table('lini_waktu')
    ->join('lini_waktu_target', 'lini_waktu.id', '=', 'lini_waktu_target.lini_waktu_id')
    ->join('target_account_m', 'lini_waktu_target.target_account_m_id', '=', 'target_account_m.id')
    ->join('account_managers', 'lini_waktu.nik_am', '=', 'account_managers.nik')
    ->select('account_managers.nama', 'lini_waktu.tahun', 'lini_waktu.quartal', 'target_account_m.t_revenue')
    ->where('lini_waktu.tahun', 2024)
    ->whereIn('account_managers.nik', ['810001', '820001', '830001'])
    ->orderBy('account_managers.nik')
    ->orderBy('lini_waktu.quartal')
    ->get();

foreach ($sampleTargets as $target) {
    $revenueFormatted = 'Rp ' . number_format($target->t_revenue / 1000000000, 2) . 'B';
    echo sprintf("%-20s %d Q%d: %s\n", $target->nama, $target->tahun, $target->quartal, $revenueFormatted);
}

echo "\n✅ VERIFICATION COMPLETE!\n";
