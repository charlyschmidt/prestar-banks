document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | API KEY GENERADA
    |--------------------------------------------------------------------------
    */

    const generatedKeyData =
        document.getElementById('generatedApiKeyData');


    if (generatedKeyData) {

        const apiKey =
            generatedKeyData.dataset.apiKey;

        const integrationName =
            generatedKeyData.dataset.integrationName;


        Swal.fire({
            heightAuto: false,

            scrollbarPadding: false,

            icon: 'success',

            title: 'API Key generada',

            html: `

                <div class="api-generated-content">

                    <p>
                        Clave para
                        <strong>${integrationName}</strong>
                    </p>

                    <p>
                        Copiala y guardala en un lugar seguro.
                        <strong>
                            Esta clave no volverá a mostrarse.
                        </strong>
                    </p>

                    <input
                        id="generatedApiKey"
                        class="settings-input"
                        value="${apiKey}"
                        readonly
                    >

                </div>

            `,

            confirmButtonText: 'Copiar API Key',

            showCancelButton: true,

            cancelButtonText: 'Cerrar',

            buttonsStyling: false,

            customClass: {

                popup: 'aeria-swal',

                title: 'aeria-swal-title',

                htmlContainer: 'aeria-swal-text',

                actions: 'aeria-swal-actions',

                confirmButton: 'aeria-swal-confirm',

                cancelButton: 'aeria-swal-cancel'

            },

            preConfirm: async () => {

                try {

                    await navigator.clipboard.writeText(
                        apiKey
                    );

                } catch (error) {

                    const input =
                        document.getElementById(
                            'generatedApiKey'
                        );

                    input.select();

                    document.execCommand('copy');

                }

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | REVOCAR API KEY
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.api-revoke-form')
        .forEach(form => {

            form.addEventListener(
                'submit',
                async event => {

                    event.preventDefault();


                    const result =
                        await Swal.fire({

                            icon: 'warning',

                            title: 'Revocar API Key',

                            text:
                                'El sistema que utiliza esta clave dejará de tener acceso a AERIA.',

                            showCancelButton: true,

                            confirmButtonText:
                                'Sí, revocar',

                            cancelButtonText:
                                'Cancelar',

                            buttonsStyling: false,

                            customClass: {

                                popup:
                                    'aeria-swal',

                                title:
                                    'aeria-swal-title',

                                htmlContainer:
                                    'aeria-swal-text',

                                actions:
                                    'aeria-swal-actions',

                                confirmButton:
                                    'aeria-swal-confirm-danger',

                                cancelButton:
                                    'aeria-swal-cancel'

                            }

                        });


                    if (!result.isConfirmed) {
                        return;
                    }


                    form.submit();

                }
            );

        });

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR API KEY
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.api-delete-form')
        .forEach(form => {

            form.addEventListener(
                'submit',
                async event => {

                    event.preventDefault();

                    const result = await Swal.fire({

                        icon: 'warning',

                        title: 'Eliminar API Key',

                        text:
                            'La API Key será eliminada definitivamente del historial.',

                        showCancelButton: true,

                        confirmButtonText:
                            'Sí, eliminar',

                        cancelButtonText:
                            'Cancelar',

                        buttonsStyling: false,

                        customClass: {

                            popup:
                                'aeria-swal',

                            title:
                                'aeria-swal-title',

                            htmlContainer:
                                'aeria-swal-text',

                            actions:
                                'aeria-swal-actions',

                            confirmButton:
                                'aeria-swal-confirm-danger',

                            cancelButton:
                                'aeria-swal-cancel'

                        }

                    });

                    if (!result.isConfirmed) {
                        return;
                    }

                    form.submit();

                }
            );

        });

});