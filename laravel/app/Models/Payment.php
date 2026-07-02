<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Payment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'sale_id',
        'amount',
        'payment_method',
        'transaction_id',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
