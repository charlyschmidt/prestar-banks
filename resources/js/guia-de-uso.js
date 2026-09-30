document.addEventListener('DOMContentLoaded', () => {

    const links = document.querySelectorAll('.guide-nav a');
    const sections = document.querySelectorAll('.guide-section[id]');

    /*
    |--------------------------------------------------------------------------
    | Scroll suave al hacer click
    |--------------------------------------------------------------------------
    */

    links.forEach(link => {

        link.addEventListener('click', event => {

            const targetId = link.getAttribute('href');

            if (!targetId || !targetId.startsWith('#')) {
                return;
            }

            const target = document.querySelector(targetId);

            if (!target) {
                return;
            }

            event.preventDefault();

            // Header 72px + nav 58px + pequeño margen
            const offset = 150;

            const start = window.scrollY;

            const targetPosition =
                target.getBoundingClientRect().top +
                window.scrollY -
                offset;

            const distance = targetPosition - start;

            const duration = 750;

            let startTime = null;

            const easeInOutCubic = t =>
                t < 0.5
                    ? 4 * t * t * t
                    : 1 - Math.pow(-2 * t + 2, 3) / 2;


            function animation(currentTime) {

                if (!startTime) {
                    startTime = currentTime;
                }

                const elapsed = currentTime - startTime;

                const progress = Math.min(
                    elapsed / duration,
                    1
                );

                window.scrollTo(
                    0,
                    start + distance * easeInOutCubic(progress)
                );

                if (progress < 1) {

                    requestAnimationFrame(animation);

                }

            }

            requestAnimationFrame(animation);

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Marcar automáticamente la sección activa
    |--------------------------------------------------------------------------
    */

    function updateActiveSection() {

        const offset = 180;

        let currentSection = null;

        sections.forEach(section => {

            const rect = section.getBoundingClientRect();

            if (rect.top <= offset) {
                currentSection = section;
            }

        });


        if (!currentSection) {
            return;
        }


        const currentId = currentSection.id;


        links.forEach(link => {

            const targetId = link
                .getAttribute('href')
                ?.replace('#', '');

            link.classList.toggle(
                'active',
                targetId === currentId
            );

        });

    }


    window.addEventListener(
        'scroll',
        updateActiveSection,
        { passive: true }
    );


    updateActiveSection();

});