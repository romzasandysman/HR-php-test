<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @if (isset($setToken))
            <meta name="csrf-token" content="{{ csrf_token() }}">
        @endif

        <title>{{$title}}</title>

        <!-- Fonts -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="row header">
                    <div class="wrap-title col-md-12">
                        <div class="title-block">
                            {{$title}}
                        </div>
                    </div>
            </div>
            <div class="flex-center position-ref full-height">
                <div class="content">
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
