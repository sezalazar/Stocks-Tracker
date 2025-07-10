<template>
    <div class="p-4">
        <div v-if="Object.keys(groupedInstruments).length === 0" class="text-center text-gray-500">
            <p>{{ initializationMessage }}</p>
        </div>

        <div v-else class="overflow-x-auto rounded-lg border">
            <table class="min-w-full divide-y divide-gray-200 data-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="column in tableColumnDefinitions"
                            :key="column.key"
                            scope="col"
                            :class="['px-6 py-3 text-xs font-medium uppercase tracking-wider text-gray-500', column.class]"
                        >
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <template v-for="group in groupedInstruments" :key="group.underlyingName">
                        <tr>
                            <td :colspan="tableColumnDefinitions.length" class="bg-gray-100 px-6 py-2 text-sm font-bold text-gray-700">
                                {{ group.underlyingName }}
                            </td>
                        </tr>

                        <tr v-for="instrument in group.options" :key="instrument.originalId">
                            <td
                                v-for="column in tableColumnDefinitions"
                                :key="column.key"
                                :class="[
                                    'whitespace-nowrap px-6 py-4 text-sm text-gray-900',
                                    column.class,
                                    { 'cell-flash': flashState[`${instrument.originalId}-${column.key}`] },
                                ]"
                            >
                                {{ instrument[column.key] }}
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue'

const initializationMessage = ref('Inicializando componente...')
const marketInstrumentsData = reactive({})
const flashState = reactive({})

const formatStrikePrice = (underlying, strikeString) => {
    const intValue = parseInt(strikeString, 10)
    let strikeNumber = parseFloat(strikeString)

    switch (underlying) {
        case 'ALUA': {
            const min = 300,
                max = 1300
            if (intValue >= min && intValue <= max) {
                strikeNumber = intValue
            } else {
                const decimalValue = parseFloat(strikeString.slice(0, -1) + '.' + strikeString.slice(-1))
                if (decimalValue >= min && decimalValue <= max) {
                    strikeNumber = decimalValue
                }
            }
            break
        }

        case 'GGAL': {
            const min = 2000,
                max = 18000
            if (intValue >= min && intValue <= max) {
                strikeNumber = intValue
            } else {
                const decimalValue = parseFloat(strikeString.slice(0, -1) + '.' + strikeString.slice(-1))
                if (decimalValue >= min && decimalValue <= max) {
                    strikeNumber = decimalValue
                }
            }
            break
        }

        case 'COME': {
            const min = 50,
                max = 500
            if (intValue >= min && intValue <= max) {
                strikeNumber = intValue
            } else {
                for (let decimals = 1; decimals < strikeString.length && decimals <= 3; decimals++) {
                    const position = strikeString.length - decimals
                    const decimalValue = parseFloat(strikeString.slice(0, position) + '.' + strikeString.slice(position))
                    if (decimalValue >= min && decimalValue <= max) {
                        strikeNumber = decimalValue
                        break
                    }
                }
            }
            break
        }

        case 'BYMA':
            if (strikeString.length > 3) {
                strikeNumber = parseFloat(strikeString.slice(0, 3) + '.' + strikeString.slice(3))
            } else {
                strikeNumber = parseFloat(strikeString)
            }
            break

        case 'EDN':
        case 'METR':
        case 'PAMP':
            if (strikeString.length > 4) {
                strikeNumber = parseFloat(strikeString.slice(0, 4) + '.' + strikeString.slice(4))
            } else {
                strikeNumber = parseFloat(strikeString)
            }
            break

        case 'YPFD':
            if (strikeString.length > 5) {
                strikeNumber = parseFloat(strikeString.slice(0, 5) + '.' + strikeString.slice(5))
            } else {
                strikeNumber = parseFloat(strikeString)
            }
            break

        default:
            if (strikeString.length > 1) {
                strikeNumber = parseFloat(strikeString.slice(0, -1) + '.' + strikeString.slice(-1))
            } else {
                strikeNumber = parseFloat(strikeString)
            }
            break
    }
    return strikeNumber
}

const tableColumnDefinitions = ref([
    { key: 'instrument', label: 'Instrumento', class: 'text-left sticky left-0 bg-white z-10 w-48 min-w-[12rem]' },
    { key: 'strikePrice', label: 'Strike', class: 'text-right font-medium' },
    { key: 'bidPrice', label: 'Bid', class: 'text-right' },
    { key: 'offerPrice', label: 'Offer', class: 'text-right' },
    { key: 'lastPrice', label: 'Last', class: 'text-right' },
    { key: 'volume', label: 'Volume', class: 'text-right' },
    { key: 'settlementPrice', label: 'C. Ant.', class: 'text-right' },
    { key: 'lastUpdate', label: 'Actualizado', class: 'text-center' },
])

const dataIndices = {
    sequence: 0,
    updateType: 1,
    bidPrice: 2,
    offerPrice: 3,
    bidSize: 4,
    offerSize: 5,
    lastTradeTime: 6,
    lastPrice: 7,
    volume: 8,
    settlementPrice: 14,
}

const underlyingMapping = {
    ALU: 'ALUA',
    BYM: 'BYMA',
    COM: 'COME',
    EDN: 'EDN',
    GFG: 'GGAL',
    MET: 'METR',
    PAM: 'PAMP',
    YPF: 'YPFD',
}

const groupedInstruments = computed(() => {
    const instruments = Object.values(marketInstrumentsData)

    const groups = instruments.reduce((acc, instrument) => {
        let underlyingName = 'Otros'
        for (const prefix in underlyingMapping) {
            if (instrument.instrument.startsWith(prefix)) {
                underlyingName = underlyingMapping[prefix]
                break
            }
        }
        if (!acc[underlyingName]) {
            acc[underlyingName] = []
        }
        acc[underlyingName].push(instrument)
        return acc
    }, {})

    for (const group in groups) {
        groups[group].sort((a, b) => a.instrument.localeCompare(b.instrument))
    }

    return Object.keys(groups)
        .map((key) => ({
            underlyingName: key,
            options: groups[key],
        }))
        .sort((a, b) => a.underlyingName.localeCompare(b.underlyingName))
})

let marketDataChannel = null

const handleMarketDataUpdate = (eventData) => {
    const marketDataString = eventData.data

    if (marketDataString && typeof marketDataString === 'string' && marketDataString.startsWith('M:')) {
        if (initializationMessage.value !== 'Receiving data from the market...') {
            initializationMessage.value = 'Receiving data from the market...'
        }

        const parts = marketDataString.split('|')
        const instrumentId = parts[0].split(':')[1]
        const fieldsArray = parts.slice(1)

        if (instrumentId && Array.isArray(fieldsArray)) {
            const displayInstrumentId = instrumentId.replace('bm_MERV_', '')
            let strikePrice = '-'

            const match = displayInstrumentId.match(/^([A-Z]{3,4})([CV])(\d+)([A-Z])/)

            if (match) {
                const underlying = match[1]
                const strikeString = match[3]

                const formattedStrike = formatStrikePrice(underlying, strikeString)

                strikePrice = formattedStrike.toLocaleString('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 4,
                })
            }

            marketInstrumentsData[instrumentId] = {
                instrument: displayInstrumentId,
                strikePrice: strikePrice,
                bidPrice: fieldsArray[dataIndices.bidPrice] || '-',
                offerPrice: fieldsArray[dataIndices.offerPrice] || '-',
                lastPrice: fieldsArray[dataIndices.lastPrice] || '-',
                volume: fieldsArray[dataIndices.volume] || '-',
                settlementPrice: fieldsArray[dataIndices.settlementPrice] || '-',
                lastUpdate: new Date().toLocaleTimeString(),
            }
        }
    } else {
        console.warn('[WebhookTab] Unexpected format received:', eventData)
    }
}

onMounted(() => {
    if (typeof window.Echo === 'undefined') {
        initializationMessage.value = 'Error: window.Echo is undefined.'
        console.error(initializationMessage.value, '(bootstrap.js) is not loaded.')
        return
    }

    console.log('[WebhookTab] Echo is available. Subscribing to the "market-data" channel.')
    initializationMessage.value = 'Connecting to the channel'

    marketDataChannel = window.Echo.channel('market-data').listen('.MatrizBookUpdated', (eventData) => {
        handleMarketDataUpdate(eventData)
    })
})

onUnmounted(() => {
    if (marketDataChannel) {
        marketDataChannel.stopListening('.MatrizBookUpdated')
        window.Echo.leaveChannel('market-data')
        console.log('[WebhookTab] Unmounted.')
    }
})
</script>
