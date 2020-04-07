@foreach($arOrders['GROUPS'] as $key => $arOrderGroup)
    <ul class="nav nav-tabs">
        <li class="nav-item @if($key == 0) active @endif">
            <a class="nav-link" data-toggle="tab" href="#orders-{{$key}}">{{$arOrderGroup['NAME']}}</a>
        </li>
    </ul>
@endforeach