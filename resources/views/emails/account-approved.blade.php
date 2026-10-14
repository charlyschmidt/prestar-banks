
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
        Cuenta habilitada
    </div>

    <h1 style="
        margin: 0 0 18px;
        color: #ffffff;
        font-size: 24px;
        font-weight: 600;
        line-height: 32px;
        letter-spacing: -0.5px;
    ">
        Tu cuenta fue aprobada
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
        Tu cuenta de AERIA Finance ya está habilitada.
        ¡Todo está listo para que comiences a utilizar la plataforma!
    </p>

    <div style="
        margin: 0 0 28px;
        padding: 18px 20px;
        background: #111111;
        border: 1px solid #242424;
        border-radius: 10px;
    ">

        <div style="
            margin-bottom: 8px;
            color: #707070;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        ">
            Tu período de prueba
        </div>

        <div style="
            color: #ffffff;
            font-size: 17px;
            font-weight: 600;
            line-height: 24px;
        ">
            7 días gratuitos
        </div>

        <div style="
            margin-top: 8px;
            color: #a0a0a0;
            font-size: 13px;
            line-height: 21px;
        ">
            Tu período de prueba comenzará automáticamente
            cuando ingreses por primera vez a AERIA Finance.

            No perderás ningún día de prueba antes
            de comenzar a utilizar la plataforma.
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
                    href="{{ $loginUrl }}"
                    style="
                        display: inline-block;
                        padding: 13px 22px;
                        color: #050505;
                        font-size: 13px;
                        font-weight: 700;
                        text-decoration: none;
                    "
                >
                    Ingresar a AERIA Finance
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
        Una vez finalizados tus 7 días gratuitos,
        podrás elegir cómo continuar utilizando
        AERIA Finance.
    </p>

@endsection
