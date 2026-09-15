document.addEventListener('DOMContentLoaded', () => {


    if (!document.getElementById('transactions-body')) {
        return;
    }



    Echo.channel('dashboard')

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



            const row = `

<tr>

    <!-- FECHA -->
    <td>
        Hoy
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
                ${transaction.user?.name ?? 'Sin registro'}
            </span>

        </div>

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

            ${transaction.can_execute

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

            ${transaction.can_manage

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


    const confirmed = confirm(
        '¿Confirmás que esta transferencia fue realizada?'
    );


    if (!confirmed) {
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


        /*
        |--------------------------------------------------------------------------
        | Mostrar badge "Ejecutada"
        |--------------------------------------------------------------------------
        */

        const badge = document.querySelector(
            `[data-execution-badge="${transactionId}"]`
        );


        if (badge) {

            badge.style.display =
                'inline-flex';

        }


        /*
        |--------------------------------------------------------------------------
        | Quitar botón Ejecutar
        |--------------------------------------------------------------------------
        */

        button.remove();


    } catch (error) {

        button.disabled = false;

        alert(
            error.message
        );

    }

});