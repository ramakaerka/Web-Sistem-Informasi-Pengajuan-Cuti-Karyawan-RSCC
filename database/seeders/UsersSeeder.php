<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Cuti;
use Faker\Factory as Faker;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $faker = Faker::create();
        // $role = ['karyawan'];
        // $unitKaryawan = ['Rawat Jalan & Home Care','Rawat Inap Lantai 2','Rawat Inap Lantai 3','Kamar Operasi & CSSD','Maternal & Perinatal','Hemodialisa','ICU','IGD'];
        // $jabatanKaryawan = ['karyawan'];
        // $jenkel=['L','P'];
        // $jenisCuti=['cuti_sakit','cuti_melahirkan','cuti_panjang','cuti_tahunan','cuti_menikah','cuti_kelahiran_anak','cuti_pernikahan_anak','cuti_mati_sedarah','cuti_mati_klg_serumah','cuti_mati_ortu','cuti_lainnya'];

        // for ($i = 0; $i < 8; $i++) {
        //     $user=User::create([
        //         'name' => $faker->name,
        //         'username' => $faker->username,
        //         'role' => $faker->randomElement($role),
        //         'password' => bcrypt('password'),
        //     ]);

        //     $karyawan=$user->karyawan()->create([
        //         'nama_lengkap' =>$user->name,
        //         'no_pokok' => $faker->randomNumber(8),
        //         'role'=> $user->role,
        //         'unit'=>$faker->randomElement($unitKaryawan),
        //         'jabatan'=>'karyawan',
        //         'sisa_cuti'=>12,
        //         'alamat'=>$faker->address(),
        //         'email'=>$faker->email,
        //         'jenis_kelamin'=>$faker->randomElement($jenkel),
        //         'no_telepon'=>$faker->phoneNumber,
        //         'user_id'=>$user->id
        //     ]);

        //     $karyawan->cuti()->create([
        //         'karyawan_id'=>$karyawan->id,
              
        //         'jenis_cuti'=>$faker->randomElement($jenisCuti),
        //         'tanggal_pengajuan'=>$faker->date('Y-m-d'),
        //         'tanggal_mulai'=>$faker->date('Y-m-d'),
        //         'tanggal_selesai'=>$faker->date('Y-m-d'),
        //         'jumlah_hari'=> $faker->numberBetween(1,12),
        //         'status_manager'=>'pending',
        //         'status_admin'=>'pending',
        //         'alasan'=>$faker->text(50)
        //     ]);
        // }
        $faker = Faker::create();
        $role = ['karyawan'];
        $unitKaryawan = ['Laboratorium','Radiologi','Gizi','Rekam Medis','Laundry','Pendaftaran','Farmasi','Rehabilitasi Medis'];
        $jabatanKaryawan = ['karyawan'];
        $statusManager = ['approved:Kanit Laboratorium','approved:Kanit Radiologi','approved:Kanit Gizi','approved:Kanit Rekam Medis','approved:Kanit Laundry','approved:Kanit Pendaftaran','approved:Kanit Farmasi','approved:Kanit Rehabilitasi Medis'];
        $jenkel=['L','P'];
        $jenisCuti=['cuti_sakit','cuti_melahirkan','cuti_panjang','cuti_tahunan','cuti_menikah','cuti_kelahiran_anak','cuti_pernikahan_anak','cuti_mati_sedarah','cuti_mati_klg_serumah','cuti_mati_ortu','cuti_lainnya'];

        for ($i = 0; $i < 10; $i++) {
            $user=User::create([
                'name' => $faker->name,
                'username' => $faker->username,
                'role' => $faker->randomElement($role),
                'password' => bcrypt('password'),
            ]);

            $karyawan=$user->karyawan()->create([
                'nama_lengkap' =>$user->name,
                'no_pokok' => $faker->randomNumber(8),
                'role'=> $user->role,
                'unit'=>$unitKaryawan[$i % count($unitKaryawan)],
                'jabatan'=>'karyawan',
                'sisa_cuti'=>12,
                'alamat'=>$faker->address(),
                'email'=>$faker->email,
                'jenis_kelamin'=>$faker->randomElement($jenkel),
                'no_telepon'=>$faker->phoneNumber,
                'user_id'=>$user->id
            ]);

            $karyawan->cuti()->create([
                'karyawan_id'=>$karyawan->id,
              
                'jenis_cuti'=>$faker->randomElement($jenisCuti),
                'tanggal_pengajuan'=>$faker->date('Y-m-d'),
                'tanggal_mulai'=>$faker->date('Y-m-d'),
                'tanggal_selesai'=>$faker->date('Y-m-d'),
                'jumlah_hari'=> $faker->numberBetween(1,12),
                'status_manager'=>$statusManager[$i % count($statusManager)],
                'status_admin'=>'pending',
                'alasan'=>$faker->text(50)
            ]);
        }
    }
}
