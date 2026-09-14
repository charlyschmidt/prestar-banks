import {
    realtimeSoundEnabled,
    setRealtimeSound,
    playRealtimeSound
} from './realtime-sound';


document.addEventListener('DOMContentLoaded', () => {

    const soundToggle =
        document.getElementById('realtimeSound');

    const testButton =
        document.getElementById('testRealtimeSound');


    if (soundToggle) {

        soundToggle.checked =
            realtimeSoundEnabled();


        soundToggle.addEventListener('change', () => {

            setRealtimeSound(
                soundToggle.checked
            );

            if (soundToggle.checked) {

                playRealtimeSound();

            }

        });

    }


    if (testButton) {

        testButton.addEventListener('click', () => {

            playRealtimeSound();

        });

    }

});