import { playRealtimeSound } from './realtime-sound';

document.addEventListener('DOMContentLoaded', () => {

    // Solo ejecutar en el dashboard
    if (!document.querySelector('.dashboard')) {
        return;
    }

    console.log('Dashboard JS cargado');

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

    if (!window.Echo) {
        console.error('Laravel Echo no está disponible');
        return;
    }

    console.log('Laravel Echo disponible');
    console.log('Conectando al canal dashboard...');


    window.Echo
        .channel('dashboard')
        .listen('.transaction.created', (event) => {

            console.log('NUEVO MOVIMIENTO RECIBIDO:', event);


            /*
            |--------------------------------------------------------------------------
            | Datos
            |--------------------------------------------------------------------------
            */

            const accountId = event.account?.id;

            if (!accountId) {
                console.error('El evento no contiene account.id');
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CARD DEL BANCO
            |--------------------------------------------------------------------------
            */

            const card = document.querySelector(
                `.bank-card[data-account-id="${accountId}"]`
            );


            if (card) {

                const balanceElement = card.querySelector(
                    '[data-balance]'
                );

                if (balanceElement) {

                    balanceElement.textContent =
                        '$' + formatMoney(event.balance);

                }


                const incomeElement = card.querySelector(
                    '[data-income]'
                );

                if (incomeElement) {

                    incomeElement.innerHTML = `
                    <i class="bi bi-arrow-up"></i>
                    $${formatMoney(event.income)}
                `;

                }


                const expenseElement = card.querySelector(
                    '[data-expense]'
                );

                if (expenseElement) {

                    expenseElement.innerHTML = `
                    <i class="bi bi-arrow-down"></i>
                    $${formatMoney(event.expense)}
                `;

                }

                const reserveElement = card.querySelector(
                    '[data-reserve]'
                );

                if (reserveElement) {

                    reserveElement.innerHTML = `
                        <i class="bi bi-lock"></i>
                        $${formatMoney(event.reserve)}
                    `;

                }


                const movementsElement = card.querySelector(
                    '[data-movements]'
                );

                if (movementsElement) {

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

                card.classList.add('balance-updated');

                setTimeout(() => {

                    card.classList.remove(
                        'balance-updated'
                    );

                }, 700);

            }


            /*
            |--------------------------------------------------------------------------
            | HEADER
            |--------------------------------------------------------------------------
            */

            const headerBalance = document.querySelector(
                '[data-header-balance]'
            );

            if (headerBalance) {

                headerBalance.textContent =
                    '$' + formatMoney(event.balanceTotal);

            }


            const headerIncome = document.querySelector(
                '[data-header-income]'
            );

            if (headerIncome) {

                headerIncome.textContent =
                    '+ $' + formatMoney(event.dayIncome);

            }


            const headerExpense = document.querySelector(
                '[data-header-expense]'
            );

            if (headerExpense) {

                headerExpense.textContent =
                    '- $' + formatMoney(event.dayExpense);

            }

            /*
            |--------------------------------------------------------------------------
            | Último movimiento
            |--------------------------------------------------------------------------
            */

            const lastUpdate = document.querySelector(
                '#last-update'
            );

            if (lastUpdate && event.transaction) {

                const date = new Date(
                    event.transaction.date
                );

                const hours = String(
                    date.getHours()
                ).padStart(2, '0');

                const minutes = String(
                    date.getMinutes()
                ).padStart(2, '0');

                const dayDate = new Date(
                    event.dayDate
                );

                const day = String(
                    dayDate.getDate()
                ).padStart(2, '0');

                const month = String(
                    dayDate.getMonth() + 1
                ).padStart(2, '0');

                const year =
                    dayDate.getFullYear();

                lastUpdate.innerHTML = `
                    Jornada abierta:
                    ${day}/${month}/${year}
                    · Último movimiento:
                    ${hours}:${minutes}
                    ·
                    ${escapeHtml(event.transaction.description || '')}
                    ·
                    ${escapeHtml(event.account.name)}
                `;

            }

            /*
            |--------------------------------------------------------------------------
            | TABLA DE MOVIMIENTOS
            |--------------------------------------------------------------------------
            */

            addMovementToTable(event);
            playRealtimeSound();

        });




});

/*
Agregar movimiento a la tabla
--------------------------------------------------------------------------
*/

function addMovementToTable(
    event,
    animate = true
) {

    const tbody = document.querySelector(
        '#movements-body'
    );

    if (!tbody) {
        return;
    }


    const movement = event.transaction;


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
            `tr[data-movement-id="${movement.id}"]`
        )
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Fecha
    |--------------------------------------------------------------------------
    */

    const date = new Date(movement.date);


    const day = String(
        date.getDate()
    ).padStart(2, '0');


    const month = String(
        date.getMonth() + 1
    ).padStart(2, '0');


    const hours = String(
        date.getHours()
    ).padStart(2, '0');


    const minutes = String(
        date.getMinutes()
    ).padStart(2, '0');


    const dateText =
        `${day}/${month}`;


    const timeText =
        `${hours}:${minutes}`;


    /*
    |--------------------------------------------------------------------------
    | Tipo
    |--------------------------------------------------------------------------
    */

    const isIncome = [
        'income'
    ].includes(
        movement.type
    );

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

    const row = document.createElement('tr');


    row.dataset.movementId =
        movement.id;


    row.innerHTML = `

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
        movement.user?.name || 'Sin registro'
    )}
            </span>

        </div>

    </td>


    <td>

        <strong>
            ${escapeHtml(
        movement.description || 'Sin descripción'
    )}
        </strong>

    </td>


    <td>

        ${escapeHtml(
        event.account.name
    )}

    </td>


    <td>

        ${typeHtml}

    </td>


   <td class="amount">

    ${isIncome
            ? `
                <span class="amount-income">
                    +
                    $${formatMoney(
                movement.amount
            )}
                </span>
            `
            : `
                <span class="amount-expense">
                    -
                    $${formatMoney(
                movement.amount
            )}
                </span>
            `
        }

</td>


    <td>

        ${formatMoney(event.initialBalance) === '0,00'
            ? ''
            : '$' + formatMoney(
                event.initialBalance
            )
        }

    </td>


    <td>

        $${formatMoney(
            movement.balance_after
        )}

    </td>

`;


    /*
    |--------------------------------------------------------------------------
    | Insertar arriba
    |--------------------------------------------------------------------------
    */

    tbody.prepend(row);


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

    if (!document.querySelector('.dashboard')) {
        return;
    }


    console.log('Sincronizando dashboard...');


    try {

        const response = await fetch(
            '/dashboard/sync',
            {
                method: 'GET',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },

                cache: 'no-store'
            }
        );


        if (!response.ok) {

            throw new Error(
                `Error HTTP ${response.status}`
            );

        }


        const data = await response.json();


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
        | HEADER
        |--------------------------------------------------------------------------
        */

        const headerBalance =
            document.querySelector(
                '[data-header-balance]'
            );

        if (headerBalance) {

            headerBalance.textContent =
                '$' + formatMoney(
                    data.balance_total
                );

        }


        const headerIncome =
            document.querySelector(
                '[data-header-income]'
            );

        if (headerIncome) {

            headerIncome.textContent =
                '+ $' + formatMoney(
                    data.day_income
                );

        }


        const headerExpense =
            document.querySelector(
                '[data-header-expense]'
            );

        if (headerExpense) {

            headerExpense.textContent =
                '- $' + formatMoney(
                    data.day_expense
                );

        }


        /*
        |--------------------------------------------------------------------------
        | CARDS
        |--------------------------------------------------------------------------
        */

        data.accounts.forEach(account => {

            const card =
                document.querySelector(
                    `.bank-card[data-account-id="${account.id}"]`
                );


            if (!card) {
                return;
            }


            const balanceElement =
                card.querySelector(
                    '[data-balance]'
                );

            if (balanceElement) {

                balanceElement.textContent =
                    '$' + formatMoney(
                        account.balance
                    );

            }


            const incomeElement =
                card.querySelector(
                    '[data-income]'
                );

            if (incomeElement) {

                incomeElement.innerHTML = `
                    <i class="bi bi-arrow-up"></i>
                    $${formatMoney(account.income)}
                `;

            }


            const expenseElement =
                card.querySelector(
                    '[data-expense]'
                );

            if (expenseElement) {

                expenseElement.innerHTML = `
                    <i class="bi bi-arrow-down"></i>
                    $${formatMoney(account.expense)}
                `;


            }

            const reserveElement =
                card.querySelector(
                    '[data-reserve]'
                );

            if (reserveElement) {

                reserveElement.innerHTML = `
                    <i class="bi bi-lock"></i>
                    $${formatMoney(account.reserve)}
                `;

            }

            const movementsElement =
                card.querySelector(
                    '[data-movements]'
                );

            if (movementsElement) {

                movementsElement.innerHTML = `
                    <i class="bi bi-arrow-left-right"></i>
                    ${account.movements}
                `;

            }

        });


        /*
        |--------------------------------------------------------------------------
        | TABLA
        |--------------------------------------------------------------------------
        */

        const tbody =
            document.querySelector(
                '#movements-body'
            );


        if (tbody) {

            /*
             * Limpiamos la tabla porque ahora
             * la verdad viene nuevamente de la BD.
             */

            tbody.innerHTML = '';


            /*
             * addMovementToTable usa prepend().
             *
             * Recorremos al revés para que finalmente
             * el movimiento más nuevo quede arriba.
             */

            [...data.movements]
                .reverse()
                .forEach(movement => {

                    addMovementToTable(
                        {
                            transaction: {
                                id: movement.id,
                                type: movement.type,
                                amount: movement.amount,
                                description: movement.description,
                                date: movement.date,
                                balance_after:
                                    movement.balance_after,

                                user: movement.user
                            },

                            account:
                                movement.account,

                            initialBalance:
                                movement.initial_balance

                        },
                        false
                    );

                });

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
Formatear dinero
--------------------------------------------------------------------------
*/
function formatMoney(value) {

    const number = Number(value);

    if (Number.isNaN(number)) {
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
Escapar HTML
--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    const div =
        document.createElement('div');

    div.textContent =
        value;

    return div.innerHTML;

}