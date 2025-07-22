<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Reset tahunan setiap 1 Januari
        $schedule->call(function () {
            Log::info('Reset cuti tahunan dijalankan.');
            DB::transaction(function () {
                $updated=DB::table('karyawan')->update([
                    'sisa_cuti_sebelumnya' => DB::raw('sisa_cuti'),
                    'sisa_cuti' => 12,
                    'cuti_expired_at' => now()->startOfYear()->addMonths(6)->toDateString(),
                ]);
        
                DB::table('manager')->update([
                    'sisa_cuti_sebelumnya' => DB::raw('sisa_cuti'),
                    'sisa_cuti' => 12,
                    'cuti_expired_at' => now()->startOfYear()->addMonths(6)->toDateString(),
                ]);

                DB::table('admin')->update([
                    'sisa_cuti_sebelumnya' => DB::raw('sisa_cuti'),
                    'sisa_cuti' => 12,
                    'cuti_expired_at' => now()->startOfYear()->addMonths(6)->toDateString(),
                ]);

                dump($updated);
            });
        })->yearlyOn(1, 1, '00:00');

        $schedule->call(function () {
            Log::info('Reset cuti tahun sebelumnya dijalankan.');
            DB::transaction(function () {
                $tables = ['karyawan', 'manager', 'admin']; 
                foreach ($tables as $table) {
                    $updated=DB::table($table)
                        ->whereDate('cuti_expired_at', '<=', now()->toDateString())
                        ->update(['sisa_cuti_sebelumnya' => 0]);
                }
                dump($updated);
            });
        })->yearlyOn(7, 1, '00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
