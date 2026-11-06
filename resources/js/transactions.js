/*
|--------------------------------------------------------------------------
| AERIA Finance - Movimientos
|--------------------------------------------------------------------------
|
| Gestión de movimientos en tiempo real.
| Compatible con Laravel Echo / Reverb.
|
| Incluye:
| - Clientes asociados
| - Nuevos movimientos
| - Actualización de movimientos
| - Ejecución de transferencias
| - Detalle de transferencias
| - Permisos de acciones
| - Numeración de movimientos
|
*/


/*
|--------------------------------------------------------------------------
| Seguridad HTML
|--------------------------------------------------------------------------
*/

function escapeTransactionHtml(value) {
    const div = document.createElement('div');

    div.textContent = String(value ?? '');

    return div.innerHTML;
}


/*
|--------------------------------------------------------------------------
| Formatear importe
|--------------------------------------------------------------------------
*/

function formatTransactionAmount(value) {
    return Number(value ?? 0).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}


/*
|--------------------------------------------------------------------------
| Renumerar movimientos
|--------------------------------------------------------------------------
*/

function renumberTransactions() {

    const tbody = document.getElementById(
        'transactions-body'
    );

    if (!tbody) {
        return;
    }

    const rows = tbody.querySelectorAll(
        'tr[data-transaction-id]'
    );

    const total = rows.length;

    rows.forEach((row, index) => {

        const counter = row.querySelector(
            '[data-movement-counter]'
        );

        if (counter) {
            counter.textContent = total - index;
        }

    });
}


/*
|--------------------------------------------------------------------------
| Sincronizar tabla después de editar
|--------------------------------------------------------------------------
|
| TransactionUpdated envía el ID del movimiento.
| Recuperamos la tabla renderizada por Laravel.
|
| Esto mantiene los permisos y el diseño Blade.
| No modificamos balances ni recargamos toda la página.
|
*/

async function syncTransactionsTable() {

    const tbody = document.getElementById(
        'transactions-body'
    );

    if (!tbody) {
        return;
    }

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
            }
        );

        if (!response.ok || response.redirected) {
            throw new Error(
                `Error sincronizando movimientos: HTTP ${response.status}`
            );
        }

        const html = await response.text();

        const serverDocument = new DOMParser()
            .parseFromString(html, 'text/html');

        const updatedBody = serverDocument.getElementById(
            'transactions-body'
        );

        if (!updatedBody) {
            throw new Error(
                'La respuesta no contiene la tabla de movimientos.'
            );
        }

        const newRows = Array.from(
            updatedBody.childNodes
        ).map(node => document.importNode(node, true));

        tbody.replaceChildren(...newRows);

        renumberTransactions();

    } catch (error) {

        console.error(
            'Error sincronizando movimientos:',
            error
        );

    }
}


/*
|--------------------------------------------------------------------------
| Marcar transferencia como ejecutada
|--------------------------------------------------------------------------
*/

function markTransactionAsExecuted(transactionId) {

    const row = document.querySelector(
        `tr[data-transaction-id="${transactionId}"]`
    );

    if (!row) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Mostrar badge
    |--------------------------------------------------------------------------
    */

    const badge = row.querySelector(
        `[data-execution-badge="${transactionId}"]`
    );

    if (badge) {
        badge.hidden = false;
        badge.style.display = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones
    |--------------------------------------------------------------------------
    */

    const actions = row.querySelector(
        '.table-actions'
    );

    if (!actions) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Quitar ejecutar, editar y eliminar
    |--------------------------------------------------------------------------
    */

    actions.querySelectorAll(
        '.execute-transaction-button, a[href*="/edit"], form'
    ).forEach(element => {
        element.remove();
    });

    /*
    |--------------------------------------------------------------------------
    | Agregar detalle
    |--------------------------------------------------------------------------
    */

    if (!actions.querySelector(
        '.view-transaction-button'
    )) {

        actions.insertAdjacentHTML(
            'afterbegin',
            `
                <button
                    type="button"
                    class="icon-button view-transaction-button"
                    data-transaction-id="${transactionId}"
                    title="Ver detalle de transferencia"
                >
                    <i class="bi bi-eye"></i>
                </button>
            `
        );
    }
}


/*
|--------------------------------------------------------------------------
| Abrir detalle de transferencia
|--------------------------------------------------------------------------
*/

document.addEventListener('click', (event) => {

    const button = event.target.closest(
        '.view-transaction-button'
    );

    if (!button) {
        return;
    }

    openTransactionDetail(
        button.dataset.transactionId
    );
});


async function openTransactionDetail(transactionId) {

    try {

        const response = await fetch(
            `/transactions/${transactionId}/detail`,
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },

                credentials: 'same-origin',
            }
        );

        if (!response.ok) {
            throw new Error(
                'No se pudo cargar el detalle de la transferencia.'
            );
        }

        const transaction = await response.json();

        /*
        |--------------------------------------------------------------------------
        | Importe
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'transaction-detail-amount'
        ).textContent =
            `${transaction.currency} ${formatTransactionAmount(
                transaction.amount
            )}`;

        /*
        |--------------------------------------------------------------------------
        | Ejecución
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'transaction-detail-executed-at'
        ).textContent =
            transaction.executed_at ?? '—';

        /*
        |--------------------------------------------------------------------------
        | Cuenta de origen
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'transaction-detail-origin'
        ).textContent =
            transaction.account?.name ??
            'Cuenta eliminada';

        document.getElementById(
            'transaction-detail-origin-currency'
        ).textContent =
            `Cuenta de origen · ${transaction.currency}`;

        /*
        |--------------------------------------------------------------------------
        | Banco de destino
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'transaction-detail-destination'
        ).textContent =
            transaction.destination_bank ?? '—';

        /*
        |--------------------------------------------------------------------------
        | Fecha
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'transaction-detail-date'
        ).textContent =
            transaction.date ?? '—';

        /*
        |--------------------------------------------------------------------------
        | Moneda
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'transaction-detail-currency'
        ).textContent =
            transaction.currency ?? '---';

        /*
        |--------------------------------------------------------------------------
        | Descripción
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'transaction-detail-description'
        ).textContent =
            transaction.description ??
            'Sin descripción';

        document.getElementById(
            'transaction-detail-execution-date'
        ).textContent =
            transaction.executed_at ?? '—';

        /*
        |--------------------------------------------------------------------------
        | Cliente asociado
        |--------------------------------------------------------------------------
        |
        | Solo se actualiza si el modal tiene
        | un elemento para mostrar el cliente.
        |
        | La vista y el endpoint se adaptarán
        | en el siguiente paso.
        |
        */

        const clientElement = document.getElementById(
            'transaction-detail-client'
        );

        if (clientElement) {

            clientElement.textContent =
                transaction.client?.name ??
                'Sin cliente asociado';

        }

        /*
        |--------------------------------------------------------------------------
        | Logo de origen
        |--------------------------------------------------------------------------
        */

        const logo = document.getElementById(
            'transaction-detail-origin-logo'
        );

        const placeholder = document.getElementById(
            'transaction-detail-origin-placeholder'
        );

        if (logo && placeholder) {

            if (transaction.account?.logo) {

                logo.src = transaction.account.logo;

                logo.alt =
                    transaction.account.name ?? '';

                logo.style.display = '';

                placeholder.style.display = 'none';

            } else {

                logo.src = '';

                logo.style.display = 'none';

                placeholder.style.display = '';

            }
        }

        /*
        |--------------------------------------------------------------------------
        | Mostrar modal
        |--------------------------------------------------------------------------
        */

        const modalElement = document.getElementById(
            'transactionDetailModal'
        );

        const modal =
            bootstrap.Modal.getOrCreateInstance(
                modalElement
            );

        modal.show();

    } catch (error) {

        console.error(error);

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
        });

    }
}


/*
|--------------------------------------------------------------------------
| Movimientos en tiempo real
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {

    const tbody = document.getElementById(
        'transactions-body'
    );

    if (!tbody) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Empresa activa
    |--------------------------------------------------------------------------
    */

    const companyId =
        document.body.dataset.companyId;

    const transactionsPage =
        document.querySelector('.transactions-page');

    const currentUserId = Number(
        transactionsPage?.dataset.userId
    );

    const canExecuteTransactions =
        transactionsPage?.dataset.canExecute === '1';

    const isSuperAdmin =
        transactionsPage?.dataset.isSuperAdmin === '1';

    if (!companyId) {

        console.error(
            'No se encontró la empresa activa en movimientos'
        );

        return;
    }

    if (!window.Echo) {

        console.error(
            'Laravel Echo no está disponible'
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Canal de empresa
    |--------------------------------------------------------------------------
    */

    const channelName =
        `dashboard.${companyId}`;

    console.log(
        'Movimientos conectado al canal:',
        channelName
    );

    window.Echo.private(channelName)

        /*
        |--------------------------------------------------------------------------
        | Nuevo movimiento
        |--------------------------------------------------------------------------
        */

        .listen('.transaction.created', (event) => {

            if (
                String(event.companyId) !==
                String(companyId)
            ) {
                return;
            }

            const transaction = event.transaction;
            const account = event.account;

            if (!transaction?.id || !account) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Evitar duplicados
            |--------------------------------------------------------------------------
            */

            const existingRow = tbody.querySelector(
                `tr[data-transaction-id="${transaction.id}"]`
            );

            if (existingRow) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Tipo de movimiento
            |--------------------------------------------------------------------------
            */

            const isChequeLiquidation =
                transaction.source ===
                'cheque_liquidation';

            const isIncome =
                transaction.type === 'income';

            const isReserve =
                transaction.type === 'reserve';

            /*
            |--------------------------------------------------------------------------
            | Fecha
            |--------------------------------------------------------------------------
            */

            const transactionDate = new Date(
                transaction.date
            );

            const formattedDate =
                transactionDate.toLocaleDateString(
                    'es-AR',
                    {
                        day: '2-digit',
                        month: '2-digit',
                    }
                );

            const formattedTime =
                transactionDate.toLocaleTimeString(
                    'es-AR',
                    {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    }
                );

            /*
            |--------------------------------------------------------------------------
            | Permisos
            |--------------------------------------------------------------------------
            */

            const canExecute =
                !isChequeLiquidation &&
                canExecuteTransactions &&
                transaction.type === 'expense' &&
                !transaction.executed_at;

            const canManage =
                !isChequeLiquidation &&
                !transaction.executed_at &&
                (
                    isSuperAdmin ||
                    Number(transaction.user?.id) ===
                    currentUserId
                );

            /*
            |--------------------------------------------------------------------------
            | Cliente
            |--------------------------------------------------------------------------
            */

            const clientHtml = transaction.client?.name
                ? `
                    <div class="text-muted small mt-1">
                        <i class="bi bi-person me-1"></i>
                        ${escapeTransactionHtml(
                            transaction.client.name
                        )}
                    </div>
                `
                : '';

            /*
            |--------------------------------------------------------------------------
            | Tipo visual
            |--------------------------------------------------------------------------
            */

            let typeHtml;

            if (isIncome) {

                typeHtml = `
                    <span class="movement-income">
                        <i class="bi bi-arrow-up"></i>
                        Ingreso
                    </span>
                `;

            } else if (isReserve) {

                typeHtml = `
                    <span class="tag reserve-tag">
                        <i class="bi bi-lock"></i>
                        Reserva
                    </span>
                `;

            } else {

                typeHtml = `
                    <span class="movement-expense">
                        <i class="bi bi-arrow-down"></i>
                        Egreso
                    </span>
                `;
            }

            /*
            |--------------------------------------------------------------------------
            | Origen del movimiento
            |--------------------------------------------------------------------------
            */

            let sourceHtml = '';

            if (isChequeLiquidation) {

                sourceHtml = `
                    <i class="bi bi-bank2"></i>
                    <span>Liquidación de cheques</span>
                `;

            } else if (transaction.source === 'api') {

                sourceHtml = `
                    <i class="bi bi-plug"></i>
                    <span>
                        ${escapeTransactionHtml(
                            transaction.api_key?.name ??
                            'Integración API'
                        )}
                    </span>
                `;

            } else if (
                transaction.source === 'import'
            ) {

                sourceHtml = `
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    <span>Importación</span>
                `;

            } else {

                const username =
                    transaction.user?.email
                        ? transaction.user.email.split('@')[0]
                        : 'Sin registro';

                sourceHtml = `
                    <i class="bi bi-person-circle"></i>
                    <span>
                        ${escapeTransactionHtml(username)}
                    </span>
                `;
            }

            /*
            |--------------------------------------------------------------------------
            | Banco destino
            |--------------------------------------------------------------------------
            */

            const destinationHtml =
                transaction.type === 'expense'
                    ? `
                        <div class="destination-bank">

                            <i class="bi bi-bank"></i>

                            <span>
                                ${escapeTransactionHtml(
                                    transaction.destination_bank ??
                                    'No informado'
                                )}
                            </span>

                        </div>

                        <div
                            class="execution-badge"
                            data-execution-badge="${transaction.id}"
                            ${transaction.executed_at
                                ? ''
                                : 'hidden'}
                        >
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Ejecutada</span>
                        </div>
                    `
                    : `
                        <span class="text-muted">
                            —
                        </span>
                    `;

            /*
            |--------------------------------------------------------------------------
            | Acciones
            |--------------------------------------------------------------------------
            */

            let actionsHtml = '';

            if (canExecute) {

                actionsHtml += `
                    <button
                        type="button"
                        class="icon-button execute-transaction-button"
                        data-transaction-id="${transaction.id}"
                        data-execute-url="/transactions/${transaction.id}/execute"
                        title="Ejecutar transferencia"
                    >
                        <i class="bi bi-send-check"></i>
                    </button>
                `;
            }

            if (canManage) {

                actionsHtml += `
                    <a
                        href="/transactions/${transaction.id}/edit"
                        class="icon-button"
                        title="Editar movimiento"
                    >
                        <i class="bi bi-pencil"></i>
                    </a>
                `;
            }

            /*
            |--------------------------------------------------------------------------
            | Fila
            |--------------------------------------------------------------------------
            */

            const row = `

                <tr data-transaction-id="${transaction.id}">

                    <!-- CONTADOR -->
                    <td data-movement-counter>
                        0
                    </td>

                    <!-- FECHA -->
                    <td>

                        <div class="movement-date">

                            <strong>
                                ${formattedDate}
                            </strong>

                            <small>
                                ${formattedTime}
                            </small>

                        </div>

                    </td>

                    <!-- CUENTA -->
                    <td>

                        <div class="account-cell">

                            ${account.logo
                                ? `
                                    <img
                                        src="/storage/${encodeURI(
                                            String(account.logo)
                                        ).replace(/"/g, '%22')}"
                                        alt="${escapeTransactionHtml(
                                            account.name
                                        )}"
                                    >
                                `
                                : `
                                    <div class="mini-logo">
                                        <i class="bi bi-bank"></i>
                                    </div>
                                `
                            }

                            <span>
                                ${escapeTransactionHtml(
                                    account.name
                                )}
                            </span>

                        </div>

                    </td>

                    <!-- BANCO DESTINO -->
                    <td>
                        ${destinationHtml}
                    </td>

                    <!-- USUARIO / ORIGEN -->
                    <td>

                        <div class="movement-user">
                            ${sourceHtml}
                        </div>

                    </td>

                    <!-- MONEDA -->
                    <td>

                        <span class="movement-currency">
                            ${escapeTransactionHtml(
                                transaction.currency ?? '---'
                            )}
                        </span>

                    </td>

                    <!-- TIPO -->
                    <td>
                        ${typeHtml}
                    </td>

                    <!-- DESCRIPCIÓN / CLIENTE -->
                    <td>

                        ${escapeTransactionHtml(
                            transaction.description ??
                            'Sin descripción'
                        )}

                        ${clientHtml}

                    </td>

                    <!-- MONTO -->
                    <td class="text-end">

                        ${isIncome
                            ? `
                                <span class="amount-income">
                                    +$${formatTransactionAmount(
                                        transaction.amount
                                    )}
                                </span>
                            `
                            : `
                                <span class="amount-expense">
                                    -$${formatTransactionAmount(
                                        transaction.amount
                                    )}
                                </span>
                            `
                        }

                    </td>

                    <!-- ACCIONES -->
                    <td>

                        <div class="table-actions">
                            ${actionsHtml}
                        </div>

                    </td>

                </tr>
            `;

            /*
            |--------------------------------------------------------------------------
            | Insertar movimiento
            |--------------------------------------------------------------------------
            */

            const emptyRow = tbody.querySelector(
                'tr[data-empty-row]'
            );

            if (emptyRow) {
                emptyRow.remove();
            }

            tbody.insertAdjacentHTML(
                'afterbegin',
                row
            );

            renumberTransactions();

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

            markTransactionAsExecuted(
                event.transaction_id
            );

        })

        /*
        |--------------------------------------------------------------------------
        | Movimiento actualizado
        |--------------------------------------------------------------------------
        |
        | El evento actual solo trae transaction_id.
        | Volvemos a consultar la tabla del servidor.
        |
        */

        .listen('.transaction.updated', (event) => {

            console.log(
                'Movimiento actualizado:',
                event.transaction_id
            );

            if (!event.transaction_id) {
                return;
            }

            syncTransactionsTable();

        });

});


/*
|--------------------------------------------------------------------------
| Ejecutar transferencia
|--------------------------------------------------------------------------
*/

document.addEventListener('click', async (event) => {

    const button = event.target.closest(
        '.execute-transaction-button'
    );

    if (!button) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmación
    |--------------------------------------------------------------------------
    */

    const result = await Swal.fire({

        icon: 'question',

        title: 'Confirmar transferencia',

        text: '¿Confirmás que esta transferencia fue realizada?',

        showCancelButton: true,

        confirmButtonText: 'Sí, confirmar',

        cancelButtonText: 'Cancelar',

        buttonsStyling: false,

        customClass: {

            popup: 'aeria-swal',

            title: 'aeria-swal-title',

            htmlContainer: 'aeria-swal-text',

            actions: 'aeria-swal-actions',

            confirmButton: 'aeria-swal-confirm',

            cancelButton: 'aeria-swal-cancel'

        }

    });

    if (!result.isConfirmed) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Datos
    |--------------------------------------------------------------------------
    */

    const transactionId =
        button.dataset.transactionId;

    const executeUrl =
        button.dataset.executeUrl;

    button.disabled = true;

    try {

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        );

        if (!csrfToken) {
            throw new Error(
                'No se encontró el token CSRF.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ejecutar operación
        |--------------------------------------------------------------------------
        */

        const response = await fetch(
            executeUrl,
            {
                method: 'PATCH',

                headers: {

                    'Accept': 'application/json',

                    'X-Requested-With': 'XMLHttpRequest',

                    'X-CSRF-TOKEN': csrfToken.content

                },

                credentials: 'same-origin',
            }
        );

        const data = await response.json();

        if (!response.ok) {

            throw new Error(
                data.message ??
                'No se pudo ejecutar la transferencia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Actualizar interfaz
        |--------------------------------------------------------------------------
        */

        markTransactionAsExecuted(
            transactionId
        );

    } catch (error) {

        button.disabled = false;

        Swal.fire({

            icon: 'error',

            title: 'Error al ejecutar',

            text: error.message,

        });

    }

});
