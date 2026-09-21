document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Archivo seleccionado
    |--------------------------------------------------------------------------
    */

    const fileInput = document.getElementById('statement');
    const fileName = document.getElementById('statement-file-name');
    const fileDetail = document.getElementById('statement-file-detail');


    if (fileInput && fileName && fileDetail) {

        fileInput.addEventListener('change', () => {

            const file = fileInput.files?.[0];

            if (!file) {

                fileName.textContent = 'Seleccionar extracto';

                fileDetail.textContent =
                    'CSV, XLSX o XLS · Máximo 10 MB';

                return;
            }


            fileName.textContent = file.name;


            const sizeInMB = file.size / (1024 * 1024);


            if (sizeInMB < 1) {

                const sizeInKB = Math.max(
                    1,
                    Math.round(file.size / 1024)
                );

                fileDetail.textContent =
                    `${sizeInKB} KB · Archivo listo para procesar`;

            } else {

                fileDetail.textContent =
                    `${sizeInMB.toFixed(2)} MB · Archivo listo para procesar`;

            }

        });

    }



    /*
    |--------------------------------------------------------------------------
    | Bloquear formularios durante submit
    |--------------------------------------------------------------------------
    |
    | Funciona tanto para:
    |
    | 1. Subir extracto → Continuar
    | 2. Mapeo → Comparar movimientos
    |
    */

    const submitForms = [
        {
            formId: 'movement-control-form',
            buttonId: 'movement-control-submit',
        },
        {
            formId: 'movement-control-mapping-form',
            buttonId: 'movement-control-compare',
        },
    ];


    submitForms.forEach(({ formId, buttonId }) => {

        const form = document.getElementById(formId);
        const button = document.getElementById(buttonId);


        if (!form || !button) {
            return;
        }


        form.addEventListener('submit', () => {

            /*
             * Dejamos que el navegador haga primero
             * su validación HTML5.
             *
             * El evento submit solamente ocurre cuando
             * required, file, select, etc. son válidos.
             */

            if (button.disabled) {
                return;
            }


            button.disabled = true;

            button.setAttribute('aria-disabled', 'true');

            button.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm"
                    aria-hidden="true">
                </span>

                <span>
                    Procesando...
                </span>
            `;

        });

    });

});