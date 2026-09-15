<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>
        Finanzas
    </title>


    @vite(['resources/js/app.js'])
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>



<body>


    <div class="app-layout">





        {{-- SIDEBAR DESKTOP / MOBILE --}}


        @include('layouts.sidebar')







        <div class="main-area">






            {{-- HEADER GLOBAL --}}


            @include('layouts.header')








            <main class="main-content">


                @if (session('error'))
                    <div class="alert-error m-3 m-md-4">


                        <i class="bi bi-exclamation-triangle"></i>


                        {{ session('error') }}


                    </div>
                @endif







                @if (session('success'))
                    <div class="alert-success m-3 m-md-4">


                        <i class="bi bi-check-circle"></i>


                        {{ session('success') }}


                    </div>
                @endif






                @yield('content')






            </main>






        </div>





    </div>




</body>


</html>
