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
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignid('employee_id')->constrained('employees');
            $table->string('nama_barang');
            $table->integer('jumlah')->default(1);
            $table->string('kondisi_barang');
            $table->datetime('tanggal_mulai');
            $table->datetime('tanggal_selesai');
            $table->foreignid('diketahui_oleh')->nullable()->constrained('users');
            $table->foreignid('disetujui_oleh')->nullable()->constrained('users');
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_loans');
    }
};
