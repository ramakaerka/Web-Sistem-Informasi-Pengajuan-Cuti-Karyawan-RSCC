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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('no_pokok');
            $table->string('role')->nullable();
            $table->string('unit');
            $table->bigInteger('unit_id')->nullable();
            $table->bigInteger('jabatan_id')->nullable();
            $table->string('jabatan');
            $table->integer('sisa_cuti')->default(12);
            $table->integer('sisa_cuti_sebelumnya')->nullable();
            $table->text('alamat');                     
            $table->string('email');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->integer('no_telepon');
            $table->string('foto')->nullable();
            $table->string('ttd')->nullable();
            $table->unsignedBigInteger('user_id')->unique(); 
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
