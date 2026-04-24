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

    'barcode',
    'item_code',
    'name',
    'variant',
    'description',

    'quantity',
    'unit',
    'category_name',

    'cost',
    'unit_price',
    'sell_price',

    'discount_percent',
    'discount_amount',

    'line_amount',
    'vat_percent',
    'vat_amount',
    'net_amount',
    'grand_total_amount',

    'created_by',
    'remarks',
];

protected $casts = [
    'quantity'            => 'decimal:4',

    'cost'                => 'decimal:2',
    'unit_price'          => 'decimal:2',
    'sell_price'          => 'decimal:2',

    'discount_percent'    => 'decimal:2',
    'discount_amount'     => 'decimal:2',

    'line_amount'         => 'decimal:2',
    'vat_percent'         => 'decimal:2',
    'vat_amount'          => 'decimal:2',
    'net_amount'          => 'decimal:2',
    'grand_total_amount'  => 'decimal:2',
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
