<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceHeader extends Model
{
    use HasFactory;

    protected $table = 'sale_invoice_headers';

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'due_date',
        'customer_id',
        'currency_id',
        'total_amount',
        'discount_amount',
        'payment_method',
        'vat_amount',
        'return_amount',
        'remarks',
    ];
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'return_amount' => 'decimal:2',
    ];

    /**
     * Get all lines for this invoice
     */
    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'sale_invoice_id');
    }

    /**
     * Customer relationship (if you have customers table)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
