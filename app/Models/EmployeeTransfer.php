<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTransfer extends Model
{
    protected $fillable = [
        'employee_id', 'jabatan_lama', 'divisi_lama', 'jabatan_baru',
        'divisi_baru', 'divisi_baru', 'tanggal_efektif', 'keterangan',
        'attachment', 'attachment_name',
    ];

    protected $casts = [
        'tanggal_efektif' => 'date',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
