
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('cheques-confirm-form');

    const paymentMethod = document.getElementById(
        'cheques-payment-method'
    );

    const accountSelect = document.getElementById(
        'cheques-account-balance'
    );

    const destinationField = document.getElementById(
        'cheques-destination-field'
    );

    const bankSearch = document.getElementById(
        'cheques-destination-search'
    );

    const destinationBank = document.getElementById(
        'cheques-destination-bank'
    );

    const optionsContainer = document.getElementById(
        'cheques-destination-options'
    );

    /*
    |--------------------------------------------------------------------------
    | SweetAlert - Confirmar, retener y eliminar
    |--------------------------------------------------------------------------
    */

    const confirmButton = document.getElementById('cheques-confirm-button');
    const holdButton = document.getElementById('cheques-hold-button');
    const deleteButton = document.getElementById('cheques-delete-button');

    let submitting = false;

    function submitOnce(targetForm) {
        if (!targetForm || submitting) return;

        if (!targetForm.reportValidity()) return;

        submitting = true;
        targetForm.requestSubmit();
    }

    if (confirmButton) {
        confirmButton.addEventListener('click', async function () {
            if (!form || !form.reportValidity()) return;

            if (paymentMethod?.value === 'transfer' &&
                !destinationBank?.value.trim()) {
                bankSearch?.setCustomValidity(
                    'Seleccioná un banco de la lista.'
                );
                bankSearch?.reportValidity();
                bankSearch?.focus();
                return;
            }

            const amount = confirmButton.dataset.amount || '';

            const result = await Swal.fire({
                title: '¿Confirmar operación?',
                text: `Se entregarán ${amount} al cliente. Se registrará un egreso real y el cheque ingresará en cartera.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, confirmar operación',
                cancelButtonText: 'Cancelar',
                reverseButtons: false,
                customClass: {
                    popup: 'aeria-swal',
                    confirmButton: 'aeria-swal-danger',
                    cancelButton: 'aeria-swal-secondary'
                }
            });

            if (result.isConfirmed) {
                submitOnce(form);
            }
        });
    }

    if (holdButton) {
        holdButton.addEventListener('click', async function () {
            const result = await Swal.fire({
                title: '¿Retener liquidación?',
                text: 'La operación quedará retenida y no se registrarán movimientos financieros.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, retener',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'aeria-swal',
                    confirmButton: 'aeria-swal-danger',
                    cancelButton: 'aeria-swal-secondary'
                }
            });

            if (result.isConfirmed) {
                submitOnce(
                    document.getElementById('cheques-hold-form')
                );
            }
        });
    }

    if (deleteButton) {
        deleteButton.addEventListener('click', async function () {
            const result = await Swal.fire({
                title: '¿Eliminar operación?',
                text: 'Esta acción es definitiva y no se puede deshacer.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'aeria-swal',
                    confirmButton: 'aeria-swal-danger',
                    cancelButton: 'aeria-swal-secondary'
                }
            });

            if (result.isConfirmed) {
                submitOnce(
                    document.getElementById('cheques-delete-form')
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Formulario de confirmación y buscador de bancos
    |--------------------------------------------------------------------------
    */

    if (
        !form ||
        !paymentMethod ||
        !accountSelect ||
        !destinationField ||
        !bankSearch ||
        !destinationBank ||
        !optionsContainer
    ) {
        return;
    }

    const accountOptions = Array.from(
        accountSelect.querySelectorAll('option[data-account-type]')
    );

    const bankOptions = Array.from(
        optionsContainer.querySelectorAll('.searchable-select-option')
    );

    function normalize(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function closeBankOptions() {
        optionsContainer.hidden = true;
        bankSearch.setAttribute('aria-expanded', 'false');
    }

    function filterBankOptions() {
        const query = normalize(bankSearch.value);
        let visible = 0;

        bankOptions.forEach(function (option) {
            const matches = normalize(
                option.dataset.value
            ).includes(query);

            option.hidden = !matches;
            option.style.display = matches ? '' : 'none';

            if (matches) visible++;
        });

        optionsContainer.hidden = visible === 0;

        bankSearch.setAttribute(
            'aria-expanded',
            visible > 0 ? 'true' : 'false'
        );
    }

    function clearBank() {
        bankSearch.value = '';
        destinationBank.value = '';
        bankSearch.setCustomValidity('');
        closeBankOptions();
    }

    function updatePaymentMethod() {
        const method = paymentMethod.value;
        const isTransfer = method === 'transfer';

        destinationField.style.display = isTransfer ? '' : 'none';
        bankSearch.disabled = !isTransfer;
        destinationBank.disabled = !isTransfer;

        if (!isTransfer) clearBank();

        accountOptions.forEach(function (option) {
            const accountType = option.dataset.accountType;

            const compatible =
                (method === 'cash' && accountType === 'cash') ||
                (method === 'transfer' &&
                    ['bank', 'wallet'].includes(accountType));

            option.disabled = !compatible;
            option.hidden = !compatible;
            option.style.display = compatible ? '' : 'none';
        });

        const selected = accountSelect.selectedOptions[0];

        if (selected && selected.disabled) {
            accountSelect.value = '';
        }
    }

    bankOptions.forEach(function (option) {
        option.addEventListener('click', function () {
            const value = option.dataset.value || '';

            destinationBank.value = value;
            bankSearch.value = value;
            bankSearch.setCustomValidity('');

            closeBankOptions();
        });
    });

    bankSearch.addEventListener('input', function () {
        destinationBank.value = '';
        bankSearch.setCustomValidity('');
        filterBankOptions();
    });

    bankSearch.addEventListener('focus', function () {
        if (paymentMethod.value === 'transfer') {
            filterBankOptions();
        }
    });

    bankSearch.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeBankOptions();
            return;
        }

        if (event.key === 'Enter' && !optionsContainer.hidden) {
            const firstVisible = bankOptions.find(
                option => !option.hidden
            );

            if (firstVisible) {
                event.preventDefault();
                firstVisible.click();
            }
        }
    });

    document.addEventListener('click', function (event) {
        if (!destinationField.contains(event.target)) {
            closeBankOptions();
        }
    });

    form.addEventListener('submit', function (event) {
        if (paymentMethod.value !== 'transfer') return;

        if (!destinationBank.value.trim()) {
            event.preventDefault();
            submitting = false;

            bankSearch.setCustomValidity(
                'Seleccioná un banco de la lista.'
            );

            bankSearch.reportValidity();
            bankSearch.focus();
            filterBankOptions();
            return;
        }

        bankSearch.setCustomValidity('');
    });

    const previousBank = destinationBank.value.trim();

    if (previousBank) {
        const exists = bankOptions.some(
            option => option.dataset.value === previousBank
        );

        if (exists) {
            bankSearch.value = previousBank;
        } else {
            destinationBank.value = '';
        }
    }

    paymentMethod.addEventListener('change', updatePaymentMethod);
    updatePaymentMethod();
});
