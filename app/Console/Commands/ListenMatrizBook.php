<?php

namespace App\Console\Commands;

use App\Events\MatrizBookUpdated; // Asegúrate que esta clase exista y esté correcta
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use WebSocket\Client;
use WebSocket\ConnectionException;
use WebSocket\TimeoutException;
use GuzzleHttp\Cookie\CookieJar;

class ListenMatrizBook extends Command
{
    protected $signature = 'matriz:listen-book';
    protected $description = 'Escucha el order-book de Matriz vía WebSocket para opciones de MERVAL configuradas.';

    // Mapeo de subyacentes a prefijos de opción
    protected array $mapping = [
        'ALUA' => 'ALU', 'BYMA' => 'BYM', 'COME' => 'COM',
        'EDN'  => 'EDN', 'GGAL' => 'GFG', 'METR' => 'MET',
        'PAMP' => 'PAM', 'YPFD' => 'YPF',
    ];

    public function handle(): int
    {
        $this->info('--- Iniciando matriz:listen-book ---');

        // 1. VALIDACIÓN DE SUBYACENTES
        $configuredMervTickers = config('tickers.merv', []);
        if (empty($configuredMervTickers)) {
            $this->error("No hay subyacentes en config/tickers.merv");
            Log::warning("[ListenMatrizBook] No hay subyacentes configurados.");
            return Command::FAILURE;
        }
        $this->info('Subyacentes configurados: ' . implode(', ', $configuredMervTickers));

        // 2. AUTENTICACIÓN MANUAL
        $baseUrl = rtrim(config('services.matriz_api.base_url'), '/');
        $loginUrl = "{$baseUrl}/auth/login";
        $profileUrl = "{$baseUrl}/api/v2/profile";

        $this->info("Base URL de Matriz: {$baseUrl}");
        if (empty($baseUrl)) {
            $this->error('services.matriz_api.base_url no configurado');
            return Command::FAILURE;
        }

        $jar = new CookieJar();
        $this->info('→ Sembrando cookie anónima...');
        Http::withOptions(['cookies' => $jar, 'verify' => false])
            ->withHeaders(['Accept' => 'text/html'])
            ->get($baseUrl . '/');

        $this->info('→ Obteniendo CSRF token inicial...');
        $ds = round(microtime(true) * 1000) . '-' . rand(100000, 999999);
        $resp = Http::withOptions(['cookies' => $jar, 'verify' => false])
            ->withHeaders(['accept' => 'application/json'])
            ->get("{$profileUrl}?_ds={$ds}");

        if (!$resp->successful()) {
            $this->error("Error al obtener profile inicial ({$resp->status()}) Body: " . $resp->body());
            return Command::FAILURE;
        }
        $csrfToken = $resp->json('csrfToken');
        if (!$csrfToken) {
            $this->error("No se pudo obtener CSRF token del profile inicial. Respuesta: " . $resp->body());
            return Command::FAILURE;
        }
        $this->info("→ csrfToken recibido: {$csrfToken}");

        $this->info('→ Realizando login...');
        $resp = Http::withOptions(['cookies' => $jar, 'verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-CSRF-Token' => $csrfToken,
                'Origin'       => $baseUrl,
                'Referer'      => $baseUrl . '/',
            ])
            ->post($loginUrl, [
                'username' => config('services.matriz_api.username'),
                'password' => config('services.matriz_api.password'),
            ]);

        if (!$resp->successful()) {
            $this->error("Login fallido ({$resp->status()}) Body: " . $resp->body());
            return Command::FAILURE;
        }
        $this->info('→ Login exitoso');

        // 3. OBTENER PROFILE FINAL (sessionId, connectionId y cuentas)
        $this->info('→ Obteniendo profile final...');
        $ds2 = round(microtime(true) * 1000) . '-' . rand(100000, 999999);
        $resp = Http::withOptions(['cookies' => $jar, 'verify' => false])
            ->withHeaders(['accept' => 'application/json'])
            ->get("{$profileUrl}?_ds={$ds2}");

        if (!$resp->successful()) {
            $this->error("Error al obtener profile final ({$resp->status()}) Body: " . $resp->body());
            return Command::FAILURE;
        }
        $profile = $resp->json();
        $sessionId = $profile['sessionId'] ?? null;
        $connectionId = $profile['connectionId'] ?? null;
        $accountId = 34204; // Hardcodeado como en tu ejemplo. Considera extraerlo de $profile['accounts'][0]['id'] si es necesario.

        $this->info("→ sessionId={$sessionId}, connectionId={$connectionId}, accountId={$accountId}");

        if (!$sessionId || !$connectionId) {
            $this->error('Faltan sessionId o connectionId en profile final. Respuesta: ' . $resp->body());
            return Command::FAILURE;
        }

        Cache::put('matriz_session_data', [
            'sessionId' => $sessionId,
            'connectionId' => $connectionId,
            'accountId' => $accountId,
            'cached_at' => now()->toIso8601String(),
        ], now()->addMinutes(50));
        $this->info('→ Datos de sesión cacheados');

        // 4. OBTENER INSTRUMENTOS (ref-data)
        $this->info('Obteniendo todos los instrumentos de Matriz (ref-data)...');
        $refUrl = "{$baseUrl}/api/v2/ref-data";
        $ds3 = round(microtime(true) * 1000) . '-' . rand(100000, 999999);
        $allSecurities = []; // Inicializar para asegurar que esté definida
        $respSec = Http::withOptions(['cookies' => $jar, 'verify' => false])
            ->withHeaders(['Accept' => 'application/json'])
            ->get("{$refUrl}?_ds={$ds3}");

        if (!$respSec->successful()) {
            $this->error("Falló ref-data ({$respSec->status()})");
            Log::error("[ListenMatrizBook] Ref-data error: {$respSec->body()}");
            // Podríamos decidir si es fatal, por ahora se advertirá más adelante si no hay productos para suscribir.
        } else {
            $allSecurities = $respSec->json('securities', []);
            $this->info("Total de instrumentos obtenidos de ref-data: " . count($allSecurities));
        }

        // 5. SIMULAR CALLS A REPORT PARA "ACTIVAR" LOS DATOS DE OPCIONES EN EL BACKEND
        if ($accountId) {
            $commonHeaders = [
                'accept' => 'application/json, text/plain, */*',
                'dnt' => '1',
                'sec-ch-ua' => '"Chromium";v="136", "Google Chrome";v="136", "Not.A/Brand";v="99"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Linux"',
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'
            ];
            $this->info("→ Simulando llamadas a /report para influir en los flujos de datos del backend...");
            $dsFav = round(microtime(true) * 1000) . '-' . rand(100000, 999999);
            // ASIGNAR AQUÍ:
            $respFav = Http::withOptions(['cookies' => $jar, 'verify' => false]) 
                ->withHeaders(array_merge($commonHeaders, ['referer' => $baseUrl . '/principal/favoritos']))
                ->get("{$baseUrl}/api/v2/accounts/{$accountId}/report?_ds={$dsFav}");
            $this->info("  → Report favoritos (status {$respFav->status()})");

            $dsOpt = round(microtime(true) * 1000) . '-' . rand(100000, 999999);
            $respOpt = Http::withOptions(['cookies' => $jar, 'verify' => false])
                ->withHeaders(array_merge($commonHeaders, ['referer' => $baseUrl . '/byma/opciones']))
                ->get("{$baseUrl}/api/v2/accounts/{$accountId}/report?_ds={$dsOpt}");
            $this->info("  → Report opciones (status {$respOpt->status()})");
        } else {
            $this->warn('No se encontró accountId; omitiendo simulación de calls a /report.');
        }

        // 6. PREPARAR PRODUCTOS PARA SUSCRIPCIÓN WEBSOCKET
        $this->info("Preparando tópicos para la suscripción WebSocket...");
        $topicsToSubscribe = [];
        if (!empty($allSecurities)) {
            $configuredUnderlyingPrefixes = array_values($this->mapping); // e.g. ['ALU', 'YPF']
            foreach ($allSecurities as $security) {
                // Solo opciones (CFI Code empieza con 'O') y que tengan símbolo e id
                if (isset($security['cfiCode']) && strtoupper(substr($security['cfiCode'], 0, 1)) === 'O' && 
                    isset($security['symbol']) && isset($security['id'])) {
                    
                    foreach ($configuredUnderlyingPrefixes as $prefix) {
                        // Si el símbolo de la opción (e.g., "YPFV39000J") empieza con el prefijo del underlying mapeado ("YPF")
                        // Y el marketSegmentId indica que es de MERV (para ser más precisos)
                        if (strpos($security['symbol'], $prefix) === 0 && 
                            isset($security['marketSegmentId']) && $security['marketSegmentId'] === 'bm_MERV') {
                            
                            // El tópico es "md." + el id completo del instrumento
                            $topicsToSubscribe[] = "md." . $security['id']; // e.g., "md.bm_MERV_YPFV39000J_24hs"
                            break; // Pasar al siguiente security una vez que encontramos un prefijo coincidente
                        }
                    }
                }
            }
            // Eliminar duplicados si los hubiera (aunque la lógica de break ya debería prevenir muchos)
            $topicsToSubscribe = array_unique($topicsToSubscribe);
            // Re-indexar el array por si acaso alguna librería lo necesita como lista secuencial JSON
            $topicsToSubscribe = array_values($topicsToSubscribe); 
        }

        if (empty($topicsToSubscribe)) {
            $this->warn("No se encontraron instrumentos de opciones en ref-data para los subyacentes configurados (" . implode(', ', $configuredMervTickers) . ") que coincidan con el segmento MERV. No se enviará mensaje de suscripción específico.");
        } else {
            $this->info("Se prepararon " . count($topicsToSubscribe) . " tópicos para la suscripción a market data.");
            // $this->line("Tópicos a suscribir: " . json_encode($topicsToSubscribe)); // Descomentar para depuración
        }

        // 7. SUSCRIPCIÓN WEBSOCKET
        $this->info('Conectando al WebSocket...');
        try {
            $cached = Cache::get('matriz_session_data');
            if (!$cached || !isset($cached['sessionId']) || !isset($cached['connectionId'])) {
                $this->error("No se encontraron datos de sesión cacheados válidos para el WebSocket.");
                return Command::FAILURE;
            }
            $sid = rawurlencode($cached['sessionId']);
            $cid = rawurlencode($cached['connectionId']);
            
            $wsUrl = str_replace(['http:', 'https:'], ['ws:', 'wss:'], $baseUrl)
                . "/ws?session_id={$sid}&conn_id={$cid}";

            $sslContextOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
            $streamContext = stream_context_create($sslContextOptions);

            $cookieHeader = collect($jar->toArray())->map(fn ($c) => "{$c['Name']}={$c['Value']}")->implode('; ');
            $wsHeaders = [
                'Origin'           => $baseUrl,
                'User-Agent'       => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36',
                'Accept-Language'  => 'es-US,es-419;q=0.9,es;q=0.8,it;q=0.7',
                'Cache-Control'    => 'no-cache', 'Pragma'           => 'no-cache',
                'Sec-WebSocket-Extensions' => 'permessage-deflate; client_max_window_bits',
                'Cookie'           => $cookieHeader,
            ];
            
            $this->info("Intentando conectar a WebSocket: {$wsUrl}");
            // $this->info("Headers para WebSocket: " . json_encode($wsHeaders)); // Descomentar para depuración detallada

            $client = new Client($wsUrl, [
                'headers' => $wsHeaders, 'timeout' => 90,
                'context' => $streamContext, 'persistent' => true,
            ]);
            $this->info('Cliente WebSocket instanciado y conectado.');

            // ENVIAR MENSAJE DE SUSCRIPCIÓN
            if (!empty($topicsToSubscribe)) {
                // La aplicación real parece enviar múltiples mensajes de suscripción si la lista es muy larga,
                // o quizás los agrupa. Por ahora, intentaremos enviar todos los tópicos en un solo mensaje.
                // Si son demasiados (más de 1000 como antes), podría ser necesario dividirlos.
                // Vamos a probar con un solo mensaje primero.

                $subscriptionMessageData = [
                    '_req'      => "S",
                    'topicType' => "md",
                    'topics'    => $topicsToSubscribe,
                    'replace'   => false,
                ];
                $subscriptionMessageJson = json_encode($subscriptionMessageData);
                
                $this->info("Enviando mensaje de suscripción (nuevo formato)...");
                // $this->line("Detalle suscripción: {$subscriptionMessageJson}"); // Descomentar para ver el JSON exacto
                $client->text($subscriptionMessageJson);
                $this->info("Mensaje de suscripción enviado. Escuchando mensajes de market data...");
            } else {
                $this->warn("No hay tópicos específicos para suscribir. El servidor probablemente no enviará datos de mercado.");
            }

            $firstMessageProcessed = false; // Para loguear el primer mensaje real
            
            // Bucle para recibir mensajes
            while (true) {
                $this->line("Estado del cliente antes de receive(): {$client}");
                 if (str_contains((string)$client, '(closed)')) {
                    $this->error("El cliente WS se reporta como cerrado antes de intentar recibir. Saliendo.");
                    break; 
                }
                $msg = $client->receive();

                if ($msg === false || $msg === null || $msg === "") {
                    $this->warn("receive() devolvió un valor indicativo de posible cierre o no datos. Estado: {$client}");
                    if (str_contains((string)$client, '(closed)')) {
                         $this->error("El cliente WS se cerró. Saliendo del bucle.");
                         break;
                    }
                    $this->info("No se recibió mensaje (timeout o conexión inactiva). Continuando escucha...");
                    sleep(1); 
                    continue;
                }
                
                $this->line("Mensaje recibido: {$msg}");
                if (is_string($msg) && !empty(trim($msg))) {
                     if (class_exists(MatrizBookUpdated::class)) {
                        broadcast(new MatrizBookUpdated($msg))->toOthers();
                     } else { $this->warn("La clase de evento MatrizBookUpdated no existe."); }
                } elseif (is_array($msg) && empty($msg)) {
                    $this->warn("Mensaje recibido como array vacío. Estado del cliente: {$client}. Probablemente conexión cerrada.");
                    if (str_contains((string)$client, '(closed)')) break;
                }
                 usleep(100000); // 0.1 segundos de pausa
            }

        } catch (ConnectionException $e) {
            $this->error("Excepción de Conexión WS: " . $e->getMessage());
            Log::error('Excepción de Conexión WebSocket', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        } catch (TimeoutException $e) {
            $this->error("Excepción de Timeout WS (leyendo): " . $e->getMessage());
            Log::error('Excepción de Timeout WebSocket (leyendo)', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Error WS Genérico: " . $e->getMessage());
            $this->error("Tipo de Excepción: " . get_class($e));
            Log::error('Error WebSocket Genérico', ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
             if (strpos($e->getMessage(), 'get_resource_type()') !== false && isset($client)) {
                $this->error("El error 'get_resource_type()' ocurrió. Estado del cliente al momento del error: {$client}");
            }
            return Command::FAILURE;
        } finally {
            if (isset($client) && $client instanceof Client && !str_contains((string)$client, '(closed)')) {
                $this->info("Cerrando conexión WebSocket...");
                $client->close();
            }
        }

        $this->info('--- Fin del comando matriz:listen-book ---');
        return Command::SUCCESS;
    }
}