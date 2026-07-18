<?php
/**
 * SAFE – robustní HTTP klient pro externí API (SendGrid, SheetDB).
 *
 * Proč to tu je:
 * Původní kód volal přímo curl_exec(). Na Windows/XAMPP ale PHP často nemá
 * nastavené kořenové certifikáty (curl.cainfo / openssl.cafile v php.ini),
 * takže KAŽDÝ https požadavek tiše selže (curl_exec vrátí false).
 * Výsledek: rezervace se nezapsala do tabulky a neodešel žádný e-mail,
 * aniž by se cokoliv zobrazilo.
 *
 * Řešení:
 *  1) k projektu je přibalen soubor config/cacert.pem s kořenovými certifikáty,
 *     který se použije automaticky (nemusí se nic nastavovat v php.ini),
 *  2) když v PHP chybí rozšíření cURL, použije se záloha přes stream
 *     (file_get_contents), takže to funguje i na hostingu bez cURL,
 *  3) každé selhání se zapíše do storage/logs/http.log, aby bylo poznat proč.
 */

/** Cesta k přibalenému balíku certifikátů (nebo null, když tam není). */
function safe_ca_bundle(): ?string
{
    static $ca = false;
    if ($ca === false) {
        $path = realpath(__DIR__ . '/../../config/cacert.pem');   // realpath kvůli Windows
        $ca = ($path !== false && is_readable($path)) ? $path : null;
    }
    return $ca;
}

/** Zápis do logu (storage/logs/http.log). Nikdy nespadne, když se nedá psát. */
function safe_http_log(string $message): void
{
    $dir = __DIR__ . '/../../storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents(
        $dir . '/http.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
        FILE_APPEND
    );
}

/**
 * Provede HTTP požadavek.
 *
 * @param string      $url
 * @param string      $method   GET | POST | PATCH | PUT | DELETE
 * @param string|null $body     tělo požadavku (obvykle JSON)
 * @param array       $headers  např. ['Content-Type: application/json']
 *
 * @return array{ok:bool, status:int, body:string, error:string}
 *         ok = true jen když se spojení povedlo A status je 2xx
 */
function safe_http_request(string $url, string $method = 'GET', ?string $body = null, array $headers = []): array
{
    $method = strtoupper($method);
    $result = ['ok' => false, 'status' => 0, 'body' => '', 'error' => ''];

    // ---------- 1) cURL (preferované) ----------
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Certifikáty ověřujeme (bezpečné), jen jim řekneme, kde je hledat.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $ca = safe_ca_bundle();
        if ($ca !== null) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca);
        }

        $response = curl_exec($ch);
        $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno    = curl_errno($ch);
        $errmsg   = curl_error($ch);
        curl_close($ch);

        if ($response !== false && $errno === 0) {
            $result = [
                'ok'     => ($status >= 200 && $status < 300),
                'status' => $status,
                'body'   => (string) $response,
                'error'  => ($status >= 200 && $status < 300) ? '' : "HTTP $status",
            ];
            if (!$result['ok']) {
                safe_http_log("cURL $method $url -> HTTP $status; odpověď: " . substr((string) $response, 0, 300));
            }
            return $result;
        }

        // cURL selhal – zkusíme ještě zálohu přes stream (níže).
        $result['error'] = "cURL chyba ($errno): $errmsg";
        safe_http_log("cURL $method $url selhal: {$result['error']} – zkouším zálohu přes stream");
    } else {
        $result['error'] = 'V PHP chybí rozšíření cURL';
        safe_http_log('cURL není k dispozici – zkouším zálohu přes stream');
    }

    // ---------- 2) Záloha: stream (file_get_contents) ----------
    if (!ini_get('allow_url_fopen')) {
        $result['error'] .= ' + allow_url_fopen je vypnuté (nelze poslat požadavek)';
        safe_http_log("Požadavek $method $url NELZE odeslat: {$result['error']}");
        return $result;
    }

    $opts = [
        'http' => [
            'method'        => $method,
            'header'        => implode("\r\n", $headers),
            'timeout'       => 25,
            'ignore_errors' => true,   // ať dostaneme i tělo u chybových stavů
        ],
        'ssl' => [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ],
    ];
    if ($body !== null) {
        $opts['http']['content'] = $body;
    }
    $ca = safe_ca_bundle();
    if ($ca !== null) {
        $opts['ssl']['cafile'] = $ca;
    }

    $response = @file_get_contents($url, false, stream_context_create($opts));

    $status = 0;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
                $status = (int) $m[1];
            }
        }
    }

    if ($response === false) {
        $last = error_get_last();
        $result['error'] .= ' | stream selhal: ' . ($last['message'] ?? 'neznámá chyba');
        safe_http_log("Stream $method $url selhal: {$result['error']}");
        return $result;
    }

    $ok = ($status >= 200 && $status < 300);
    if (!$ok) {
        safe_http_log("Stream $method $url -> HTTP $status; odpověď: " . substr($response, 0, 300));
    }

    return [
        'ok'     => $ok,
        'status' => $status,
        'body'   => $response,
        'error'  => $ok ? '' : "HTTP $status",
    ];
}
