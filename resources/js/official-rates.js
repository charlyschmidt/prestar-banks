import { createApp } from 'vue';
import OfficialRatesBar from './components/OfficialRatesBar.vue';

function mountOfficialRates() {
    const container = document.getElementById('official-rates-app');
    if (!container) return;

    let initial = { rates: {}, is_today: false, stale: true };

    try {
        initial = JSON.parse(container.dataset.initialRates || '{}');
    } catch (error) {
        console.warn('AERIA: datos iniciales de cotizaciones inválidos.', error);
    }

    // No montar Vue si falta la lista: dejar el Blade ya filtrado visible.
    // Evita que un dato de configuración inválido reactive todas las monedas.
    let visibleMarkets;
    try {
        const parsed = JSON.parse(container.dataset.visibleMarkets || 'null');
        if (!Array.isArray(parsed)) {
            console.error('AERIA: falta la selección de cotizaciones en Blade.');
            return;
        }
        visibleMarkets = parsed;
    } catch (error) {
        console.error('AERIA: configuración de cotizaciones inválida.', error);
        return;
    }

    createApp(OfficialRatesBar, { initial, visibleMarkets }).mount(container);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountOfficialRates, { once: true });
} else {
    mountOfficialRates();
}
