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
        Hola {{ $userName }},
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
        margin: 0 0 28px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 22px;
    ">
        Tu cuenta se encuentra pendiente de aprobación.
        Te enviaremos otro correo cuando esté habilitada
        para comenzar a utilizar la plataforma.
    </p>


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
    </p>


@endsection