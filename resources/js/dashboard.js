document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    const dashboard =
        document.querySelector('.dashboard');


    /*
     * Solo ejecutar en el dashboard.
     */

    if (!dashboard) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Empresa activa
    |--------------------------------------------------------------------------
    */

    const companyId =
        document.body.dataset.companyId;


    if (!companyId) {

        console.error(
            'No se encontró la empresa activa en el dashboard'
        );

        return;
    }


    console.log(
        'Dashboard JS cargado para empresa:',
        companyId
    );


    /*
    |--------------------------------------------------------------------------
    | Resincronización al volver a la pestaña
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'visibilitychange',
        () => {

            if (
                document.visibilityState === 'visible'
            ) {

                syncDashboard();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Back Forward Cache
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'pageshow',
        event => {

            if (event.persisted) {

                syncDashboard();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Laravel Echo
    |--------------------------------------------------------------------------
    */

    if (!window.Echo) {

        console.error(
            'Laravel Echo no está disponible'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Canal por empresa
    |--------------------------------------------------------------------------
    */

    const channelName =
        `dashboard.${companyId}`;


    console.log(
        'Laravel Echo disponible'
    );

    console.log(
        'Conectando al canal:',
        channelName
    );


    window.Echo
        .private(channelName)
        .listen(
            '.transaction.created',
            (event) => {

                console.log(
                    'NUEVO MOVIMIENTO RECIBIDO:',
                    event
                );


                /*
                |--------------------------------------------------------------------------
                | Seguridad adicional
                |--------------------------------------------------------------------------
                */

                if (
                    String(event.companyId)
                    !==
                    String(companyId)
                ) {

                    console.error(
                        'Evento descartado: pertenece a otra empresa'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Identificar cuenta / moneda
                |--------------------------------------------------------------------------
                */

                const accountId =
                    event.account?.id;


                const accountBalanceId =
                    event.accountBalance?.id
                    ??
                    event.account_balance_id
                    ??
                    event.transaction?.account_balance_id;


                const currency =
                    event.accountBalance?.currency
                    ??
                    event.currency
                    ??
                    event.transaction?.currency
                    ??
                    '';


                if (!accountId) {

                    console.error(
                        'El evento no contiene account.id'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Card del banco
                |--------------------------------------------------------------------------
                */

                const card =
                    document.querySelector(
                        `.bank-card[data-account-id="${accountId}"]`
                    );


                if (card && accountBalanceId) {

                    /*
                     * Buscamos específicamente el bloque
                     * correspondiente a la moneda afectada.
                     */

                    const currencyBlock =
                        card.querySelector(
                            `[data-account-balance-id="${accountBalanceId}"]`
                        );


                    if (currencyBlock) {


                        /*
                        |--------------------------------------------------------------------------
                        | Saldo actual
                        |--------------------------------------------------------------------------
                        */

                        const balanceElement =
                            currencyBlock.querySelector(
                                '[data-balance]'
                            );


                        if (
                            balanceElement
                            &&
                            event.balance !== undefined
                        ) {

                            balanceElement.textContent =
                                formatCurrencyAmount(
                                    currency,
                                    event.balance
                                );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Ingresos
                        |--------------------------------------------------------------------------
                        */

                        const incomeElement =
                            currencyBlock.querySelector(
                                '[data-income]'
                            );


                        if (
                            incomeElement
                            &&
                            event.income !== undefined
                        ) {

                            incomeElement.innerHTML = `
                                <i class="bi bi-arrow-up"></i>
                                ${formatMoney(event.income)}
                            `;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Egresos
                        |--------------------------------------------------------------------------
                        */

                        const expenseElement =
                            currencyBlock.querySelector(
                                '[data-expense]'
                            );


                        if (
                            expenseElement
                            &&
                            event.expense !== undefined
                        ) {

                            expenseElement.innerHTML = `
                                <i class="bi bi-arrow-down"></i>
                                ${formatMoney(event.expense)}
                            `;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Reservas
                        |--------------------------------------------------------------------------
                        */

                        const reserveElement =
                            currencyBlock.querySelector(
                                '[data-reserve]'
                            );


                        if (
                            reserveElement
                            &&
                            event.reserve !== undefined
                        ) {

                            reserveElement.innerHTML = `
                                <i class="bi bi-lock"></i>
                                ${formatMoney(event.reserve)}
                            `;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Cantidad de movimientos
                        |--------------------------------------------------------------------------
                        */

                        const movementsElement =
                            currencyBlock.querySelector(
                                '[data-movements]'
                            );


                        if (
                            movementsElement
                            &&
                            event.movements !== undefined
                        ) {

                            movementsElement.innerHTML = `
                                <i class="bi bi-arrow-left-right"></i>
                                ${event.movements}
                            `;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Animación
                        |--------------------------------------------------------------------------
                        */

                        currencyBlock.classList.add(
                            'balance-updated'
                        );


                        setTimeout(() => {

                            currencyBlock.classList.remove(
                                'balance-updated'
                            );

                        }, 700);

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Header
                |--------------------------------------------------------------------------
                |
                | Si el evento ya trae los totales agrupados
                | por moneda los mostramos.
                |
                | Si todavía el evento viejo manda números,
                | no rompemos la interfaz: syncDashboard()
                | recuperará el estado real desde la BD.
                |--------------------------------------------------------------------------
                */

                if (
                    event.balanceTotal
                    &&
                    typeof event.balanceTotal === 'object'
                ) {

                    updateCurrencyTotal(
                        '[data-header-balance]',
                        event.balanceTotal
                    );

                }


                if (
                    event.dayIncome
                    &&
                    typeof event.dayIncome === 'object'
                ) {

                    updateCurrencyTotal(
                        '[data-header-income]',
                        event.dayIncome,
                        '+ '
                    );

                }


                if (
                    event.dayExpense
                    &&
                    typeof event.dayExpense === 'object'
                ) {

                    updateCurrencyTotal(
                        '[data-header-expense]',
                        event.dayExpense,
                        '- '
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Último movimiento
                |--------------------------------------------------------------------------
                */

                const lastUpdate =
                    document.querySelector(
                        '#last-update'
                    );


                if (
                    lastUpdate
                    &&
                    event.transaction
                ) {

                    const date =
                        new Date(
                            event.transaction.date
                        );


                    const hours =
                        String(
                            date.getHours()
                        ).padStart(
                            2,
                            '0'
                        );


                    const minutes =
                        String(
                            date.getMinutes()
                        ).padStart(
                            2,
                            '0'
                        );


                    let dayText = '';


                    if (event.dayDate) {

                        const dayDate =
                            new Date(
                                event.dayDate
                            );


                        const day =
                            String(
                                dayDate.getDate()
                            ).padStart(
                                2,
                                '0'
                            );


                        const month =
                            String(
                                dayDate.getMonth() + 1
                            ).padStart(
                                2,
                                '0'
                            );


                        const year =
                            dayDate.getFullYear();


                        dayText =
                            `${day}/${month}/${year}`;

                    }


                    lastUpdate.innerHTML = `
                        ${dayText
                            ? `Jornada abierta: ${dayText} · `
                            : ''
                        }
                        Último movimiento:
                        ${hours}:${minutes}
                        ·
                        ${escapeHtml(
                            event.transaction.description || ''
                        )}
                        ·
                        ${escapeHtml(
                            event.account?.name || ''
                        )}
                        ${currency
                            ? ` · ${escapeHtml(currency)}`
                            : ''
                        }
                    `;

                }


                /*
                |--------------------------------------------------------------------------
                | Tabla de movimientos
                |--------------------------------------------------------------------------
                */

                addMovementToTable(
                    normalizeRealtimeEvent(
                        event
                    )
                );


                playRealtimeSound();


                /*
                |--------------------------------------------------------------------------
                | Resincronización
                |--------------------------------------------------------------------------
                |
                | El evento puede todavía estar usando el payload
                | anterior. La BD es la fuente definitiva.
                |
                | Sincronizamos después del evento para actualizar
                | cards y totales con la estructura multimoneda.
                |--------------------------------------------------------------------------
                */

                syncDashboard();

            }
        );

});


/*
|--------------------------------------------------------------------------
| Normalizar evento realtime
|--------------------------------------------------------------------------
*/

function normalizeRealtimeEvent(event) {

    const currency =
        event.accountBalance?.currency
        ??
        event.currency
        ??
        event.transaction?.currency
        ??
        '';


    const accountBalanceId =
        event.accountBalance?.id
        ??
        event.account_balance_id
        ??
        event.transaction?.account_balance_id
        ??
        null;


    return {

        transaction: {

            ...event.transaction,

            account_balance_id:
                accountBalanceId,

            currency:
                currency,

            user:
                event.transaction?.user
                ??
                event.user
                ??
                null,

        },

        account:
            event.account
            ??
            null,

        account_balance: {

            id:
                accountBalanceId,

            currency:
                currency,

        },

        currency:
            currency,

        initialBalance:
            event.initialBalance
            ??
            event.transaction?.initial_balance
            ??
            0,

    };

}



function renumberDashboardMovements() {

    const tbody =
        document.querySelector(
            '#movements-body'
        );

    if (!tbody) {
        return;
    }

    const rows =
        tbody.querySelectorAll(
            'tr[data-transaction-id]'
        );

    const total =
        rows.length;

    rows.forEach(
        (row, index) => {

            const counter =
                row.querySelector(
                    '[data-movement-counter]'
                );

            if (counter) {

                counter.textContent =
                    total - index;

            }

        }
    );

}

/*
|--------------------------------------------------------------------------
| Agregar movimiento a la tabla
|--------------------------------------------------------------------------
*/

function addMovementToTable(event, animate = true) {

    const tbody =
        document.querySelector(
            '#movements-body'
        );


    if (!tbody) {
        return;
    }


    const movement =
        event.transaction;


    if (!movement) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Evitar duplicados
    |--------------------------------------------------------------------------
    */

    if (
        tbody.querySelector(
            `tr[data-transaction-id="${movement.id}"]`
        )
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Eliminar fila "Sin movimientos"
    |--------------------------------------------------------------------------
    */

    const emptyRow =
        tbody.querySelector(
            'tr[data-empty-row]'
        );


    if (emptyRow) {

        emptyRow.remove();

    }


    /*
    |--------------------------------------------------------------------------
    | Moneda
    |--------------------------------------------------------------------------
    */

    const currency =
        movement.currency
        ??
        event.account_balance?.currency
        ??
        event.currency
        ??
        '---';


    const accountBalanceId =
        movement.account_balance_id
        ??
        event.account_balance?.id
        ??
        '';


    /*
    |--------------------------------------------------------------------------
    | Fecha
    |--------------------------------------------------------------------------
    */

    const date =
        new Date(
            movement.date
        );


    const day =
        String(
            date.getDate()
        ).padStart(
            2,
            '0'
        );


    const month =
        String(
            date.getMonth() + 1
        ).padStart(
            2,
            '0'
        );


    const hours =
        String(
            date.getHours()
        ).padStart(
            2,
            '0'
        );


    const minutes =
        String(
            date.getMinutes()
        ).padStart(
            2,
            '0'
        );


    const dateText =
        `${day}/${month}`;


    const timeText =
        `${hours}:${minutes}`;


    /*
    |--------------------------------------------------------------------------
    | Tipo
    |--------------------------------------------------------------------------
    */

    const isIncome =
        movement.type === 'income';


    const isReserve =
        movement.type === 'reserve';


    let typeHtml;


    if (isIncome) {

        typeHtml = `
            <span class="tag income-tag">
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
            <span class="tag expense-tag">
                <i class="bi bi-arrow-down"></i>
                Egreso
            </span>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | Fila
    |--------------------------------------------------------------------------
    */

    const row =
        document.createElement(
            'tr'
        );


    row.dataset.transactionId =
        movement.id;


    if (accountBalanceId) {

        row.dataset.accountBalanceId =
            accountBalanceId;

    }


    row.innerHTML = `

    <!-- CONTEO -->
    <td data-movement-counter>
        0
    </td>

    <!-- FECHA -->
    <td>

        <div class="movement-date">

                <strong>
                    ${dateText}
                </strong>

                <small>
                    ${timeText}
                </small>

            </div>

        </td>


        <td>

            <div class="movement-user">

                <i class="bi bi-person-circle"></i>

                <span>
                    ${escapeHtml(
        movement.user?.email
            ? movement.user.email.split('@')[0]
            : 'Sin registro'
    )}
                </span>

            </div>

        </td>


        <td>

            <strong>
                ${escapeHtml(
        movement.description
        ||
        'Sin descripción'
    )}
            </strong>

        </td>


       <td>

            ${event.account?.id
                    ? `
                    <a
                        href="/accounts/${event.account.id}/movements"
                        class="account-cell-link"
                    >
                        ${escapeHtml(event.account.name || '')}
                    </a>
                `
                    : ''
                }

        </td>


        <td>

            <span class="movement-currency">
                ${escapeHtml(currency)}
            </span>

        </td>


        <td>

            ${typeHtml}

        </td>


        <td class="amount">

            ${isIncome
            ? `
                        <span class="amount-income">
                            +
                            ${formatMoney(
                movement.amount
            )}
                        </span>
                    `
            : `
                        <span class="amount-expense">
                            -
                            ${formatMoney(
                movement.amount
            )}
                        </span>
                    `
        }

        </td>


        <td>

            ${formatMoney(
            event.initialBalance
            ??
            movement.initial_balance
            ??
            0
        )}

        </td>


        <td>

            ${formatMoney(
            movement.balance_after
            ??
            0
        )}

        </td>

    `;


    /*
    |--------------------------------------------------------------------------
    | Insertar arriba
    |--------------------------------------------------------------------------
    */

    tbody.prepend(
        row
    );

    renumberDashboardMovements();

    /*
    |--------------------------------------------------------------------------
    | Animación
    |--------------------------------------------------------------------------
    */

    if (animate) {

        row.classList.add(
            'movement-new'
        );


        setTimeout(() => {

            row.classList.remove(
                'movement-new'
            );

        }, 1000);

    }

}


/*
|--------------------------------------------------------------------------
| Sincronizar dashboard con la base de datos
|--------------------------------------------------------------------------
*/

async function syncDashboard() {

    if (
        !document.querySelector(
            '.dashboard'
        )
    ) {

        return;

    }


    console.log(
        'Sincronizando dashboard...'
    );


    try {

        const response =
            await fetch(
                '/dashboard/sync',
                {
                    method: 'GET',

                    headers: {

                        'Accept':
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest'

                    },

                    cache:
                        'no-store',

                    credentials:
                        'same-origin'
                }
            );


        if (!response.ok) {

            throw new Error(
                `Error HTTP ${response.status}`
            );

        }


        const data =
            await response.json();


        /*
        |--------------------------------------------------------------------------
        | No hay jornada
        |--------------------------------------------------------------------------
        */

        if (!data.has_day) {

            window.location.reload();

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        updateCurrencyTotal(
            '[data-header-balance]',
            data.balance_total
        );


        updateCurrencyTotal(
            '[data-header-income]',
            data.day_income,
            '+ '
        );


        updateCurrencyTotal(
            '[data-header-expense]',
            data.day_expense,
            '- '
        );


        /*
        |--------------------------------------------------------------------------
        | Cards
        |--------------------------------------------------------------------------
        */

        if (
            Array.isArray(
                data.accounts
            )
        ) {

            data.accounts.forEach(
                account => {

                    const card =
                        document.querySelector(
                            `.bank-card[data-account-id="${account.id}"]`
                        );


                    if (!card) {

                        return;

                    }


                    if (
                        !Array.isArray(
                            account.balances
                        )
                    ) {

                        return;

                    }


                    account.balances.forEach(
                        balance => {

                            /*
                             * Buscamos exactamente el saldo
                             * correspondiente a esa moneda.
                             */

                            const currencyBlock =
                                card.querySelector(
                                    `[data-account-balance-id="${balance.account_balance_id}"]`
                                );


                            if (!currencyBlock) {

                                return;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Saldo
                            |--------------------------------------------------------------------------
                            */

                            const balanceElement =
                                currencyBlock.querySelector(
                                    '[data-balance]'
                                );


                            if (balanceElement) {

                                balanceElement.textContent =
                                    formatCurrencyAmount(
                                        balance.currency,
                                        balance.balance
                                    );

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Saldo inicial
                            |--------------------------------------------------------------------------
                            */

                            const initialBalanceElement =
                                currencyBlock.querySelector(
                                    '[data-initial-balance]'
                                );


                            if (
                                initialBalanceElement
                            ) {

                                initialBalanceElement.textContent =
                                    formatCurrencyAmount(
                                        balance.currency,
                                        balance.initial_balance
                                    );

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Ingresos
                            |--------------------------------------------------------------------------
                            */

                            const incomeElement =
                                currencyBlock.querySelector(
                                    '[data-income]'
                                );


                            if (incomeElement) {

                                incomeElement.innerHTML = `
                                    <i class="bi bi-arrow-up"></i>
                                    ${formatMoney(
                                    balance.income
                                )}
                                `;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Egresos
                            |--------------------------------------------------------------------------
                            */

                            const expenseElement =
                                currencyBlock.querySelector(
                                    '[data-expense]'
                                );


                            if (expenseElement) {

                                expenseElement.innerHTML = `
                                    <i class="bi bi-arrow-down"></i>
                                    ${formatMoney(
                                    balance.expense
                                )}
                                `;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Reservas
                            |--------------------------------------------------------------------------
                            */

                            const reserveElement =
                                currencyBlock.querySelector(
                                    '[data-reserve]'
                                );


                            if (reserveElement) {

                                reserveElement.innerHTML = `
                                    <i class="bi bi-lock"></i>
                                    ${formatMoney(
                                    balance.reserve
                                )}
                                `;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Movimientos
                            |--------------------------------------------------------------------------
                            */

                            const movementsElement =
                                currencyBlock.querySelector(
                                    '[data-movements]'
                                );


                            if (
                                movementsElement
                            ) {

                                movementsElement.innerHTML = `
                                    <i class="bi bi-arrow-left-right"></i>
                                    ${balance.movements ?? 0}
                                `;

                            }

                        }
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Tabla
        |--------------------------------------------------------------------------
        */

        const tbody =
            document.querySelector(
                '#movements-body'
            );


        if (tbody) {

            /*
             * La verdad viene nuevamente de la BD.
             */

            tbody.innerHTML = '';


            if (
                Array.isArray(
                    data.movements
                )
                &&
                data.movements.length
            ) {

                /*
                 * addMovementToTable usa prepend().
                 *
                 * Recorremos al revés para que el
                 * movimiento más nuevo quede arriba.
                 */

                [...data.movements]
                    .reverse()
                    .forEach(
                        movement => {

                            addMovementToTable(
                                {

                                    transaction: {

                                        id:
                                            movement.id,

                                        account_balance_id:
                                            movement.account_balance_id,

                                        currency:
                                            movement.currency
                                            ??
                                            movement.account_balance?.currency
                                            ??
                                            '',

                                        type:
                                            movement.type,

                                        amount:
                                            movement.amount,

                                        description:
                                            movement.description,

                                        date:
                                            movement.date,

                                        initial_balance:
                                            movement.initial_balance,

                                        balance_after:
                                            movement.balance_after,

                                        user:
                                            movement.user

                                    },

                                    account:
                                        movement.account,

                                    account_balance:
                                        movement.account_balance,

                                    currency:
                                        movement.currency
                                        ??
                                        movement.account_balance?.currency
                                        ??
                                        '',

                                    initialBalance:
                                        movement.initial_balance

                                },
                                false
                            );

                        }
                    );

            } else {

                /*
                 * No hay movimientos.
                 */

                const row =
                    document.createElement(
                        'tr'
                    );


                row.dataset.emptyRow =
                    'true';


                row.innerHTML = `
                    <td
                        colspan="10"
                        class="text-center"
                    >
                        Sin movimientos todavía.
                    </td>
                `;


                tbody.appendChild(
                    row
                );

            }

        }


        console.log(
            'Dashboard sincronizado correctamente'
        );


    } catch (error) {

        console.error(
            'Error sincronizando dashboard:',
            error
        );

    }

}


/*
|--------------------------------------------------------------------------
| Actualizar total agrupado por moneda
|--------------------------------------------------------------------------
*/

function updateCurrencyTotal(
    selector,
    totals,
    prefix = ''
) {

    const element =
        document.querySelector(selector);


    if (!element) {
        return;
    }


    if (
        !totals ||
        typeof totals !== 'object' ||
        Array.isArray(totals)
    ) {

        element.innerHTML = `
            <span class="header-currency-value">
                ${prefix}0,00
            </span>
        `;

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Mostrar únicamente monedas con importe distinto de cero
    |--------------------------------------------------------------------------
    */

    const entries =
        Object.entries(totals)
            .filter(
                ([currency, amount]) =>
                    Number(amount) !== 0
            );


    /*
    |--------------------------------------------------------------------------
    | Todo está en cero
    |--------------------------------------------------------------------------
    |
    | No asumimos ninguna moneda.
    |--------------------------------------------------------------------------
    */

    if (!entries.length) {

        element.innerHTML = `
            <span class="header-currency-value">
                ${prefix}0,00
            </span>
        `;

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Valores por moneda
    |--------------------------------------------------------------------------
    */

    element.innerHTML =
        entries
            .map(
                ([currency, amount]) => `
                    <span class="header-currency-value">
                        ${escapeHtml(prefix)}
                        ${escapeHtml(currency)}
                        ${formatMoney(amount)}
                    </span>
                `
            )
            .join('');

}


/*
|--------------------------------------------------------------------------
| Formatear moneda + importe
|--------------------------------------------------------------------------
*/

function formatCurrencyAmount(
    currency,
    value
) {

    const code =
        currency
        ||
        '';


    return `${code} ${formatMoney(value)}`.trim();

}


/*
|--------------------------------------------------------------------------
| Formatear dinero
|--------------------------------------------------------------------------
*/

function formatMoney(value) {

    const number =
        Number(value);


    if (
        Number.isNaN(number)
    ) {

        return '0,00';

    }


    return number.toLocaleString(
        'es-AR',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );

}


/*
|--------------------------------------------------------------------------
| Escapar HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    const div =
        document.createElement(
            'div'
        );


    div.textContent =
        value ?? '';


    return div.innerHTML;

}


/*
|--------------------------------------------------------------------------
| Sonido realtime
|--------------------------------------------------------------------------
|
| Conservamos compatibilidad con la función utilizada
| anteriormente por el dashboard.
|--------------------------------------------------------------------------
*/

function playRealtimeSound() {

    try {

        const audio =
            document.querySelector(
                '#realtime-sound'
            );


        if (
            audio
            &&
            typeof audio.play === 'function'
        ) {

            audio.currentTime = 0;

            audio
                .play()
                .catch(() => { });

        }

    } catch (error) {

        /*
         * El sonido nunca debe romper
         * la actualización del dashboard.
         */

    }

}