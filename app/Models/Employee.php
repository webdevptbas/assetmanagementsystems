<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama', 'nik', 'nip', 'jabatan', 'divisi',
        'alamat', 'no_bpjs_ketenagakerjaan', 'no_bpjs_kesehatan',
        'jenis_kelamin', 'foto',
        'kontak_darurat_nama',
    ];

    public function transfers(){
        return $this->hasMany(EmployeeTransfer::class)->latest();
    }
}
