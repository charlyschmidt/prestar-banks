document.addEventListener('DOMContentLoaded', function () {

    const modal =
        document.getElementById('resetFinancialDayModal');

    const codeElement =
        document.getElementById('resetFinancialDayCode');

    const codeInput =
        document.getElementById('resetFinancialDayCodeInput');

    const confirmButton =
        document.getElementById('confirmResetFinancialDayButton');


    if (
        !modal ||
        !codeElement ||
        !codeInput ||
        !confirmButton
    ) {
        return;
    }


    let confirmationCode = '';


    /*
    |--------------------------------------------------------------------------
    | Generar código aleatorio de 4 dígitos
    |--------------------------------------------------------------------------
    */

    function generateCode()
    {
        confirmationCode =
            String(
                Math.floor(
                    1000 + Math.random() * 9000
                )
            );

        codeElement.textContent =
            confirmationCode;

        codeInput.value =
            '';

        confirmButton.disabled =
            true;
    }


    /*
    |--------------------------------------------------------------------------
    | Al abrir el modal
    |--------------------------------------------------------------------------
    */

    modal.addEventListener(
        'show.bs.modal',
        function () {

            generateCode();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Validar código ingresado
    |--------------------------------------------------------------------------
    */

    codeInput.addEventListener(
        'input',
        function () {

            // Solo números y máximo 4 caracteres
            this.value =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 4);


            confirmButton.disabled =
                this.value !== confirmationCode;

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Limpiar al cerrar
    |--------------------------------------------------------------------------
    */

    modal.addEventListener(
        'hidden.bs.modal',
        function () {

            confirmationCode =
                '';

            codeElement.textContent =
                '----';

            codeInput.value =
                '';

            confirmButton.disabled =
                true;

        }
    );

});