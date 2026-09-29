document.addEventListener('DOMContentLoaded', () => {

    const payerEmailInput = document.getElementById(
        'subscription-payer-email'
    );

    const forms = document.querySelectorAll(
        '.subscription-payer-form'
    );

    if (!payerEmailInput || !forms.length) {
        return;
    }


    forms.forEach((form) => {

        form.addEventListener('submit', (event) => {

            const email = payerEmailInput.value
                .trim()
                .toLowerCase();

            /*
            |--------------------------------------------------------------------------
            | Validar email
            |--------------------------------------------------------------------------
            */

            if (
                !email
                || !payerEmailInput.checkValidity()
            ) {
                event.preventDefault();

                payerEmailInput.reportValidity();
                payerEmailInput.focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Copiar email al formulario enviado
            |--------------------------------------------------------------------------
            */

            const hiddenInput = form.querySelector(
                '.subscription-payer-email-hidden'
            );

            if (!hiddenInput) {
                event.preventDefault();
                return;
            }

            hiddenInput.value = email;

        });

    });

});