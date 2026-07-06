<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use SoftDeletes;
    protected $fillable = ['nama'];

    public function largeAssets(){
        return $this->hasMany(LargeAsset::class);
    }

    public function getAssetCountAttribute(){
        return $this->largeAssets()->count();
    }
}
