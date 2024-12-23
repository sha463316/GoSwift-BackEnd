<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'price',
        'description',
        'discount',
        'amount',
        'quantity',
        'store_id',
    ];


    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    function likes()
    {
        return $this->hasMany(Like::class);
    }

    function orders()
    {
        return $this->hasMany(Order::class);
    }

    function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
