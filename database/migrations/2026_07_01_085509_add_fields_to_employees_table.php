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
        Schema::table('employees', function (Blueprint $table) {
            $table->text('alamat')->nullable()->after('divisi');
            $table->string('no_bpjs_ketenagakerjaan')->nullable()->after('alamat');
            $table->string('no_bpjs_kesehatan')->nullable()->after('no_bpjs_ketenagakerjaan');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('no_bpjs_kesehatan');
            $table->string('foto')->nullable()->after('jenis_kelamin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'no_bpjs_ketenagakerjaan', 'no_bpjs_kesehatan', 'jenis_kelamin', 'foto']);
        });
    }
};
