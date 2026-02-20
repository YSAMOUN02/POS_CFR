<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
      use HasFactory;

    protected $table = 'sale_invoice_lines';

    protected $fillable = [
        'sale_invoice_id',
        'product_id',

        // Snapshot
        'barcode',
        'item_code',
        'name',
        'variant',
        'description',
        'unit',
        'category_name',

        // Pricing
        'cost',
        'unit_price',
        'sell_price',
        'quantity',

        // Discount
        'discount_percent',
        'discount_amount',

        // Totals
        'line_amount',
        'vat',
        'vat_amount',
        'total_amount',

        'remarks',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'line_amount' => 'decimal:2',
        'vat' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function invoice()
    {
        return $this->belongsTo(InvoiceHeader::class, 'sale_invoice_id');
    }

    public function item()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
