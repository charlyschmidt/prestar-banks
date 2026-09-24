const canvas = document.getElementById('welcome-particles');

if (canvas) {

    const ctx = canvas.getContext('2d');

    let width;
    let height;
    let dpr;

    let particles = [];

    let mouseX = 0;
    let mouseY = 0;

    let currentX = 0;
    let currentY = 0;

    const nebulaClouds = [
        document.querySelector('.welcome-nebula-cloud-1'),
        document.querySelector('.welcome-nebula-cloud-2'),
        document.querySelector('.welcome-nebula-cloud-3')
    ];

    /* ==========================
       RESIZE
    ========================== */

    function resize() {

        const rect = canvas.getBoundingClientRect();

        width = rect.width;
        height = rect.height;

        dpr = Math.min(
            window.devicePixelRatio || 1,
            2
        );

        canvas.width = width * dpr;
        canvas.height = height * dpr;

        ctx.setTransform(
            dpr,
            0,
            0,
            dpr,
            0,
            0
        );

        createParticles();
    }


    /* ==========================
       PARTICLES
    ========================== */

    function createParticles() {

        particles = [];

        const isMobile = width < 768;

        const total = isMobile
            ? 40
            : 100;


        for (let i = 0; i < total; i++) {

            const depth =
                0.2 + Math.random() * 0.8;

            particles.push({

                x: Math.random() * width,

                y: Math.random() * height,

                depth: depth,

                radius:
                    0.5 +
                    Math.random() * 1.2,

                alpha:
                    0.12 +
                    Math.random() * 0.30,

                speedX:
                    (Math.random() - 0.5) *
                    0.08,

                speedY:
                    (Math.random() - 0.5) *
                    0.05,

                phase:
                    Math.random() *
                    Math.PI *
                    2,

                pulseSpeed:
                    0.0005 +
                    Math.random() *
                    0.0012,

                pulseAmount:
                    0.10 +
                    Math.random() *
                    0.18

            });

        }

    }


    /* ==========================
       MOUSE
    ========================== */

    window.addEventListener(
        'mousemove',
        function (event) {

            mouseX =
                (event.clientX / window.innerWidth - 0.5) * 2;

            mouseY =
                (event.clientY / window.innerHeight - 0.5) * 2;

        }
    );


    document.addEventListener(
        'mouseleave',
        function () {

            mouseX = 0;
            mouseY = 0;

        }
    );


    /* ==========================
       ANIMATION
    ========================== */

    function animate(time) {

        ctx.clearRect(
            0,
            0,
            width,
            height
        );


        /*
         * Suavizado del mouse
         */

        currentX +=
            (mouseX - currentX) *
            0.06;

        currentY +=
            (mouseY - currentY) *
            0.06;

            /* ==========================
   NEBULOSA
========================== */

nebulaClouds.forEach(
    function (cloud, index) {

        if (!cloud) {
            return;
        }

        /*
         * Movimiento muy corto.
         * La nube es enorme, no queremos
         * desplazarla fuera de su posición.
         */

        const mouseDepth = [
            8,
            5,
            10
        ][index];

        const floatAmount = [
            6,
            4,
            7
        ][index];


        /*
         * Flotación lenta
         */

        const floatX =
            Math.sin(
                time * 0.00006 +
                index * 2.1
            ) * floatAmount;

        const floatY =
            Math.cos(
                time * 0.000045 +
                index * 1.7
            ) * floatAmount;


        /*
         * Reacción al mouse
         */

        const mouseOffsetX =
            currentX * mouseDepth;

        const mouseOffsetY =
            currentY * mouseDepth;


        cloud.style.transform =
            `translate3d(
                ${floatX + mouseOffsetX}px,
                ${floatY + mouseOffsetY}px,
                0
            )`;
    }
);

        particles.forEach(
            function (particle) {


                /* ==========================
                   MOVIMIENTO NATURAL
                ========================== */

                particle.x +=
                    particle.speedX;

                particle.y +=
                    particle.speedY;


                /*
                 * Reaparecer del otro lado
                 */

                if (particle.x < -50) {
                    particle.x = width + 50;
                }

                if (particle.x > width + 50) {
                    particle.x = -50;
                }

                if (particle.y < -50) {
                    particle.y = height + 50;
                }

                if (particle.y > height + 50) {
                    particle.y = -50;
                }


                /* ==========================
                   PARALLAX
                ========================== */

                const movement =
                    10 +
                    particle.depth * 55;

                const offsetX =
                    currentX *
                    movement;

                const offsetY =
                    currentY *
                    movement;


                /* ==========================
                   BRILLO
                ========================== */

                const pulse =
                    Math.sin(
                        time *
                        particle.pulseSpeed +
                        particle.phase
                    );

                let alpha =
                    particle.alpha +
                    pulse *
                    particle.pulseAmount;


                alpha = Math.max(
                    0.02,
                    Math.min(
                        alpha,
                        0.65
                    )
                );


                /* ==========================
                   DRAW
                ========================== */

                ctx.beginPath();

                ctx.arc(
                    particle.x + offsetX,
                    particle.y + offsetY,
                    particle.radius,
                    0,
                    Math.PI * 2
                );

                ctx.fillStyle =
                    `rgba(255,255,255,${alpha})`;

                ctx.fill();

            }
        );


        /*
         * IMPORTANTE:
         * siempre seguimos animando
         */

        requestAnimationFrame(
            animate
        );
    }


    /* ==========================
       INIT
    ========================== */

    window.addEventListener(
        'resize',
        resize
    );

    resize();

    requestAnimationFrame(
        animate
    );

}