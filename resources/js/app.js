import './bootstrap';


/*
|--------------------------------------------------------------------------
| Bootstrap
|--------------------------------------------------------------------------
*/

import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;


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

import '../css/cheques.css';

/*
|--------------------------------------------------------------------------
| Javascript propio
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

import './cheques/show.js';

// Vue solo se carga cuando existe el componente (evita peso en páginas públicas).
const loadOfficialRatesBar = () => {
    if (!document.getElementById('official-rates-app')) return;

    import('./official-rates').catch(error => {
        console.error('AERIA: no se pudo cargar la barra de cotizaciones.', error);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadOfficialRatesBar, { once: true });
} else {
    loadOfficialRatesBar();
}
