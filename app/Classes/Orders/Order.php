<?php
/**
 * Copyright (c) 2020. Martynov A.V. sandysman@mail.ru
 */

namespace App\Classes\Orders;


use App\OrderProduct;
use App\Partner;
use App\Product;
use App\Vendor;
use App\Order as dbOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

trait Order
{

    private static $messNewStatus = 'orders.newStatus';
    private static $messAcceptStatus = 'orders.acceptStatus';
    private static $messFinishStatus = 'orders.finishStatus';
    private static $cacheIdOrderProductsPrefix = 'products_order_';

    private static $requestPathProductId = 'productID';
    private static $requestPathProductQuantity = 'productQuantity';
    private static $requestPathOrderClientEmail = 'email';
    private static $requestPathOrderPartner = 'partner';
    private static $requestPathOrderStatus = 'status';

    private static $arOrderStatuses = [
        0 => '',
        10 => '',
        20 => '',
    ];

    private function getOrderData($orderID)
    {
        if (!$orderID) return null;

        $arOrder = [];
        $arVendors = null;

        $dbOrder = dbOrder::find($orderID);
        $dbPartner =  $dbOrder->partner;

        $arOrder['PRICE'] = 0;
        $arOrder['EMAIL'] = $dbOrder->client_email;
        $arOrder['ID'] = $dbOrder->id;
        $arOrder['PARTNER'] = ['ID' => $dbPartner->id, 'NAME' => $dbPartner->name];
        $arOrder['STATUS'] = $dbOrder->status;

        $this->prepareProductsOfOrder(self::$cacheIdOrderProductsPrefix.$arOrder['ID'], $arOrder, $dbOrder,$arVendors);

        return $arOrder;
    }

    private function preparePartnersForView()
    {
        $arPartners = Partner::getPartnersAll();
        $arReturn = [];

        foreach ($arPartners as $arPartner) {
            $arReturn[$arPartner['ID']] = $arPartner['NAME'];
        }

        return $arReturn;
    }

    private function getOrdersPossibleStatuses()
    {
        foreach (self::$arOrderStatuses as $keyStatus => &$nameStatus) {
            $nameStatus = $this->getStatusName($keyStatus);
        }

        return self::$arOrderStatuses;
    }

    private function getStatusName($statusCode = 0)
    {
        switch ($statusCode){
            case 20:
                return  Lang::get(self::$messFinishStatus);
                break;

            case 10:
                return  Lang::get(self::$messAcceptStatus);
                break;

            default:
                return  Lang::get(self::$messNewStatus);
                break;
        }
    }

    private function getAllOrders()
    {
        return \App\Order::all();
    }

    private function fetchAllOrders($dbOrders)
    {
        if (!$dbOrders) return null;

        $arOrders = [];
        $arVendors = [];
        $arPartners = [];

        foreach ($dbOrders as &$dbOrder){
            $arOrder = [];
            $arOrder['ID'] = $dbOrder->id;
            $arOrder['PRICE'] = 0;
            $arOrder['STATUS'] = $this->getStatusName($dbOrder->status);
            $arOrder['PARTNER'] = $dbOrder->partner_id;

            if (!in_array($arOrder['PARTNER'],$arPartners)){
                $arPartners[] = $arOrder['PARTNER'];
            }

            $this->prepareProductsOfOrder(self::$cacheIdOrderProductsPrefix.$arOrder['ID'],$arOrder,$dbOrder,$arVendors);

            $arOrders['ORDERS'][] = $arOrder;
        }

        asort($arPartners);
        $arOrders['PARTNERS_DATA'] = $this->getPartnersByIds($arPartners);

        if (method_exists($this, 'getVendorsByIds')) {
            asort($arVendors);
            $arOrders['VENDORS'] = $this->getVendorsByIds($arVendors);
        }

        return $arOrders;
    }

    private function prepareProductsOfOrder($cacheIdOrder, &$arOrder, $dbOrder, &$arVendors)
    {
        $arOrder['PRODUCTS'] = [];
        if (method_exists($this, 'getCacheValue')) {
            $arProducts = $this->getCacheValue($cacheIdOrder);
        }

        if (!$arProducts){
            $arProducts = $dbOrder->products;

            if (method_exists($this, 'saveInCacheValue')) {
                $this->saveInCacheValue($cacheIdOrder, $arProducts);
            }
        }

        foreach ($arProducts as $arProduct){
            $arOrderProduct = &$arOrder['PRODUCTS'][$arProduct->id];
            $arOrderProduct['VENDOR'] = $arProduct->vendor_id;
            $arOrderProduct['PRICE'] = $arProduct->pivot->price;
            $arOrderProduct['NAME'] = $arProduct->name;
            $arOrderProduct['QUANTITY'] = isset($arOrderProduct['QUANTITY']) ? : 0;
            $arOrderProduct['QUANTITY'] += $arProduct->pivot->quantity;
            $arOrderProduct['ID'] = $arProduct->id;

            $arOrder['PRICE'] += $arProduct->pivot->price;

            if (isset($arVendors) && !in_array($arOrderProduct['VENDOR'], $arVendors)){
                $arVendors[] = $arOrderProduct['VENDOR'];
            }
        }
    }

    private function getPartnersByIds(array $arPartnersIDs)
    {
        if (!$arPartnersIDs) return null;

        if (method_exists($this, 'getCacheValue')) {
            $cacheId = 'find_partners_'.implode('_',$arPartnersIDs);
            $arReturn = $this->getCacheValue($cacheId);
        }

        if ($arReturn){
            return $arReturn;
        }

        $dbPartners = Partner::find($arPartnersIDs);
        foreach ($dbPartners as $arPartner){
            $arReturn[$arPartner->id]['ID'] = $arPartner->id;
            $arReturn[$arPartner->id]['NAME'] = $arPartner->name;
        }

        if (method_exists($this, 'saveInCacheValue')) {
            $this->saveInCacheValue($cacheId, $arReturn);
        }
        return $arReturn;
    }


    private function updateOrderFromRequest($orderId, Request $request){
        if (!$orderId) return false;

        $arDataOrderFromRequest = $this->getDataFromRequestForUpdateOrder($request);
        if (!$arDataOrderFromRequest) return false;

        if (!$this->updateMainOrderTable($orderId,$arDataOrderFromRequest)) return false;
        if (!$this->updatePivotTableOfOrders($orderId,$arDataOrderFromRequest)) return false;

        Cache::forget(self::$cacheIdOrderProductsPrefix.$orderId);
        return true;
    }

    private function updateMainOrderTable($orderId, $arDataOrderFromRequest)
    {
        $dbOrder = dbOrder::find($orderId);
        $dbOrder->client_email = $arDataOrderFromRequest['EMAIL'];
        $dbOrder->status = $arDataOrderFromRequest['STATUS'];
        $dbOrder->partner_id = $arDataOrderFromRequest['PARTNER'];
        return $dbOrder->save();
    }

    private function updatePivotTableOfOrders($orderId, $arDataOrderFromRequest)
    {
        $dbOrders = OrderProduct::where('order_id', $orderId)->get();
        $arPrices = $this->getPricesOfProducts($arDataOrderFromRequest['PRODUCTS']);

        foreach ($dbOrders as $dbOrder) {
            $quantity = $arDataOrderFromRequest['PRODUCTS'][$dbOrder->product_id]['QUANTITY'];
            $dbOrder->quantity = $quantity;
            $dbOrder->price = intval($quantity) * intval($arPrices[$dbOrder->product_id]['PRICE']);

            if (!$dbOrder->save()){
                return false;
            }
        }

        return true;
    }

    private function getPricesOfProducts(array $arProducts){
        if (!$arProducts) return null;

        $arPrices = [];
        $arProductsIDs = [];
        foreach ($arProducts as $arProduct){
            $arProductsIDs[] = $arProduct['ID'];
        }

        $dbProducts = Product::find($arProductsIDs);
        foreach ($dbProducts as $dbProduct){
            $arPrices[$dbProduct->id]['PRICE'] = $dbProduct->price;
        }

        return $arPrices;
    }

    private function getDataFromRequestForUpdateOrder(Request $request){
        return [
            'EMAIL' => $request->get(self::$requestPathOrderClientEmail),
            'PARTNER' => $request->get(self::$requestPathOrderPartner),
            'PRODUCTS' => $this->getProductsForUpdateOrder($request),
            'STATUS' => $request->get(self::$requestPathOrderStatus),
        ];
    }

    private function getProductsForUpdateOrder(Request $request)
    {
        $arProductsIDs = $request->get(self::$requestPathProductId);
        $arProductsQuantity = $request->get(self::$requestPathProductQuantity);
        $arReturn = [];

        foreach ($arProductsIDs as $key => $productID){
            $arReturn[$productID]['ID'] = $productID;
            $arReturn[$productID]['QUANTITY'] = $arProductsQuantity[$key];
        }

        return $arReturn;
    }
}