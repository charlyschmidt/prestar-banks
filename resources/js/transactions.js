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

function markTransactionAsExecuted(transactionId) {

    const row =
        document.querySelector(
            `tr[data-transaction-id="${transactionId}"]`
        );

    if (!row) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Badge Ejecutada
    |--------------------------------------------------------------------------
    */

    const badge =
        row.querySelector(
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

    const actions =
        row.querySelector(
            '.table-actions'
        );

    if (!actions) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Eliminamos ejecutar / editar / eliminar
    |--------------------------------------------------------------------------
    */

    actions
        .querySelectorAll(
            '.execute-transaction-button, a[href*="/edit"], form'
        )
        .forEach(element => {
            element.remove();
        });


    /*
    |--------------------------------------------------------------------------
    | Agregamos ojo
    |--------------------------------------------------------------------------
    */

    if (
        !actions.querySelector(
            '.view-transaction-button'
        )
    ) {

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


document.addEventListener('click', (event) => {

    const button =
        event.target.closest(
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
                }
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
        | Datos principales
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'transaction-detail-amount'
        ).textContent =
            `${transaction.currency} ${Number(transaction.amount).toLocaleString(
                'es-AR',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }
            )}`;


        document.getElementById(
            'transaction-detail-executed-at'
        ).textContent =
            transaction.executed_at ?? '—';


        document.getElementById(
            'transaction-detail-origin'
        ).textContent =
            transaction.account?.name ?? 'Cuenta eliminada';


        document.getElementById(
            'transaction-detail-origin-currency'
        ).textContent =
            `Cuenta de origen · ${transaction.currency}`;


        document.getElementById(
            'transaction-detail-destination'
        ).textContent =
            transaction.destination_bank ?? '—';


        document.getElementById(
            'transaction-detail-date'
        ).textContent =
            transaction.date ?? '—';


        document.getElementById(
            'transaction-detail-currency'
        ).textContent =
            transaction.currency ?? '---';


        document.getElementById(
            'transaction-detail-description'
        ).textContent =
            transaction.description ?? 'Sin descripción';


        document.getElementById(
            'transaction-detail-execution-date'
        ).textContent =
            transaction.executed_at ?? '—';


        /*
        |--------------------------------------------------------------------------
        | Logo origen
        |--------------------------------------------------------------------------
        */

        const logo =
            document.getElementById(
                'transaction-detail-origin-logo'
            );

        const placeholder =
            document.getElementById(
                'transaction-detail-origin-placeholder'
            );


        if (transaction.account?.logo) {

            logo.src =
                transaction.account.logo;

            logo.alt =
                transaction.account.name ?? '';

            logo.style.display = '';

            placeholder.style.display = 'none';

        } else {

            logo.src = '';

            logo.style.display = 'none';

            placeholder.style.display = '';

        }


        /*
        |--------------------------------------------------------------------------
        | Abrir modal
        |--------------------------------------------------------------------------
        */

        const modalElement =
            document.getElementById(
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

document.addEventListener('DOMContentLoaded', () => {


    if (!document.getElementById('transactions-body')) {
        return;
    }



    const companyId =
        document.body.dataset.companyId;

    const transactionsPage =
        document.querySelector('.transactions-page');

    const currentUserId =
        Number(transactionsPage?.dataset.userId);

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

    const channelName =
        `dashboard.${companyId}`;

    console.log(
        'Movimientos conectado al canal:',
        channelName
    );

    Echo.private(channelName)

        .listen('.transaction.created', (event) => {


            console.log(
                'Nuevo movimiento:',
                event
            );


            const transaction = event.transaction;
            const account = event.account;



            const tbody = document.getElementById(
                'transactions-body'
            );


            const isIncome =
                [
                    'income'
                ].includes(transaction.type);

            const transactionDate =
                new Date(transaction.date);

            const formattedDate =
                transactionDate.toLocaleDateString('es-AR', {
                    day: '2-digit',
                    month: '2-digit',
                });

            const formattedTime =
                transactionDate.toLocaleTimeString('es-AR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                });

            const canExecute =
                canExecuteTransactions
                && transaction.type === 'expense'
                && !transaction.executed_at;

            const canManage =
                !transaction.executed_at
                && (
                    isSuperAdmin
                    || Number(transaction.user?.id) === currentUserId
                );

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
                    ? `<img src="/storage/${account.logo}" alt="${account.name}">`
                    : `
                                    <div class="mini-logo">
                                        <i class="bi bi-bank"></i>
                                    </div>
                                `
                }

                        <span>
                            ${account.name}
                        </span>

                    </div>

                </td>


                <!-- BANCO DESTINO -->
                <td>

                    ${transaction.type === 'expense' &&
                    transaction.destination_bank

                    ? `
                                <div class="destination-bank">

                                    <i class="bi bi-bank"></i>

                                    <span>
                                        ${transaction.destination_bank}
                                    </span>

                                </div>

                                <div
                                    class="execution-badge"
                                    data-execution-badge="${transaction.id}"
                                    style="display: none;"
                                >

                                    <i class="bi bi-check-circle-fill"></i>

                                    <span>
                                        Ejecutada
                                    </span>

                                </div>
                            `

                    : `
                                <span class="text-muted">
                                    —
                                </span>
                            `
                }

                </td>


                <!-- USUARIO -->
                <td>

                    <div class="movement-user">

                        <i class="bi bi-person-circle"></i>

                        <span>
                            ${transaction.user?.email
                    ? transaction.user.email.split('@')[0]
                    : 'Sin registro'
                }
                        </span>

                    </div>

                </td>

               <!-- MONEDA -->
                <td>
                    <span class="movement-currency">
                        ${transaction.currency ?? '---'}
                    </span>
                </td>


                <!-- TIPO -->
                <td>

                    ${isIncome

                    ? `
                                <span class="movement-income">

                                    <i class="bi bi-arrow-up"></i>

                                    Ingreso

                                </span>
                            `

                    : `
                                <span class="movement-expense">

                                    <i class="bi bi-arrow-down"></i>

                                    Egreso

                                </span>
                            `
                }

                </td>


                <!-- DESCRIPCIÓN -->
                <td>

                    ${transaction.description ?? 'Sin descripción'}

                </td>


                <!-- MONTO -->
                <td class="text-end">

                    ${isIncome

                    ? `
                                <span class="amount-income">

                                    +
                                    $${Number(transaction.amount).toLocaleString(
                        'es-AR',
                        {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }
                    )}

                                </span>
                            `

                    : `
                                <span class="amount-expense">

                                    -
                                    $${Number(transaction.amount).toLocaleString(
                        'es-AR',
                        {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }
                    )}

                                </span>
                            `
                }

                </td>


                <!-- ACCIONES -->
                <td>

                    <div class="table-actions">

                        ${canExecute

                    ? `
                                    <button
                                        type="button"
                                        class="icon-button execute-transaction-button"
                                        data-transaction-id="${transaction.id}"
                                        data-execute-url="/transactions/${transaction.id}/execute"
                                        title="Ejecutar transferencia"
                                    >

                                        <i class="bi bi-send-check"></i>

                                    </button>
                                `

                    : ''
                }

                        ${canManage

                    ? `
                                    <a
                                        href="/transactions/${transaction.id}/edit"
                                        class="icon-button"
                                        title="Editar movimiento"
                                    >

                                        <i class="bi bi-pencil"></i>

                                    </a>
                                `

                    : ''
                }

                    </div>

                </td>

            </tr>

            `;



            tbody.insertAdjacentHTML(
                'afterbegin',
                row
            );
            renumberTransactions();

        })

        .listen('.transaction.executed', (event) => {

            console.log(
                'TRANSFERENCIA EJECUTADA RECIBIDA:',
                event
            );

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


    const transactionId =
        button.dataset.transactionId;

    const executeUrl =
        button.dataset.executeUrl;


    /*
    |--------------------------------------------------------------------------
    | Bloquear botón mientras procesa
    |--------------------------------------------------------------------------
    */

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


        const response = await fetch(
            executeUrl,
            {
                method: 'PATCH',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken.content
                }
            }
        );


        const data = await response.json();


        if (!response.ok) {

            throw new Error(
                data.message ??
                'No se pudo ejecutar la transferencia.'
            );

        }


        markTransactionAsExecuted(
            transactionId
        );


    } catch (error) {

        button.disabled = false;

        alert(
            error.message
        );

    }

});