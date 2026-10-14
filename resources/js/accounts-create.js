
/*
|--------------------------------------------------------------------------
| AERIA Finance - Creación de cuentas
|--------------------------------------------------------------------------
| Mostrar saldos iniciales únicamente cuando:
| 1. Existe una jornada abierta (controlado por Blade).
| 2. La moneda está seleccionada.
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector(
        'form[action*="/accounts"]'
    );

    if (!form) {
        return;
    }

    const checkboxes = form.querySelectorAll(
        'input[name="currencies[]"]'
    );

    function updateInitialBalances() {

        checkboxes.forEach(checkbox => {

            const currency = checkbox.value;

            const container = Array.from(
                form.querySelectorAll(
                    '.account-initial-balance-item'
                )
            ).find(
                item => item.dataset.currency === currency
            );

            if (!container) {
                return;
            }

            const input = container.querySelector('input');

            container.style.display = checkbox.checked
                ? 'block'
                : 'none';

            if (input) {
                input.disabled = !checkbox.checked;
            }

        });
    }

    checkboxes.forEach(checkbox => {

        checkbox.addEventListener(
            'change',
            updateInitialBalances
        );

    });

    updateInitialBalances();

});
