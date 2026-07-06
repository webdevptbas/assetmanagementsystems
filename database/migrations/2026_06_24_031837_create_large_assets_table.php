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
        Schema::create('large_assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->nullable();
            $table->string('plat_nomor')->nullable();
            $table->string('nama_barang');
            $table->foreignId('asset_type_id')->constrained('asset_types');
            $table->date('tanggal_pembelian');
            $table->boolean('garansi')->default(false);
            $table->date('warranty_end_date')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('note');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('large_assets');
    }
};
