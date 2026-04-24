<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $fillable = [
        'expense_date',
        'product_id',
        'expense_code',
        'expense_name',
        'qty',
        'unit_price',
        'amount',
        'payment_method',
        'note',
        'status',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'status' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
