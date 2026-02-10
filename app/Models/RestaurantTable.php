<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
   use HasFactory;

    protected $table = 'restaurant_tables';

    protected $fillable = [
        'name',
        'status', // e.g., available, occupied
        'location',
    ];

    // RELATIONSHIPS
    public function tableProducts()
    {
        return $this->hasMany(TableProduct::class, 'table_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'table_products_tables', 'table_id', 'product_id')
                    ->withPivot([
                        'qty',
                        'price',
                        'discount_percent',
                        'vat',
                        'gross_amount',
                        'discount_amount',
                        'net_amount'
                    ])
                    ->withTimestamps();
    }
}
