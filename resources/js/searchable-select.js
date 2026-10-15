
document.addEventListener('DOMContentLoaded', () => {

    const searchableSelects =
        document.querySelectorAll('.searchable-select');

    if (!searchableSelects.length) {
        return;
    }

    searchableSelects.forEach(select => {

        const input = select.querySelector(
            '.searchable-select-input'
        );

        const valueInput = select.querySelector(
            '.searchable-select-value'
        );

        const optionsContainer = select.querySelector(
            '.searchable-select-options'
        );

        const options = Array.from(
            select.querySelectorAll(
                '.searchable-select-option'
            )
        );

        if (!input || !valueInput || !optionsContainer) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Normalizar texto
        |--------------------------------------------------------------------------
        */

        function normalizeText(text) {

            return String(text || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();

        }

        /*
        |--------------------------------------------------------------------------
        | Mostrar TODAS las opciones
        |--------------------------------------------------------------------------
        */

        function showAllOptions() {

            options.forEach(option => {
                option.hidden = false;
            });

            optionsContainer.hidden =
                options.length === 0;

        }

        /*
        |--------------------------------------------------------------------------
        | Filtrar opciones mientras escribe
        |--------------------------------------------------------------------------
        */

        function filterOptions() {

            const search = normalizeText(input.value);

            let visibleCount = 0;

            options.forEach(option => {

                const optionText = normalizeText(
                    option.dataset.value ??
                    option.textContent
                );

                const visible = optionText.includes(search);

                option.hidden = !visible;

                if (visible) {
                    visibleCount++;
                }

            });

            optionsContainer.hidden =
                visibleCount === 0;

        }

        /*
        |--------------------------------------------------------------------------
        | Abrir selector
        |
        | Siempre mostrar todas las opciones.
        | No borrar la selección actual.
        |--------------------------------------------------------------------------
        */

        input.addEventListener('focus', () => {
            showAllOptions();
        });

        input.addEventListener('click', () => {
            showAllOptions();
        });

        /*
        |--------------------------------------------------------------------------
        | Buscar mientras escribe
        |--------------------------------------------------------------------------
        */

        input.addEventListener('input', () => {

            // Al escribir, invalidar selección anterior.
            valueInput.value = '';

            filterOptions();

            valueInput.dispatchEvent(
                new Event('change', {
                    bubbles: true
                })
            );

        });

        /*
        |--------------------------------------------------------------------------
        | Seleccionar opción
        |--------------------------------------------------------------------------
        */

        options.forEach(option => {

            option.addEventListener('click', () => {

                const value =
                    option.dataset.value ??
                    option.textContent.trim();

                valueInput.value = value;

                input.value =
                    option.textContent.trim();

                optionsContainer.hidden = true;

                /*
                 * Notificar a otros componentes
                 * sobre el cambio.
                 */

                valueInput.dispatchEvent(
                    new Event('change', {
                        bubbles: true
                    })
                );

            });

        });

        /*
        |--------------------------------------------------------------------------
        | Restaurar selección anterior
        |--------------------------------------------------------------------------
        */

        if (valueInput.value) {

            const selectedOption = options.find(
                option =>
                    String(option.dataset.value) ===
                    String(valueInput.value)
            );

            input.value = selectedOption
                ? selectedOption.textContent.trim()
                : valueInput.value;

        }

        /*
        |--------------------------------------------------------------------------
        | Cerrar al hacer clic afuera
        |--------------------------------------------------------------------------
        */

        document.addEventListener('click', event => {

            if (!select.contains(event.target)) {

                optionsContainer.hidden = true;

            }

        });

        /*
        |--------------------------------------------------------------------------
        | Cerrar con tecla Escape
        |--------------------------------------------------------------------------
        */

        input.addEventListener('keydown', event => {

            if (event.key === 'Escape') {

                optionsContainer.hidden = true;

                input.blur();

            }

        });

    });

});
