<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'monthly_price',
        'user_limit',
        'product_limit',
        'branch_limit',
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }
}
