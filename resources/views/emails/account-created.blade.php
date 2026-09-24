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
        Registro recibido
    </div>


    <h1 style="
        margin: 0 0 18px;
        color: #ffffff;
        font-size: 24px;
        font-weight: 600;
        line-height: 32px;
        letter-spacing: -0.5px;
    ">
        Tu cuenta está en revisión
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
        margin: 0 0 18px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 22px;
    ">
        Recibimos correctamente tu registro en AERIA Finance.
    </p>


    <p style="
        margin: 0 0 22px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 22px;
    ">
        Tu empresa se encuentra pendiente de aprobación.
        Te enviaremos otro correo cuando tu cuenta sea habilitada.
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
            Prueba gratuita
        </div>

        <div style="
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            line-height: 22px;
        ">
            7 días gratuitos
        </div>

        <div style="
            margin-top: 4px;
            color: #8d8d8d;
            font-size: 12px;
            line-height: 19px;
        ">
            Tu período de prueba comenzará recién cuando
            tu cuenta sea aprobada.
        </div>

    </div>


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
        No necesitás realizar ninguna acción por el momento.
        Te avisaremos por email cuando puedas ingresar.
    </p>


@endsection