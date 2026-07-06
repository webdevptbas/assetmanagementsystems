<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetLoan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'large_asset_id', 'nama_barang', 'jumlah',
        'kondisi_barang', 'tanggal_mulai', 'tanggal_selesai',
        'diketahui_oleh', 'disetujui_oleh', 'status'
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function largeAsset()
    {
        return $this->belongsTo(LargeAsset::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function diketahuiOleh(){
        return $this->belongsTo(User::class, 'diketahui_oleh');
    }

    public function disetujuiOleh(){
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
