<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'email',
        'phone',
        'address',
        'subscription_id',
        'status', // pending, approved, rejected, suspended
    ];

    /**
     * Get the subscription tier plan associated with this shop.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the employees belonging to this shop.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}