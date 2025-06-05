  import Echo from 'laravel-echo';

  import Pusher from 'pusher-js';


  window.Pusher = Pusher;


  const echo = new Echo({

      broadcaster: 'reverb',

      key: import.meta.env.VITE_REVERB_APP_KEY,

      wsHost: import.meta.env.VITE_REVERB_HOST,

      wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,

      wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,

      forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',

      disableStats: true,

      enabledTransports: ['ws', 'wss'],

  });


  window.Echo = echo;

  console.log('[Echo.js] Echo initialized with Reverb:', echo);


  echo.connector.socket?.on('connect', () => {

      console.log('[Echo.js] Conectado al servidor Reverb.');

  });


  echo.connector.socket?.on('disconnect', () => {

      console.warn('[Echo.js] Desconectado del servidor Reverb.');

  });


  echo.connector.socket?.on('error', (error) => {

      console.error('[Echo.js] Error de conexión del socket de Reverb:', error);

  });