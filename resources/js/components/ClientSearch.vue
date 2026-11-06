
<template>
    <div class="aeria-client-search">

        <label
            :for="inputId"
            class="aeria-client-search-label"
        >
            Cliente
            <span class="aeria-client-optional">
                (opcional)
            </span>
        </label>

        <!--
        ---------------------------------------------------------
        Cliente seleccionado
        ---------------------------------------------------------
        -->

        <div
            v-if="selectedClient"
            class="aeria-client-selected"
        >
            <div class="aeria-client-selected-icon">
                <i class="bi bi-person-check"></i>
            </div>

            <div class="aeria-client-selected-info">

                <strong>
                    {{ selectedClient.name }}
                </strong>

                <small v-if="selectedClient.tax_id">
                    CUIT/CUIL: {{ formatTaxId(selectedClient.tax_id) }}
                </small>

                <small
                    v-else-if="selectedClient.document_number"
                >
                    DNI: {{ selectedClient.document_number }}
                </small>

            </div>

            <button
                type="button"
                class="aeria-client-clear"
                title="Quitar cliente"
                aria-label="Quitar cliente"
                @click="clearSelection"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!--
        ---------------------------------------------------------
        Buscador
        ---------------------------------------------------------
        -->

        <div
            v-else
            ref="searchContainer"
            class="aeria-client-search-container"
        >

            <div class="aeria-client-search-input-wrapper">

                <i class="bi bi-search"></i>

                <input
                    :id="inputId"
                    ref="searchInput"
                    v-model="search"
                    type="text"
                    class="dark-input aeria-client-search-input"
                    placeholder="Buscar por nombre, CUIT o DNI..."
                    autocomplete="off"
                    role="combobox"
                    aria-autocomplete="list"
                    :aria-expanded="showResults"
                    :aria-controls="listboxId"
                    :aria-activedescendant="
                        activeIndex >= 0 && showResults
                            ? optionId(activeIndex)
                            : undefined
                    "
                    @focus="handleFocus"
                    @keydown.down.prevent="moveDown"
                    @keydown.up.prevent="moveUp"
                    @keydown.enter.prevent="selectActive"
                    @keydown.esc="closeResults"
                    @blur="handleBlur"
                >

                <span
                    v-if="loading"
                    class="aeria-client-loading"
                >
                    <span
                        class="spinner-border spinner-border-sm"
                        role="status"
                        aria-label="Buscando clientes"
                    ></span>
                </span>

                <button
                    v-else-if="search"
                    type="button"
                    class="aeria-client-input-clear"
                    title="Limpiar búsqueda"
                    aria-label="Limpiar búsqueda"
                    @mousedown.prevent
                    @click="resetSearch"
                >
                    <i class="bi bi-x"></i>
                </button>

            </div>

            <!--
            -----------------------------------------------------
            Resultados
            -----------------------------------------------------
            -->

            <div
                v-if="showResults"
                :id="listboxId"
                class="aeria-client-results"
                role="listbox"
            >

                <template v-if="loading && !results.length">

                    <div class="aeria-client-message">
                        Buscando clientes...
                    </div>

                </template>

                <template v-else-if="error">

                    <div class="aeria-client-message aeria-client-error">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ error }}
                    </div>

                </template>

                <template v-else-if="results.length">

                    <button
                        v-for="(client, index) in results"
                        :id="optionId(index)"
                        :key="client.id"
                        type="button"
                        class="aeria-client-option"
                        :class="{ 'is-active': activeIndex === index }"
                        role="option"
                        :aria-selected="activeIndex === index"
                        @mousedown.prevent
                        @mouseenter="activeIndex = index"
                        @click="selectClient(client)"
                    >

                        <div class="aeria-client-option-icon">
                            <i
                                :class="
                                    client.person_type === 'company'
                                        ? 'bi bi-building'
                                        : 'bi bi-person'
                                "
                            ></i>
                        </div>

                        <div class="aeria-client-option-info">

                            <strong>
                                {{ client.name }}
                            </strong>

                            <small v-if="client.tax_id">
                                CUIT/CUIL:
                                {{ formatTaxId(client.tax_id) }}
                            </small>

                            <small v-else-if="client.document_number">
                                DNI: {{ client.document_number }}
                            </small>

                        </div>

                        <i class="bi bi-chevron-right aeria-client-option-arrow"></i>

                    </button>

                </template>

                <template v-else>

                    <div class="aeria-client-message">
                        <i class="bi bi-person-x"></i>
                        No encontramos clientes.
                    </div>

                </template>

            </div>

        </div>

        <!--
        ---------------------------------------------------------
        Valor enviado a Laravel
        ---------------------------------------------------------
        -->

        <input
            type="hidden"
            name="client_id"
            :value="selectedClient?.id ?? ''"
        >

        <div class="aeria-client-help">
            Podés dejar este campo vacío si el movimiento
            no corresponde a un cliente registrado.
        </div>

    </div>
</template>

<script setup>
import {
    ref,
    computed,
    watch,
    onBeforeUnmount,
    nextTick,
    useId,
} from 'vue';

const props = defineProps({

    searchUrl: {
        type: String,
        required: true,
    },

    initialClientId: {
        type: [String, Number],
        default: '',
    },

    initialClientName: {
        type: String,
        default: '',
    },

    initialClientTaxId: {
        type: String,
        default: '',
    },

});

/*
|--------------------------------------------------------------------------
| Identificadores accesibles
|--------------------------------------------------------------------------
*/

const uid = useId();

const inputId = `aeria-client-input-${uid}`;
const listboxId = `aeria-client-list-${uid}`;

function optionId(index) {
    return `aeria-client-option-${uid}-${index}`;
}

/*
|--------------------------------------------------------------------------
| Estado
|--------------------------------------------------------------------------
*/

const search = ref('');
const results = ref([]);
const loading = ref(false);
const error = ref('');

const isOpen = ref(false);
const activeIndex = ref(-1);

const searchInput = ref(null);

let debounceTimer = null;
let blurTimer = null;
let requestController = null;
let requestSequence = 0;

/*
|--------------------------------------------------------------------------
| Cliente inicialmente seleccionado
|--------------------------------------------------------------------------
*/

const selectedClient = ref(
    props.initialClientId && props.initialClientName
        ? {
            id: props.initialClientId,
            name: props.initialClientName,
            tax_id: props.initialClientTaxId,
            document_number: '',
            person_type: 'individual',
        }
        : null
);

/*
|--------------------------------------------------------------------------
| Mostrar resultados
|--------------------------------------------------------------------------
*/

const showResults = computed(() => {
    return (
        isOpen.value &&
        search.value.trim().length >= 2
    );
});

/*
|--------------------------------------------------------------------------
| Formato CUIT argentino
|--------------------------------------------------------------------------
*/

function formatTaxId(value) {

    const digits = String(value || '').replace(/\D/g, '');

    if (digits.length !== 11) {
        return value;
    }

    return (
        digits.substring(0, 2) + '-' +
        digits.substring(2, 10) + '-' +
        digits.substring(10)
    );
}

/*
|--------------------------------------------------------------------------
| Cancelar petición pendiente
|--------------------------------------------------------------------------
*/

function cancelRequest() {

    if (requestController) {
        requestController.abort();
        requestController = null;
    }

    requestSequence++;
}

/*
|--------------------------------------------------------------------------
| Buscar clientes
|--------------------------------------------------------------------------
*/

async function fetchClients(term) {

    cancelRequest();

    const sequence = requestSequence;

    requestController = new AbortController();

    loading.value = true;
    error.value = '';

    try {

        const url = new URL(
            props.searchUrl,
            window.location.origin
        );

        url.searchParams.set('q', term);

        const response = await fetch(
            url.toString(),
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
            }
        );

        if (!response.ok) {
            throw new Error(
                'No se pudo consultar el padrón de clientes.'
            );
        }

        const data = await response.json();

        if (sequence !== requestSequence) {
            return;
        }

        results.value = Array.isArray(data.clients)
            ? data.clients
            : [];

        activeIndex.value = -1;

    } catch (err) {

        if (
            err.name === 'AbortError' ||
            sequence !== requestSequence
        ) {
            return;
        }

        results.value = [];

        error.value = 'No se pudieron cargar los clientes. Intentá nuevamente.';

    } finally {

        if (sequence === requestSequence) {
            loading.value = false;
            requestController = null;
        }

    }
}

/*
|--------------------------------------------------------------------------
| Buscar después de 300 ms
|--------------------------------------------------------------------------
*/

watch(search, (value) => {

    clearTimeout(debounceTimer);
    cancelRequest();

    results.value = [];
    activeIndex.value = -1;
    error.value = '';

    const term = value.trim();

    if (term.length < 2) {
        loading.value = false;
        isOpen.value = false;
        return;
    }

    isOpen.value = true;
    loading.value = true;

    debounceTimer = setTimeout(() => {
        fetchClients(term);
    }, 300);

});

/*
|--------------------------------------------------------------------------
| Seleccionar cliente
|--------------------------------------------------------------------------
*/

function selectClient(client) {

    selectedClient.value = {
        id: client.id,
        name: client.name,
        tax_id: client.tax_id || '',
        document_number: client.document_number || '',
        person_type: client.person_type,
    };

    clearTimeout(debounceTimer);
    cancelRequest();

    isOpen.value = false;
    results.value = [];
    search.value = '';
    loading.value = false;
}

/*
|--------------------------------------------------------------------------
| Quitar selección
|--------------------------------------------------------------------------
*/

async function clearSelection() {

    selectedClient.value = null;

    resetSearch();

    await nextTick();

    searchInput.value?.focus();
}

/*
|--------------------------------------------------------------------------
| Limpiar búsqueda
|--------------------------------------------------------------------------
*/

function resetSearch() {

    clearTimeout(debounceTimer);
    cancelRequest();

    search.value = '';
    results.value = [];
    loading.value = false;
    error.value = '';
    isOpen.value = false;
    activeIndex.value = -1;
}

/*
|--------------------------------------------------------------------------
| Foco
|--------------------------------------------------------------------------
*/

function handleFocus() {

    if (search.value.trim().length >= 2) {
        isOpen.value = true;
    }
}

/*
|--------------------------------------------------------------------------
| Perder foco
|--------------------------------------------------------------------------
*/

function handleBlur() {

    clearTimeout(blurTimer);

    blurTimer = setTimeout(() => {
        isOpen.value = false;
    }, 150);
}

/*
|--------------------------------------------------------------------------
| Navegación con teclado
|--------------------------------------------------------------------------
*/

function moveDown() {

    if (!results.value.length) {
        return;
    }

    isOpen.value = true;

    activeIndex.value =
        (activeIndex.value + 1) % results.value.length;
}

function moveUp() {

    if (!results.value.length) {
        return;
    }

    isOpen.value = true;

    activeIndex.value =
        activeIndex.value <= 0
            ? results.value.length - 1
            : activeIndex.value - 1;
}

function selectActive() {

    if (
        activeIndex.value >= 0 &&
        results.value[activeIndex.value]
    ) {
        selectClient(results.value[activeIndex.value]);
    }
}

function closeResults() {
    isOpen.value = false;
}

/*
|--------------------------------------------------------------------------
| Limpieza
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {

    clearTimeout(debounceTimer);
    clearTimeout(blurTimer);

    cancelRequest();

});
</script>