/*
|--------------------------------------------------------------------------
| AERIA Finance
| Movimientos por cuenta - Tiempo real
|--------------------------------------------------------------------------
|
| Escucha los eventos existentes de Laravel Reverb.
|
| No recalcula saldos en JavaScript.
| El servidor sigue siendo la fuente de verdad.
|
| Actualiza:
| - Resumen de saldos por moneda
| - Tabla de movimientos
| - Cantidad de movimientos
| - Clientes asociados
| - Estado de ejecución
|
*/

const ACCOUNT_PAGE_SELECTOR = '.account-movements-page';

let accountMovementsInitialized = false;

let synchronizationController = null;

let synchronizationVersion = 0;


/*
|--------------------------------------------------------------------------
| Sincronizar movimientos de la cuenta
|--------------------------------------------------------------------------
*/

async function syncAccountMovements() {

    const page = document.querySelector(
        ACCOUNT_PAGE_SELECTOR
    );

    if (!page) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Cancelar sincronización anterior
    |--------------------------------------------------------------------------
    */

    if (synchronizationController) {
        synchronizationController.abort();
    }

    synchronizationController = new AbortController();

    const controller = synchronizationController;

    const currentVersion = ++synchronizationVersion;

    try {

        /*
        |--------------------------------------------------------------------------
        | Recuperar estado actualizado desde Laravel
        |--------------------------------------------------------------------------
        */

        const response = await fetch(
            window.location.href,
            {
                method: 'GET',

                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },

                credentials: 'same-origin',

                cache: 'no-store',

                signal: controller.signal,
            }
        );

        if (!response.ok || response.redirected) {

            throw new Error(
                `No se pudieron sincronizar los movimientos (HTTP ${response.status}).`
            );
        }

        const html = await response.text();

        /*
        |--------------------------------------------------------------------------
        | Evitar respuestas antiguas
        |--------------------------------------------------------------------------
        */

        if (currentVersion !== synchronizationVersion) {
            return;
        }

        const serverDocument = new DOMParser()
            .parseFromString(
                html,
                'text/html'
            );

        const incomingPage = serverDocument.querySelector(
            ACCOUNT_PAGE_SELECTOR
        );

        if (!incomingPage) {

            throw new Error(
                'La respuesta no contiene la vista de movimientos.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Resumen de saldos por moneda
        |--------------------------------------------------------------------------
        */

        const currentSummary = page.querySelector(
            '.account-balance-summary'
        );

        const incomingSummary = incomingPage.querySelector(
            '.account-balance-summary'
        );

        if (currentSummary && incomingSummary) {

            currentSummary.replaceWith(
                document.importNode(
                    incomingSummary,
                    true
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tabla y contador de movimientos
        |--------------------------------------------------------------------------
        |
        | Reutilizamos el HTML Blade.
        | Así respetamos permisos, formato y clientes.
        |
        */

        const currentSection = page.querySelector(
            '.account-movements-table-section'
        );

        const incomingSection = incomingPage.querySelector(
            '.account-movements-table-section'
        );

        if (currentSection && incomingSection) {

            currentSection.replaceWith(
                document.importNode(
                    incomingSection,
                    true
                )
            );
        }

        console.log(
            'AERIA: movimientos de cuenta sincronizados.'
        );

    } catch (error) {

        if (error.name === 'AbortError') {
            return;
        }

        console.error(
            'AERIA: error al sincronizar movimientos de cuenta:',
            error
        );

    } finally {

        if (synchronizationController === controller) {
            synchronizationController = null;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Inicializar Laravel Echo
|--------------------------------------------------------------------------
*/

function initializeAccountMovementsRealtime() {

    const page = document.querySelector(
        ACCOUNT_PAGE_SELECTOR
    );

    if (!page || accountMovementsInitialized) {
        return;
    }

    const companyId =
        document.body.dataset.companyId;

    if (!companyId) {

        console.error(
            'AERIA: no se encontró la empresa activa.'
        );

        return;
    }

    if (!window.Echo) {

        console.error(
            'AERIA: Laravel Echo no está disponible.'
        );

        return;
    }

    accountMovementsInitialized = true;

    const channelName = `dashboard.${companyId}`;

    /*
    |--------------------------------------------------------------------------
    | Escuchar el mismo canal que el dashboard
    |--------------------------------------------------------------------------
    */

    window.Echo.private(channelName)

        /*
        |--------------------------------------------------------------------------
        | Movimiento creado
        |--------------------------------------------------------------------------
        */

        .listen('.transaction.created', (event) => {

            if (
                String(event.companyId) !==
                String(companyId)
            ) {
                return;
            }

            syncAccountMovements();

        })

        /*
        |--------------------------------------------------------------------------
        | Movimiento actualizado
        |--------------------------------------------------------------------------
        |
        | TransactionUpdated envía transaction_id.
        | El canal privado ya identifica la empresa.
        |
        */

        .listen('.transaction.updated', (event) => {

            if (!event.transaction_id) {
                return;
            }

            syncAccountMovements();

        })

        /*
        |--------------------------------------------------------------------------
        | Transferencia ejecutada
        |--------------------------------------------------------------------------
        */

        .listen('.transaction.executed', (event) => {

            if (
                String(event.companyId) !==
                String(companyId)
            ) {
                return;
            }

            if (!event.transaction_id) {
                return;
            }

            syncAccountMovements();

        });

    /*
    |--------------------------------------------------------------------------
    | Resincronizar al volver a la pestaña
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'visibilitychange',
        () => {

            if (document.visibilityState === 'visible') {
                syncAccountMovements();
            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Restauración desde caché del navegador
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'pageshow',
        (event) => {

            if (event.persisted) {
                syncAccountMovements();
            }

        }
    );

    console.log(
        'AERIA: tiempo real de cuenta conectado:',
        channelName
    );
}


/*
|--------------------------------------------------------------------------
| Iniciar
|--------------------------------------------------------------------------
*/

if (document.readyState === 'loading') {

    document.addEventListener(
        'DOMContentLoaded',
        initializeAccountMovementsRealtime
    );

} else {

    initializeAccountMovementsRealtime();

}
