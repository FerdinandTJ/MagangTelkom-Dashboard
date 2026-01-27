<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\LiniWaktu;
use Illuminate\Support\Facades\DB;

class NKIExportSheet implements FromArray, WithTitle, WithStrictNullComparison, WithEvents
{
    protected $quarter;
    protected $year;
    protected $region;
    protected $quartalsToInclude;

    public function __construct($quarter, $year, $region = null, $quartalsToInclude = null)
    {
        $this->quarter = $quarter;
        $this->year = $year;
        $this->region = $region;
        $this->quartalsToInclude = $quartalsToInclude ?? ["Q{$quarter}"];
    }

    public function array(): array
    {
        // Get first LiniWaktu for this year to get percentage data
        $firstLiniWaktu = LiniWaktu::where('tahun', $this->year)->first();
        
        // Row 1: percentage_result and percentage_proses
        $row1 = array_fill(0, 46, ''); // 46 columns (A to AT)
        $row1[6] = $firstLiniWaktu ? $firstLiniWaktu->percentage_result : 0; // Column G
        $row1[36] = $firstLiniWaktu ? $firstLiniWaktu->percentage_proses : 0; // Column AK
        
        // Row 2: All other percentages
        $row2 = array_fill(0, 46, '');
        if ($firstLiniWaktu) {
            $row2[6] = $firstLiniWaktu->percentage_revenue;      // Column G
            $row2[9] = $firstLiniWaktu->percentage_scaling;      // Column J
            $row2[12] = $firstLiniWaktu->percentage_datin;       // Column M
            $row2[15] = $firstLiniWaktu->percentage_hsi;         // Column P
            $row2[18] = $firstLiniWaktu->percentage_wireline;    // Column S
            $row2[21] = $firstLiniWaktu->percentage_wifi;        // Column V
            $row2[24] = $firstLiniWaktu->percentage_cyc;         // Column Y
            $row2[27] = $firstLiniWaktu->percentage_cr;          // Column AB
            $row2[30] = $firstLiniWaktu->percentage_profit;      // Column AE
            $row2[33] = $firstLiniWaktu->percentage_customer;    // Column AH
            $row2[36] = $firstLiniWaktu->percentage_maps;        // Column AK
            $row2[39] = $firstLiniWaktu->percentage_lop;         // Column AN
            $row2[42] = $firstLiniWaktu->percentage_capability;  // Column AQ
            $row2[45] = $firstLiniWaktu->percentage_cc;          // Column AT
        }
        
        // Row 3: Headers
        $headers = [
            'Quartal',                  // A
            'NIK AM',                   // B
            'Nama AM',                  // C
            'Segment AM',               // D
            'Witel ID',                 // E
            'Total Target Revenue',     // F
            'Total Realisasi Revenue',  // G
            'Ach Revenue Plan',         // H
            'Total Target Scaling',     // I
            'Total Realisasi Scaling',  // J
            'Ach Scaling',              // K
            'Target Datin',             // L
            'Realisasi Datin',          // M
            'Ach Sales Datin',          // N
            'Target HSI',               // O
            'Realisasi HSI',            // P
            'Ach HSI',                  // Q
            'Target Wireline',          // R
            'Realisasi Wireline',       // S
            'Ach Wireline',             // T
            'Target Wifi',              // U
            'Realisasi Wifi',           // V
            'Ach Wifi',                 // W
            'Target CYC',               // X
            'Realisasi CYC',            // Y
            'Ach CYC',                  // Z
            'Target CR',                // AA
            'Realisasi CR',             // AB
            'Ach CR',                   // AC
            'Target Profit',            // AD
            'Realisasi Profit',         // AE
            'Ach Profit',               // AF
            'Target NPS',               // AG
            'Realisasi NPS',            // AH
            'Ach NPS',                  // AI
            'Target MAPS',              // AJ
            'Realisasi MAPS',           // AK
            'Ach MAPS',                 // AL
            'Target LOP',               // AM
            'Realisasi LOP',            // AN
            'Ach LOP',                  // AO
            'Target Capability',        // AP
            'Realisasi Capability',     // AQ
            'Ach Capability',           // AR
            'Target CC',                // AS
            'Realisasi CC',             // AT
            'Ach CC',                   // AU
            'Ach Result',               // AV
            'Ach Proses',               // AW
            'NKI Adjustment',           // AX
        ];
        
        // Build data query
        $query = DB::table('lini_waktu as lw')
            ->join('account_managers as am', 'lw.nik_am', '=', 'am.nik')
            ->join('witels as w', 'am.idwitels', '=', 'w.idwitels')
            ->join('regions as r', 'w.region_id', '=', 'r.id')
            ->leftJoin('lini_waktu_target as lwt', 'lw.id', '=', 'lwt.lini_waktu_id')
            ->leftJoin('target_account_m as t', 'lwt.target_id', '=', 't.id')
            ->leftJoin('account_manager_company as amc', 't.account_manager_company_id', '=', 'amc.id')
            ->whereIn('lw.quartal', $this->quartalsToInclude)
            ->where('lw.tahun', $this->year)
            ->where('r.id', 2)
            ->select([
                'am.nik',
                'am.nama',
                'amc.segment',
                'w.idwitels as witel_id',
                // Aggregate data per AM per quarter
                DB::raw('SUM(t.t_revenue) as total_t_revenue'),
                DB::raw('SUM(lwt.r_revenue) as total_r_revenue'),
                DB::raw('AVG(lwt.ach_revenue_plan) as ach_revenue_plan'),
                DB::raw('SUM(t.t_scalling) as total_t_scalling'),
                DB::raw('SUM(lwt.r_scalling) as total_r_scalling'),
                DB::raw('AVG(lwt.ach_scaling) as ach_scaling'),
                DB::raw('SUM(t.t_datin) as total_t_datin'),
                DB::raw('SUM(lwt.r_datin) as total_r_datin'),
                DB::raw('AVG(lwt.ach_sales_datin) as ach_sales_datin'),
                DB::raw('SUM(t.t_hsi) as total_t_hsi'),
                DB::raw('SUM(lwt.r_hsi) as total_r_hsi'),
                DB::raw('AVG(lwt.ach_hsi) as ach_hsi'),
                DB::raw('SUM(t.t_wireline) as total_t_wireline'),
                DB::raw('SUM(lwt.r_wireline) as total_r_wireline'),
                DB::raw('AVG(lwt.ach_wireline) as ach_wireline'),
                DB::raw('SUM(t.t_wifi) as total_t_wifi'),
                DB::raw('SUM(lwt.r_wifi) as total_r_wifi'),
                DB::raw('AVG(lwt.ach_wifi) as ach_wifi'),
                DB::raw('SUM(t.t_cyc) as total_t_cyc'),
                DB::raw('SUM(lwt.r_cyc) as total_r_cyc'),
                DB::raw('AVG(lwt.ach_cyc) as ach_cyc'),
                DB::raw('SUM(t.t_cr) as total_t_cr'),
                DB::raw('SUM(lwt.r_cr) as total_r_cr'),
                DB::raw('AVG(lwt.ach_cr) as ach_cr'),
                DB::raw('SUM(t.t_profit) as total_t_profit'),
                DB::raw('SUM(lwt.r_profit) as total_r_profit'),
                DB::raw('AVG(lwt.ach_profit) as ach_profit'),
                DB::raw('SUM(t.t_nps) as total_t_nps'),
                DB::raw('SUM(lwt.r_nps) as total_r_nps'),
                DB::raw('AVG(lwt.ach_nps) as ach_nps'),
                DB::raw('SUM(t.t_maps) as total_t_maps'),
                DB::raw('SUM(lwt.r_maps) as total_r_maps'),
                DB::raw('AVG(lwt.ach_maps) as ach_maps'),
                DB::raw('SUM(t.t_lop) as total_t_lop'),
                DB::raw('SUM(lwt.r_lop) as total_r_lop'),
                DB::raw('AVG(lwt.ach_lop) as ach_lop'),
                DB::raw('SUM(t.t_capability) as total_t_capability'),
                DB::raw('SUM(lwt.r_capability) as total_r_capability'),
                DB::raw('AVG(lwt.ach_capability) as ach_capability'),
                DB::raw('SUM(t.t_cc) as total_t_cc'),
                DB::raw('SUM(lwt.r_cc) as total_r_cc'),
                DB::raw('AVG(lwt.ach_cc) as ach_cc'),
                DB::raw('AVG(lwt.ach_result) as ach_result'),
                DB::raw('AVG(lwt.ach_proses) as ach_proses'),
                DB::raw('AVG(lwt.nki_adjustment) as nki_adjustment'),
            ])
            ->groupBy('am.nik', 'am.nama', 'amc.segment', 'w.idwitels', 'r.code')
            ->orderBy('r.code')
            ->orderBy('am.nik');
        
        // Note: Sheet NKI khusus untuk AM dari region TREG HQ 2 (id=2)
        // Region filter dari parameter tidak digunakan untuk sheet ini
        
        $results = $query->get();
        
        // Convert results to array format
        $dataRows = [];
        foreach ($results as $row) {
            $dataRows[] = [
                count($this->quartalsToInclude) > 1 ? 'YTD' : $this->quartalsToInclude[0],  // A - Show YTD or single quarter
                $row->nik,                              // B
                $row->nama,                             // C
                $row->segment ?? '',                    // D
                $row->witel_id,                         // E
                $row->total_t_revenue ?? 0,             // F
                $row->total_r_revenue ?? 0,             // G
                $row->ach_revenue_plan ?? 0,            // H
                $row->total_t_scalling ?? 0,            // I
                $row->total_r_scalling ?? 0,            // J
                $row->ach_scaling ?? 0,                 // K
                $row->total_t_datin ?? 0,               // L
                $row->total_r_datin ?? 0,               // M
                $row->ach_sales_datin ?? 0,             // N
                $row->total_t_hsi ?? 0,                 // O
                $row->total_r_hsi ?? 0,                 // P
                $row->ach_hsi ?? 0,                     // Q
                $row->total_t_wireline ?? 0,            // R
                $row->total_r_wireline ?? 0,            // S
                $row->ach_wireline ?? 0,                // T
                $row->total_t_wifi ?? 0,                // U
                $row->total_r_wifi ?? 0,                // V
                $row->ach_wifi ?? 0,                    // W
                $row->total_t_cyc ?? 0,                 // X
                $row->total_r_cyc ?? 0,                 // Y
                $row->ach_cyc ?? 0,                     // Z
                $row->total_t_cr ?? 0,                  // AA
                $row->total_r_cr ?? 0,                  // AB
                $row->ach_cr ?? 0,                      // AC
                $row->total_t_profit ?? 0,              // AD
                $row->total_r_profit ?? 0,              // AE
                $row->ach_profit ?? 0,                  // AF
                $row->total_t_nps ?? 0,                 // AG
                $row->total_r_nps ?? 0,                 // AH
                $row->ach_nps ?? 0,                     // AI
                ($row->total_t_maps ?? 0) * 100,        // AJ (convert decimal to percentage)
                ($row->total_r_maps ?? 0) * 100,        // AK (convert decimal to percentage)
                $row->ach_maps ?? 0,                    // AL
                $row->total_t_lop ?? 0,                 // AM
                $row->total_r_lop ?? 0,                 // AN
                $row->ach_lop ?? 0,                     // AO
                $row->total_t_capability ?? 0,          // AP
                $row->total_r_capability ?? 0,          // AQ
                $row->ach_capability ?? 0,              // AR
                $row->total_t_cc ?? 0,                  // AS
                $row->total_r_cc ?? 0,                  // AT
                $row->ach_cc ?? 0,                      // AU
                $row->ach_result ?? 0,                  // AV
                $row->ach_proses ?? 0,                  // AW
                $row->nki_adjustment ?? 0,              // AX
            ];
        }
        
        // Return all rows: percentages + header + data
        return array_merge([$row1, $row2, $headers], $dataRows);
    }

    public function title(): string
    {
        return "NKI {$this->year}";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $highestRow = $event->sheet->getHighestRow();
                
                // Format percentage cells in rows 1 and 2
                $event->sheet->getStyle('G1')->getNumberFormat()->setFormatCode('0%');
                $event->sheet->getStyle('AK1')->getNumberFormat()->setFormatCode('0%');
                
                $percentageCols = ['G', 'J', 'M', 'P', 'S', 'V', 'Y', 'AB', 'AE', 'AH', 'AK', 'AN', 'AQ', 'AT'];
                foreach ($percentageCols as $col) {
                    $event->sheet->getStyle("{$col}2")->getNumberFormat()->setFormatCode('0%');
                }
                
                // Make header row bold (row 3)
                $event->sheet->getStyle('A3:AX3')->getFont()->setBold(true);
                
                // Format currency columns (Rp) - F, G, I, J, AM, AN
                $currencyCols = ['F', 'G', 'I', 'J', 'AM', 'AN'];
                foreach ($currencyCols as $col) {
                    $event->sheet->getStyle("{$col}4:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('"Rp "#,##0');
                }
                
                // Format achievement columns as percentage - H,K,N,Q,T,W,Z,AC,AF,AI,AJ,AK,AL,AO,AR,AU,AV,AW,AX
                $achCols = ['H', 'K', 'N', 'Q', 'T', 'W', 'Z', 'AC', 'AF', 'AI', 'AJ', 'AK', 'AL', 'AO', 'AR', 'AU', 'AV', 'AW', 'AX'];
                foreach ($achCols as $col) {
                    $event->sheet->getStyle("{$col}4:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('0%');
                }
                
                // Format number columns (regular numbers without currency)
                $numberCols = ['L', 'M', 'O', 'P', 'R', 'S', 'U', 'V', 'X', 'Y', 'AA', 'AB', 'AD', 'AE', 'AG', 'AH', 'AP', 'AQ', 'AS', 'AT'];
                foreach ($numberCols as $col) {
                    $event->sheet->getStyle("{$col}4:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }
                
                // Auto-size columns
                foreach (range('A', 'Z') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
                // Extended columns (AA to AX)
                foreach (['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX'] as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
