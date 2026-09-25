<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="Contactá con AERIA Finance. Estamos disponibles para responder consultas sobre nuestra plataforma de gestión financiera.">

    <title>Contacto | AERIA Finance</title>

    @vite(['resources/css/welcome.css', 'resources/css/legal.css', 'resources/css/footer.css', 'resources/js/welcome.js'])

</head>

<body>


    <main class="legal-page">


        {{-- Fondo AERIA --}}

        <canvas id="welcome-particles" class="welcome-particles" aria-hidden="true"></canvas>



        <div class="legal-container">


            {{-- Header --}}

            <header class="legal-header">

                <a href="{{ route('home') }}" class="legal-brand">
                    AERIA <span>Finance</span>
                </a>

                <a href="{{ route('home') }}" class="legal-back">
                    Volver al inicio
                </a>

            </header>



            {{-- Contenido --}}

            <section class="legal-content">


                <div class="legal-eyebrow">
                    Contacto
                </div>


                <h1>
                    ¿En qué podemos ayudarte?
                </h1>


                <p class="legal-intro">
                    Si tenés una consulta sobre AERIA Finance,
                    nuestro servicio o tu cuenta, escribinos a través
                    del formulario.
                </p>



                <div class="contact-layout">


                    {{-- Formulario --}}

                    <div class="contact-card">


                        @if (session('success'))
                            <div class="contact-success">
                                {{ session('success') }}
                            </div>
                        @endif



                        @if ($errors->any())

                            <div class="contact-errors">

                                <strong>
                                    Revisá los datos ingresados.
                                </strong>

                                <ul>

                                    @foreach ($errors->all() as $error)
                                        <li>
                                            {{ $error }}
                                        </li>
                                    @endforeach

                                </ul>

                            </div>

                        @endif



                        <form method="POST" action="{{ route('contact.store') }}" class="contact-form">

                            @csrf


                            <div class="contact-field">

                                <label for="name">
                                    Nombre
                                </label>

                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                    maxlength="100" autocomplete="name" required>

                            </div>



                            <div class="contact-field">

                                <label for="email">
                                    Correo electrónico
                                </label>

                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    maxlength="150" autocomplete="email" required>

                            </div>



                            <div class="contact-field">

                                <label for="message">
                                    Mensaje
                                </label>

                                <textarea id="message" name="message" rows="6" maxlength="5000" required>{{ old('message') }}</textarea>

                            </div>



                            <button type="submit" class="contact-submit">
                                Enviar mensaje
                            </button>


                        </form>


                    </div>



                    {{-- Datos de contacto --}}

                    <aside class="contact-info">


                        <div class="contact-info-label">
                            Correo electrónico
                        </div>

                        <a href="mailto:ayuda@aeriafinance.com.ar" class="contact-email">
                            ayuda@aeriafinance.com.ar
                        </a>


                        <p>
                            Utilizá este canal para consultas comerciales,
                            administrativas o relacionadas con el uso
                            de AERIA Finance.
                        </p>



                        <div class="contact-legal">

                            <div class="contact-info-label">
                                Información legal
                            </div>

                            <strong>
                                Carlos Alberto Schmidt
                            </strong>

                            <span>
                                CUIT 20-32838616-0
                            </span>

                            <span>
                                Argentina
                            </span>

                        </div>


                    </aside>


                </div>


            </section>


            @include('partials.public-footer')


        </div>


    </main>


</body>

</html>
