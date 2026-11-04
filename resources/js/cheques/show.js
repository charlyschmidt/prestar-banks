
/*
|--------------------------------------------------------------------------
| AERIA Finance — Liquidaciones de cheques
|--------------------------------------------------------------------------
|
| Responsabilidades:
|
| - Confirmación de operaciones con SweetAlert.
| - Retener liquidaciones.
| - Eliminar liquidaciones.
| - Evitar envíos duplicados.
| - Coordinar confirmación con Vue 3.
|
| Vue administra exclusivamente los medios de pago.
|
*/

document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Formularios
    |--------------------------------------------------------------------------
    */

    const confirmForm = document.getElementById(
        'cheques-confirm-form'
    );

    const confirmButton = document.getElementById(
        'cheques-confirm-button'
    );

    const confirmStatus = document.getElementById(
        'cheques-confirm-status'
    );

    const holdForm = document.getElementById(
        'cheques-hold-form'
    );

    const holdButton = document.getElementById(
        'cheques-hold-button'
    );

    const deleteForm = document.getElementById(
        'cheques-delete-form'
    );

    const deleteButton = document.getElementById(
        'cheques-delete-button'
    );

    /*
    |--------------------------------------------------------------------------
    | Estado
    |--------------------------------------------------------------------------
    */

    let submitting = false;

    /*
     * La distribución debe estar efectivamente
     * guardada en Laravel.
     */

    const savedComplete =
        confirmButton?.dataset.savedComplete === '1';

    /*
     * Vue debe informar su estado.
     *
     * Inicialmente no permitimos confirmar
     * hasta que el componente esté preparado.
     */

    let vueReady = false;

    let vueDirty = true;

    let vueBalanced = false;

    /*
    |--------------------------------------------------------------------------
    | Estado del botón de confirmación
    |--------------------------------------------------------------------------
    */

    function updateConfirmButton() {

        if (!confirmButton) {
            return;
        }

        const canConfirm =
            savedComplete &&
            vueReady &&
            !vueDirty &&
            vueBalanced &&
            !submitting;

        confirmButton.disabled = !canConfirm;

        if (!confirmStatus) {
            return;
        }

        if (!savedComplete) {

            confirmStatus.textContent =
                'Primero guardá una distribución completa de los medios de pago.';

            return;
        }

        if (!vueReady) {

            confirmStatus.textContent =
                'Verificando los medios de pago guardados...';

            return;
        }

        if (vueDirty) {

            confirmStatus.textContent =
                'Hay modificaciones sin guardar. Guardá nuevamente los medios de pago antes de confirmar.';

            return;
        }

        if (!vueBalanced) {

            confirmStatus.textContent =
                'La distribución debe coincidir exactamente con el neto de la liquidación.';

            return;
        }

        if (submitting) {

            confirmStatus.textContent =
                'Procesando la operación...';

            return;
        }

        confirmStatus.textContent =
            'La distribución está guardada y completa. Podés confirmar la operación.';
    }

    /*
    |--------------------------------------------------------------------------
    | Comunicación con Vue 3
    |--------------------------------------------------------------------------
    |
    | El componente ChequeDeliveries.vue emitirá:
    |
    | cheques:deliveries-state
    |
    | Con:
    |
    | {
    |     dirty: boolean,
    |     balanced: boolean
    | }
    |
    | No utilizamos variables globales ni
    | accedemos al estado interno de Vue.
    |
    */

    document.addEventListener(
        'cheques:deliveries-state',
        (event) => {

            if (
                !event.detail ||
                typeof event.detail.dirty !== 'boolean' ||
                typeof event.detail.balanced !== 'boolean'
            ) {
                return;
            }

            vueReady = true;

            vueDirty = event.detail.dirty;

            vueBalanced = event.detail.balanced;

            updateConfirmButton();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Enviar formulario una única vez
    |--------------------------------------------------------------------------
    */

    function submitOnce(targetForm) {

        if (!targetForm || submitting) {
            return;
        }

        if (!targetForm.reportValidity()) {
            return;
        }

        submitting = true;

        updateConfirmButton();

        try {

            targetForm.requestSubmit();

        } catch (error) {

            submitting = false;

            updateConfirmButton();

            console.error(
                'AERIA: error al enviar el formulario.',
                error
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmar liquidación
    |--------------------------------------------------------------------------
    */

    if (confirmButton && confirmForm) {

        confirmButton.addEventListener(
            'click',
            async () => {

                if (
                    confirmButton.disabled ||
                    submitting ||
                    !savedComplete ||
                    !vueReady ||
                    vueDirty ||
                    !vueBalanced
                ) {
                    return;
                }

                const amount =
                    confirmButton.dataset.amount || '';

                const result = await Swal.fire({

                    title: '¿Confirmar liquidación?',

                    text:
                        `Se entregarán ${amount} al cliente. ` +
                        'Se descontarán los fondos de las cuentas seleccionadas, ' +
                        'se entregarán los cheques de terceros correspondientes ' +
                        'y los cheques recibidos ingresarán en cartera.',

                    icon: 'warning',

                    showCancelButton: true,

                    confirmButtonText:
                        'Sí, confirmar operación',

                    cancelButtonText:
                        'Cancelar',

                    reverseButtons: false,

                    customClass: {
                        popup: 'aeria-swal',
                        confirmButton: 'aeria-swal-danger',
                        cancelButton: 'aeria-swal-secondary'
                    }
                });

                if (!result.isConfirmed) {
                    return;
                }

                /*
                 * Verificamos nuevamente el estado.
                 *
                 * El operador podría haber modificado
                 * la distribución mientras SweetAlert
                 * estaba abierto.
                 */

                if (
                    submitting ||
                    !savedComplete ||
                    !vueReady ||
                    vueDirty ||
                    !vueBalanced
                ) {
                    updateConfirmButton();
                    return;
                }

                submitOnce(confirmForm);
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Retener liquidación
    |--------------------------------------------------------------------------
    */

    if (holdButton && holdForm) {

        holdButton.addEventListener(
            'click',
            async () => {

                if (submitting) {
                    return;
                }

                const result = await Swal.fire({

                    title: '¿Retener liquidación?',

                    text:
                        'La operación quedará retenida y no se registrarán movimientos financieros.',

                    icon: 'question',

                    showCancelButton: true,

                    confirmButtonText:
                        'Sí, retener',

                    cancelButtonText:
                        'Cancelar',

                    customClass: {
                        popup: 'aeria-swal',
                        confirmButton: 'aeria-swal-danger',
                        cancelButton: 'aeria-swal-secondary'
                    }
                });

                if (result.isConfirmed) {
                    submitOnce(holdForm);
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar liquidación
    |--------------------------------------------------------------------------
    */

    if (deleteButton && deleteForm) {

        deleteButton.addEventListener(
            'click',
            async () => {

                if (submitting) {
                    return;
                }

                const result = await Swal.fire({

                    title: '¿Eliminar operación?',

                    text:
                        'Esta acción es definitiva y no se puede deshacer.',

                    icon: 'error',

                    showCancelButton: true,

                    confirmButtonText:
                        'Sí, eliminar',

                    cancelButtonText:
                        'Cancelar',

                    customClass: {
                        popup: 'aeria-swal',
                        confirmButton: 'aeria-swal-danger',
                        cancelButton: 'aeria-swal-secondary'
                    }
                });

                if (result.isConfirmed) {
                    submitOnce(deleteForm);
                }
            }
        );
    }

    /*
|--------------------------------------------------------------------------
| Estado inicial y sincronización con Vue
|--------------------------------------------------------------------------
*/

    document.addEventListener(
        'cheques:deliveries-state',
        (event) => {
            vueReady = true;
            vueDirty = event.detail.dirty;
            vueBalanced = event.detail.balanced;

            updateConfirmButton();
        }
    );

    /*
     * Avisamos que show.js ya está escuchando.
     * Vue responderá con su estado actual.
     */

    document.dispatchEvent(
        new CustomEvent('cheques:request-deliveries-state')
    );

});
