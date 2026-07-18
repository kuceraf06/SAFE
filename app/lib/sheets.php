<?php
/**
 * SAFE – tabulka rezervací v Google Sheets (přes službu SheetDB).
 *
 * Sloupce v tabulce:
 *   email | jméno | doprovod | cena | jméno hráče/trenéra 1..3 |
 *   kategorie 1..3 | kód rezervace | rezervace ("platí" / "zrušeno")
 *
 * Všechna volání jdou přes app/lib/http.php (certifikáty, záloha bez cURL,
 * logování chyb do storage/logs/http.log).
 */

require_once __DIR__ . '/http.php';

/** Adresa API tabulky z config/config.php. */
function safe_sheet_url(): string
{
    static $url = null;
    if ($url === null) {
        $cfg = require __DIR__ . '/../../config/config.php';
        $url = rtrim($cfg['sheetdb_api_url'] ?? '', '/');
    }
    return $url;
}

/** Poslední chyba práce s tabulkou (pro zobrazení uživateli / diagnostiku). */
function safe_sheet_last_error(?string $set = null): string
{
    static $error = '';
    if ($set !== null) {
        $error = $set;
    }
    return $error;
}

/**
 * Najde všechny řádky s danou e-mailovou adresou.
 *
 * @return array|null  pole řádků, nebo null když se tabulka nepodařilo načíst
 */
function safe_sheet_find_email(string $email): ?array
{
    safe_sheet_last_error('');

    $url = safe_sheet_url();
    if ($url === '') {
        safe_sheet_last_error('Není nastavená adresa tabulky rezervací (config/config.php).');
        return null;
    }

    $res = safe_http_request($url . '/search?email=' . urlencode($email), 'GET');
    if (!$res['ok']) {
        safe_sheet_last_error('Nepodařilo se spojit s tabulkou rezervací (' . ($res['error'] ?: 'neznámá chyba') . ').');
        return null;
    }

    $rows = json_decode($res['body'], true);
    return is_array($rows) ? $rows : [];
}

/** Má tento e-mail už platnou (nezrušenou) rezervaci? */
function safe_sheet_email_has_reservation(array $rows, string $email): bool
{
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rowEmail = $row['email'] ?? '';
        $state    = $row['rezervace'] ?? '';
        if ($rowEmail === $email && $state === 'platí') {
            return true;
        }
    }
    return false;
}

/** Vygeneruje náhodný devítimístný kód rezervace, který se v tabulce neopakuje. */
function safe_sheet_generate_code(array $existingCodes = []): string
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $code = '';
        for ($i = 0; $i < 9; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
    } while (in_array($code, $existingCodes, true));

    return $code;
}

/**
 * Zapíše novou rezervaci do tabulky.
 *
 * @param array $data  řádek tabulky (klíče = názvy sloupců)
 * @return bool
 */
function safe_sheet_add_reservation(array $data): bool
{
    safe_sheet_last_error('');

    $url = safe_sheet_url();
    if ($url === '') {
        safe_sheet_last_error('Není nastavená adresa tabulky rezervací (config/config.php).');
        return false;
    }

    $res = safe_http_request(
        $url,
        'POST',
        json_encode($data, JSON_UNESCAPED_UNICODE),
        ['Content-Type: application/json']
    );

    if (!$res['ok']) {
        safe_sheet_last_error('Rezervaci se nepodařilo zapsat do tabulky (' . ($res['error'] ?: 'neznámá chyba') . ').');
        safe_http_log('Zápis rezervace selhal: ' . $res['error'] . ' | odpověď: ' . substr($res['body'], 0, 300));
        return false;
    }

    return true;
}

/**
 * Označí rezervaci daného e-mailu jako zrušenou.
 * (Stejně jako v původním webu: rezervace = "zrušeno", e-mail se přeznačí,
 *  aby ta adresa šla použít pro novou rezervaci.)
 */
function safe_sheet_cancel_reservation(string $email): bool
{
    safe_sheet_last_error('');

    $url = safe_sheet_url();
    if ($url === '') {
        safe_sheet_last_error('Není nastavená adresa tabulky rezervací (config/config.php).');
        return false;
    }

    $res = safe_http_request(
        $url . '/email/' . rawurlencode($email),   // rawurlencode: v cestě URL musí být %20, ne +
        'PATCH',
        json_encode([
            'rezervace' => 'zrušeno',
            'email'     => $email . ' - zrušen',
        ], JSON_UNESCAPED_UNICODE),
        ['Content-Type: application/json']
    );

    if (!$res['ok']) {
        safe_sheet_last_error('Rezervaci se nepodařilo zrušit v tabulce (' . ($res['error'] ?: 'neznámá chyba') . ').');
        safe_http_log('Zrušení rezervace selhalo: ' . $res['error'] . ' | odpověď: ' . substr($res['body'], 0, 300));
        return false;
    }

    return true;
}

/**
 * Smaže řádek rezervace podle jejího kódu.
 *
 * Používá se, když se rezervace zapsala do tabulky, ale nepodařilo se
 * odeslat potvrzovací e-mail – pak nemá smysl ji v tabulce nechávat,
 * protože uživatel svůj kód nezná a nemohl by rezervaci zrušit.
 */
function safe_sheet_delete_reservation(string $code): bool
{
    safe_sheet_last_error('');

    $url = safe_sheet_url();
    if ($url === '' || $code === '') {
        return false;
    }

    // POZOR: název sloupce obsahuje mezeru i diakritiku, musí se zakódovat
    // pro cestu v URL (rawurlencode dělá %20, urlencode by udělal +).
    $sloupec = rawurlencode('kód rezervace');

    $res = safe_http_request($url . '/' . $sloupec . '/' . rawurlencode($code), 'DELETE');

    if (!$res['ok']) {
        safe_sheet_last_error('Řádek se nepodařilo z tabulky odstranit (' . ($res['error'] ?: 'neznámá chyba') . ').');
        safe_http_log('Mazání rezervace ' . $code . ' selhalo: ' . $res['error']);
        return false;
    }

    return true;
}
