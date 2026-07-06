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
        Schema::table('small_assets', function (Blueprint $table) {
            $table->string('satuan')->nullable()->after('stok');
            $table->Integer('pcs_per_box')->nullable()->after('stok');
            $table->text('keterangan')->nullable()->after('pcs_per_box');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('small_assets', function (Blueprint $table) {
            $table->dropColumn(['satuan', 'pcs_per_box', 'keterangan']);
        });
    }
};
