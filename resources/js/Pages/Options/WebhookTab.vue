<template>
  <div class="p-4">
    <div v-if="instrumentsForTable.length === 0" class="text-center text-gray-500">
      <p>{{ initializationMessage }}</p>
    </div>

    <div v-else class="overflow-x-auto rounded-lg border">
      <table class="min-w-full divide-y divide-gray-200">
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
          <tr v-for="instrument in instrumentsForTable" :key="instrument.instrument">
            <td
              v-for="column in tableColumnDefinitions"
              :key="column.key"
              :class="['whitespace-nowrap px-6 py-4 text-sm', column.class]"
            >
              {{ instrument[column.key] }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';

const initializationMessage = ref('Inicializando componente...');
const marketInstrumentsData = reactive({});

const tableColumnDefinitions = ref([
  { key: 'instrument', label: 'Instrumento', class: 'text-left sticky left-0 bg-white z-10 w-48 min-w-[12rem]' },
  { key: 'bidPrice', label: 'Bid', class: 'text-right' },
  { key: 'offerPrice', label: 'Offer', class: 'text-right' },
  { key: 'lastPrice', label: 'Last', class: 'text-right' },
  { key: 'volume', label: 'Volume', class: 'text-right' },
  { key: 'settlementPrice', label: 'C. Ant.', class: 'text-right' },
  { key: 'lastUpdate', label: 'Actualizado', class: 'text-center' },
]);

const dataIndices = {
  sequence: 0, updateType: 1, bidPrice: 2, offerPrice: 3, bidSize: 4,
  offerSize: 5, lastTradeTime: 6, lastPrice: 7, volume: 8, settlementPrice: 14,
};

const instrumentsForTable = computed(() => {
  return Object.values(marketInstrumentsData).sort((a, b) => a.instrument.localeCompare(b.instrument));
});

let marketDataChannel = null;

const handleMarketDataUpdate = (eventData) => {
  if (eventData && typeof eventData.data === 'string' && eventData.data.startsWith('M:')) {
    if (initializationMessage.value !== 'Receiving data from the market...') {
        initializationMessage.value = 'Receiving data from the market...';
    }

    const parts = eventData.data.split('|');
    const instrumentId = parts[0].split(':')[1];
    const fieldsArray = parts.slice(1);

    if (instrumentId && Array.isArray(fieldsArray)) {
      marketInstrumentsData[instrumentId] = {
        instrument: instrumentId,
        bidPrice: fieldsArray[dataIndices.bidPrice] || '-',
        offerPrice: fieldsArray[dataIndices.offerPrice] || '-',
        lastPrice: fieldsArray[dataIndices.lastPrice] || '-',
        volume: fieldsArray[dataIndices.volume] || '-',
        settlementPrice: fieldsArray[dataIndices.settlementPrice] || '-',
        lastUpdate: new Date().toLocaleTimeString(),
      };
    }
  } else {
      console.warn('[WebhookTab] Unexpected format received:', eventData);
  }
};

onMounted(() => {
  if (typeof window.Echo === 'undefined') {
    initializationMessage.value = 'Error: window.Echo is undefined.';
    console.error(initializationMessage.value, '(bootstrap.js) is not loaded.');
    return;
  }

  console.log('[WebhookTab] Echo is available. Subscribing to the "market-data" channel.');
  initializationMessage.value = 'Connecting to the channel';
  
  marketDataChannel = window.Echo.channel('market-data')
    .listen('MatrizBookUpdated', (eventData) => {
        console.log('[WebhookTab Listener] MatrizBookUpdated received:', eventData); // Descomentar para depurar
        handleMarketDataUpdate(eventData);
    });
});

onUnmounted(() => {
  if (marketDataChannel) {
    marketDataChannel.stopListening('MatrizBookUpdated');
    window.Echo.leaveChannel('market-data');
    console.log('[WebhookTab] Unmounted.');
  }
});
</script>