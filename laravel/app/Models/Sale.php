<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Sale extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'branch_id',
        'user_id',
        'customer_id',
        'invoice_number',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'payment_status', // Paid, Partial, Refunded, Unpaid
        'payment_method', // Cash, Card, Bank Transfer, Split
        'hold_status',    // active, completed
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
