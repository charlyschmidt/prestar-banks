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
        Nueva consulta
    </div>


    <h1 style="
        margin: 0 0 18px;
        color: #ffffff;
        font-size: 24px;
        font-weight: 600;
        line-height: 32px;
        letter-spacing: -0.5px;
    ">
        Nuevo mensaje desde AERIA Finance
    </h1>


    <p style="
        margin: 0 0 22px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 22px;
    ">
        Se recibió una nueva consulta desde el formulario
        de contacto del sitio web.
    </p>


    <div style="
        margin: 0 0 16px;
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
            Nombre
        </div>

        <div style="
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            line-height: 22px;
        ">
            {{ $senderName }}
        </div>

    </div>


    <div style="
        margin: 0 0 16px;
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
            Correo electrónico
        </div>

        <div style="
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            line-height: 22px;
        ">
            {{ $senderEmail }}
        </div>

    </div>


    <div style="
        margin: 0 0 28px;
        padding: 18px 20px;
        background: #111111;
        border: 1px solid #242424;
        border-radius: 10px;
    ">

        <div style="
            margin-bottom: 10px;
            color: #707070;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        ">
            Mensaje
        </div>

        <div style="
            color: #b0b0b0;
            font-size: 14px;
            line-height: 22px;
            white-space: pre-line;
        ">{{ $contactMessage }}</div>

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
        Podés responder directamente a este correo.
        La respuesta será enviada a {{ $senderEmail }}.
    </p>


@endsection