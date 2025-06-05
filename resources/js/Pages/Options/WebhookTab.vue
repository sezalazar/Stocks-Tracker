<script setup>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';

const initializationMessage = ref('Inicializando WebhookTab...');
const marketInstrumentsData = reactive({});

// Index mapping for message fields – adjust if payload structure changes
const dataIndices = {
  sequence: 0, updateType: 1, bidPrice: 2, offerPrice: 3, bidSize: 4,
  offerSize: 5, lastTradeTime: 6, lastPrice: 7, volume: 8, settlementPrice: 14,
};

// Columns to render in the market data table
const tableColumnDefinitions = ref([
  { key: 'instrument', label: 'Instrumento', class: 'text-left sticky left-0 bg-white z-10 w-48 min-w-[12rem]' },
  { key: 'bidPrice', label: 'Bid', class: 'text-right' },
  { key: 'offerPrice', label: 'Offer', class: 'text-right' },
  { key: 'lastPrice', label: 'Last', class: 'text-right' },
  { key: 'volume', label: 'Volume', class: 'text-right' },
  { key: 'settlementPrice', label: 'C. Ant.', class: 'text-right' },
  { key: 'lastUpdate', label: 'Actualizado', class: 'text-center' },
]);

// Sort instruments alphabetically for table display
const instrumentsForTable = computed(() => {
  return Object.values(marketInstrumentsData).sort((a, b) => a.instrument.localeCompare(b.instrument));
});

// Parse and store incoming market data messages
const handleMarketDataUpdate = (eventPayload) => {
  if (eventPayload && eventPayload.type === 'market_data' && eventPayload.parsed) {
    const instrumentId = eventPayload.parsed.instrument;
    const fieldsArray = eventPayload.parsed.fields;
    if (instrumentId && Array.isArray(fieldsArray)) {
      marketInstrumentsData[instrumentId] = {
        instrument: instrumentId,
        bidPrice: fieldsArray[dataIndices.bidPrice] !== undefined && fieldsArray[dataIndices.bidPrice] !== '' ? fieldsArray[dataIndices.bidPrice] : '-',
        offerPrice: fieldsArray[dataIndices.offerPrice] !== undefined && fieldsArray[dataIndices.offerPrice] !== '' ? fieldsArray[dataIndices.offerPrice] : '-',
        lastPrice: fieldsArray[dataIndices.lastPrice] !== undefined && fieldsArray[dataIndices.lastPrice] !== '' ? fieldsArray[dataIndices.lastPrice] : '-',
        volume: fieldsArray[dataIndices.volume] !== undefined && fieldsArray[dataIndices.volume] !== '' ? fieldsArray[dataIndices.volume] : '-',
        settlementPrice: fieldsArray[dataIndices.settlementPrice] !== undefined && fieldsArray[dataIndices.settlementPrice] !== '' ? fieldsArray[dataIndices.settlementPrice] : '-',
        lastUpdate: new Date().toLocaleTimeString(),
      };
    }
  }
};

let channelObj = null;

// Initialize Echo listeners and subscribe to WebSocket channel
const setupEchoListeners = () => {
  console.log('[WebhookTab] Running setupEchoListeners.');
  if (!(window.Echo && window.Echo.connector && window.Echo.connector.socket)) {
    console.error('[WebhookTab] Echo or its internal socket is not ready.');
    initializationMessage.value = 'Echo is not ready. Check echo.js or try again shortly.';
    return;
  }

  console.log('[WebhookTab] Echo and socket found. Socket status:', window.Echo.connector.socket.connected ? 'CONNECTED' : 'NOT CONNECTED');
  initializationMessage.value = 'Echo detected. Setting up listeners...';

  const socket = window.Echo.connector.socket;

  const subscribeToChannelLogic = () => {
    console.log('[WebhookTab] Subscribing to "market-data" channel...');
    initializationMessage.value = 'Subscribing to market data channel...';

    // Leave any previous subscription to avoid duplicate listeners
    if (channelObj) {
      console.log('[WebhookTab] Leaving previous channel subscription.');
      channelObj.stopListening('MatrizBookUpdated');
      window.Echo.leaveChannel('market-data');
    }

    channelObj = window.Echo.channel('market-data');

    channelObj.listen('MatrizBookUpdated', (eventData) => {
      console.log('[WebhookTab Listener] "MatrizBookUpdated" event received:', JSON.parse(JSON.stringify(eventData)));
      initializationMessage.value = 'Receiving market data...';
      handleMarketDataUpdate(eventData);
    })
    .error((error) => {
      console.error('[WebhookTab] Error in .listen() for MatrizBookUpdated:', error);
      initializationMessage.value = 'Error while listening: ' + (error.message || JSON.stringify(error));
    });

    channelObj.on('pusher:subscription_succeeded', () => {
      console.log('[WebhookTab] Successfully subscribed to "market-data".');
      initializationMessage.value = 'Subscribed. Waiting for market updates...';
    });

    channelObj.on('pusher:subscription_error', (statusCode) => {
      console.error(`[WebhookTab] Failed to subscribe to "market-data": ${statusCode}`);
      initializationMessage.value = `Subscription error (code: ${statusCode}).`;
    });

    console.log('[WebhookTab] Listeners set up for "market-data".');
  };

  if (socket.connected) {
    console.log('[WebhookTab] Echo socket already connected. Subscribing now.');
    subscribeToChannelLogic();
  } else {
    console.log('[WebhookTab] Socket not connected yet. Adding connect listener...');
    initializationMessage.value = 'Waiting for WebSocket connection...';

    socket.removeListener?.('connect', subscribeToChannelLogic);

    socket.on('connect', () => {
      console.log('[WebhookTab] "connect" event received. Subscribing now.');
      subscribeToChannelLogic();
    });
  }

  socket.on('disconnect', () => {
    console.warn('[WebhookTab] Socket disconnected.');
    initializationMessage.value = 'Disconnected. Trying to reconnect or refresh the page.';
  });

  socket.on('error', (error) => {
    console.error('[WebhookTab] Socket error received:', error);
    initializationMessage.value = 'Socket connection error: ' + (error.message || 'Unknown error');
  });
};

// Run once when component mounts
onMounted(() => {
  console.log('[WebhookTab] onMounted - starting setup.');
  initializationMessage.value = 'Checking Echo status...';

  // Retry if Echo isn't ready yet (e.g., due to loading delays)
  const checkEchoAndSetup = () => {
    if (window.Echo && window.Echo.connector && window.Echo.connector.socket) {
      setupEchoListeners();
    } else {
      console.warn('[WebhookTab] Echo/socket not ready yet. Retrying in 1s...');
      initializationMessage.value = 'Echo not ready yet, waiting...';
      setTimeout(checkEchoAndSetup, 1000);
    }
  };

  checkEchoAndSetup();
});

// Cleanup on component unmount
onUnmounted(() => {
  console.log('[WebhookTab] onUnmounted - cleaning up listeners.');
  if (channelObj) {
    channelObj.stopListening('MatrizBookUpdated');
    console.log('[WebhookTab] Stopped listening to "MatrizBookUpdated".');
  }
  if (window.Echo) {
    window.Echo.leave('market-data');
    console.log('[WebhookTab] Left "market-data" channel.');
  }
});
</script>
