<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WarehouseProduct extends  Pivot
{
 protected $table = 'warehouse_product'; // your pivot table
    protected $fillable = ['product_id', 'warehouse_id', 'qty'];
    public $timestamps = true; // optional but recommended
}
