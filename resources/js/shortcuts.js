document.addEventListener('keydown', (event) => {

    /*
    |--------------------------------------------------------------------------
    | No ejecutar atajos mientras el usuario está escribiendo
    |--------------------------------------------------------------------------
    */

    const element = event.target;

    const isTyping =
        element.tagName === 'INPUT' ||
        element.tagName === 'TEXTAREA' ||
        element.tagName === 'SELECT' ||
        element.isContentEditable;

    if (isTyping) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | SHIFT + M -> Nuevo movimiento
    |--------------------------------------------------------------------------
    */

    if (
        event.shiftKey &&
        event.key.toLowerCase() === 'm'
    ) {

        event.preventDefault();

        const url =
            document.body.dataset.createTransactionUrl;

        if (url) {
            window.location.href = url;
        }
    }

});