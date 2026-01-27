<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\AccountManager;
use App\Models\LiniWaktu;
use App\Models\LiniWaktuTarget;
use Illuminate\Support\Facades\DB;

class TWSExportSheet implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison, WithEvents
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

    public function collection()
    {
        // Build query untuk mendapatkan semua data AM beserta target dan realisasi
        $query = DB::table('account_managers as am')
            ->join('witels as w', 'am.idwitels', '=', 'w.idwitels')
            ->join('regions as r', 'w.region_id', '=', 'r.id')
            ->join('account_manager_company as amc', 'am.nik', '=', 'amc.nik_am')
            ->join('companies as c', 'amc.nip_nas', '=', 'c.nip_nas')
            ->join('witels as company_witel', 'c.idwitels', '=', 'company_witel.idwitels')
            ->join('regions as company_region', 'company_witel.region_id', '=', 'company_region.id')
            ->join('target_account_m as t', 'amc.id', '=', 't.account_manager_company_id')
            ->join('lini_waktu_target as lwt', 't.id', '=', 'lwt.target_id')
            ->join('lini_waktu as lw', 'lwt.lini_waktu_id', '=', 'lw.id')
            ->whereIn('lw.quartal', $this->quartalsToInclude)
            ->where('lw.tahun', $this->year)
            ->select([
                'am.nik',                           // A
                'am.nama',                          // B
                'am.posisi',                        // C
                'r.id as region_id',                // D
                'w.idwitels as id_witels',          // E
                'r.name as region_name',            // F
                'w.nama_witels as witel_name',      // G
                'am.no_gsm',                        // H
                'amc.pembagian',                    // I
                'c.nip_nas',                        // J
                'c.nama_perusahaan',                // K
                'company_region.name as company_region_name',  // L
                'company_witel.nama_witels as company_witel_name',  // M
                'company_witel.idwitels as company_witel_id',  // N
                'company_region.id as company_region_id',      // O
                'amc.proporsi',                     // P
                DB::raw('SUM(t.t_revenue) as t_revenue'),      // Q
                DB::raw('SUM(t.t_sustain) as t_sustain'),      // R
                DB::raw('SUM(t.t_scalling) as t_scalling'),    // S
                DB::raw('SUM(t.t_ngtma) as t_ngtma'),          // T
                DB::raw('SUM(lwt.r_revenue) as r_revenue'),    // U
                DB::raw('SUM(lwt.r_sustain) as r_sustain'),    // V
                DB::raw('SUM(lwt.r_scalling) as r_scalling'),  // W
                DB::raw('SUM(lwt.r_ngtma) as r_ngtma'),        // X
            ])
            ->groupBy(
                'am.nik', 'am.nama', 'am.posisi', 
                'r.id', 'w.idwitels', 'r.name', 'w.nama_witels', 
                'am.no_gsm', 'amc.pembagian', 'c.nip_nas', 'c.nama_perusahaan',
                'company_region.name', 'company_witel.nama_witels',
                'company_witel.idwitels', 'company_region.id', 'amc.proporsi'
            )
            ->orderBy('r.code')
            ->orderBy('w.idwitels')
            ->orderBy('am.nik')
            ->orderBy('c.nip_nas');

        // Apply region filter if specified
        if ($this->region && $this->region !== 'ALL') {
            $query->where('r.code', $this->region);
        }

        $results = $query->get();

        // Format the data
        return $results->map(function ($row) {
            return [
                $row->nik,
                $row->nama,
                $row->posisi,
                $row->region_id,
                $row->id_witels,
                $row->region_name,          // F
                $row->witel_name,           // G
                $row->no_gsm,
                $row->pembagian,
                $row->nip_nas,
                $row->nama_perusahaan,
                $row->company_region_name,  // L
                $row->company_witel_name,   // M
                $row->company_witel_id,
                $row->company_region_id,
                $row->proporsi,
                $this->formatCurrency($row->t_revenue),
                $this->formatCurrency($row->t_sustain),
                $this->formatCurrency($row->t_scalling),
                $this->formatCurrency($row->t_ngtma),
                $this->formatCurrency($row->r_revenue),
                $this->formatCurrency($row->r_sustain),
                $this->formatCurrency($row->r_scalling),
                $this->formatCurrency($row->r_ngtma),
            ];
        });
    }

    /**
     * Format currency values to Indonesian format (with dot separator)
     */
    protected function formatCurrency($value)
    {
        if (is_null($value) || $value == 0) {
            return 0;
        }
        
        // Return as number, Excel will handle formatting
        return (float) $value;
    }

    public function headings(): array
    {
        return [
            'NIK',                      // A
            'Nama AM',                  // B
            'Posisi',                   // C
            'Region ID',                // D
            'ID Witel',                 // E
            '',                         // F - blank
            '',                         // G - blank
            'No. GSM',                  // H
            'Pembagian',                // I
            'NIP NAS',                  // J
            'Nama Perusahaan',          // K
            '',                         // L - blank
            '',                         // M - blank
            'Company Witel ID',         // N
            'Company Region ID',        // O
            'Proporsi (%)',             // P
            'Target Revenue',           // Q
            'Target Sustain',           // R
            'Target Scaling',           // S
            'Target NGTMA',             // T
            'Realisasi Revenue',        // U
            'Realisasi Sustain',        // V
            'Realisasi Scaling',        // W
            'Realisasi NGTMA',          // X
        ];
    }

    public function title(): string
    {
        return "TWS {$this->year}";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Add Q and Year info in row 1
                $event->sheet->setCellValue('A1', "Q{$this->quarter} {$this->year}");
                
                // Make header row bold (row 2)
                $event->sheet->getStyle('A2:X2')->getFont()->setBold(true);
                
                // Format currency columns (Q-X)
                $highestRow = $event->sheet->getHighestRow();
                $currencyColumns = ['Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'];
                
                foreach ($currencyColumns as $col) {
                    $event->sheet->getStyle("{$col}3:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }
                
                // Auto-size columns
                foreach (range('A', 'X') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
