@extends('layouts.index')

@section('content')
    <div class="row container-order-edit">
        <div class="row">
            <div class="col-md-12 title m-b-md">
                Редактирование заказа
            </div>
        </div>

        <div class="form row">
            <div class="col-md-12">
                @include('orders.parts.messages')
                {{ Form::open(['action' => ['Orders\OrdersController@editOrder', $arOrder['ID']]]) }}
                @csrf
                <div class="form-group">
                    {{ Form::label('email', 'email клинента:') }}
                    {{ Form::text('email', $arOrder['EMAIL'],[
                        'class' => 'form-control',
                        'required' => true,
                        'autofocus' => true,
                        ])
                    }}
                </div>
                <div class="form-group">
                    {{ Form::label('partner', 'Партнер:')}}
                    {{ Form::select('partner', $arPartners,$arOrder['PARTNER']['ID'])}}
                </div>
                <div class="form-group">
                    {{ Form::label('products', 'Продукты') }}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-4">Наименование</div>
                            <div class="col-md-4">Количество</div>
                        </div>
                        <div class="wrap-of-products col-md-12">
                            @foreach($arOrder['PRODUCTS'] as $arProduct)
                                <div class="product-item col-md-12">
                                    <div class="col-md-4">
                                        {{ Form::hidden('productID[]', $arProduct['ID'])}}
                                        {{ Form::label('productQuantity', $arProduct['NAME']) }}
                                    </div>
                                    <div class="col-md-4">
                                        {{ Form::text('productQuantity[]', $arProduct['QUANTITY'],[
                                            'class' => 'form-control',
                                            'required' => true,
                                            'autofocus' => true,
                                            ]) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {{ Form::label('status', 'статус заказа:') }}
                    {{ Form::select('status', $arStatusesNames, $arOrder['STATUS'])}}
                </div>
                <div class="form-group">
                    {{ Form::label('price', 'Стоимость заказа:') }}
                    {{ Form::text('price', $arOrder['PRICE'],[
                        'class' => 'form-control',
                        'required' => true,
                        'autofocus' => true,
                        'disabled' => true
                        ])
                    }}
                </div>
                <div class="form-group form-actions">
                    {{ Form::submit('Сохранить изменения в заказе', array('class' => 'btn btn-primary')) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection
