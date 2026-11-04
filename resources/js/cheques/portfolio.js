/*
|--------------------------------------------------------------------------
| AERIA Finance — Cartera de cheques en tiempo real
|--------------------------------------------------------------------------
|
| Escucha confirmaciones de liquidaciones mediante Reverb.
|
| Cuando se confirma una operación:
|
| - Actualiza los cheques disponibles.
| - Actualiza la cantidad de cheques.
| - Actualiza el nominal total.
| - Respeta el orden por vencimiento.
| - Actualiza la paginación.
|
| Laravel continúa siendo la fuente de los datos.
| No reconstruimos las filas manualmente.
|
*/

function initializeChequePortfolioRealtime() {

    /*
    |--------------------------------------------------------------------------
    | Verificar pantalla
    |--------------------------------------------------------------------------
    */

    const portfolioPage = document.querySelector(
        '.cheque-portfolio-page'
    );

    if (!portfolioPage) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Empresa activa
    |--------------------------------------------------------------------------
    */

    const companyId = document.body.dataset.companyId;

    if (!companyId) {
        console.error(
            'AERIA Cheques: no se encontró la empresa activa.'
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Laravel Echo
    |--------------------------------------------------------------------------
    */

    if (!window.Echo) {
        console.error(
            'AERIA Cheques: Laravel Echo no está disponible.'
        );

        return;
    }

    const channelName = `dashboard.${companyId}`;

    /*
    |--------------------------------------------------------------------------
    | Estado de sincronización
    |--------------------------------------------------------------------------
    */

    let refreshTimer = null;

    let currentRequest = null;

    let requestVersion = 0;

    /*
    |--------------------------------------------------------------------------
    | Sincronizar cartera desde Laravel
    |--------------------------------------------------------------------------
    |
    | Solicitamos nuevamente la URL actual.
    |
    | Extraemos únicamente el contenido de
    | .cheque-portfolio-page.
    |
    | No recargamos el documento completo.
    |
    */

    async function syncPortfolio() {

        /*
         * Si existe una petición anterior,
         * la cancelamos para evitar resultados viejos.
         */

        if (currentRequest) {
            currentRequest.abort();
        }

        const controller = new AbortController();

        currentRequest = controller;

        const version = ++requestVersion;

        try {

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

            if (!response.ok) {
                throw new Error(
                    `Error HTTP ${response.status}`
                );
            }

            /*
             * Evitar reemplazar la pantalla si Laravel
             * redirigió la petición al login.
             */

            if (
                response.redirected &&
                new URL(response.url).pathname !==
                    window.location.pathname
            ) {
                throw new Error(
                    'La respuesta fue redirigida a otra página.'
                );
            }

            const html = await response.text();

            /*
             * Si comenzó otra sincronización,
             * descartamos esta respuesta.
             */

            if (version !== requestVersion) {
                return;
            }

            const parser = new DOMParser();

            const documentResponse = parser.parseFromString(
                html,
                'text/html'
            );

            const updatedPortfolio =
                documentResponse.querySelector(
                    '.cheque-portfolio-page'
                );

            if (!updatedPortfolio) {
                throw new Error(
                    'La respuesta no contiene la cartera de cheques.'
                );
            }

            const currentPortfolio =
                document.querySelector(
                    '.cheque-portfolio-page'
                );

            if (!currentPortfolio) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Actualización del contenido
            |--------------------------------------------------------------------------
            |
            | Se actualizan simultáneamente:
            |
            | - Indicadores.
            | - Tabla.
            | - Mensaje de cartera vacía.
            | - Paginación.
            |
            | Mantiene el scroll y no recarga la app.
            |
            */

            currentPortfolio.innerHTML =
                updatedPortfolio.innerHTML;

            /*
            |--------------------------------------------------------------------------
            | Animación suave del resumen
            |--------------------------------------------------------------------------
            */

            const stats = currentPortfolio.querySelector(
                '.cheque-portfolio-stats'
            );

            if (stats) {

                stats.classList.add(
                    'cheque-portfolio-realtime-updated'
                );

                setTimeout(() => {
                    stats.classList.remove(
                        'cheque-portfolio-realtime-updated'
                    );
                }, 1000);
            }

            console.log(
                'AERIA Cheques: cartera sincronizada.'
            );

        } catch (error) {

            if (error.name === 'AbortError') {
                return;
            }

            console.error(
                'AERIA Cheques: error sincronizando cartera.',
                error
            );

        } finally {

            if (currentRequest === controller) {
                currentRequest = null;
            }

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Agrupar eventos cercanos
    |--------------------------------------------------------------------------
    |
    | Evitamos solicitar varias veces la misma tabla
    | cuando llegan notificaciones consecutivas.
    |
    */

    function schedulePortfolioSync() {

        if (refreshTimer !== null) {
            clearTimeout(refreshTimer);
        }

        refreshTimer = setTimeout(() => {

            refreshTimer = null;

            /*
             * Si el usuario no está viendo la pestaña,
             * sincronizamos cuando vuelva.
             */

            if (document.visibilityState === 'hidden') {
                return;
            }

            syncPortfolio();

        }, 250);
    }

    /*
    |--------------------------------------------------------------------------
    | Escuchar liquidaciones confirmadas
    |--------------------------------------------------------------------------
    |
    | Evento emitido desde:
    |
    | ChequeLiquidationController::confirm()
    |
    | Evento PHP:
    |
    | ChequeLiquidationConfirmed
    |
    */

    window.Echo
        .private(channelName)
        .listen(
            '.cheque.liquidation.confirmed',
            event => {

                /*
                |--------------------------------------------------------------------------
                | Seguridad adicional por empresa
                |--------------------------------------------------------------------------
                */

                if (
                    String(event.companyId) !==
                    String(companyId)
                ) {
                    return;
                }

                console.log(
                    'AERIA Cheques: liquidación confirmada.',
                    event
                );

                schedulePortfolioSync();
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Resincronizar al volver a la pestaña
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'visibilitychange',
        () => {

            if (
                document.visibilityState === 'visible'
            ) {
                schedulePortfolioSync();
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
        event => {

            if (event.persisted) {
                schedulePortfolioSync();
            }

        }
    );

    console.log(
        'AERIA Cheques: cartera conectada a',
        channelName
    );
}

/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
*/

if (document.readyState === 'loading') {

    document.addEventListener(
        'DOMContentLoaded',
        initializeChequePortfolioRealtime,
        { once: true }
    );

} else {

    initializeChequePortfolioRealtime();

}
