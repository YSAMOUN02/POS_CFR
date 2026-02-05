<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Optional: relation to POS items
    public function posItems()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
