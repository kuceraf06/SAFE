<?php
/**
 * SAFE – ochrana formulářů proti spamu.
 *
 * Cíl: zastavit roboty, ale NEOBTĚŽOVAT návštěvníky. Žádná CAPTCHA, žádné
 * opisování znaků, žádná externí (placená) služba. Pro člověka je celá
 * ochrana neviditelná – formulář vyplní a odešle úplně stejně jako dosud.
 *
 * Čtyři vrstvy, každá chytá jiný typ robota:
 *
 *   1) NÁSTRAHA (honeypot) – ve formuláři je skryté políčko, které člověk
 *      nevidí a nevyplní. Robot vyplňuje všechno, na co narazí, takže se
 *      prozradí. Chytá drtivou většinu jednoduchých robotů.
 *
 *   2) ČAS – formulář si pamatuje (podepsaně, aby to nešlo podvrhnout), kdy
 *      byl načten. Člověk potřebuje aspoň pár vteřin na vyplnění; robot
 *      odesílá během zlomku vteřiny.
 *
 *   3) POČET ODESLÁNÍ – z jedné adresy jde odeslat jen omezený počet zpráv
 *      za hodinu. Brání zahlcení, i kdyby robot obešel předchozí vrstvy.
 *
 *   4) OBSAH – rozpozná náhodné shluky písmen typu "ygRgbNYDBSgVvMyGhA",
 *      kterými se roboti vyplňují pole. Schválně mírně nastavené, aby
 *      neodmítalo běžné zprávy.
 *
 * Všechno zablokované se zapisuje do storage/logs/spam.log, takže se dá
 * zpětně ověřit, jestli náhodou nepropadla i pravá zpráva.
 */

/** Jak dlouho (v sekundách) musí být formulář otevřený, než ho lze odeslat. */
const SAFE_ANTISPAM_MIN_SECONDS = 3;

/** Po jaké době platnost formuláře vyprší (12 hodin). */
const SAFE_ANTISPAM_MAX_SECONDS = 43200;

/** Kolik zpráv smí odejít z jedné IP adresy za hodinu. */
const SAFE_ANTISPAM_MAX_PER_HOUR = 5;

/** Název skrytého políčka-nástrahy. */
const SAFE_ANTISPAM_TRAP = 'website_url';

/**
 * Vloží do formuláře skrytá pole ochrany.
 * Volá se uvnitř <form>. Pro návštěvníka je výsledek neviditelný.
 */
function safe_antispam_fields(): string
{
    $cfg    = require __DIR__ . '/../../config/config.php';
    $secret = (string)($cfg['app_secret'] ?? 'safe-fallback-secret');

    $ts  = time();
    $sig = hash_hmac('sha256', (string)$ts, $secret);

    $trap = SAFE_ANTISPAM_TRAP;

    // Nástraha je schovaná mimo obrazovku (ne display:none – to někteří
    // roboti poznají). Zároveň ji přeskakuje tabulátor a nenapovídá prohlížeč.
    return <<<HTML
    <div style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;" aria-hidden="true">
        <label for="{$trap}">Toto pole nevyplňujte</label>
        <input type="text" id="{$trap}" name="{$trap}" value="" tabindex="-1" autocomplete="off">
    </div>
    <input type="hidden" name="_ts" value="{$ts}">
    <input type="hidden" name="_sig" value="{$sig}">
HTML;
}

/**
 * Ověří odeslaný formulář.
 *
 * @param array  $pola  pole s obsahem formuláře, která se mají prověřit na obsah
 *                      (např. ['name' => 'Jan', 'message' => 'Dobrý den...'])
 * @param string $lang  'cs' | 'en' – jazyk chybové hlášky
 *
 * @return array{ok:bool, message:string, silent:bool}
 *         ok      = true, když je vše v pořádku a smí se odeslat
 *         message = hláška pro návštěvníka, když ok = false
 *         silent  = true → tvářit se navenek jako úspěch (jistý robot),
 *                   zprávu ale zahodit
 */
function safe_antispam_check(array $pola = [], string $lang = 'cs'): array
{
    $t = safe_antispam_texts($lang);

    // ---- 1) NÁSTRAHA -----------------------------------------------------
    $trap = trim((string)($_POST[SAFE_ANTISPAM_TRAP] ?? ''));
    if ($trap !== '') {
        safe_antispam_log('nastraha', 'vyplnene skryte pole', $pola);
        // Robot ať si myslí, že uspěl – nebude to zkoušet jinak.
        return ['ok' => false, 'message' => '', 'silent' => true];
    }

    // ---- 2) ČAS ----------------------------------------------------------
    $cfg    = require __DIR__ . '/../../config/config.php';
    $secret = (string)($cfg['app_secret'] ?? 'safe-fallback-secret');

    $ts  = (string)($_POST['_ts'] ?? '');
    $sig = (string)($_POST['_sig'] ?? '');

    if ($ts === '' || $sig === '' || !hash_equals(hash_hmac('sha256', $ts, $secret), $sig)) {
        // Chybí nebo nesedí podpis – formulář neprošel normální cestou.
        safe_antispam_log('cas', 'chybny nebo chybejici podpis', $pola);
        return ['ok' => false, 'message' => $t['retry'], 'silent' => false];
    }

    $uplynulo = time() - (int)$ts;

    if ($uplynulo < SAFE_ANTISPAM_MIN_SECONDS) {
        safe_antispam_log('cas', "odeslano za {$uplynulo}s", $pola);
        return ['ok' => false, 'message' => $t['too_fast'], 'silent' => false];
    }

    if ($uplynulo > SAFE_ANTISPAM_MAX_SECONDS) {
        safe_antispam_log('cas', 'formular prilis stary', $pola);
        return ['ok' => false, 'message' => $t['expired'], 'silent' => false];
    }

    // ---- 3) POČET ODESLÁNÍ Z JEDNÉ ADRESY --------------------------------
    if (!safe_antispam_rate_ok()) {
        safe_antispam_log('pocet', 'prekrocen limit odeslani', $pola);
        return ['ok' => false, 'message' => $t['too_many'], 'silent' => false];
    }

    // ---- 4) OBSAH --------------------------------------------------------
    foreach ($pola as $nazev => $hodnota) {
        if (safe_antispam_je_nesmysl((string)$hodnota)) {
            safe_antispam_log('obsah', "nahodny text v poli '{$nazev}'", $pola);
            return ['ok' => false, 'message' => $t['gibberish'], 'silent' => false];
        }
    }

    return ['ok' => true, 'message' => '', 'silent' => false];
}

/**
 * Rozpozná náhodný shluk písmen, kterým se vyplňují roboti
 * (např. "ygRgbNYDBSgVvMyGhA" nebo "FrABjIyZVlgUzCfvlALjgOOe").
 *
 * Schválně opatrné – raději spam propustí, než aby odmítlo pravou zprávu.
 * Musí se sejít VÍCE příznaků najednou.
 */
function safe_antispam_je_nesmysl(string $text): bool
{
    $text = trim($text);

    // Krátké texty neřešíme (např. jméno "Jan", zpráva "Ahoj").
    if (mb_strlen($text) < 12) {
        return false;
    }

    // Text s mezerami a interpunkcí je skoro jistě psaný člověkem.
    // Roboti sypou jeden slepený řetězec.
    if (preg_match('/[\s.,!?;:()]/u', $text)) {
        return false;
    }

    // Od téhle chvíle řešíme jen souvislý řetězec bez mezer delší než 12 znaků.
    // Musí být jen z písmen (jinak to bývá odkaz, kód objednávky apod.).
    if (!preg_match('/^[a-zA-Z]+$/', $text)) {
        return false;
    }

    $body = 0;

    // (a) Podíl samohlásek. Běžná čeština i angličtina mají zhruba 35–45 %.
    //     Náhodný shluk jich má výrazně míň.
    $samohlasky = preg_match_all('/[aeiouyáéěíóúůýAEIOUYÁÉĚÍÓÚŮÝ]/u', $text);
    $podil      = $samohlasky / max(1, mb_strlen($text));
    if ($podil < 0.22) {
        $body += 2;
    }

    // (b) Náhodné střídání velkých a malých písmen.
    //     "ygRgbNYDBSg" střídá skoro u každého znaku; člověk tak nepíše.
    $prepnuti = 0;
    $delka    = strlen($text);
    for ($i = 1; $i < $delka; $i++) {
        $predchozi = ctype_upper($text[$i - 1]);
        $aktualni  = ctype_upper($text[$i]);
        if ($predchozi !== $aktualni) {
            $prepnuti++;
        }
    }
    if ($delka > 0 && ($prepnuti / $delka) > 0.35) {
        $body += 2;
    }

    // (c) Dlouhá řada souhlásek za sebou (5 a víc) – v běžném slově vzácné.
    if (preg_match('/[bcdfghjklmnpqrstvwxzBCDFGHJKLMNPQRSTVWXZ]{5,}/u', $text)) {
        $body += 1;
    }

    // Blokujeme až při shodě více příznaků.
    return $body >= 3;
}

/**
 * Hlídá počet odeslání z jedné IP adresy.
 * Ukládá se do storage/logs/rate.json (žádná databáze není potřeba).
 */
function safe_antispam_rate_ok(): bool
{
    $ip     = safe_antispam_ip();
    $soubor = dirname(__DIR__, 2) . '/storage/logs/rate.json';
    $slozka = dirname($soubor);

    if (!is_dir($slozka) || !is_writable($slozka)) {
        // Když se nedá zapisovat, ochranu raději vypneme, než abychom
        // zablokovali web. Ostatní tři vrstvy fungují dál.
        return true;
    }

    $ted   = time();
    $hodina = 3600;

    $fp = @fopen($soubor, 'c+');
    if ($fp === false) {
        return true;
    }

    $ok = true;
    if (flock($fp, LOCK_EX)) {
        $obsah = stream_get_contents($fp);
        $data  = json_decode((string)$obsah, true);
        if (!is_array($data)) {
            $data = [];
        }

        // úklid starých záznamů
        foreach ($data as $klic => $casy) {
            $data[$klic] = array_values(array_filter(
                (array)$casy,
                static fn($c) => ($ted - (int)$c) < $hodina
            ));
            if (empty($data[$klic])) {
                unset($data[$klic]);
            }
        }

        $moje = $data[$ip] ?? [];
        if (count($moje) >= SAFE_ANTISPAM_MAX_PER_HOUR) {
            $ok = false;
        } else {
            $moje[]     = $ted;
            $data[$ip]  = $moje;
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);

    return $ok;
}

/** IP adresa návštěvníka (bere ohled na reverzní proxy). */
function safe_antispam_ip(): string
{
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $klic) {
        if (!empty($_SERVER[$klic])) {
            $hodnota = (string)$_SERVER[$klic];
            // X-Forwarded-For může mít víc adres oddělených čárkou
            $prvni = trim(explode(',', $hodnota)[0]);
            if (filter_var($prvni, FILTER_VALIDATE_IP)) {
                return $prvni;
            }
        }
    }
    return 'neznama';
}

/** Zápis zablokovaného pokusu do logu (pro zpětnou kontrolu). */
function safe_antispam_log(string $vrstva, string $duvod, array $pola): void
{
    $soubor = dirname(__DIR__, 2) . '/storage/logs/spam.log';
    if (!is_dir(dirname($soubor)) || !is_writable(dirname($soubor))) {
        return;
    }

    $ukazka = [];
    foreach ($pola as $nazev => $hodnota) {
        $ukazka[] = $nazev . '=' . mb_substr(str_replace(["\r", "\n"], ' ', (string)$hodnota), 0, 60);
    }

    $radek = sprintf(
        "[%s] %s | %s | %s | %s\n",
        date('Y-m-d H:i:s'),
        safe_antispam_ip(),
        $vrstva,
        $duvod,
        implode('; ', $ukazka)
    );

    @file_put_contents($soubor, $radek, FILE_APPEND | LOCK_EX);
}

/** Hlášky pro návštěvníka. */
function safe_antispam_texts(string $lang): array
{
    if ($lang === 'en') {
        return [
            'retry'     => 'The form could not be submitted. Please reload the page and try again.',
            'too_fast'  => 'The form was submitted too quickly. Please try again.',
            'expired'   => 'The form has expired. Please reload the page and try again.',
            'too_many'  => 'Too many messages have been sent from your address. Please try again later.',
            'gibberish' => 'Please fill in the form with a real name and message.',
        ];
    }

    return [
        'retry'     => 'Formulář se nepodařilo odeslat. Načtěte prosím stránku znovu a zkuste to znovu.',
        'too_fast'  => 'Formulář byl odeslán příliš rychle. Zkuste to prosím ještě jednou.',
        'expired'   => 'Platnost formuláře vypršela. Načtěte prosím stránku znovu a zkuste to znovu.',
        'too_many'  => 'Z vaší adresy už bylo odesláno příliš mnoho zpráv. Zkuste to prosím později.',
        'gibberish' => 'Vyplňte prosím formulář skutečným jménem a zprávou.',
    ];
}
