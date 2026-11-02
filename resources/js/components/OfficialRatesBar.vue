<script setup>
import { computed, onMounted, onBeforeUnmount, shallowRef } from 'vue';

const props = defineProps({
    initial: { type: Object, default: () => ({ rates: {}, stale: true }) },
    visibleMarkets: { type: Array, default: () => [] },
});

const data = shallowRef(props.initial);

const markets = [
    { code: 'OFICIAL', name: 'USD Oficial', short: 'USD Ofic.' },
    { code: 'EUR_OFICIAL', name: 'EUR Oficial', short: 'EUR Ofic.' },
    { code: 'BRL_OFICIAL', name: 'BRL Oficial', short: 'BRL Ofic.' },
    { code: 'BLUE', name: 'USD Blue', short: 'USD Blue' },
    { code: 'INTERIOR', name: 'USD Blue Interior', short: 'USD Int.' },
    { code: 'EURO_BLUE', name: 'EUR Blue', short: 'EUR Blue' },
    { code: 'REAL_BLUE', name: 'BRL Blue', short: 'BRL Blue' },
];

// Una empresa puede ocultar mercados sin afectar la actualización global de Reverb.
const displayedMarkets = computed(() =>
    markets.filter(market => props.visibleMarkets.includes(market.code))
);

const formatMoney = new Intl.NumberFormat('es-AR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
});

const isStale = computed(() => data.value?.stale === true);
const status = computed(() => isStale.value
    ? 'Finanzas Argy · último dato disponible / datos incompletos'
    : 'Finanzas Argy · compra y venta · actualización periódica de 5 minutos');

function rate(code, side) {
    const raw = data.value?.rates?.[code]?.[side];
    const value = raw === undefined || raw === null ? NaN : Number(raw);
    return Number.isFinite(value) && value > 0
        ? '$' + formatMoney.format(value)
        : '—';
}

function quoteTitle(market) {
    const quote = data.value?.rates?.[market.code];
    const date = quote?.confirmed_at || data.value?.checked_at || null;
    return `${market.name} · Compra ${rate(market.code, 'buy')} · Venta ${rate(market.code, 'sell')}`
        + (quote?.stale ? ' · Último dato válido' : '')
        + (date ? ` · Fecha: ${date}` : '')
        + ' · Fuente: Finanzas Argy';
}

let echoChannel = null;

onMounted(() => {
    if (!window.Echo) return;

    echoChannel = window.Echo.channel('official-rates');
    echoChannel.listen('.official.rates.updated', payload => {
        if (payload && typeof payload === 'object' && payload.rates) {
            data.value = payload;
        }
    });
});

onBeforeUnmount(() => {
    if (echoChannel && window.Echo) {
        window.Echo.leave('official-rates');
    }
});
</script>

<template>
    <div class="aeria-fx-bar" :title="status" aria-label="Cotizaciones de referencia en pesos argentinos. Fuente Finanzas Argy.">
        <div class="aeria-fx-bar-content">
            <span class="aeria-fx-bar-heading">Cotizaciones:</span>
            <div class="aeria-fx-bar-values">
                <span v-for="market in displayedMarkets" :key="market.code"
                    class="aeria-fx-pair"
                    :class="{'aeria-fx-pair-stale': data?.rates?.[market.code]?.stale}"
                    :title="quoteTitle(market)">
                    <span class="aeria-fx-code">
                        <span class="aeria-fx-full-name">{{ market.name }}</span>
                        <span class="aeria-fx-short-name">{{ market.short }}</span>
                    </span>
                    <span class="aeria-fx-buy">C <strong>{{ rate(market.code, 'buy') }}</strong></span>
                    <span class="aeria-fx-sell">V <strong>{{ rate(market.code, 'sell') }}</strong></span>
                </span>
            </div>
            <span class="aeria-fx-bar-meta" role="status" aria-live="polite">{{ status }}</span>
        </div>
    </div>
</template>
