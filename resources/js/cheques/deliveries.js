
/*
|--------------------------------------------------------------------------
| AERIA Finance — Entregas múltiples de cheques
|--------------------------------------------------------------------------
|
| Monta el componente Vue 3 exclusivamente cuando
| existe #cheque-deliveries-app.
|
| Recibe desde Blade:
|
| - Neto de la liquidación
| - Cuentas y monedas disponibles
| - Cheques de terceros en cartera
| - Bancos
| - Entregas previamente guardadas
| - Ruta para guardar entregas
| - Token CSRF
|
*/

import { createApp } from 'vue';

import ChequeDeliveries from '../components/ChequeDeliveries.vue';

/*
|--------------------------------------------------------------------------
| Leer JSON de forma segura
|--------------------------------------------------------------------------
*/

function parseJson(value, fallback = []) {

    if (!value) {
        return fallback;
    }

    try {

        const parsed = JSON.parse(value);

        return Array.isArray(parsed)
            ? parsed
            : fallback;

    } catch (error) {

        console.error(
            'AERIA: error al interpretar datos de entregas.',
            error
        );

        return fallback;
    }
}

/*
|--------------------------------------------------------------------------
| Montar componente
|--------------------------------------------------------------------------
*/

function mountChequeDeliveries() {

    const element = document.getElementById(
        'cheque-deliveries-app'
    );

    if (!element) {
        return;
    }

    /*
     * Evitar doble montaje.
     */

    if (element.dataset.vueMounted === 'true') {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Propiedades desde Blade
    |--------------------------------------------------------------------------
    */

    const netAmount =
        element.dataset.netAmount || '0';

    const accounts = parseJson(
        element.dataset.accounts
    );

    const cheques = parseJson(
        element.dataset.cheques
    );

    const banks = parseJson(
        element.dataset.banks
    );

    const existingDeliveries = parseJson(
        element.dataset.existingDeliveries
    );

    const saveUrl =
        element.dataset.saveUrl || '';

    const csrfToken =
        element.dataset.csrfToken || '';

    /*
    |--------------------------------------------------------------------------
    | Validación básica de configuración
    |--------------------------------------------------------------------------
    */

    if (!saveUrl || !csrfToken) {

        console.error(
            'AERIA: faltan la ruta de guardado o el token CSRF.'
        );

        element.innerHTML = `
            <p class="cheques-note">
                No se pudo inicializar el formulario
                de medios de pago.
            </p>
        `;

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Inicializar Vue
    |--------------------------------------------------------------------------
    */

    try {

        const app = createApp(
            ChequeDeliveries,
            {
                netAmount,
                accounts,
                cheques,
                banks,
                existingDeliveries,
                saveUrl,
                csrfToken,
            }
        );

        app.mount(element);

        element.dataset.vueMounted = 'true';

    } catch (error) {

        console.error(
            'AERIA: no se pudo montar el componente de entregas.',
            error
        );
    }
}

/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
*/

if (document.readyState === 'loading') {

    document.addEventListener(
        'DOMContentLoaded',
        mountChequeDeliveries,
        { once: true }
    );

} else {

    mountChequeDeliveries();

}
