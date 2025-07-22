<?php

namespace App\Exports;

use App\Models\Cuti;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CutiExport implements FromCollection, WithHeadings, WithMapping
{
    protected $query;
    protected $collection;
    protected $tahun;

    public function __construct($query, $tahun = null)
    {
        $this->tahun = $tahun ?? date('Y');
        
        // Eksekusi query dan proses grouping di sini
        $data = $query->get();
        
        // Group by user
        $grouped = $data->groupBy(function($item) {
            return $item->karyawan_id ?? $item->manager_id ?? $item->admin_id;
        });
        
        $this->collection = $grouped;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'NAMA',
            'NIK',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12',
            'SISA CUTI',
        ];
    }

    public function map($group): array
    {
        // Ambil item pertama sebagai representasi user
        $cuti = $group->first();
        
        $userId = $cuti->karyawan_id ?? $cuti->manager_id ?? $cuti->admin_id;
        
        // Kumpulkan semua tanggal dari semua cuti user ini
        $allDates = [];
        foreach ($group as $item) {
            $start = Carbon::parse($item->tanggal_mulai);
            $end = Carbon::parse($item->tanggal_selesai);
            
            $current = $start->copy();
            while ($current->lte($end)) {
                $allDates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }
        
        // Ambil 12 tanggal unik pertama
        $uniqueDates = array_unique($allDates);
        sort($uniqueDates);
        $displayDates = array_slice($uniqueDates, 0, 12);
        
        $data = [
            $cuti->karyawan->nama_lengkap ?? $cuti->manager->nama_lengkap ?? $cuti->admin->nama_lengkap ?? '',
            $cuti->karyawan->nik ?? $cuti->manager->nik ?? $cuti->admin->nik ?? '',
        ];
        
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $displayDates[$i-1] ?? '';
        }
        
        $usedDays = count($allDates);
        $sisaCuti = max(0, 12 - $usedDays);
    
        $data[] = ($usedDays >= 12) ? '0' : $sisaCuti;
        
        return $data;
    }
}
