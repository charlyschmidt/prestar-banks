/*
|--------------------------------------------------------------------------
| AERIA Finance — Liquidaciones de cheques en tiempo real
|--------------------------------------------------------------------------
|
| Escucha:
|
| cheque.liquidation.confirmed
|
| Actualiza desde Laravel:
|
| - Listado de liquidaciones.
| - Estados de las operaciones.
| - Cantidad total de operaciones.
| - Paginación.
|
| No reconstruimos filas manualmente.
| Laravel continúa siendo la fuente de datos.
|
*/

function initializeChequeLiquidationsRealtime() {

    /*
    |--------------------------------------------------------------------------
    | Verificar pantalla
    |--------------------------------------------------------------------------
    */

    const page = document.querySelector(
        '.cheque-liquidations-page'
    );

    if (!page) {
        return;
    }

    const contentSelector =
        '[data-cheque-liquidations-content]';

    /*
    |--------------------------------------------------------------------------
    | Empresa activa
    |--------------------------------------------------------------------------
    */

    const companyId =
        document.body.dataset.companyId;

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
    | Control de sincronización
    |--------------------------------------------------------------------------
    */

    let refreshTimer = null;
    let currentRequest = null;
    let requestVersion = 0;

    /*
    |--------------------------------------------------------------------------
    | Obtener HTML actualizado desde Laravel
    |--------------------------------------------------------------------------
    |
    | Consultamos la URL actual para conservar:
    |
    | - Página seleccionada.
    | - Parámetros de búsqueda.
    | - Ordenamiento.
    |
    | Solo reemplazamos el listado y la paginación.
    |
    */

    async function syncLiquidations() {

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
            |--------------------------------------------------------------------------
            | Verificar redirecciones
            |--------------------------------------------------------------------------
            |
            | Si Laravel redirige al login o a
            | otra pantalla, no reemplazamos la vista.
            |
            */

            const responseUrl = new URL(
                response.url,
                window.location.origin
            );

            const currentUrl = new URL(
                window.location.href
            );

            if (
                responseUrl.pathname !== currentUrl.pathname
            ) {
                throw new Error(
                    'La respuesta fue redirigida a otra página.'
                );
            }

            const html = await response.text();

            /*
            |--------------------------------------------------------------------------
            | Descartar respuestas anteriores
            |--------------------------------------------------------------------------
            */

            if (version !== requestVersion) {
                return;
            }

            const parsedDocument = new DOMParser()
                .parseFromString(
                    html,
                    'text/html'
                );

            const updatedContent =
                parsedDocument.querySelector(
                    contentSelector
                );

            const currentContent =
                document.querySelector(
                    contentSelector
                );

            if (!updatedContent || !currentContent) {
                throw new Error(
                    'No se encontró el contenido de liquidaciones.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Actualizar listado
            |--------------------------------------------------------------------------
            */

            currentContent.innerHTML =
                updatedContent.innerHTML;

            /*
            |--------------------------------------------------------------------------
            | Destacar las filas actualizadas
            |--------------------------------------------------------------------------
            |
            | El evento indica qué liquidación
            | fue confirmada.
            |
            | Si la fila está en la página actual,
            | aplicamos una animación suave.
            |
            */

            if (lastConfirmedLiquidationId !== null) {

                const row = Array.from(
                    currentContent.querySelectorAll(
                        '[data-liquidation-id]'
                    )
                ).find(
                    element =>
                        element.dataset.liquidationId ===
                        String(lastConfirmedLiquidationId)
                );

                if (row) {

                    row.classList.add(
                        'cheque-liquidation-realtime-updated'
                    );

                    setTimeout(() => {
                        row.classList.remove(
                            'cheque-liquidation-realtime-updated'
                        );
                    }, 1200);
                }

                lastConfirmedLiquidationId = null;
            }

            console.log(
                'AERIA Cheques: listado de liquidaciones sincronizado.'
            );

        } catch (error) {

            if (error.name === 'AbortError') {
                return;
            }

            console.error(
                'AERIA Cheques: error sincronizando liquidaciones.',
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
    | Agrupar notificaciones cercanas
    |--------------------------------------------------------------------------
    */

    let lastConfirmedLiquidationId = null;

    function scheduleLiquidationsSync(
        liquidationId = null
    ) {

        if (liquidationId !== null) {
            lastConfirmedLiquidationId = liquidationId;
        }

        if (refreshTimer !== null) {
            clearTimeout(refreshTimer);
        }

        refreshTimer = setTimeout(() => {

            refreshTimer = null;

            if (document.visibilityState === 'hidden') {
                return;
            }

            syncLiquidations();

        }, 250);
    }

    /*
    |--------------------------------------------------------------------------
    | Evento de liquidación confirmada
    |--------------------------------------------------------------------------
    */

    window.Echo
        .private(channelName)
        .listen(
            '.cheque.liquidation.confirmed',
            event => {

                /*
                |--------------------------------------------------------------------------
                | Seguridad por empresa
                |--------------------------------------------------------------------------
                */

                if (
                    String(event.companyId) !==
                    String(companyId)
                ) {
                    return;
                }

                console.log(
                    'AERIA Cheques: confirmación recibida.',
                    event
                );

                scheduleLiquidationsSync(
                    event.liquidationId ?? null
                );
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Volver a una pestaña inactiva
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'visibilitychange',
        () => {

            if (
                document.visibilityState === 'visible'
            ) {
                scheduleLiquidationsSync();
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
                scheduleLiquidationsSync();
            }

        }
    );

    console.log(
        'AERIA Cheques: listado conectado a',
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
        initializeChequeLiquidationsRealtime,
        { once: true }
    );

} else {

    initializeChequeLiquidationsRealtime();

}
