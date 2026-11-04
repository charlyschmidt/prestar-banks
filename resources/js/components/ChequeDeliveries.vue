<script setup>
import {
    computed,
    ref,
    watch,
    onMounted,
    onBeforeUnmount,
} from 'vue';

/*
|--------------------------------------------------------------------------
| AERIA Finance — Entregas múltiples de cheques
|--------------------------------------------------------------------------
|
| Responsabilidades:
|
| - Distribución del neto entre medios de pago.
| - Efectivo ARS / USD / EUR.
| - Transferencias bancarias.
| - Cheques de terceros en cartera.
| - Control de saldos disponibles.
| - Impuestos de transferencia.
| - Cotizaciones y equivalencias.
| - Guardado de la distribución.
| - Comunicación con la confirmación financiera.
|
| Los movimientos reales son responsabilidad de Laravel.
|
*/

const props = defineProps({
    netAmount: {
        type: [String, Number],
        required: true,
    },

    accounts: {
        type: Array,
        default: () => [],
    },

    cheques: {
        type: Array,
        default: () => [],
    },

    banks: {
        type: Array,
        default: () => [],
    },

    existingDeliveries: {
        type: Array,
        default: () => [],
    },

    saveUrl: {
        type: String,
        default: '',
    },

    csrfToken: {
        type: String,
        default: '',
    },
});

/*
|--------------------------------------------------------------------------
| Formatos monetarios
|--------------------------------------------------------------------------
*/

const moneyFormatter = new Intl.NumberFormat('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const rateFormatter = new Intl.NumberFormat('es-AR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 6,
});

function parseNumber(value) {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : NaN;
    }

    let text = String(value ?? '')
        .trim()
        .replace(/\s/g, '');

    if (!text) return NaN;

    if (text.includes(',')) {
        text = text.replace(/\./g, '').replace(',', '.');
    } else if (/^\d{1,3}(\.\d{3})+$/.test(text)) {
        text = text.replace(/\./g, '');
    }

    if (!/^\d+(\.\d+)?$/.test(text)) {
        return NaN;
    }

    const number = Number(text);

    return Number.isFinite(number) ? number : NaN;
}

function toCents(value) {
    const number = parseNumber(value);

    if (
        !Number.isFinite(number) ||
        number < 0 ||
        number > 1000000000
    ) {
        return null;
    }

    const cents = Math.round(number * 100);

    return Number.isSafeInteger(cents)
        ? cents
        : null;
}

function formatMoney(value) {
    return moneyFormatter.format(Number(value) || 0);
}

function formatCents(value) {
    return formatMoney(value / 100);
}

function formatRate(value) {
    return rateFormatter.format(Number(value) || 0);
}

function formatCurrencyCents(cents, currency) {
    return `${currency} ${formatCents(cents)}`;
}

function normalizedDecimal(value, decimals) {
    const number = parseNumber(value);

    if (!Number.isFinite(number)) {
        return '';
    }

    return number.toFixed(decimals);
}

/*
|--------------------------------------------------------------------------
| Estado de las entregas
|--------------------------------------------------------------------------
*/

let nextId = 0;

function newDelivery() {
    return {
        id: ++nextId,

        type: '',

        accountBalanceId: '',

        deliveredChequeId: '',

        currency: 'ARS',

        amount: '',

        exchangeRate: '1',

        destinationBank: '',

        bankSearch: '',

        bankOpen: false,
    };
}

/*
|--------------------------------------------------------------------------
| Restaurar una distribución guardada
|--------------------------------------------------------------------------
*/

function restoreDelivery(item) {
    const delivery = newDelivery();

    delivery.type = item.type || '';

    delivery.currency = item.currency || 'ARS';

    delivery.amount = formatMoney(item.amount);

    delivery.exchangeRate = formatRate(
        item.exchange_rate
    );

    delivery.deliveredChequeId =
        item.delivered_cheque_id
            ? String(item.delivered_cheque_id)
            : '';

    delivery.destinationBank =
        item.destination_bank ||
        item.notes ||
        '';

    delivery.bankSearch = delivery.destinationBank;

    if (['cash', 'transfer'].includes(delivery.type)) {
        const account = props.accounts.find(account =>
            Number(account.account_id) ===
            Number(item.account_id) &&
            account.currency === item.currency
        );

        delivery.accountBalanceId = account
            ? String(account.id)
            : '';
    }

    return delivery;
}

const restored = props.existingDeliveries.map(
    restoreDelivery
);

const deliveries = ref(
    restored.length
        ? restored
        : [newDelivery()]
);

const saving = ref(false);

/*
|--------------------------------------------------------------------------
| Cuentas disponibles
|--------------------------------------------------------------------------
*/

function availableAccounts(delivery) {
    return props.accounts.filter(account => {
        if (delivery.type === 'cash') {
            return account.type === 'cash';
        }

        if (delivery.type === 'transfer') {
            return ['bank', 'wallet'].includes(
                account.type
            );
        }

        return false;
    });
}

function selectedAccount(delivery) {
    return props.accounts.find(account =>
        String(account.id) ===
        String(delivery.accountBalanceId)
    );
}

function accountBalanceCents(account) {
    if (
        !account ||
        account.available_balance === null ||
        account.available_balance === undefined
    ) {
        return null;
    }

    return toCents(account.available_balance);
}

/*
|--------------------------------------------------------------------------
| Impuestos de transferencia
|--------------------------------------------------------------------------
|
| Respetamos el comportamiento de TransactionService:
|
| Débito = importe + impuesto de transferencia.
|
| "No corresponde" sigue siendo un destino no vacío
| y por ahora no exime del impuesto.
|
*/

function transferTaxRate(delivery) {
    if (delivery.type !== 'transfer') {
        return 0;
    }

    const account = selectedAccount(delivery);

    const rate = parseNumber(
        account?.transfer_tax_rate ?? 0
    );

    if (
        !Number.isFinite(rate) ||
        rate < 0
    ) {
        return NaN;
    }

    return rate;
}

function debitCents(delivery, amountCents = null) {
    const amount = amountCents ??
        toCents(delivery.amount);

    if (amount === null) {
        return null;
    }

    if (delivery.type !== 'transfer') {
        return amount;
    }

    const rate = transferTaxRate(delivery);

    if (!Number.isFinite(rate)) {
        return null;
    }

    return amount + Math.round(
        amount * rate / 100
    );
}

/*
|--------------------------------------------------------------------------
| Saldos disponibles
|--------------------------------------------------------------------------
|
| Si dos entregas utilizan la misma cuenta y moneda,
| ambas comparten el mismo saldo.
|
| Para calcular el disponible de una entrega,
| restamos los débitos de las demás entregas.
|
*/

function availableForDeliveryCents(delivery) {
    const account = selectedAccount(delivery);

    if (!account) {
        return null;
    }

    const balanceCents = accountBalanceCents(account);

    if (balanceCents === null) {
        return null;
    }

    const allocatedCents = deliveries.value.reduce(
        (total, other) => {

            if (
                other.id === delivery.id ||
                !['cash', 'transfer'].includes(other.type) ||
                String(other.accountBalanceId) !==
                String(delivery.accountBalanceId)
            ) {
                return total;
            }

            return total + (debitCents(other) ?? 0);
        },
        0
    );

    return Math.max(
        0,
        balanceCents - allocatedCents
    );
}

/*
|--------------------------------------------------------------------------
| Cheques disponibles
|--------------------------------------------------------------------------
*/

function selectedCheque(delivery) {
    return props.cheques.find(cheque =>
        String(cheque.id) ===
        String(delivery.deliveredChequeId)
    );
}

function chequeAlreadySelected(chequeId, currentId) {
    return deliveries.value.some(delivery =>
        delivery.id !== currentId &&
        delivery.type === 'third_party_cheque' &&
        String(delivery.deliveredChequeId) ===
        String(chequeId)
    );
}

/*
|--------------------------------------------------------------------------
| Cálculo de equivalentes en ARS
|--------------------------------------------------------------------------
*/

function equivalentCents(delivery) {
    const amountCents = toCents(delivery.amount);

    const rate = parseNumber(
        delivery.exchangeRate
    );

    if (
        amountCents === null ||
        amountCents <= 0 ||
        !Number.isFinite(rate) ||
        rate <= 0 ||
        rate > 1000000000
    ) {
        return null;
    }

    if (delivery.currency === 'ARS') {
        return rate === 1
            ? amountCents
            : null;
    }

    const equivalent = Math.round(
        amountCents * rate
    );

    if (
        !Number.isSafeInteger(equivalent) ||
        equivalent <= 0 ||
        equivalent > 100000000000
    ) {
        return null;
    }

    return equivalent;
}

/*
|--------------------------------------------------------------------------
| Totales de la liquidación
|--------------------------------------------------------------------------
*/

const netCents = computed(() =>
    toCents(props.netAmount) ?? 0
);

const distributedCents = computed(() =>
    deliveries.value.reduce(
        (total, delivery) =>
            total + (
                equivalentCents(delivery) ?? 0
            ),
        0
    )
);

const pendingCents = computed(() =>
    netCents.value - distributedCents.value
);

/*
|--------------------------------------------------------------------------
| Máximo permitido por entrega
|--------------------------------------------------------------------------
|
| Se considera:
|
| - Saldo disponible.
| - Impuesto de transferencia.
| - Otras entregas de la misma cuenta.
| - Neto pendiente de la liquidación.
|
| Utilizamos búsqueda binaria en centavos.
|
*/

function maximumAmountCents(delivery) {
    if (!['cash', 'transfer'].includes(
        delivery.type
    )) {
        return 0;
    }

    const account = selectedAccount(delivery);

    if (!account) {
        return 0;
    }

    const available = availableForDeliveryCents(
        delivery
    );

    if (available === null || available <= 0) {
        return 0;
    }

    const rate = parseNumber(
        delivery.exchangeRate
    );

    if (
        !Number.isFinite(rate) ||
        rate <= 0
    ) {
        return 0;
    }

    /*
     * Excluimos la entrega actual del total para
     * calcular cuánto podríamos asignarle.
     */

    const currentEquivalent =
        equivalentCents(delivery) ?? 0;

    const otherDeliveriesCents =
        distributedCents.value - currentEquivalent;

    const remainingNet = Math.max(
        0,
        netCents.value - otherDeliveriesCents
    );

    if (remainingNet <= 0) {
        return 0;
    }

    let low = 0;

    let high = Math.min(
        available,
        100000000000
    );

    while (low < high) {
        const middle = Math.ceil(
            (low + high) / 2
        );

        const debit = debitCents(
            delivery,
            middle
        );

        const equivalent =
            delivery.currency === 'ARS'
                ? middle
                : Math.round(middle * rate);

        if (
            debit !== null &&
            debit <= available &&
            equivalent <= remainingNet
        ) {
            low = middle;
        } else {
            high = middle - 1;
        }
    }

    return low;
}

function useMaximum(delivery) {
    const maximum = maximumAmountCents(
        delivery
    );

    if (maximum <= 0) {
        return;
    }

    delivery.amount = formatCents(maximum);
}

/*
|--------------------------------------------------------------------------
| Validación de saldos
|--------------------------------------------------------------------------
*/

function hasSufficientBalance(delivery) {
    if (!['cash', 'transfer'].includes(
        delivery.type
    )) {
        return true;
    }

    const available = availableForDeliveryCents(
        delivery
    );

    const debit = debitCents(delivery);

    return available !== null &&
        debit !== null &&
        debit > 0 &&
        debit <= available;
}

function showBalanceAlert(delivery) {
    if (!['cash', 'transfer'].includes(
        delivery.type
    )) {
        return false;
    }

    if (
        String(delivery.amount || '').trim() === ''
    ) {
        return false;
    }

    const amountCents = toCents(
        delivery.amount
    );

    if (amountCents === null || amountCents <= 0) {
        return false;
    }

    return !hasSufficientBalance(delivery);
}

/*
|--------------------------------------------------------------------------
| Buscador de bancos
|--------------------------------------------------------------------------
*/

function normalize(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

const bankOptions = computed(() =>
    Array.from(
        new Set([
            'No corresponde',
            ...props.banks,
        ])
    )
);

function filteredBanks(delivery) {
    const query = normalize(
        delivery.bankSearch
    );

    return bankOptions.value.filter(bank =>
        normalize(bank).includes(query)
    );
}

function selectBank(delivery, bank) {
    delivery.destinationBank = bank;
    delivery.bankSearch = bank;
    delivery.bankOpen = false;
}

function searchBank(delivery) {
    delivery.destinationBank = '';
    delivery.bankOpen = true;
}

function closeBank(delivery) {
    delivery.bankOpen = false;

    if (!delivery.destinationBank) {
        delivery.bankSearch = '';
    }
}

function handleBankBlur(delivery, event) {
    const container = event.currentTarget;

    if (
        container.contains(event.relatedTarget)
    ) {
        return;
    }

    closeBank(delivery);
}

/*
|--------------------------------------------------------------------------
| Cambios de medio de pago
|--------------------------------------------------------------------------
*/

function changeType(delivery) {
    delivery.accountBalanceId = '';
    delivery.deliveredChequeId = '';
    delivery.destinationBank = '';
    delivery.bankSearch = '';
    delivery.bankOpen = false;

    delivery.currency = 'ARS';
    delivery.amount = '';
    delivery.exchangeRate = '1';
}

function changeAccount(delivery) {
    const account = selectedAccount(
        delivery
    );

    delivery.currency =
        account?.currency || 'ARS';

    delivery.amount = '';

    delivery.exchangeRate =
        delivery.currency === 'ARS'
            ? '1'
            : '';
}

function changeCheque(delivery) {
    const cheque = selectedCheque(
        delivery
    );

    delivery.currency = 'ARS';
    delivery.exchangeRate = '1';

    delivery.amount = cheque
        ? formatMoney(cheque.nominal_amount)
        : '';
}

/*
|--------------------------------------------------------------------------
| Agregar y eliminar entregas
|--------------------------------------------------------------------------
*/

function addDelivery() {
    if (deliveries.value.length >= 100) {
        return;
    }

    deliveries.value.push(
        newDelivery()
    );
}

function removeDelivery(id) {
    deliveries.value = deliveries.value.filter(
        delivery => delivery.id !== id
    );

    if (!deliveries.value.length) {
        deliveries.value.push(
            newDelivery()
        );
    }
}

/*
|--------------------------------------------------------------------------
| Validación individual
|--------------------------------------------------------------------------
*/

function isValidDelivery(delivery) {
    if (![
        'cash',
        'transfer',
        'third_party_cheque',
    ].includes(delivery.type)) {
        return false;
    }

    const equivalent = equivalentCents(
        delivery
    );

    if (
        equivalent === null ||
        equivalent <= 0
    ) {
        return false;
    }

    if (delivery.type === 'third_party_cheque') {
        const cheque = selectedCheque(
            delivery
        );

        if (!cheque) {
            return false;
        }

        return (
            delivery.currency === 'ARS' &&
            parseNumber(delivery.exchangeRate) === 1 &&
            !chequeAlreadySelected(
                cheque.id,
                delivery.id
            ) &&
            toCents(delivery.amount) ===
            toCents(cheque.nominal_amount)
        );
    }

    const account = selectedAccount(
        delivery
    );

    if (!account) {
        return false;
    }

    if (account.currency !== delivery.currency) {
        return false;
    }

    if (
        delivery.type === 'cash' &&
        account.type !== 'cash'
    ) {
        return false;
    }

    if (
        delivery.type === 'transfer' &&
        !['bank', 'wallet'].includes(
            account.type
        )
    ) {
        return false;
    }

    if (
        delivery.type === 'transfer' &&
        !bankOptions.value.includes(
            delivery.destinationBank
        )
    ) {
        return false;
    }

    return hasSufficientBalance(
        delivery
    );
}

/*
|--------------------------------------------------------------------------
| Validación general
|--------------------------------------------------------------------------
*/

const allValid = computed(() =>
    deliveries.value.length > 0 &&
    deliveries.value.every(
        isValidDelivery
    )
);

const isBalanced = computed(() =>
    allValid.value &&
    pendingCents.value === 0
);

const canSave = computed(() =>
    !!props.saveUrl &&
    !!props.csrfToken &&
    !saving.value &&
    allValid.value &&
    distributedCents.value > 0 &&
    pendingCents.value >= 0
);

const distributionMessage = computed(() => {
    if (!allValid.value) {
        return 'Revisá los medios de pago, los importes y los saldos disponibles.';
    }

    if (pendingCents.value < 0) {
        return 'Los medios de pago superan el neto de la liquidación.';
    }

    if (pendingCents.value > 0) {
        return 'Podés guardar esta distribución parcial. Para confirmar, primero debés completar el neto.';
    }

    return 'La distribución coincide con el neto de la liquidación.';
});

/*
|--------------------------------------------------------------------------
| Preparar datos para Laravel
|--------------------------------------------------------------------------
*/

const payload = computed(() =>
    deliveries.value.map(delivery => ({
        type: delivery.type,

        account_balance_id:
            delivery.type === 'third_party_cheque'
                ? ''
                : delivery.accountBalanceId,

        delivered_cheque_id:
            delivery.type === 'third_party_cheque'
                ? delivery.deliveredChequeId
                : '',

        currency: delivery.currency,

        amount: normalizedDecimal(
            delivery.amount,
            2
        ),

        exchange_rate: normalizedDecimal(
            delivery.exchangeRate,
            6
        ),

        destination_bank:
            delivery.type === 'transfer'
                ? delivery.destinationBank
                : '',
    }))
);

/*
|--------------------------------------------------------------------------
| Estado guardado / cambios pendientes
|--------------------------------------------------------------------------
|
| La confirmación solo se habilita cuando:
|
| - Hay una distribución guardada.
| - El neto está completamente distribuido.
| - No existen cambios pendientes.
|
*/

function distributionSnapshot(rows) {
    return JSON.stringify(
        rows.map(delivery => ({
            type: delivery.type,

            account_balance_id:
                delivery.type === 'third_party_cheque'
                    ? ''
                    : String(
                        delivery.accountBalanceId || ''
                    ),

            delivered_cheque_id:
                delivery.type === 'third_party_cheque'
                    ? String(
                        delivery.deliveredChequeId || ''
                    )
                    : '',

            currency: delivery.currency,

            amount: normalizedDecimal(
                delivery.amount,
                2
            ),

            exchange_rate: normalizedDecimal(
                delivery.exchangeRate,
                6
            ),

            destination_bank:
                delivery.type === 'transfer'
                    ? delivery.destinationBank
                    : '',
        }))
    );
}

const savedSnapshot = distributionSnapshot(
    restored
);

const currentSnapshot = computed(() =>
    distributionSnapshot(
        deliveries.value
    )
);

const isDirty = computed(() =>
    props.existingDeliveries.length === 0 ||
    currentSnapshot.value !== savedSnapshot
);

/*
|--------------------------------------------------------------------------
| Comunicación Vue → show.js
|--------------------------------------------------------------------------
*/

function notifyDistributionState() {
    document.dispatchEvent(
        new CustomEvent(
            'cheques:deliveries-state',
            {
                detail: {
                    dirty: isDirty.value,
                    balanced: isBalanced.value,
                },
            }
        )
    );
}

watch(
    [currentSnapshot, isBalanced],
    notifyDistributionState,
    {
        flush: 'sync',
    }
);

/*
|--------------------------------------------------------------------------
| Sincronización del estado de entregas
|--------------------------------------------------------------------------
*/

function respondToStateRequest() {
    notifyDistributionState();
}

onMounted(() => {
    document.addEventListener(
        'cheques:request-deliveries-state',
        respondToStateRequest
    );

    notifyDistributionState();
});

onBeforeUnmount(() => {
    document.removeEventListener(
        'cheques:request-deliveries-state',
        respondToStateRequest
    );
});

onBeforeUnmount(() => {
    document.dispatchEvent(
        new CustomEvent(
            'cheques:deliveries-state',
            {
                detail: {
                    dirty: true,
                    balanced: false,
                },
            }
        )
    );
});

/*
|--------------------------------------------------------------------------
| Guardar distribución
|--------------------------------------------------------------------------
*/

function submitDeliveries(event) {
    if (!canSave.value) {
        event.preventDefault();
        return;
    }

    saving.value = true;
}
</script>

<template>
    <div class="cheque-deliveries">

        <!-- ENCABEZADO -->

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">

            <div class="cheques-section-title mb-0">
                Distribución de la entrega
            </div>

            <span class="cheques-status">
                Pagos múltiples
            </span>
        </div>

        <p class="cheques-note mb-4">
            Combiná efectivo, transferencias y cheques
            de terceros para entregar el neto al cliente.
        </p>

        <!-- RESUMEN GENERAL -->

        <div class="cheques-summary-grid mb-4">

            <div>
                <span>Neto a entregar</span>

                <strong>
                    $ {{ formatCents(netCents) }}
                </strong>
            </div>

            <div>
                <span>Total distribuido</span>

                <strong class="cheques-amount-income">
                    $ {{ formatCents(distributedCents) }}
                </strong>
            </div>

            <div class="cheques-summary-total">
                <span>Saldo pendiente</span>

                <strong :class="pendingCents === 0
                        ? 'cheques-amount-income'
                        : 'cheques-amount-expense'
                    ">
                    $ {{ formatCents(pendingCents) }}
                </strong>
            </div>

        </div>

        <!-- FORMULARIO DE GUARDADO -->

        <form :action="saveUrl || undefined" method="POST" @submit="submitDeliveries">

            <input type="hidden" name="_token" :value="csrfToken">

            <!-- DATOS NORMALIZADOS PARA LARAVEL -->

            <template v-for="(item, index) in payload" :key="'payload-' + index">
                <input v-for="(value, field) in item" :key="field" type="hidden"
                    :name="`deliveries[${index}][${field}]`" :value="value">
            </template>

            <!-- MEDIOS DE PAGO -->

            <div v-for="(delivery, index) in deliveries" :key="delivery.id" class="cheques-delivery-card">

                <!-- ENCABEZADO DE FILA -->

                <div class="cheques-delivery-card-header">

                    <strong>
                        Medio de pago #{{ index + 1 }}
                    </strong>

                    <button type="button" class="cheques-delivery-remove" aria-label="Eliminar medio de pago"
                        title="Eliminar medio de pago" @click="removeDelivery(delivery.id)">
                        <i class="bi bi-trash"></i>
                    </button>

                </div>

                <!-- FILA 1: MEDIO / CUENTA / MONEDA -->

                <div class="cheques-delivery-grid">

                    <!-- FORMA DE ENTREGA -->

                    <div class="form-group">

                        <label>
                            Forma de entrega
                        </label>

                        <select v-model="delivery.type" class="form-select" @change="changeType(delivery)">
                            <option value="">
                                Seleccionar
                            </option>

                            <option value="cash">
                                Efectivo
                            </option>

                            <option value="transfer">
                                Transferencia bancaria
                            </option>

                            <option value="third_party_cheque">
                                Cheque de terceros
                            </option>
                        </select>

                    </div>

                    <!-- CUENTA DE ORIGEN -->

                    <div v-if="
                        ['cash', 'transfer'].includes(delivery.type)
                    " class="form-group">

                        <label>
                            Cuenta de origen
                        </label>

                        <select v-model="delivery.accountBalanceId" class="form-select"
                            @change="changeAccount(delivery)">
                            <option value="">
                                Seleccionar cuenta
                            </option>

                            <option v-for="account in availableAccounts(delivery)" :key="account.id"
                                :value="String(account.id)">
                                {{ account.name }}
                                · {{ account.currency }}
                            </option>
                        </select>

                        <p v-if="
                            selectedAccount(delivery) &&
                            accountBalanceCents(
                                selectedAccount(delivery)
                            ) !== null
                        " class="cheques-delivery-balance">
                            <i class="bi bi-wallet2"></i>

                            Saldo actual:

                            <strong>
                                {{
                                    formatCurrencyCents(
                                        accountBalanceCents(
                                            selectedAccount(delivery)
                                        ),
                                        delivery.currency
                                    )
                                }}
                            </strong>
                        </p>

                        <p v-else-if="selectedAccount(delivery)" class="cheques-note mt-2 mb-0">
                            Esta cuenta no tiene saldo disponible
                            en la jornada actual.
                        </p>

                    </div>

                    <!-- CHEQUE DE TERCEROS -->

                    <div v-if="delivery.type === 'third_party_cheque'" class="form-group">

                        <label>
                            Cheque disponible en cartera
                        </label>

                        <select v-model="delivery.deliveredChequeId" class="form-select"
                            @change="changeCheque(delivery)">
                            <option value="">
                                Seleccionar cheque
                            </option>

                            <option v-for="cheque in cheques" :key="cheque.id" :value="String(cheque.id)" :disabled="chequeAlreadySelected(
                                cheque.id,
                                delivery.id
                            )
                                ">
                                {{ cheque.bank || 'Banco' }}
                                · {{ cheque.number || 'S/N' }}
                                · $ {{ formatMoney(cheque.nominal_amount) }}
                            </option>

                        </select>

                    </div>

                    <!-- MONEDA -->

                    <div v-if="delivery.type" class="form-group">

                        <label>
                            Moneda
                        </label>

                        <input :value="delivery.currency" type="text" class="form-control dark-input" readonly>

                    </div>

                </div>

                <!-- FILA 2: IMPORTE / EQUIVALENTE / MÁXIMO -->

                <div v-if="delivery.type" class="cheques-delivery-grid cheques-delivery-second-row">

                    <!-- IMPORTE -->

                    <div class="form-group">

                        <label>
                            Importe a entregar
                        </label>

                        <input v-model="delivery.amount" type="text" inputmode="decimal" class="form-control dark-input"
                            placeholder="0,00" autocomplete="off" :readonly="delivery.type === 'third_party_cheque'
                                ">

                    </div>

                    <!-- EQUIVALENTE -->

                    <div class="form-group">

                        <label>
                            Equivalente en ARS
                        </label>

                        <input type="text" class="form-control dark-input cheques-delivery-equivalent" :value="'$ ' +
                            formatCents(
                                equivalentCents(delivery) ?? 0
                            )
                            " readonly tabindex="-1">

                    </div>

                    <!-- ACCIÓN RÁPIDA -->

                    <div v-if="
                        ['cash', 'transfer'].includes(delivery.type) &&
                        selectedAccount(delivery)
                    " class="form-group cheques-max-action">

                        <label>
                            Completar importe
                        </label>

                        <button type="button" class="primary-action-button cheques-fill-maximum" :disabled="maximumAmountCents(delivery) <= 0
                            " @click="useMaximum(delivery)">
                            <i class="bi bi-wallet2"></i>
                            Completar con el máximo
                        </button>

                        <p class="cheques-note mt-2 mb-0">
                            Máximo para esta entrega:

                            <strong>
                                {{
                                    formatCurrencyCents(
                                        maximumAmountCents(delivery),
                                        delivery.currency
                                    )
                                }}
                            </strong>
                        </p>

                    </div>

                </div>

                <!-- FILA 3: COTIZACIÓN / BANCO DESTINO -->

                <div v-if="
                    delivery.type === 'transfer' ||
                    (
                        delivery.type &&
                        delivery.currency !== 'ARS'
                    )
                " class="cheques-delivery-grid cheques-delivery-extra-row">

                    <!-- COTIZACIÓN -->

                    <div v-if="delivery.currency !== 'ARS'" class="form-group">

                        <label>
                            Cotización a ARS
                        </label>

                        <input v-model="delivery.exchangeRate" type="text" inputmode="decimal"
                            class="form-control dark-input" placeholder="Ej. 1.500,00">

                    </div>

                    <!-- BANCO DE DESTINO -->

                    <div v-if="delivery.type === 'transfer'" class="form-group">

                        <label>
                            Banco de destino
                        </label>

                        <div class="bank-search-select searchable-select" @focusout="
                            handleBankBlur(delivery, $event)
                            ">

                            <input v-model="delivery.bankSearch" type="text" class="dark-input searchable-select-input"
                                placeholder="Escribí para buscar un banco..." autocomplete="off" role="combobox"
                                aria-autocomplete="list" :aria-expanded="delivery.bankOpen"
                                @input="searchBank(delivery)" @focus="delivery.bankOpen = true" @keydown.esc="
                                    closeBank(delivery)
                                    " @keydown.enter.prevent="
                                    filteredBanks(delivery).length
                                        ? selectBank(
                                            delivery,
                                            filteredBanks(delivery)[0]
                                        )
                                        : null
                                    ">

                            <div v-if="
                                delivery.bankOpen &&
                                filteredBanks(delivery).length
                            " class="bank-search-options searchable-select-options" role="listbox">

                                <button v-for="bank in filteredBanks(delivery)" :key="bank" type="button"
                                    class="bank-search-option searchable-select-option" :data-value="bank" role="option"
                                    @mousedown.prevent="
                                        selectBank(delivery, bank)
                                        " @click="
                                        selectBank(delivery, bank)
                                        ">
                                    {{ bank }}
                                </button>

                            </div>

                        </div>

                        <p v-if="
                            delivery.destinationBank &&
                            transferTaxRate(delivery) > 0
                        " class="cheques-note mt-2 mb-0">
                            Impuesto:
                            {{ formatRate(transferTaxRate(delivery)) }} %

                            · Débito estimado:

                            <strong>
                                {{
                                    formatCurrencyCents(
                                        debitCents(delivery) ?? 0,
                                        delivery.currency
                                    )
                                }}
                            </strong>
                        </p>

                    </div>

                </div>

                <!-- ALERTA ANCHO COMPLETO -->

                <div v-if="showBalanceAlert(delivery)" class="cheques-balance-alert" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>

                    <span>
                        El importe supera el saldo disponible
                        de la cuenta seleccionada.
                    </span>
                </div>

            </div>

            <!-- AGREGAR MEDIO -->

            <button type="button" class="secondary-button cheques-add-delivery" :disabled="deliveries.length >= 100"
                @click="addDelivery">
                <i class="bi bi-plus-circle"></i>
                Agregar otro medio de pago
            </button>

            <!-- RESULTADO -->

            <div class="cheques-net mt-4">

                <div>
                    <span>
                        Saldo pendiente de distribución
                    </span>

                    <strong class="cheques-net-amount" :class="isBalanced
                            ? 'cheques-amount-income'
                            : 'cheques-amount-expense'
                        ">
                        $ {{ formatCents(pendingCents) }}
                    </strong>
                </div>

            </div>

            <!-- MENSAJE DE VALIDACIÓN -->

            <p class="cheques-note mt-3 mb-3" role="status" aria-live="polite">
                <i class="bi" :class="isBalanced
                        ? 'bi-check-circle'
                        : 'bi-info-circle'
                    "></i>

                {{ distributionMessage }}
            </p>

            <!-- GUARDAR -->

            <button type="submit" class="primary-action-button" :disabled="!canSave">
                <i class="bi bi-save"></i>

                {{
                    saving
                        ? 'Guardando distribución...'
                        : 'Guardar medios de pago'
                }}
            </button>

            <p class="cheques-note mt-3 mb-0">
                Guardar la distribución no genera movimientos
                financieros. Los saldos se validarán nuevamente
                al confirmar la operación.
            </p>

        </form>

    </div>
</template>
