<?php


namespace App\Classes\Orders;

use App\Order as dbOrder;

trait OrderGroups
{
    private $dateDBFormat = 'Y-m-d h:i:s';
    private $dateDBFormatCurrentDay = 'Y-m-d 00:00:00';
    private $arStatuses = [
        'newStatus' => 0,
        'acceptStatus' => 10,
        'finishStatus' => 20,
    ];

    private function getAllGroupsOfOrders()
    {
        $arReturn = [];
        $arReturn['GROUPS']['PAST_ORDERS'] = $this->getPastOrders();
        $arReturn['GROUPS']['CURRENT_ORDERS'] = $this->getCurrentOrders();
        $arReturn['GROUPS']['NEW_ORDERS'] = $this->getNewOrders();
        $arReturn['GROUPS']['COMPLETE_ORDERS'] = $this->getCompleteOrders();

        return $arReturn;
    }
    private function getPastOrders()
    {
        return dbOrder::where('delivery_dt', '<', date($this->dateDBFormat))
                        ->where('status', $this->arStatuses['acceptStatus'])
                        ->orderBy('delivery_dt', 'asc')
                        ->paginate(50);
    }

    private function getCurrentOrders()
    {
        return dbOrder::where('delivery_dt', '>', date($this->dateDBFormat))
            ->where('delivery_dt', '<', date($this->dateDBFormat, $this->getNextDayTime()))
            ->where('status', $this->arStatuses['acceptStatus'])
            ->orderBy('delivery_dt', 'desc')
            ->get();
    }

    private function getNewOrders()
    {
        return dbOrder::where('delivery_dt', '>', date($this->dateDBFormat))
            ->where('status', $this->arStatuses['newStatus'])
            ->orderBy('delivery_dt', 'desc')
            ->paginate(50);
    }

    private function getCompleteOrders()
    {
        return dbOrder::where('delivery_dt', '>', date($this->dateDBFormatCurrentDay))
            ->where('delivery_dt', '<', date($this->dateDBFormatCurrentDay, $this->getNextDayTime()))
            ->where('status', $this->arStatuses['finishStatus'])
            ->orderBy('delivery_dt', 'asc')
            ->paginate(50);
    }

    private function getNextDayTime()
    {
        return time() + (60 * 60 * 24);
    }
}