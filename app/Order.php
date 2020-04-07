<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $primaryKey = 'id';

    public function partner()
    {
        return $this->belongsTo('App\Partner', 'partner_id');
    }

    public function products()
    {
        return $this->belongsToMany('App\Product', 'order_products', 'order_id', 'product_id')
            ->withPivot('price', 'quantity')->using('App\OrderProduct');
    }
}
