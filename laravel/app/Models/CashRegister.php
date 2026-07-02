<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class CashRegister extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'branch_id',
        'user_id',
        'opening_balance',
        'closing_balance',
        'status', // open, closed
        'opened_at',
        'closed_at',
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
}
