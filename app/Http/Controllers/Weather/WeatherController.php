<?php

namespace App\Http\Controllers\Weather;

use http\Env;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller,
    Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class WeatherController extends Controller
{

    private $apiWeather = 'https://api.weather.yandex.ru/v1/forecast';
    private $strUrlOfYandexWeather = 'https://yandex.ru/pogoda/';
    private $strBryanskLat = '53.243562';
    private $strBryanskLon = '34.363407';
    private $proxyEnv = 'krk-iwsva101.corp.suek.ru:8080';
    private $cacheWeatherPrefix = 'weather_for_';


    public function getWeatherByLatLong($lat, $long){
        return view('weather', ['weatherData' => $this->getDataOfWeather($lat, $long)]);
    }

    public function getWeatherByOfBryansk(){
        return view('weather', ['weatherData' => $this->getDataOfWeather($this->strBryanskLat, $this->strBryanskLon)]);
    }

    private function getDataOfWeather($lat, $long){
        if (!$lat || !$long) return null;

        $cacheId = $this->cacheWeatherPrefix.$lat.$long;
        $arDatAOfWeather = $this->getDataWeatherFromCache($cacheId);

        if (!$arDatAOfWeather){
            $arDatAOfWeather = $this->sendRequestToYandexApi($lat, $long);
            $this->saveDataOfWeatherInCache($cacheId, $arDatAOfWeather);
        }

        if ($arDatAOfWeather){
            return $this->setNameOfCity($this->makeArrayForJsonDataWeather($arDatAOfWeather));
        }else{
            return null;
        }
    }

    private function getDataWeatherFromCache($key = ''){
        if (Cache::has($key)) {
            return Cache::get($key);
        }else{
            return null;
        }
    }

    private function saveDataOfWeatherInCache($key, $arDataOfWeathe){
        Cache::add($key, $arDataOfWeathe, Carbon::now()->addHours(1));
    }

    private function sendRequestToYandexApi($lat, $long){
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $this->apiWeather, [
                'query' => ['lat' =>  $lat, 'lon' => $long],
                'headers' => ['X-Yandex-API-Key' => \env('YANDEX_API_KEY')],
                'proxy' => $this->proxyEnv,
                'verify' => false
            ]
        );

        if ($res->getStatusCode() == 200){
            return json_decode($res->getBody());
        }else{
            return null;
        }
    }

    private function makeArrayForJsonDataWeather($arData){
        if (!$arData) return null;

        $arData = (array)$arData;
        $arData['info'] = (array)$arData['info'];
        if (isset($arData['info']['tzinfo'])){
            $arData['info']['tzinfo'] = (array)$arData['info']['tzinfo'];
        }

        $arData['fact'] = (array)$arData['fact'];
        if (isset($arData['fact']['accum_prec'])){
            $arData['fact']['accum_prec'] = (array)$arData['fact']['accum_prec'];
        }
        return $arData;
    }

    private function setNameOfCity($arDataOfWeather){
        if ($arDataOfWeather['info']['lat'] == $this->strBryanskLat & $arDataOfWeather['info']['lon'] == $this->strBryanskLon){
            $arDataOfWeather['NAME'] = 'Брянска';
        }else{
            $arDataOfWeather['NAME'] = $arDataOfWeather['info']['tzinfo']['name'];
        }

        return $arDataOfWeather;
    }
}
