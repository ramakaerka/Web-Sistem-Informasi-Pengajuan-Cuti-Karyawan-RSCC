<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use App\Models\HariLibur;

class HariLiburHelper
{
    public static function ambilDanSimpanHariLibur()
    {
        $response = Http::get('https://api-harilibur.vercel.app/api');

        if ($response->successful()) {
            $data = $response->json();

            foreach ($data as $libur) {
                HariLibur::updateOrCreate(
                    ['tanggal' => $libur['holiday_date']],
                    [
                        'nama' => $libur['holiday_name'],
                        'is_nasional' => $libur['is_national_holiday']
                    ]
                );
            }
        }
    }
}