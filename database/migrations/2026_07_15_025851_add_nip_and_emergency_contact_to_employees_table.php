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
            $table->string('nip')->nullable()->after('nik');
            $table->string('kontak_darurat_nama')->nullable()->after('alamat');
            $table->string('kontak_darurat_nomor')->nullable()->after('kontak_darurat_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['nip', 'kontak_darurat_nama', 'kontak_darurat_nomor']);
        });
    }
};
