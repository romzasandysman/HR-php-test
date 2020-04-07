@extends('layouts.index')

@section('content')
    <div class="row container-orders">
        @include('orders.parts.nav')
        <div class="tab-content" id="orders">
            @foreach($arOrders['GROUPS'] as $key => $arOrderGroup)
                <div class="row wrap-orders tab-pane show @if($key == 0) active @else fade @endif" id="orders-{{$key}}" role="tabpanel" aria-labelledby="orders-{{$key}}-tab">
                    <div class="row col-md-12">
                        <div class="col-md-1">Номер</div>
                        <div class="col-md-2">Партнер</div>
                        <div class="col-md-2">Сумма</div>
                        <div class="col-md-5">Состав
                            <div class="row">
                                <div class="col-md-4">Нименование</div>
                                <div class="col-md-2">Цена</div>
                                <div class="col-md-4">Производитель</div>
                            </div>
                        </div>
                        <div class="col-md-2">Статус</div>
                    </div>
                    @foreach ($arOrderGroup['ORDERS'] as $arOrder)
                        <div class="col-md-12 order-block">
                            <div class="row order-content">
                                <div class="order-id col-md-1"><a href="/order/{{ $arOrder['ID'] }}">{{ $arOrder['ID'] }}</a></div>
                                <div class="order-partner col-md-2">{{ $arOrderGroup['PARTNERS_DATA'][$arOrder['PARTNER']]['NAME'] }}</div>
                                <div class="order-price col-md-2">{{ $arOrder['PRICE'] }}</div>
                                <div class="order-products row col-md-5">
                                    @foreach ($arOrder['PRODUCTS'] as $arProduct)
                                        <div class="row product-content">
                                            <div class="col-md-4">{{ $arProduct['NAME'] }}</div>
                                            <div class="col-md-2">{{ $arProduct['PRICE'] }}руб.</div>
                                            <div class="col-md-6">{{ $arOrderGroup['VENDORS'][$arProduct['VENDOR']]['NAME'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="order-status col-md-2">{{ $arOrder['STATUS'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endsection
