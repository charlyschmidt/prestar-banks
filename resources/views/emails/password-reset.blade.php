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
        Seguridad de la cuenta
    </div>


    <h1 style="
        margin: 0 0 18px;
        color: #ffffff;
        font-size: 24px;
        font-weight: 600;
        line-height: 32px;
        letter-spacing: -0.5px;
    ">
        Restablecer contraseña
    </h1>


    <p style="
        margin: 0 0 18px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 22px;
    ">
        Hola {{ $userName }},
    </p>


    <p style="
        margin: 0 0 28px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 22px;
    ">
        Recibimos una solicitud para cambiar la contraseña
        asociada a tu cuenta de AERIA Finance.
    </p>


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
                    href="{{ $resetUrl }}"
                    style="
                        display: inline-block;
                        padding: 13px 22px;
                        color: #050505;
                        font-size: 13px;
                        font-weight: 700;
                        text-decoration: none;
                    "
                >
                    Crear nueva contraseña
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
        margin: 0 0 8px;
        color: #777777;
        font-size: 12px;
        line-height: 19px;
    ">
        Si no solicitaste este cambio, ignorá este correo.
        Tu contraseña actual continuará funcionando.
    </p>


    <p style="
        margin: 0;
        color: #777777;
        font-size: 12px;
        line-height: 19px;
    ">
        Por seguridad, este enlace tiene un tiempo limitado de validez.
    </p>

@endsection