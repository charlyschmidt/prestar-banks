document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.money-input').forEach(input => {

        /*
        |--------------------------------------------------------------------------
        | Valor inicial
        |--------------------------------------------------------------------------
        |
        | Laravel puede enviar:
        |
        | 1250000.50
        |
        | y nosotros mostramos:
        |
        | 1.250.000,50
        |
        */

        if (input.value) {
            input.value = formatInitialMoney(input.value);
        }


        /*
        |--------------------------------------------------------------------------
        | Mientras escribe
        |--------------------------------------------------------------------------
        */

        input.addEventListener('input', function () {

            let value = this.value;

            /*
             * Como visualmente trabajamos con formato argentino:
             *
             * 1.250.000,50
             *
             * eliminamos los puntos de miles.
             */

            value = value.replace(/\./g, '');

            // Solo números y coma
            value = value.replace(/[^\d,]/g, '');


            /*
             * Solo una coma decimal
             */

            const firstComma = value.indexOf(',');

            if (firstComma !== -1) {

                value =
                    value.substring(0, firstComma + 1) +
                    value
                        .substring(firstComma + 1)
                        .replace(/,/g, '');

            }


            const parts = value.split(',');

            let integer = parts[0] || '';

            let decimal = parts[1] !== undefined
                ? parts[1].substring(0, 2)
                : null;


            /*
             * Eliminar ceros innecesarios a la izquierda
             */

            integer = integer.replace(/^0+(?=\d)/, '');

            if (integer === '') {
                integer = '0';
            }


            /*
             * Separadores de miles
             */

            integer = Number(integer).toLocaleString('es-AR');


            /*
             * Mantener los decimales mientras escribe
             */

            this.value = decimal !== null
                ? `${integer},${decimal}`
                : integer;

        });


        /*
        |--------------------------------------------------------------------------
        | Al salir del input
        |--------------------------------------------------------------------------
        |
        | Fuerza siempre dos decimales:
        |
        | 1.250      -> 1.250,00
        | 1.250,5    -> 1.250,50
        | 1.250,50   -> 1.250,50
        |
        */

        input.addEventListener('blur', function () {

            if (!this.value) {
                return;
            }

            this.value = formatArgentineMoney(this.value);

        });

    });



    /*
    |--------------------------------------------------------------------------
    | Antes de enviar formularios
    |--------------------------------------------------------------------------
    |
    | 1.250.000,50
    |
    | pasa a:
    |
    | 1250000.50
    |
    */

    document.querySelectorAll('form').forEach(form => {

        form.addEventListener('submit', () => {

            form.querySelectorAll('.money-input').forEach(input => {

                if (!input.value) {
                    return;
                }

                input.value = normalizeMoneyForBackend(
                    input.value
                );

            });

        });

    });

});



/*
|--------------------------------------------------------------------------
| Formatear valor inicial proveniente de Laravel
|--------------------------------------------------------------------------
|
| Laravel / MySQL:
|
| 1250000.50
|
| Resultado:
|
| 1.250.000,50
|
*/

function formatInitialMoney(value) {

    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return '';
    }


    let stringValue = String(value).trim();


    /*
     * Si ya viene con coma, asumimos formato argentino.
     */

    if (stringValue.includes(',')) {

        return formatArgentineMoney(
            stringValue
        );

    }


    /*
     * Si viene con punto pero sin coma,
     * asumimos formato decimal del backend:
     *
     * 1250.50
     */

    const number = Number(stringValue);


    if (Number.isNaN(number)) {
        return '';
    }


    return number.toLocaleString(
        'es-AR',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );

}



/*
|--------------------------------------------------------------------------
| Formatear formato argentino
|--------------------------------------------------------------------------
*/

function formatArgentineMoney(value) {

    if (!value) {
        return '';
    }


    let normalized = String(value)
        .replace(/\./g, '')
        .replace(',', '.');


    const number = Number(normalized);


    if (Number.isNaN(number)) {
        return '';
    }


    return number.toLocaleString(
        'es-AR',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );

}



/*
|--------------------------------------------------------------------------
| Convertir para Laravel
|--------------------------------------------------------------------------
|
| 1.250.000,50
|
| ->
|
| 1250000.50
|
*/

function normalizeMoneyForBackend(value) {

    return String(value)
        .replace(/\./g, '')
        .replace(',', '.');

}