<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Customer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'name',
        'email',
        'phone',
        'address',
        'customer_group',
        'loyalty_points',
        'balance',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
