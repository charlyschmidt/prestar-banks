document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.money-input').forEach(input => {

        /*
        |--------------------------------------------------------------------------
        | Valor inicial
        |--------------------------------------------------------------------------
        */

        if (input.value) {
            input.value = formatMoney(input.value);
        }


        /*
        |--------------------------------------------------------------------------
        | Mientras escribe
        |--------------------------------------------------------------------------
        */

        input.addEventListener('input', function () {

            let value = this.value;

            // Dejamos solamente números y coma
            value = value.replace(/[^\d,]/g, '');

            // Solo permitimos una coma decimal
            const parts = value.split(',');

            let integer = parts[0] || '';
            let decimal = parts[1] !== undefined
                ? parts[1].substring(0, 2)
                : null;

            // Quitamos ceros innecesarios
            integer = integer.replace(/^0+(?=\d)/, '');

            // Separador de miles
            if (integer) {
                integer = Number(integer).toLocaleString('es-AR');
            }

            this.value = decimal !== null
                ? `${integer},${decimal}`
                : integer;

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Antes de enviar cualquier formulario
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('form').forEach(form => {

        form.addEventListener('submit', () => {

            form.querySelectorAll('.money-input').forEach(input => {

                if (!input.value) {
                    return;
                }

                /*
                 * 1.250.000,50
                 *
                 * se convierte en:
                 *
                 * 1250000.50
                 */

                input.value = input.value
                    .replace(/\./g, '')
                    .replace(',', '.');

            });

        });

    });

});


/*
|--------------------------------------------------------------------------
| Formatear valor inicial
|--------------------------------------------------------------------------
*/

function formatMoney(value) {

    if (value === null || value === undefined || value === '') {
        return '';
    }

    let normalized = String(value)
        .replace(/\./g, '')
        .replace(',', '.');

    const number = Number(normalized);

    if (Number.isNaN(number)) {
        return '';
    }

    return number.toLocaleString('es-AR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });

}