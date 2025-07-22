<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proses_cuti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable(); 
            $table->unsignedBigInteger('admin_id')->nullable(); 
            $table->string('jenis_cuti');
            $table->date('tanggal_pengajuan')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('tanggal_disetujui')->nullable();
            $table->integer('jumlah_hari')->nullable();
            $table->string('status_manager')->default('pending');
            $table->string('status_admin')->default('pending');
            $table->string('validitas_suket')->nullable();
            $table->text('alasan');
            $table->text('alasan_penolakan')->nullable();
            $table->string('surat_keterangan')->nullable();
            $table->string('penalty_gaji')->nullable();
            $table->string('ttd_atasan')->nullable();
            $table->string('ttd_atasan_admin')->nullable();
            $table->string('ttd_kasi')->nullable();
            $table->string('ttd_kabag')->nullable();
            $table->string('ttd_admin')->nullable();
            $table->string('ttd_direktur')->nullable();
            $table->string('ttd_pt')->nullable();
            $table->string('ttd_pemohon')->nullable();
            $table->bigInteger('apr_atasan_id')->nullable();
            $table->bigInteger('apr_atasan_admin_id')->nullable();
            $table->bigInteger('apr_admin_id')->nullable();
            $table->bigInteger('apr_kasi_id')->nullable();
            $table->bigInteger('apr_kabag_id')->nullable();
            $table->bigInteger('apr_direktur_id')->nullable();
            $table->bigInteger('apr_pt_id')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->onDelete('cascade');

            $table->foreign('manager_id')->references('id')->on('manager')->onDelete('set null');

            $table->foreign('admin_id')->references('id')->on('admin')->onDelete('set null');

            $table->foreign('apr_atasan_id')->references('id')->on('manager')->onDelete('set null');
            $table->foreign('apr_atasan_admin_id')->references('id')->on('manager')->onDelete('set null');
            $table->foreign('apr_admin_id')->references('id')->on('admin')->onDelete('set null');
            $table->foreign('apr_kasi_id')->references('id')->on('manager')->onDelete('set null');
            $table->foreign('apr_kabag_id')->references('id')->on('manager')->onDelete('set null');
            $table->foreign('apr_direktur_id')->references('id')->on('manager')->onDelete('set null');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proses_cuti');
    }
};
