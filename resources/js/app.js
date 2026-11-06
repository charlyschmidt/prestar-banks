
import './bootstrap';

/*
|--------------------------------------------------------------------------
| Bootstrap
|--------------------------------------------------------------------------
*/

import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

/*
|--------------------------------------------------------------------------
| SweetAlert2
|--------------------------------------------------------------------------
*/

import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

/*
|--------------------------------------------------------------------------
| Bootstrap UI
|--------------------------------------------------------------------------
*/

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

/*
|--------------------------------------------------------------------------
| Dashboard / Empresa
|--------------------------------------------------------------------------
*/

import '../css/theme.css';
import '../css/components.css';
import '../css/layout.css';
import '../css/dashboard.css';
import '../css/account.css';
import '../css/movement.css';
import '../css/history.css';
import '../css/settings.css';
import '../css/settings-cotizaciones.css';

/*
|--------------------------------------------------------------------------
| AERIA — Acceso público
|--------------------------------------------------------------------------
*/

import '../css/welcome.css';
import '../css/login.css';
import '../css/register.css';
import '../css/register-pending.css';
import '../css/select-company.css';
import '../css/error.css';
import '../css/aeria-admin.css';
import '../css/movement-control.css';
import '../css/movement-control-results.css';
import '../css/account-alerts.css';
import '../css/reminders.css';
import '../css/subscription.css';
import '../css/subscription-dashboard.css';
import '../css/settings-api.css';
import '../css/clients/client-search.css';

/*
|--------------------------------------------------------------------------
| AERIA Finance — Cheques
|--------------------------------------------------------------------------
*/

import '../css/cheques.css';
import '../css/cheques/create.css';
import '../css/cheques/show.css';
import '../css/cheques/portfolio.css';

/*
|--------------------------------------------------------------------------
| Javascript general
|--------------------------------------------------------------------------
*/

import './dashboard';
import './transactions';
import './money-input';
import './settings';
import './shortcuts';
import './movement-control';
import './reminders';
import './echo';
import './reminder-notifications';
import './opening-balance-import';
import './settings-danger-zone';
import './searchable-select';
import './subscription';
import './settings-api';
import './accounts-create';

/*
|--------------------------------------------------------------------------
| AERIA Finance — Cheques
|--------------------------------------------------------------------------
*/

import './cheques/show.js';
import './cheques/create.js';
import './cheques/portfolio.js';
import './cheques/index.js';

/*
|--------------------------------------------------------------------------
| Componentes Vue 3
|--------------------------------------------------------------------------
|
| Cada componente se carga únicamente cuando
| existe su contenedor dentro del DOM.
|
| No montamos una aplicación Vue global.
|
*/

/*
|--------------------------------------------------------------------------
| Barra de cotizaciones oficiales
|--------------------------------------------------------------------------
*/

function loadOfficialRatesBar() {

    const container = document.getElementById(
        'official-rates-app'
    );

    if (!container) {
        return;
    }

    import('./official-rates').catch(error => {

        console.error(
            'AERIA: no se pudo cargar la barra de cotizaciones.',
            error
        );

    });
}

/*
|--------------------------------------------------------------------------
| Entregas múltiples de cheques
|--------------------------------------------------------------------------
|
| Vue administra:
|
| - Efectivo
| - Transferencias
| - Cheques de terceros
| - Cotizaciones
| - Distribución del neto
| - Validación visual de saldos
|
| Laravel administra los movimientos financieros.
|
*/

function loadChequeDeliveries() {

    const container = document.getElementById(
        'cheque-deliveries-app'
    );

    if (!container) {
        return;
    }

    import('./cheques/deliveries').catch(error => {

        console.error(
            'AERIA: no se pudo cargar el módulo de entregas múltiples.',
            error
        );

    });
}

/*
|--------------------------------------------------------------------------
| AERIA Clients — Buscador dinámico
|--------------------------------------------------------------------------
|
| Disponible en formularios de movimientos.
|
| - Busca clientes de la empresa activa.
| - No carga todo el padrón.
| - Solicita resultados al servidor.
| - Permite seleccionar un cliente.
| - Guarda el ID en un input oculto.
| - El cliente es opcional.
|
| La administración del padrón sigue siendo
| exclusiva del Super Admin.
|
| Laravel valida empresa, permisos y estado.
|
*/

function loadClientSearch() {

    const container = document.getElementById(
        'client-search-app'
    );

    if (!container) {
        return;
    }

    import('./clients/client-search').catch(error => {

        console.error(
            'AERIA: no se pudo cargar el buscador de clientes.',
            error
        );

    });
}

/*
|--------------------------------------------------------------------------
| Inicialización general
|--------------------------------------------------------------------------
|
| Ejecutar una única vez cuando el DOM esté listo.
|
*/

function initializeAeriaComponents() {

    loadOfficialRatesBar();

    loadChequeDeliveries();

    loadClientSearch();

}

if (document.readyState === 'loading') {

    document.addEventListener(
        'DOMContentLoaded',
        initializeAeriaComponents,
        { once: true }
    );

} else {

    initializeAeriaComponents();

}

if (document.querySelector('.account-movements-page')) {

    import('./accounts/account-movements')
        .catch(error => {

            console.error(
                'AERIA: error cargando movimientos por cuenta:',
                error
            );

        });

}