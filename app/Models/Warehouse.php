<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{



    protected $table = 'warehouses';
    protected $fillable = ['name','location'];

   public function products()
{
    return $this->belongsToMany(Product::class, 'warehouse_product')
                ->withPivot(['qty', 'track_lot', 'lot', 'expire', 'control_exp'])
                ->withTimestamps();
}

}
