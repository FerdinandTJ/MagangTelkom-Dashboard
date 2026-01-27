<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\Region;
use App\Models\Witel;

class RegionWitelExportSheet implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{
    public function collection()
    {
        $data = collect();
        
        // Get all regions with their witels
        $regions = Region::with('witels')->orderBy('code')->get();
        
        foreach ($regions as $region) {
            foreach ($region->witels as $index => $witel) {
                // Only show region info on first witel row
                $data->push([
                    'kode_region' => $index === 0 ? $region->code : '',
                    'nama_region' => $index === 0 ? $region->name : '',
                    'description_region' => $index === 0 ? $region->description : '',
                    'kode_witel' => $witel->idwitels,
                    'nama_witel' => $witel->nama_witels,
                ]);
            }
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Kode Region',
            'Nama Region',
            'Description Region',
            'Kode Witel',
            'Nama Witel',
        ];
    }

    public function title(): string
    {
        return 'region_and_witel';
    }
}
