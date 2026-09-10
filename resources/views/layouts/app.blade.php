<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>
        Finanzas
    </title>


    @vite(['resources/js/app.js'])


</head>


<body>


    <div class="app-layout">



        {{-- SIDEBAR --}}

        @include('layouts.sidebar')




        {{-- CONTENIDO PRINCIPAL --}}

        <div class="main-area">


            {{-- HEADER GLOBAL --}}

            @include('layouts.header')



            <main>
                @if (session('error'))
                    <div class="alert-error m-4">

                        <i class="bi bi-exclamation-triangle"></i>

                        {{ session('error') }}

                    </div>
                @endif


                @if (session('success'))
                    <div class="alert-success m-4">

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
