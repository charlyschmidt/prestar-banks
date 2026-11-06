/*
|--------------------------------------------------------------------------
| AERIA Finance — Client Search
|--------------------------------------------------------------------------
|
| Punto de montaje del buscador de clientes.
|
| Se integra en formularios Blade sin interferir
| con el JavaScript existente de movimientos.
|
| Responsabilidades:
|
| - Detectar el contenedor.
| - Leer la configuración enviada por Blade.
| - Montar el componente Vue 3.
| - Evitar montajes duplicados.
|
*/

import { createApp } from 'vue';

import ClientSearch from '../components/ClientSearch.vue';

/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
*/

function initializeClientSearch() {

    const container = document.getElementById(
        'client-search-app'
    );

    if (!container) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Evitar inicialización duplicada
    |--------------------------------------------------------------------------
    */

    if (container.dataset.mounted === 'true') {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Configuración desde Blade
    |--------------------------------------------------------------------------
    |
    | data-search-url:
    | Endpoint Laravel para buscar clientes.
    |
    | data-client-id:
    | ID del cliente previamente seleccionado.
    |
    | data-client-name:
    | Nombre visible del cliente seleccionado.
    |
    | data-client-tax-id:
    | CUIT/CUIL del cliente seleccionado.
    |
    */

    const searchUrl =
        container.dataset.searchUrl || '';

    const clientId =
        container.dataset.clientId || '';

    const clientName =
        container.dataset.clientName || '';

    const clientTaxId =
        container.dataset.clientTaxId || '';

    if (!searchUrl) {

        console.error(
            'AERIA Client Search: falta data-search-url.'
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Montar Vue
    |--------------------------------------------------------------------------
    */

    const app = createApp(ClientSearch, {
        searchUrl,
        initialClientId: clientId,
        initialClientName: clientName,
        initialClientTaxId: clientTaxId,
    });

    app.mount(container);

    container.dataset.mounted = 'true';
}

/*
|--------------------------------------------------------------------------
| Arranque
|--------------------------------------------------------------------------
|
| app.js ya carga este módulo cuando el DOM
| está listo, por lo que no registramos otro
| DOMContentLoaded.
|
*/

initializeClientSearch();
