
/**
 * AERIA Finance
 * Módulo Cheques - Crear liquidación
 *
 * Responsabilidades:
 * - Agregar y eliminar cheques.
 * - Renumerar los campos dinámicos.
 * - Administrar condiciones particulares.
 * - Actualizar nominales y plazos.
 * - Normalizar importes antes de enviar.
 *
 * Los cálculos financieros definitivos
 * se realizan en Laravel.
 */

document.addEventListener('DOMContentLoaded', () => {

    'use strict';

    const form = document.getElementById('cheques-form');
    const container = document.getElementById('cheques-container');
    const template = document.getElementById('cheque-template');
    const addButton = document.getElementById('add-cheque-button');

    if (!form || !container || !template) return;

    const operationDate = document.getElementById(
        'cheques-operation-date'
    );

    const counter = document.getElementById('cheques-counter');
    const summaryCount = document.getElementById('summary-count');
    const summaryNominal = document.getElementById('summary-nominal');

    const MAX_CHEQUES = 100;

    const moneyFormatter = new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    const numberFormatter = new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    /*
    |--------------------------------------------------------------------------
    | CONVERSIÓN DECIMAL
    |--------------------------------------------------------------------------
    */

    function parseDecimal(value) {

        if (typeof value === 'number') {
            return Number.isFinite(value) ? value : 0;
        }

        value = String(value ?? '').trim().replace(/\s/g, '');

        if (!value) return 0;

        const comma = value.lastIndexOf(',');
        const dot = value.lastIndexOf('.');

        if (comma !== -1 && dot !== -1) {

            if (comma > dot) {
                // 1.250.000,50
                value = value.replace(/\./g, '').replace(',', '.');
            } else {
                // 1,250,000.50
                value = value.replace(/,/g, '');
            }

        } else if (comma !== -1) {

            value = value.replace(',', '.');

        } else if (/^\d{1,3}(\.\d{3})+$/.test(value)) {

            // 1.250.000
            value = value.replace(/\./g, '');

        }

        const result = Number(value);

        return Number.isFinite(result) ? result : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMATO MONETARIO
    |--------------------------------------------------------------------------
    */

    function formatMoneyInput(input) {

        if (!input.value.trim()) return;

        const amount = parseDecimal(input.value);

        if (amount <= 0) return;

        input.value = numberFormatter.format(amount);
    }

    /*
    |--------------------------------------------------------------------------
    | FECHAS Y PLAZOS
    |--------------------------------------------------------------------------
    */

    function daysBetween(start, end) {

        if (!start || !end) return null;

        const startParts = start.split('-').map(Number);
        const endParts = end.split('-').map(Number);

        if (
            startParts.length !== 3 ||
            endParts.length !== 3 ||
            !startParts.every(Number.isInteger) ||
            !endParts.every(Number.isInteger)
        ) {
            return null;
        }

        const startUTC = Date.UTC(
            startParts[0],
            startParts[1] - 1,
            startParts[2]
        );

        const endUTC = Date.UTC(
            endParts[0],
            endParts[1] - 1,
            endParts[2]
        );

        return Math.round(
            (endUTC - startUTC) / 86400000
        );
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER CHEQUES
    |--------------------------------------------------------------------------
    */

    function getCheques() {

        return Array.from(
            container.querySelectorAll('.cheque-item')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PARÁMETROS DE CADA CHEQUE
    |--------------------------------------------------------------------------
    */

    function getParameter(item, field) {

        const custom = item.querySelector(
            '.cheque-custom-toggle'
        );

        let input = null;

        if (custom?.checked) {

            input = item.querySelector(
                `.cheque-custom-input[data-field="${field}"]`
            );

        } else {

            input = document.getElementById(
                `general-${field}`
            );
        }

        return parseDecimal(input?.value);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR RESUMEN
    |--------------------------------------------------------------------------
    */

    function updateSummary() {

        const cheques = getCheques();

        let totalCents = 0;

        cheques.forEach(item => {

            const nominalInput = item.querySelector(
                '.cheque-nominal'
            );

            const dueDateInput = item.querySelector(
                '.cheque-due-date'
            );

            const nominal = Math.max(
                0,
                parseDecimal(nominalInput?.value)
            );

            const nominalCents = Math.round(
                nominal * 100
            );

            totalCents += nominalCents;

            /*
             * Nominal individual.
             */

            const nominalDisplay = item.querySelector(
                '.cheque-nominal-display'
            );

            if (nominalDisplay) {

                nominalDisplay.textContent =
                    moneyFormatter.format(
                        nominalCents / 100
                    );
            }

            /*
             * Plazo individual.
             */

            const termDisplay = item.querySelector(
                '.cheque-term'
            );

            if (termDisplay) {

                const calendarDays = daysBetween(
                    operationDate?.value,
                    dueDateInput?.value
                );

                if (
                    calendarDays !== null &&
                    calendarDays > 0
                ) {

                    const clearing = getParameter(
                        item,
                        'clearing_days'
                    );

                    termDisplay.textContent =
                        (calendarDays + clearing) + ' días';

                } else {

                    termDisplay.textContent = '—';
                }
            }
        });

        /*
         * Resumen consolidado.
         */

        if (counter) {

            counter.textContent =
                `${cheques.length} cheque(s)`;
        }

        if (summaryCount) {

            summaryCount.textContent = cheques.length;
        }

        if (summaryNominal) {

            summaryNominal.textContent =
                moneyFormatter.format(totalCents / 100);
        }

        if (addButton) {

            addButton.disabled =
                cheques.length >= MAX_CHEQUES;
        }

        /*
         * No permitimos eliminar el último cheque.
         */

        container.querySelectorAll(
            '.cheque-remove-button'
        ).forEach(button => {

            button.disabled = cheques.length <= 1;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RENUMERAR CHEQUES
    |--------------------------------------------------------------------------
    */

    function renumberCheques() {

        getCheques().forEach((item, index) => {

            item.dataset.chequeIndex = index;

            const title = item.querySelector(
                '.cheque-item-title'
            );

            if (title) {

                title.textContent =
                    `Cheque #${index + 1}`;
            }

            /*
             * Laravel espera:
             *
             * cheques[0][nominal_amount]
             * cheques[1][nominal_amount]
             */

            item.querySelectorAll('[name]').forEach(input => {

                input.name = input.name.replace(
                    /cheques\[\d+\]/,
                    `cheques[${index}]`
                );
            });
        });

        updateSummary();
    }

    /*
    |--------------------------------------------------------------------------
    | SINCRONIZAR CONDICIONES PARTICULARES
    |--------------------------------------------------------------------------
    */

    function initializeCustomValues(item) {

        item.querySelectorAll(
            '.cheque-custom-input'
        ).forEach(input => {

            const general = document.getElementById(
                `general-${input.dataset.field}`
            );

            if (general) {

                input.value = general.value;
            }
        });
    }

    function toggleCustomFields(item, enabled) {

        const fields = item.querySelector(
            '.cheque-custom-fields'
        );

        if (!fields) return;

        fields.style.display =
            enabled ? '' : 'none';

        fields.querySelectorAll('input').forEach(input => {

            input.disabled = !enabled;

            if (enabled && !input.value.trim()) {

                const general = document.getElementById(
                    `general-${input.dataset.field}`
                );

                if (general) {

                    input.value = general.value;
                }
            }
        });

        updateSummary();
    }

    /*
    |--------------------------------------------------------------------------
    | AGREGAR CHEQUE
    |--------------------------------------------------------------------------
    */

    function addCheque() {

        const cheques = getCheques();

        if (cheques.length >= MAX_CHEQUES) {
            return;
        }

        const index = cheques.length;

        const html = template.innerHTML.replace(
            /__INDEX__/g,
            index
        );

        const wrapper = document.createElement('div');

        wrapper.innerHTML = html.trim();

        const item = wrapper.firstElementChild;

        if (!item) return;

        /*
         * Inicializamos parámetros particulares.
         * Permanecen deshabilitados hasta activarlos.
         */

        initializeCustomValues(item);

        container.appendChild(item);

        renumberCheques();

        item.querySelector('.cheque-nominal')?.focus();

        item.scrollIntoView({
            behavior: window.matchMedia(
                '(prefers-reduced-motion: reduce)'
            ).matches ? 'auto' : 'smooth',
            block: 'nearest'
        });
    }

    addButton?.addEventListener('click', addCheque);

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR CHEQUE
    |--------------------------------------------------------------------------
    */

    container.addEventListener('click', event => {

        const button = event.target.closest(
            '.cheque-remove-button'
        );

        if (!button) return;

        if (getCheques().length <= 1) return;

        const item = button.closest('.cheque-item');

        if (!item) return;

        item.remove();

        renumberCheques();
    });

    /*
    |--------------------------------------------------------------------------
    | CAMBIAR CONDICIONES PARTICULARES
    |--------------------------------------------------------------------------
    */

    container.addEventListener('change', event => {

        const toggle = event.target.closest(
            '.cheque-custom-toggle'
        );

        if (!toggle) return;

        const item = toggle.closest('.cheque-item');

        if (!item) return;

        toggleCustomFields(
            item,
            toggle.checked
        );
    });

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZACIÓN AUTOMÁTICA
    |--------------------------------------------------------------------------
    */

    form.addEventListener('input', () => {

        updateSummary();
    });

    form.addEventListener('change', () => {

        updateSummary();
    });

    /*
    |--------------------------------------------------------------------------
    | INPUTS MONETARIOS DINÁMICOS
    |--------------------------------------------------------------------------
    |
    | money-input.js inicializa los campos existentes
    | al cargar la página.
    |
    | Este controlador utiliza delegación para aplicar
    | el formato a los nuevos campos.
    |
    | No agregamos listeners individuales a cada fila.
    |
    */

    container.addEventListener('focusout', event => {

        const input = event.target;

        if (!input.matches('.cheque-nominal')) {
            return;
        }

        formatMoneyInput(input);

        updateSummary();
    });

    /*
    |--------------------------------------------------------------------------
    | PREPARAR FORMULARIO PARA LARAVEL
    |--------------------------------------------------------------------------
    |
    | Los importes se envían con punto decimal.
    |
    | Ejemplo:
    |
    | Visual: 1.250.000,50
    | Envío: 1250000.50
    |
    | No alteramos los porcentajes ni los parámetros.
    | El controlador PHP normaliza esos valores.
    |
    */

    form.addEventListener('submit', () => {

        getCheques().forEach(item => {

            const input = item.querySelector(
                '.cheque-nominal'
            );

            if (!input || !input.value.trim()) {
                return;
            }

            const amount = parseDecimal(input.value);

            if (amount > 0) {

                input.value = amount.toFixed(2);
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | RESULTADO DE SIMULACIÓN
    |--------------------------------------------------------------------------
    */

    function scrollToResult() {

        const result = document.getElementById(
            'resultado-liquidacion'
        );

        if (!result) return;

        result.scrollIntoView({
            behavior: window.matchMedia(
                '(prefers-reduced-motion: reduce)'
            ).matches ? 'auto' : 'smooth',
            block: 'start'
        });
    }

    /*
    |--------------------------------------------------------------------------
    | INICIALIZACIÓN
    |--------------------------------------------------------------------------
    */

    renumberCheques();

    if (document.getElementById('resultado-liquidacion')) {

        scrollToResult();
    }

});
