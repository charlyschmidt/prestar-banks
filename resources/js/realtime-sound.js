const SOUND_SETTING_KEY = 'prestar_realtime_sound';


export function realtimeSoundEnabled() {

    const saved = localStorage.getItem(SOUND_SETTING_KEY);

    // Activado por defecto
    return saved === null || saved === 'true';

}


export function setRealtimeSound(enabled) {

    localStorage.setItem(
        SOUND_SETTING_KEY,
        enabled ? 'true' : 'false'
    );

}


/*
|--------------------------------------------------------------------------
| Sonido suave tipo gota
|--------------------------------------------------------------------------
*/

export function playRealtimeSound(force = false) {

    if (!force && !realtimeSoundEnabled()) {
        return;
    }

    try {

        const AudioContext =
            window.AudioContext ||
            window.webkitAudioContext;

        if (!AudioContext) {
            return;
        }

        const context = new AudioContext();

        const oscillator = context.createOscillator();
        const gain = context.createGain();


        oscillator.connect(gain);
        gain.connect(context.destination);


        /*
        |--------------------------------------------------------------------------
        | Tono
        |--------------------------------------------------------------------------
        */

        oscillator.type = 'sine';

        oscillator.frequency.setValueAtTime(
            650,
            context.currentTime
        );

        oscillator.frequency.exponentialRampToValueAtTime(
            320,
            context.currentTime + 0.16
        );


        /*
        |--------------------------------------------------------------------------
        | Volumen
        |--------------------------------------------------------------------------
        */

        gain.gain.setValueAtTime(
            0.0001,
            context.currentTime
        );

        gain.gain.exponentialRampToValueAtTime(
            0.045,
            context.currentTime + 0.015
        );

        gain.gain.exponentialRampToValueAtTime(
            0.0001,
            context.currentTime + 0.22
        );


        oscillator.start();

        oscillator.stop(
            context.currentTime + 0.23
        );


        oscillator.addEventListener('ended', () => {

            context.close();

        });


    } catch (error) {

        console.warn(
            'No se pudo reproducir el sonido del movimiento.',
            error
        );

    }

}