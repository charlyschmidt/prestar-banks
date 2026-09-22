document.addEventListener('DOMContentLoaded', function () {

    const accountSelect =
        document.getElementById('reminder_account');

    const balanceSelect =
        document.getElementById('reminder_balance');


    /*
     * Este JS puede cargarse globalmente.
     * Si no estamos en Recordatorios,
     * simplemente no hacemos nada.
     */

    if (!accountSelect || !balanceSelect) {
        return;
    }


    /*
     * Datos enviados por Blade.
     */

    let balances = {};

    try {

        balances = JSON.parse(
            accountSelect.dataset.balances || '{}'
        );

    } catch (error) {

        console.error(
            'No se pudieron cargar las monedas de las cuentas.',
            error
        );

        return;
    }


    const oldBalance =
        accountSelect.dataset.oldBalance || '';


    /*
     * Cargar monedas según cuenta.
     */

    function loadBalances() {

        const accountId =
            accountSelect.value;


        balanceSelect.innerHTML = '';


        /*
         * Sin cuenta o sin balances.
         */

        if (
            !accountId ||
            !balances[accountId] ||
            balances[accountId].length === 0
        ) {

            const option =
                document.createElement('option');


            option.value = '';


            option.textContent =
                accountId
                    ? 'Sin monedas disponibles'
                    : 'Seleccioná una cuenta primero';


            balanceSelect.appendChild(
                option
            );


            balanceSelect.disabled = true;

            return;
        }


        /*
         * Opción vacía.
         */

        const emptyOption =
            document.createElement('option');


        emptyOption.value = '';

        emptyOption.textContent =
            'Sin especificar moneda';


        balanceSelect.appendChild(
            emptyOption
        );


        /*
         * Monedas disponibles.
         */

        balances[accountId].forEach(
            function (balance) {

                const option =
                    document.createElement('option');


                option.value =
                    balance.id;


                option.textContent =
                    balance.currency;


                balanceSelect.appendChild(
                    option
                );

            }
        );


        balanceSelect.disabled = false;


        /*
         * Recuperar old() si falló
         * anteriormente la validación.
         */

        if (oldBalance) {

            balanceSelect.value =
                oldBalance;

        }

    }


    /*
     * Eventos.
     */

    accountSelect.addEventListener(
        'change',
        loadBalances
    );


    /*
     * Estado inicial.
     */

    loadBalances();

});