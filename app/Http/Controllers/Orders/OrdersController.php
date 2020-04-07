<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class OrdersController extends Controller
{
    use \App\Classes\Tools\CacheTools;
    use \App\Classes\Vendors\VendorManager;
    use \App\Classes\Orders\Order;
    use \App\Classes\Orders\OrderGroups;

    public function all()
    {
        return view('orders.index')->with([
            'arOrders' => $this->getArAllGroupsOfOrders(),
            'title' => Lang::get('orders.titleAll')
        ]);
    }

    public function showOrder($id)
    {
        return view('orders.edit', [
            'arOrder' => $this->getOrderData($id),
            'arStatusesNames' => $this->getOrdersPossibleStatuses(),
            'arPartners' => $this->preparePartnersForView(),
            'title' => Lang::get('orders.titleOrder')
        ]);
    }

    public function editOrder($id, Request $request)
    {
        $error = '';
        $success = '';
        
        if ($this->updateOrderFromRequest($id, $request)){
            $success = 'Заказ успешно обновлен';
        }else{
            $error = 'Ошибка обновления заказа';
        }

        return view('orders.edit', [
            'arOrder' => $this->getOrderData($id),
            'arStatusesNames' => $this->getOrdersPossibleStatuses(),
            'arPartners' => $this->preparePartnersForView(),
            'successMess' => $success,
            'errorMess' => $error,
            'title' => Lang::get('orders.titleEditOrder')
        ]);
    }

    private function getArAllGroupsOfOrders()
    {
        $arReturn = $this->getAllGroupsOfOrders();

        foreach ($arReturn['GROUPS'] as $keyGroup => &$arOrderGroup){
            $arOrderGroup = $this->fetchAllOrders($arOrderGroup);
            if (!isset($arOrderGroup['ORDERS'])){
                unset($arReturn['GROUPS'][$keyGroup]);
            }else{
                $arReturn['GROUPS'][$keyGroup]['NAME'] = Lang::get('orders.'.$keyGroup);
            }
        }

        return $arReturn;
    }
}
