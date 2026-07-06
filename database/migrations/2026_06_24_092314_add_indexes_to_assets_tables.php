<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('large_assets', function (Blueprint $table) {
            $table->index('nama_barang');
            $table->index('kode_barang');
            $table->index('asset_type_id');
        });

        Schema::table('small_assets', function (Blueprint $table) {
            $table->index('nama_barang');
            $table->index('kode_barang');
            $table->index('small_asset_type_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('nama');
            $table->index('nik');
        });
    }

    public function down(): void
    {
        Schema::table('large_assets', function (Blueprint $table) {
            $table->dropIndex(['nama_barang']);
            $table->dropIndex(['kode_barang']);
            $table->dropIndex(['asset_type_id']);
        });

        Schema::table('small_assets', function (Blueprint $table) {
            $table->dropIndex(['nama_barang']);
            $table->dropIndex(['kode_barang']);
            $table->dropIndex(['small_asset_type_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['nik']);
        });
    }
};
