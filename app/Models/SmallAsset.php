<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SmallAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kode_barang', 'nama_barang', 'small_asset_type_id', 'stok',
        'satuan', 'pcs_per_box', 'keterangan',
    ];

    public function assetType(){
        return $this->belongsTo(SmallAssetType::class, 'small_asset_type_id');
    }

    public static function generateKode(): string
    {
        $today = now()->format('Ymd');
        $count = self::whereDate('created_at', today())
                    ->count() + 1;

        return 'BMCK-' . $today . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
