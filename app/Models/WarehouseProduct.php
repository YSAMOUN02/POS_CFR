<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WarehouseProduct extends Model
{
    protected $table = 'warehouse_product'; // your pivot table
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'original_qty',
        'qty',
        'track_lot',
        'lot',
        'expire',
        'control_exp',
        'created_at',
        'updated_at',
    ];
    public $timestamps = true; // optional but recommended

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
