<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToProsesCutiTable extends Migration
{
    public function up()
    {
        Schema::table('proses_cuti', function (Blueprint $table) {
            $table->softDeletes(); // Menambahkan kolom deleted_at
        });
    }

    public function down()
    {
        Schema::table('proses_cuti', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
