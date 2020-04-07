<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'id';

    public function orders()
    {
        return $this->belongsToMany('App\Order', 'order_products', 'product_id', 'order_id')
            ->withPivot('price', 'quantity')->using('App\OrderProduct');
    }

    public function products()
    {
        return $this->hasOne('App\Vendor', 'id');
    }
}
