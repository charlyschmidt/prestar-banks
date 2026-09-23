console.log('OPENING BALANCE IMPORT JS CARGADO');
document.addEventListener('DOMContentLoaded', function () {

    const importer =
        document.getElementById('opening-balance-import');

    const fileInput =
        document.getElementById('opening-balance-file');

    const fileButton =
        document.getElementById('opening-balance-file-button');


    /*
    |--------------------------------------------------------------------------
    | Esta vista no tiene importador
    |--------------------------------------------------------------------------
    */

    if (!importer || !fileInput || !fileButton) {
        return;
    }


    const analyzeUrl =
        importer.dataset.analyzeUrl;

    const csrfToken =
        importer.dataset.csrf;


    /*
    |--------------------------------------------------------------------------
    | Abrir selector
    |--------------------------------------------------------------------------
    */

    fileButton.addEventListener('click', function () {

        fileInput.click();

    });


    /*
    |--------------------------------------------------------------------------
    | Seleccionar y enviar archivo
    |--------------------------------------------------------------------------
    */

    fileInput.addEventListener('change', async function () {

        const file =
            this.files[0];

        if (!file) {
            return;
        }


        const originalButtonContent =
            fileButton.innerHTML;


        const formData =
            new FormData();

        formData.append(
            'file',
            file
        );


        fileButton.disabled = true;

        fileButton.innerHTML = `
            <span class="spinner-border spinner-border-sm"></span>
            Analizando...
        `;


        try {

            const response =
                await fetch(
                    analyzeUrl,
                    {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json'
                        },

                        body:
                            formData
                    }
                );


            const data =
                await response.json();


            if (!response.ok) {

                throw new Error(
                    data.message ||
                    'No se pudo analizar el archivo.'
                );

            }


            console.log(
                'IMPORTACIÓN SALDOS:',
                data
            );


            /*
            |--------------------------------------------------------------------------
            | Normalizar nombres
            |--------------------------------------------------------------------------
            */

            const normalizeName = (value) => {

                return String(value || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .replace(/\s+/g, ' ')
                    .trim();

            };


            /*
            |--------------------------------------------------------------------------
            | Inputs de saldos de la apertura
            |--------------------------------------------------------------------------
            */

            const balanceInputs =
                document.querySelectorAll(
                    '.opening-money-input[data-account-name]'
                );

            let importedCount = 0;


            /*
            |--------------------------------------------------------------------------
            | Completar saldos detectados
            |--------------------------------------------------------------------------
            */

            (data.balances || []).forEach(
                (detectedBalance) => {

                    const detectedAccount =
                        normalizeName(
                            detectedBalance.account
                        );

                    const input =
                        Array.from(balanceInputs).find(
                            (balanceInput) => {

                                const accountName =
                                    normalizeName(
                                        balanceInput.dataset.accountName
                                    );

                                const currency =
                                    String(
                                        balanceInput.dataset.currency || ''
                                    ).toUpperCase();

                                return (
                                    accountName === detectedAccount &&
                                    currency === 'ARS'
                                );
                            }
                        );

                    if (!input) {
                        return;
                    }


                    /*
                     * El money-input trabaja visualmente
                     * con formato argentino.
                     */

                    const formattedBalance =
                        Number(
                            detectedBalance.balance
                        ).toLocaleString(
                            'es-AR',
                            {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }
                        );


                    input.value =
                        formattedBalance;


                    /*
                     * Avisamos al resto del JS que
                     * el input cambió.
                     */

                    input.dispatchEvent(
                        new Event(
                            'input',
                            {
                                bubbles: true
                            }
                        )
                    );

                    input.dispatchEvent(
                        new Event(
                            'change',
                            {
                                bubbles: true
                            }
                        )
                    );


                    importedCount++;

                }
            );


            Swal.fire({
                icon: 'success',
                title: 'Saldos importados',
                text: `${importedCount} saldos fueron cargados automáticamente.`,

                confirmButtonText: 'Aceptar',

                customClass: {
                    popup: 'aeria-swal',
                    title: 'aeria-swal-title',
                    htmlContainer: 'aeria-swal-text',
                    confirmButton: 'aeria-swal-confirm'
                },

                buttonsStyling: false
            });


        } catch (error) {

            console.error(
                'Error importando saldos:',
                error
            );


            alert(
                error.message ||
                'Ocurrió un error al analizar el archivo.'
            );

        } finally {

            fileButton.disabled =
                false;

            fileButton.innerHTML =
                originalButtonContent;

            /*
             * Permite volver a seleccionar
             * el mismo archivo.
             */
            fileInput.value =
                '';

        }

    });

});