<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama', 'nik', 'jabatan', 'divisi',
        'alamat', 'no_bpjs_ketenagakerjaan', 'no_bpjs_kesehatan',
        'jenis_kelamin', 'foto',    
    ];

    public function transfers(){
        return $this->hasMany(EmployeeTransfer::class)->latest();
    }
}
