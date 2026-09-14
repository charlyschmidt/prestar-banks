@extends('layouts.app')


@section('content')
    <div class="page-container">


        <div class="page-header">

            <div>

                <h1>
                    Configuración
                </h1>

                <p>
                    Personalizá el comportamiento del dashboard.
                </p>

            </div>

        </div>



        <div class="form-card">


            <div class="setting-row">

                <div class="setting-info">

                    <div class="setting-icon">

                        <i class="bi bi-volume-up"></i>

                    </div>

                    <div>

                        <h3>
                            Sonido en nuevos movimientos
                        </h3>

                        <p>
                            Reproduce un sonido suave cuando ingresa un movimiento
                            y el dashboard se actualiza en tiempo real.
                        </p>

                    </div>

                </div>


                <div class="form-check form-switch">

                    <input class="form-check-input" type="checkbox" role="switch" id="realtimeSound">

                </div>

            </div>


            <div class="setting-test">

                <button type="button" class="primary-action-button" id="testRealtimeSound">

                    <i class="bi bi-play-circle"></i>

                    Probar sonido

                </button>

            </div>


        </div>


    </div>
@endsection
