@extends('emails.layouts.base')


@section('content')


    <div style="
        margin-bottom: 26px;
        color: #707070;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    ">
        Período de prueba finalizado
    </div>


    <h1 style="
        margin: 0 0 18px;
        color: #ffffff;
        font-size: 24px;
        font-weight: 600;
        line-height: 32px;
        letter-spacing: -0.5px;
    ">
        Tu prueba gratuita terminó
    </h1>


    <p style="
        margin: 0 0 18px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 22px;
    ">
        Hola {{ $recipientName }},
    </p>


    <p style="
        margin: 0 0 22px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 22px;
    ">
        Finalizaron tus 7 días gratuitos de AERIA Finance.
        Para continuar utilizando la plataforma,
        necesitás activar tu suscripción.
    </p>


    <div style="
        margin: 0 0 28px;
        padding: 18px 20px;
        background: #111111;
        border: 1px solid #242424;
        border-radius: 10px;
    ">

        <div style="
            margin-bottom: 6px;
            color: #707070;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        ">
            Tus datos están seguros
        </div>

        <div style="
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            line-height: 22px;
        ">
            No perdiste ninguna información
        </div>

        <div style="
            margin-top: 4px;
            color: #8d8d8d;
            font-size: 12px;
            line-height: 19px;
        ">
            Tus cuentas, movimientos y configuraciones
            permanecen guardados en AERIA Finance.
        </div>

    </div>


    <table
        role="presentation"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="margin: 0 0 30px;"
    >

        <tr>

            <td
                align="center"
                style="
                    background: #ffffff;
                    border-radius: 9px;
                "
            >

                <a
                    href="{{ $subscriptionUrl }}"
                    style="
                        display: inline-block;
                        padding: 13px 22px;
                        color: #050505;
                        font-size: 13px;
                        font-weight: 700;
                        text-decoration: none;
                    "
                >
                    Activar suscripción
                </a>

            </td>

        </tr>

    </table>


    <div style="
        height: 1px;
        margin-bottom: 24px;
        background: #242424;
    "></div>


    <p style="
        margin: 0;
        color: #777777;
        font-size: 12px;
        line-height: 19px;
    ">
        Al activar tu suscripción vas a recuperar el acceso
        y continuar exactamente desde donde estabas.
    </p>


@endsection