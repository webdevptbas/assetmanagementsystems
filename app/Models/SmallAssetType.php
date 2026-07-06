<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SmallAssetType extends Model
{
    use SoftDeletes;
    protected $fillable = ['nama'];

    public function smallAssets(){
        return $this->hasMany(SmallAsset::class);
    }
}
