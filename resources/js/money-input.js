
document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Helpers de formato argentino
    |--------------------------------------------------------------------------
    */

    const formatThousands = (value) => {
        const integer = String(value || '0')
            .replace(/^0+(?=\d)/, '') || '0';

        return integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    const normalizeForBackend = (value) => {
        return String(value)
            .trim()
            .replace(/\./g, '')
            .replace(',', '.');
    };

    const formatMoney = (value) => {
        if (value === null || value === undefined || value === '') {
            return '';
        }

        const normalized = normalizeForBackend(value);
        const number = Number(normalized);

        if (!Number.isFinite(number)) {
            return '';
        }

        return number.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    /*
    |--------------------------------------------------------------------------
    | Valores iniciales provenientes del backend
    |--------------------------------------------------------------------------
    |
    | 1250000.50 -> 1.250.000,50
    | 1250000,50 -> 1.250.000,50
    |
    */

    const formatInitialMoney = (value) => {
        if (value === null || value === undefined || value === '') {
            return '';
        }

        const stringValue = String(value).trim();

        if (stringValue.includes(',')) {
            return formatMoney(stringValue);
        }

        const number = Number(stringValue);

        if (!Number.isFinite(number)) {
            return '';
        }

        return number.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    /*
    |--------------------------------------------------------------------------
    | Valores iniciales de porcentajes
    |--------------------------------------------------------------------------
    |
    | 1.2000 -> 1,2
    | 0.6000 -> 0,6
    | 21.0000 -> 21
    | 1.2575 -> 1,2575
    |
    */

    const formatInitialPercent = (value) => {
        if (value === null || value === undefined || value === '') {
            return '';
        }

        let normalized = String(value).trim();

        if (normalized.includes(',')) {
            normalized = normalizeForBackend(normalized);
        }

        const number = Number(normalized);

        if (!Number.isFinite(number)) {
            return '';
        }

        return number.toLocaleString('es-AR', {
            useGrouping: false,
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        });
    };

    /*
    |--------------------------------------------------------------------------
    | Formatear mientras se escribe
    |--------------------------------------------------------------------------
    */

    const formatWhileTyping = (value, decimals, thousands) => {
        let cleaned = String(value)
            .replace(/\./g, '')
            .replace(/[^\d,]/g, '');

        const firstComma = cleaned.indexOf(',');

        if (firstComma !== -1) {
            cleaned =
                cleaned.substring(0, firstComma + 1) +
                cleaned.substring(firstComma + 1).replace(/,/g, '');
        }

        if (cleaned === '') {
            return '';
        }

        const parts = cleaned.split(',');

        let integer = parts[0] || '0';

        integer = integer.replace(/^0+(?=\d)/, '');

        if (integer === '') {
            integer = '0';
        }

        if (thousands) {
            integer = formatThousands(integer);
        }

        if (parts.length > 1) {
            return integer + ',' + parts[1].substring(0, decimals);
        }

        return integer;
    };

    /*
    |--------------------------------------------------------------------------
    | Importes monetarios
    |--------------------------------------------------------------------------
    |
    | .money-input
    |
    | 1000000,5 -> 1.000.000,50
    |
    */

    document.querySelectorAll('.money-input').forEach(input => {

        if (input.value) {
            input.value = formatInitialMoney(input.value);
        }

        input.addEventListener('input', function () {

            this.value = formatWhileTyping(
                this.value,
                2,
                true
            );

        });

        input.addEventListener('blur', function () {

            if (!this.value) {
                return;
            }

            this.value = formatMoney(this.value);

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Porcentajes
    |--------------------------------------------------------------------------
    |
    | .percent-input
    |
    | 1,2      -> 1,2
    | 0,6      -> 0,6
    | 21       -> 21
    | 1,2575   -> 1,2575
    |
    | No se agregan ceros al final.
    | Se permiten hasta cuatro decimales.
    |
    */

    document.querySelectorAll('.percent-input').forEach(input => {

        if (input.value) {
            input.value = formatInitialPercent(input.value);
        }

        input.addEventListener('input', function () {

            this.value = formatWhileTyping(
                this.value,
                4,
                false
            );

        });

        input.addEventListener('blur', function () {

            if (!this.value) {
                return;
            }

            this.value = formatInitialPercent(
                this.value
            );

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Normalizar antes de enviar a Laravel
    |--------------------------------------------------------------------------
    |
    | Importe:
    | 1.250.000,50 -> 1250000.50
    |
    | Porcentaje:
    | 1,2575 -> 1.2575
    |
    | Compatible con formularios que tienen
    | distintos botones y rutas de envío.
    |
    */

    document.querySelectorAll('form').forEach(form => {

        form.addEventListener('submit', () => {

            form.querySelectorAll(
                '.money-input, .percent-input'
            ).forEach(input => {

                if (!input.value) {
                    return;
                }

                const isMoney = input.classList.contains('money-input');

                if (isMoney) {
                    input.value = normalizeForBackend(
                        input.value
                    );
                } else {
                    input.value = normalizeForBackend(
                        input.value
                    );
                }

            });

        });

    });

});
