document.addEventListener('DOMContentLoaded', function () {

    const userMeta = document.querySelector(
        'meta[name="auth-user-id"]'
    );

    if (!userMeta || !window.Echo) {
        return;
    }


    const userId = userMeta.content;

    if (!userId) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    function getCsrfToken() {

        const meta = document.querySelector(
            'meta[name="csrf-token"]'
        );

        return meta
            ? meta.content
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Formatear fecha
    |--------------------------------------------------------------------------
    */

    function formatReminderDate(dateString) {

        const date = new Date(dateString);

        return date.toLocaleString(
            'es-AR',
            {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Incrementar contadores
    |--------------------------------------------------------------------------
    */

    function incrementAlertCounters() {

        const counters = document.querySelectorAll(
            '[data-header-alert-count]'
        );

        const totals = document.querySelectorAll(
            '[data-header-alert-total]'
        );


        counters.forEach(function (counter) {

            const current =
                parseInt(counter.textContent, 10) || 0;

            counter.textContent =
                current + 1;

            counter.classList.remove(
                'd-none'
            );
        });


        totals.forEach(function (total) {

            const current =
                parseInt(total.textContent, 10) || 0;

            total.textContent =
                current + 1;

            total.classList.remove(
                'd-none'
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Reducir contadores
    |--------------------------------------------------------------------------
    */

    function decrementAlertCounters() {

        const counters = document.querySelectorAll(
            '[data-header-alert-count]'
        );

        const totals = document.querySelectorAll(
            '[data-header-alert-total]'
        );


        counters.forEach(function (counter) {

            const current =
                parseInt(counter.textContent, 10) || 0;

            const next =
                Math.max(0, current - 1);

            counter.textContent =
                next;

            if (next === 0) {

                counter.classList.add(
                    'd-none'
                );

            }
        });


        totals.forEach(function (total) {

            const current =
                parseInt(total.textContent, 10) || 0;

            const next =
                Math.max(0, current - 1);

            total.textContent =
                next;

            if (next === 0) {

                total.classList.add(
                    'd-none'
                );

            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Estado vacío del dropdown
    |--------------------------------------------------------------------------
    */

    function restoreEmptyAlertState() {

        const lists = document.querySelectorAll(
            '[data-header-alert-list]'
        );


        lists.forEach(function (list) {

            /*
             * Si todavía existe cualquier alerta,
             * no mostramos el estado vacío.
             */

            if (
                list.querySelector(
                    '.header-alert-item'
                )
            ) {
                return;
            }


            /*
             * Evitar duplicarlo.
             */

            if (
                list.querySelector(
                    '[data-header-alert-empty]'
                )
            ) {
                return;
            }


            const empty =
                document.createElement('div');

            empty.className =
                'header-alert-empty';

            empty.dataset.headerAlertEmpty =
                '';


            const icon =
                document.createElement('div');

            icon.className =
                'header-alert-empty-icon';

            icon.innerHTML =
                '<i class="bi bi-check-lg"></i>';


            const content =
                document.createElement('div');


            const title =
                document.createElement('strong');

            title.textContent =
                'Sin alertas';


            const description =
                document.createElement('span');

            description.textContent =
                'No tenés avisos pendientes.';


            content.appendChild(
                title
            );

            content.appendChild(
                description
            );


            empty.appendChild(
                icon
            );

            empty.appendChild(
                content
            );


            list.appendChild(
                empty
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Crear recordatorio para header
    |--------------------------------------------------------------------------
    */

    function createReminderElement(reminder) {

        const link =
            document.createElement('a');

        link.href =
            '/reminders';

        link.className =
            'header-alert-item';

        link.dataset.headerReminder =
            reminder.id;


        /*
         * Icono
         */

        const icon =
            document.createElement('div');

        icon.className =
            'header-alert-item-icon';


        const iconElement =
            document.createElement('i');

        iconElement.className =
            'bi bi-bell-fill';


        icon.appendChild(
            iconElement
        );


        /*
         * Contenido
         */

        const content =
            document.createElement('div');

        content.className =
            'header-alert-item-content';


        const title =
            document.createElement('strong');

        title.textContent =
            reminder.title;


        const description =
            document.createElement('span');

        description.textContent =
            'Recordatorio pendiente';


        const date =
            document.createElement('small');

        date.textContent =
            formatReminderDate(
                reminder.scheduled_at
            );


        content.appendChild(
            title
        );

        content.appendChild(
            description
        );

        content.appendChild(
            date
        );


        link.appendChild(
            icon
        );

        link.appendChild(
            content
        );


        return link;
    }


    /*
    |--------------------------------------------------------------------------
    | Agregar recordatorio al header
    |--------------------------------------------------------------------------
    */

    function addReminderToHeader(reminder) {

        const lists =
            document.querySelectorAll(
                '[data-header-alert-list]'
            );


        if (!lists.length) {
            return;
        }


        /*
         * Verificamos si ya existe.
         *
         * Mobile + desktop deberían
         * tener 0 o 2 elementos.
         */

        const existing =
            document.querySelectorAll(
                `[data-header-reminder="${reminder.id}"]`
            );


        if (existing.length > 0) {
            return;
        }


        /*
         * Quitamos "Sin alertas".
         */

        document
            .querySelectorAll(
                '[data-header-alert-empty]'
            )
            .forEach(function (empty) {

                empty.remove();

            });


        /*
         * Agregamos en mobile y desktop.
         */

        lists.forEach(function (list) {

            const element =
                createReminderElement(
                    reminder
                );

            list.appendChild(
                element
            );

        });


        /*
         * Incrementamos una sola vez.
         */

        incrementAlertCounters();
    }


    /*
    |--------------------------------------------------------------------------
    | Quitar recordatorio del header
    |--------------------------------------------------------------------------
    */

    function removeReminderFromHeader(reminderId) {

        const reminders =
            document.querySelectorAll(
                `[data-header-reminder="${reminderId}"]`
            );


        /*
         * Si no estaba en el header,
         * no debemos modificar el contador.
         */

        if (!reminders.length) {
            return;
        }


        reminders.forEach(function (reminder) {

            reminder.remove();

        });


        /*
         * El contador representa alertas,
         * no cantidad de elementos DOM.
         *
         * Mobile + desktop son la misma alerta.
         */

        decrementAlertCounters();


        /*
         * Si no quedó ninguna alerta,
         * restauramos el estado vacío.
         */

        restoreEmptyAlertState();
    }


    /*
    |--------------------------------------------------------------------------
    | Formatear fecha del toast
    |--------------------------------------------------------------------------
    */

    function formatToastDate(dateString) {

        const date = new Date(dateString);
        const now = new Date();


        const isToday =
            date.getFullYear() === now.getFullYear()
            &&
            date.getMonth() === now.getMonth()
            &&
            date.getDate() === now.getDate();


        const time =
            date.toLocaleTimeString(
                'es-AR',
                {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                }
            );


        if (isToday) {

            return `Hoy · ${time}`;

        }


        const day =
            date.toLocaleDateString(
                'es-AR',
                {
                    day: '2-digit',
                    month: '2-digit',
                }
            );


        return `${day} · ${time}`;
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar toast
    |--------------------------------------------------------------------------
    */

    function closeReminderToast(toast) {

        if (!toast) {
            return;
        }


        toast.classList.add(
            'is-hiding'
        );


        setTimeout(
            function () {

                toast.remove();

            },
            250
        );
    }

    /*
|--------------------------------------------------------------------------
| Actualizar vista de recordatorios
|--------------------------------------------------------------------------
*/

    function moveReminderToCompleted(reminderId) {

        const item = document.querySelector(
            `[data-reminders-pending-list] [data-reminder-item="${reminderId}"]`
        );


        /*
         * Si no estamos en /reminders,
         * simplemente no hacemos nada.
         */

        if (!item) {
            return;
        }


        const pendingList = document.querySelector(
            '[data-reminders-pending-list]'
        );

        const completedList = document.querySelector(
            '[data-reminders-completed-list]'
        );

        const completedSection = document.querySelector(
            '[data-reminders-completed-section]'
        );


        if (
            !pendingList
            ||
            !completedList
            ||
            !completedSection
        ) {
            return;
        }


        const title =
            item.dataset.reminderTitle || 'Recordatorio';

        const scheduled =
            item.dataset.reminderScheduled;


        /*
         * Creamos el elemento realizado.
         */

        const completedItem =
            document.createElement('div');

        completedItem.className =
            'reminder-item is-completed';

        completedItem.dataset.reminderItem =
            reminderId;


        const completedIcon =
            document.createElement('div');

        completedIcon.className =
            'reminder-completed-icon';

        completedIcon.innerHTML =
            '<i class="bi bi-check-lg"></i>';


        const content =
            document.createElement('div');

        content.className =
            'reminder-content';


        const titleElement =
            document.createElement('strong');

        titleElement.textContent =
            title;


        const meta =
            document.createElement('div');

        meta.className =
            'reminder-meta';


        const date =
            document.createElement('span');

        date.textContent =
            scheduled
                ? formatReminderDate(scheduled)
                : '';


        meta.appendChild(
            date
        );

        content.appendChild(
            titleElement
        );

        content.appendChild(
            meta
        );


        /*
         * Botón eliminar.
         *
         * Conservamos el DELETE tradicional.
         */

        const actions =
            document.createElement('div');

        actions.className =
            'reminder-actions';


        const deleteForm =
            document.createElement('form');

        deleteForm.method =
            'POST';

        deleteForm.action =
            `/reminders/${reminderId}`;


        const csrfInput =
            document.createElement('input');

        csrfInput.type =
            'hidden';

        csrfInput.name =
            '_token';

        csrfInput.value =
            getCsrfToken();


        const methodInput =
            document.createElement('input');

        methodInput.type =
            'hidden';

        methodInput.name =
            '_method';

        methodInput.value =
            'DELETE';


        const deleteButton =
            document.createElement('button');

        deleteButton.type =
            'submit';

        deleteButton.className =
            'reminder-delete-button';

        deleteButton.title =
            'Eliminar recordatorio';

        deleteButton.innerHTML =
            '<i class="bi bi-trash3"></i>';


        deleteForm.appendChild(
            csrfInput
        );

        deleteForm.appendChild(
            methodInput
        );

        deleteForm.appendChild(
            deleteButton
        );


        actions.appendChild(
            deleteForm
        );


        completedItem.appendChild(
            completedIcon
        );

        completedItem.appendChild(
            content
        );

        completedItem.appendChild(
            actions
        );


        /*
         * Quitamos pendiente.
         */

        item.remove();


        /*
         * Agregamos arriba de Realizados.
         */

        completedList.prepend(
            completedItem
        );


        completedSection.classList.remove(
            'd-none'
        );


        updateReminderViewCounters();
    }


    /*
|--------------------------------------------------------------------------
| Contadores de la vista
|--------------------------------------------------------------------------
*/

    function updateReminderViewCounters() {

        const pendingList =
            document.querySelector(
                '[data-reminders-pending-list]'
            );

        const completedList =
            document.querySelector(
                '[data-reminders-completed-list]'
            );


        if (!pendingList || !completedList) {
            return;
        }


        const pendingCount =
            pendingList.querySelectorAll(
                '[data-reminder-item]'
            ).length;


        const completedCount =
            completedList.querySelectorAll(
                '[data-reminder-item]'
            ).length;


        const pendingCounter =
            document.querySelector(
                '[data-reminders-pending-count]'
            );

        const completedCounter =
            document.querySelector(
                '[data-reminders-completed-count]'
            );


        if (pendingCounter) {

            pendingCounter.textContent =
                `${pendingCount} ${pendingCount === 1
                    ? 'recordatorio'
                    : 'recordatorios'
                }`;

        }


        if (completedCounter) {

            completedCounter.textContent =
                `${completedCount} ${completedCount === 1
                    ? 'recordatorio'
                    : 'recordatorios'
                }`;

        }


        /*
         * Si no quedan pendientes,
         * mostramos el estado vacío.
         */

        if (
            pendingCount === 0
            &&
            !pendingList.querySelector(
                '.reminders-empty'
            )
        ) {

            const empty =
                document.createElement('div');

            empty.className =
                'reminders-empty';

            empty.innerHTML = `
            <div class="reminders-empty-icon">
                <i class="bi bi-bell"></i>
            </div>

            <strong>
                No tenés recordatorios pendientes
            </strong>

            <span>
                Creá uno para recibir un aviso en la fecha que necesites.
            </span>
        `;

            pendingList.appendChild(
                empty
            );

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Marcar recordatorio como realizado
    |--------------------------------------------------------------------------
    */

    async function completeReminder(
        reminderId,
        button,
        toast
    ) {

        if (
            button.dataset.processing === 'true'
        ) {
            return;
        }


        const csrfToken =
            getCsrfToken();


        if (!csrfToken) {

            console.error(
                'No se encontró el token CSRF.'
            );

            return;
        }


        button.dataset.processing =
            'true';

        button.disabled =
            true;


        const originalHtml =
            button.innerHTML;


        button.innerHTML =
            '<i class="bi bi-hourglass-split"></i>';


        try {

            const response =
                await fetch(
                    `/reminders/${reminderId}/complete`,
                    {
                        method: 'PATCH',

                        headers: {

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest',

                        },

                    }
                );


            if (!response.ok) {

                throw new Error(
                    `HTTP ${response.status}`
                );

            }


            const data =
                await response.json();


            if (!data.success) {

                throw new Error(
                    'El servidor no pudo completar el recordatorio.'
                );

            }


            /*
             * Sacamos el recordatorio
             * del dropdown mobile + desktop.
             */

            removeReminderFromHeader(
                reminderId
            );

            moveReminderToCompleted(
                reminderId
            );


            /*
             * Cerramos el toast.
             */

            closeReminderToast(
                toast
            );

        } catch (error) {

            console.error(
                'Error al completar recordatorio:',
                error
            );


            button.disabled =
                false;

            button.dataset.processing =
                'false';

            button.innerHTML =
                originalHtml;

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Mostrar toast
    |--------------------------------------------------------------------------
    */

    function showReminderToast(reminder) {

        const container =
            document.getElementById(
                'reminder-toast-container'
            );


        if (!container) {

            console.warn(
                'No se encontró #reminder-toast-container'
            );

            return;
        }


        const existing =
            container.querySelector(
                `[data-reminder-toast="${reminder.id}"]`
            );


        if (existing) {
            return;
        }


        const toast =
            document.createElement('div');

        toast.className =
            'reminder-toast';

        toast.dataset.reminderToast =
            reminder.id;


        /*
         * Icono
         */

        const icon =
            document.createElement('div');

        icon.className =
            'reminder-toast-icon';

        icon.innerHTML =
            '<i class="bi bi-bell-fill"></i>';


        /*
         * Contenido
         */

        const content =
            document.createElement('div');

        content.className =
            'reminder-toast-content';


        const label =
            document.createElement('span');

        label.className =
            'reminder-toast-label';

        label.textContent =
            'Recordatorio';


        const title =
            document.createElement('strong');

        title.className =
            'reminder-toast-title';

        title.textContent =
            reminder.title;


        const date =
            document.createElement('small');

        date.className =
            'reminder-toast-date';

        date.textContent =
            formatToastDate(
                reminder.scheduled_at
            );


        content.appendChild(
            label
        );

        content.appendChild(
            title
        );

        content.appendChild(
            date
        );


        /*
         * Acciones
         */

        const actions =
            document.createElement('div');

        actions.className =
            'reminder-toast-actions';


        /*
         * Realizado
         */

        const completeButton =
            document.createElement('button');

        completeButton.type =
            'button';

        completeButton.className =
            'reminder-toast-complete';

        completeButton.innerHTML =
            '<i class="bi bi-check-lg"></i><span>Realizado</span>';


        completeButton.addEventListener(
            'click',
            function () {

                completeReminder(
                    reminder.id,
                    completeButton,
                    toast
                );

            }
        );


        /*
         * Cerrar
         */

        const closeButton =
            document.createElement('button');

        closeButton.type =
            'button';

        closeButton.className =
            'reminder-toast-close';

        closeButton.setAttribute(
            'aria-label',
            'Cerrar recordatorio'
        );

        closeButton.innerHTML =
            '<i class="bi bi-x-lg"></i>';


        closeButton.addEventListener(
            'click',
            function () {

                closeReminderToast(
                    toast
                );

            }
        );


        actions.appendChild(
            completeButton
        );

        actions.appendChild(
            closeButton
        );


        /*
         * Toast final
         */

        toast.appendChild(
            icon
        );

        toast.appendChild(
            content
        );

        toast.appendChild(
            actions
        );


        container.appendChild(
            toast
        );


        requestAnimationFrame(
            function () {

                toast.classList.add(
                    'is-visible'
                );

            }
        );
    }



    /*
|--------------------------------------------------------------------------
| Realizado desde la vista
|--------------------------------------------------------------------------
*/

    document.addEventListener(
        'submit',
        function (event) {

            const form =
                event.target.closest(
                    '[data-reminder-complete-form]'
                );


            if (!form) {
                return;
            }


            event.preventDefault();


            const reminderId =
                form.dataset.reminderId;


            const button =
                form.querySelector(
                    '.reminder-complete-button'
                );


            if (!reminderId || !button) {
                return;
            }


            completeReminder(
                reminderId,
                button,
                null
            );

        }
    );
    /*
    |--------------------------------------------------------------------------
    | Reverb
    |--------------------------------------------------------------------------
    */

    window.Echo
        .private(`user.${userId}`)
        .listen(
            '.reminder.due',
            function (event) {

                console.log(
                    'Recordatorio recibido:',
                    event
                );


                addReminderToHeader(
                    event
                );


                showReminderToast(
                    event
                );

            }
        );

});