<?php
/**
 * Datová vrstva pro VEŘEJNÝ web SAFE.
 * -----------------------------------------------------------------------------
 * Pohledy tímto čtou data spravovaná v administraci z SQLite databáze.
 * Když databáze ještě neexistuje nebo je prázdná, funkce vrátí prázdná pole
 * a stránka se nerozbije. Připojení je jen pro čtení a záměrně samostatné.
 */

declare(strict_types=1);

/** Lehké read-only připojení ke stejné SQLite databázi jako administrace. */
function scm_public_db(): ?PDO
{
    static $conn = null;
    static $tried = false;
    if ($tried) {
        return $conn;
    }
    $tried = true;

    $configFile = __DIR__ . '/../../config/config.php';
    $dbPath = '';
    if (is_file($configFile)) {
        $cfg = require $configFile;
        $dbPath = trim((string)($cfg['db_path'] ?? ''));
    }
    if ($dbPath === '') {
        $dbPath = dirname(__DIR__, 2) . '/storage/db/safe.sqlite';
    }

    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        return null;
    }

    // Když databáze ještě neexistuje (čerstvě nahraný projekt), vytvoříme ji
    // a naplníme daty - stejně jako administrace. Web tak funguje hned.
    $needsInit = !is_file($dbPath);
    if ($needsInit) {
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    try {
        $conn = new PDO('sqlite:' . $dbPath);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $dbLib = __DIR__ . '/../../admin/lib/db.php';
        if (is_file($dbLib)) {
            require_once $dbLib;
            if ($needsInit && function_exists('scm_db_init_schema')) {
                scm_db_init_schema($conn);
            } elseif (function_exists('scm_db_migrate')) {
                // Databáze už existuje (typicky po nahrání nové verze kódu na
                // server). Doplníme sloupce, které v ní ještě nejsou - jinak by
                // web nevěděl o ceně nastavené v administraci.
                scm_db_migrate($conn);
            }
        }
    } catch (PDOException $e) {
        error_log('SAFE public DB: ' . $e->getMessage());
        $conn = null;
    }
    return $conn;
}

/** Bezpečně spustí dotaz a vrátí řádky (prázdné pole při chybě). */
function scm_fetch_all(string $sql, array $params = []): array
{
    $db = scm_public_db();
    if ($db === null) {
        return [];
    }
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('SAFE public query: ' . $e->getMessage());
        return [];
    }
}

/** Bezpečně vrátí jeden řádek (nebo null). */
function scm_fetch_one(string $sql, array $params = []): ?array
{
    $rows = scm_fetch_all($sql, $params);
    return $rows[0] ?? null;
}

/** Termín akce (datum a čas jako uložený string, nebo null). */
function safe_termin(): ?string
{
    $row = scm_fetch_one('SELECT datum_cas FROM termin ORDER BY id LIMIT 1');
    return $row['datum_cas'] ?? null;
}

/** Obrázek pozvánky (cesta relativně k public/, nebo null). */
function safe_invitation(): ?string
{
    $row = scm_fetch_one('SELECT image FROM invitation ORDER BY id LIMIT 1');
    return !empty($row['image']) ? $row['image'] : null;
}

/**
 * Cesta k logu v hlavičce (relativně k public/).
 * Když je v administraci nahrané vlastní, vrátí to; jinak výchozí logo.
 */
function safe_site_logo(): string
{
    $vychozi = 'images/common/logo-SAFE.png';
    $row = scm_fetch_one('SELECT image FROM site_logo ORDER BY id LIMIT 1');
    if (empty($row['image'])) {
        return $vychozi;
    }
    // pojistka: kdyby byl soubor smazaný z disku, spadneme na výchozí
    $plna = dirname(__DIR__, 2) . '/public/' . $row['image'];
    return is_file($plna) ? $row['image'] : $vychozi;
}

/** Jsou vstupenky/rezervace aktivní? */
function safe_reservation_active(): bool
{
    $row = scm_fetch_one('SELECT is_active FROM reservation_status ORDER BY id LIMIT 1');
    return (int)($row['is_active'] ?? 0) === 1;
}

/** Výchozí cena vstupenky pro doprovod, když v databázi ještě nic není (Kč). */
const SAFE_ESCORT_PRICE_DEFAULT = 250;

/**
 * Cena jedné vstupenky pro doprovod v Kč, jak je nastavená v administraci.
 * Hodnota 0 znamená, že jsou vstupenky zdarma.
 */
function safe_escort_price(): int
{
    $row = scm_fetch_one('SELECT escort_price FROM reservation_status ORDER BY id LIMIT 1');
    if ($row === null || !isset($row['escort_price'])) {
        return SAFE_ESCORT_PRICE_DEFAULT;   // prázdná nebo stará databáze
    }
    return max(0, (int)$row['escort_price']);
}

/** Řádky programu (seřazené). */
function safe_programme(): array
{
    return scm_fetch_all('SELECT * FROM programme ORDER BY sort_order, id');
}

/** Minulé ročníky (seřazené od nejnovějšího po nejstarší). */
function safe_events(): array
{
    return scm_fetch_all('SELECT * FROM events ORDER BY sort_order DESC, id DESC');
}

/**
 * Ocenění hráči seskupení: [rok][kategorie] => [ocenění, ...].
 * Roky jsou seřazené sestupně (nejnovější první).
 */
function safe_awards_grouped(): array
{
    $rows = scm_fetch_all('SELECT * FROM awards ORDER BY year DESC, sort_order, id');
    $out = [];
    foreach ($rows as $r) {
        $out[$r['year']][$r['category']][] = $r;
    }
    return $out;
}

/**
 * Srozumitelná chybová stránka, když se nepodaří otevřít databázi.
 *
 * Proč to tu je: bez tohohle by se web vykreslil normálně, jen úplně prázdný
 * (žádná ocenění, žádné ročníky) a chyba by skončila jen v logu serveru.
 * Ten, kdo web nasazoval, by neměl šanci poznat, co je špatně.
 *
 * Skoro vždy jde o jedinou věc: webový server nemá právo zápisu do složky,
 * kde má databáze vzniknout.
 */
function safe_db_error_page(): void
{
    $root = dirname(__DIR__, 2);

    // projdeme složky, do kterých web potřebuje zapisovat
    $slozky = [
        'storage/db'            => 'sem se ukládá databáze (bez ní web nefunguje)',
        'storage/logs'          => 'sem se zapisují chyby',
        'public/images/uploads' => 'sem se ukládají obrázky nahrané v administraci',
    ];

    $radky = '';
    foreach ($slozky as $cesta => $popis) {
        $plna     = $root . '/' . $cesta;
        $existuje = is_dir($plna);
        $zapis    = $existuje && is_writable($plna);
        $stav     = !$existuje ? 'složka neexistuje' : ($zapis ? 'v pořádku' : 'NELZE ZAPISOVAT');
        $trida    = $zapis ? 'ok' : 'bad';
        $radky   .= "<tr class=\"$trida\"><td><code>$cesta/</code></td><td>$stav</td><td>$popis</td></tr>";
    }

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>SAFE – web se nepodařilo spustit</title>
    <style>
        body { font-family: system-ui, "Segoe UI", Arial, sans-serif; background: #F8F4F4; color: #222;
               margin: 0; padding: 40px 20px; line-height: 1.6; }
        .box { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 12px;
               padding: 36px 34px; box-shadow: 0 6px 22px rgba(0,0,0,.10); }
        h1 { font-size: 24px; margin: 0 0 6px; }
        .lead { color: #666; margin: 0 0 26px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 26px; font-size: 14px; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; vertical-align: top; }
        tr.bad td { background: #fdecea; }
        tr.bad td:nth-child(2) { color: #9d281c; font-weight: 700; }
        tr.ok td:nth-child(2) { color: #1e7d32; }
        code { background: #f2f2f2; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
        .fix { background: #fffbe9; border: 1px solid #f0e2b0; border-radius: 8px; padding: 16px 18px; }
        .fix h2 { font-size: 16px; margin: 0 0 10px; }
        pre { background: #2b2b2b; color: #eee; padding: 12px 14px; border-radius: 6px;
              overflow-x: auto; font-size: 13px; margin: 8px 0 0; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Web se nepodařilo spustit</h1>
        <p class="lead">Nepodařilo se otevřít ani vytvořit databázi. Web proto nemá odkud vzít obsah.</p>

        <table>$radky</table>

        <div class="fix">
            <h2>Jak to spravit</h2>
            <p style="margin:0">Webový server potřebuje právo zápisu do označených složek. Na serveru spusťte:</p>
            <pre>chmod -R 775 storage public/images/uploads
chown -R www-data:www-data storage public/images/uploads</pre>
            <p style="margin:10px 0 0">Místo <code>www-data</code> doplňte uživatele, pod kterým běží web
            (na Synology bývá <code>http</code>). Pak stránku načtěte znovu – databáze se sama vytvoří
            i s veškerým obsahem.</p>
        </div>
    </div>
</body>
</html>
HTML;
    exit;
}
