<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRequest extends Model
{
    protected $fillable = ['employee_id', 'inventory_item_id', 'jumlah'];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    
    public function item(){
        return $this->belongsTo(SmallAsset::class, 'inventory_item_id');
    }
}
