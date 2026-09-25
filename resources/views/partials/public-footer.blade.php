@php
    $compact = $compact ?? false;
@endphp


<footer class="aeria-footer {{ $compact ? 'aeria-footer--compact' : '' }}">

    <div class="aeria-footer__inner">


        @if (!$compact)
            <div class="aeria-footer__main">


                {{-- Marca --}}

                <div class="aeria-footer__brand">

                    <a href="{{ route('home') }}" class="aeria-footer__logo">
                        AERIA <span>Finance</span>
                    </a>

                    <p>
                        Control financiero diario, multiempresa,
                        simple y en tiempo real.
                    </p>

                </div>



                {{-- Producto --}}

                <div class="aeria-footer__column">

                    <div class="aeria-footer__title">
                        Producto
                    </div>

                    <a href="{{ route('home') }}">
                        AERIA Finance
                    </a>

                    <a href="{{ route('home') }}#welcome-pricing">
                        Plan
                    </a>

                    @guest
                        <a href="{{ route('login') }}">
                            Iniciar sesión
                        </a>
                    @endguest

                    @auth
                        <a href="{{ route('dashboard') }}">
                            Ir al dashboard
                        </a>
                    @endauth

                </div>



                {{-- Soporte --}}

                <div class="aeria-footer__column">

                    <div class="aeria-footer__title">
                        Soporte
                    </div>

                    <a href="{{ route('contact') }}">
                        Contacto
                    </a>

                    <a href="mailto:ayuda@aeriafinance.com.ar">
                        ayuda@aeriafinance.com.ar
                    </a>

                </div>



                {{-- Legal --}}

                <div class="aeria-footer__column">

                    <div class="aeria-footer__title">
                        Legal
                    </div>

                    <a href="{{ route('terms') }}">
                        Términos y condiciones
                    </a>

                    <a href="{{ route('privacy') }}">
                        Política de privacidad
                    </a>

                </div>


            </div>
        @endif



        {{-- Barra inferior --}}

        <div class="aeria-footer__bottom">


            <div class="aeria-footer__legal-info">

                <span>
                    © {{ date('Y') }} AERIA Finance
                </span>

                <span class="aeria-footer__separator">
                    ·
                </span>

                <span>
                    Carlos A. Schmidt
                </span>

                <span class="aeria-footer__separator">
                    ·
                </span>

                <span>
                    CUIT 20-32838616-0
                </span>

                <span class="aeria-footer__separator">
                    ·
                </span>

                <span>
                    Argentina
                </span>

            </div>



            @if ($compact)
                <nav class="aeria-footer__compact-links">

                    <a href="{{ route('contact') }}" target="_blank" rel="noopener noreferrer">
                        Contacto
                    </a>

                    <a href="{{ route('terms') }}" target="_blank" rel="noopener noreferrer">
                        Términos
                    </a>

                    <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer">
                        Privacidad
                    </a>

                </nav>
            @endif


        </div>


    </div>

</footer>
