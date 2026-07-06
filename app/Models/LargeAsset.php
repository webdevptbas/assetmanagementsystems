<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LargeAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kode_barang', 'plat_nomor', 'nama_barang',
        'asset_type_id', 'tanggal_pembelian', 'garansi',
        'warranty_end_date', 'serial_number', 'note',
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
        'warranty_end_date' => 'date',
        'garansi'           => 'boolean',
    ];

    public function assetType(){
        return $this->belongsTo(AssetType::class);
    }

    public function activeLoans()
    {
        return $this->hasMany(\App\Models\AssetLoan::class)->where('status', 'dipinjam');
    }

    public function isAvailable(): bool
    {
        return $this->activeLoans()->count() === 0;
    }

    public static function generateKode(): string
    {
        $today = now()->format('Ymd');
        $count = self::whereDate('created_at', today())
                    ->count() + 1;

        return 'BMC-' . $today . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
