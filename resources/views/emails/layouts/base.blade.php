<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $title ?? 'AERIA Finance' }}
    </title>

</head>

<body style="
    margin: 0;
    padding: 0;
    background: #050505;
    font-family: Arial, Helvetica, sans-serif;
    color: #ffffff;
">

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="
            width: 100%;
            background: #050505;
            padding: 48px 16px;
        "
    >

        <tr>

            <td align="center">


                <table
                    role="presentation"
                    width="100%"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        width: 100%;
                        max-width: 560px;
                    "
                >


                    {{-- BRAND --}}

                    <tr>

                        <td
                            align="center"
                            style="
                                padding-bottom: 32px;
                            "
                        >

                            <div style="
                                color: #ffffff;
                                font-size: 23px;
                                font-weight: 700;
                                letter-spacing: -0.7px;
                            ">

                                AERIA

                                <span style="
                                    color: #8b8b8b;
                                    font-weight: 400;
                                ">
                                    Finance
                                </span>

                            </div>

                        </td>

                    </tr>


                    {{-- CONTENT --}}

                    <tr>

                        <td style="
                            background: #0c0c0c;
                            border: 1px solid #242424;
                            border-radius: 16px;
                            padding: 36px 34px;
                        ">

                            @yield('content')

                        </td>

                    </tr>


                    {{-- FOOTER --}}

                    <tr>

                        <td
                            align="center"
                            style="
                                padding: 28px 20px 0;
                                color: #666666;
                                font-size: 11px;
                                line-height: 18px;
                            "
                        >

                            Este es un mensaje automático de AERIA Finance.

                            <br>

                            Por favor, no respondas a este correo.

                            <br><br>

                            <span style="color: #8a8a8a;">
                                © {{ date('Y') }} AERIA Finance
                            </span>

                        </td>

                    </tr>


                </table>


            </td>

        </tr>

    </table>

</body>

</html>