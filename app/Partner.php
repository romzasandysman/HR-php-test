<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $primaryKey = 'id';

    public function orders()
    {
        return $this->hasMany('App\Order','order_id');
    }

    public static function getPartnersAll()
    {
        $dbPartners = self::all();
        $arPartners = [];

        foreach ($dbPartners as $dbPartner) {
            $arPartners[$dbPartner->id]['ID'] = $dbPartner->id;
            $arPartners[$dbPartner->id]['NAME'] = $dbPartner->name;
            $arPartners[$dbPartner->id]['EMAIL'] = $dbPartner->email;
        }

        return $arPartners;
    }
}
