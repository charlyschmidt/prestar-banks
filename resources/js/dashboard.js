document.addEventListener('DOMContentLoaded', () => {

    // Solo ejecutar en el dashboard
    if (!document.querySelector('.dashboard')) {
        return;
    }

    console.log('Dashboard JS cargado');

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


        });

});

/*
Agregar movimiento a la tabla
--------------------------------------------------------------------------
*/

function addMovementToTable(event) {

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
        'income',
        'transfer_in'
    ].includes(
        movement.type
    );


    const typeHtml = isIncome
        ? `
        <span class="tag income-tag">

            <i class="bi bi-arrow-up"></i>
            Ingreso

        </span>
    `
        : `
        <span class="tag expense-tag">

            <i class="bi bi-arrow-down"></i>
            Egreso

        </span>
    `;


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

        <strong>
            ${escapeHtml(
        movement.description || ''
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

        $${formatMoney(
        movement.amount
    )}

    </td>


    <td>

        ${formatMoney(
        event.initialBalance
    ) === '0'
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

    row.classList.add(
        'movement-new'
    );


    setTimeout(() => {

        row.classList.remove(
            'movement-new'
        );

    }, 1000);

}

/*
Formatear dinero
--------------------------------------------------------------------------
*/

function formatMoney(value) {

    return Number(value || 0).toLocaleString(
        'es-AR',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
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