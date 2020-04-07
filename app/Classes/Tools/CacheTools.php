<?php
/**
 * Copyright (c) 2020. Martynov A.V. sandysman@mail.ru
 */

namespace App\Classes\Tools;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

trait CacheTools
{
    private function getCacheValue($cacheKey)
    {
        if (Cache::has($cacheKey)){
            return Cache::get($cacheKey);
        }else{
            return null;
        }
    }

    private function saveInCacheValue($cacheKey, $value)
    {
        Cache::add($cacheKey,$value,Carbon::now()->addHours(1));
    }
}