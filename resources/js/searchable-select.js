document.addEventListener('DOMContentLoaded', () => {

    const searchableSelects =
        document.querySelectorAll(
            '.searchable-select'
        );

    if (!searchableSelects.length) {
        return;
    }


    searchableSelects.forEach(select => {

        const input =
            select.querySelector(
                '.searchable-select-input'
            );

        const valueInput =
            select.querySelector(
                '.searchable-select-value'
            );

        const optionsContainer =
            select.querySelector(
                '.searchable-select-options'
            );

        const options =
            Array.from(
                select.querySelectorAll(
                    '.searchable-select-option'
                )
            );


        if (
            !input ||
            !valueInput ||
            !optionsContainer
        ) {
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
                .replace(
                    /[\u0300-\u036f]/g,
                    ''
                )
                .toLowerCase()
                .trim();

        }


        /*
        |--------------------------------------------------------------------------
        | Filtrar opciones
        |--------------------------------------------------------------------------
        */

        function filterOptions() {

            const search =
                normalizeText(
                    input.value
                );

            let visibleCount = 0;


            options.forEach(option => {

                const optionText =
                    normalizeText(
                        option.dataset.value
                        ??
                        option.textContent
                    );

                const visible =
                    optionText.includes(
                        search
                    );


                option.hidden =
                    !visible;


                if (visible) {
                    visibleCount++;
                }

            });


            optionsContainer.hidden =
                visibleCount === 0;

        }


        /*
        |--------------------------------------------------------------------------
        | Abrir opciones
        |--------------------------------------------------------------------------
        */

        input.addEventListener(
            'focus',
            () => {

                filterOptions();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Buscar
        |--------------------------------------------------------------------------
        */

        input.addEventListener(
            'input',
            () => {

                /*
                 * Al escribir manualmente,
                 * invalidamos la selección anterior.
                 */

                valueInput.value = '';

                filterOptions();

                valueInput.dispatchEvent(
                    new Event(
                        'change',
                        {
                            bubbles: true
                        }
                    )
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Seleccionar opción
        |--------------------------------------------------------------------------
        */

        options.forEach(option => {

            option.addEventListener(
                'click',
                () => {

                    const value =
                        option.dataset.value
                        ??
                        option.textContent.trim();


                    valueInput.value =
                        value;

                    input.value =
                        option.textContent.trim();

                    optionsContainer.hidden =
                        true;


                    /*
                     * Importante:
                     * permite que otros JS reaccionen
                     * a la selección.
                     */

                    valueInput.dispatchEvent(
                        new Event(
                            'change',
                            {
                                bubbles: true
                            }
                        )
                    );

                }
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Restaurar valor existente
        |--------------------------------------------------------------------------
        */

        if (valueInput.value) {

            const selectedOption =
                options.find(
                    option =>
                        String(
                            option.dataset.value
                        ) ===
                        String(
                            valueInput.value
                        )
                );


            input.value =
                selectedOption
                    ? selectedOption.textContent.trim()
                    : valueInput.value;

        }


        /*
        |--------------------------------------------------------------------------
        | Cerrar al hacer click afuera
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            event => {

                if (
                    !select.contains(
                        event.target
                    )
                ) {

                    optionsContainer.hidden =
                        true;

                }

            }
        );

    });

});