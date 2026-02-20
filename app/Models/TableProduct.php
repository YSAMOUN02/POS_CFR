<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableProduct extends Model
{
    use HasFactory;

    protected $table = 'table_products_tables';

  protected $fillable = [
    'table_id',
    'product_id',
    'customer_id',      // add this
    'queue_no',         // add this
    'qty',
    'order_qty',        // add this
    'printed_qty',      // add this
    'price',
    'discount_percent',
    'vat',
    'gross_amount',
    'discount_amount',
    'net_amount',
];


    // RELATIONSHIPS
    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
