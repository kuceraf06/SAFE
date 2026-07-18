<?php
/**
 * SAFE - front controller
 * Čeština = výchozí jazyk (bez prefixu v URL), angličtina = /en/...
 * Příklady:  /            -> cs/home
 *            /about       -> cs/about
 *            /en          -> en/home
 *            /en/about    -> en/about
 */

$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

$route = $_GET['route'] ?? '';
$route = trim($route, '/');

// ---- detekce jazyka z prefixu /en ----
$lang = 'cs';
if ($route === 'en' || strpos($route, 'en/') === 0) {
    $lang  = 'en';
    $route = trim(substr($route, 2), '/');
}

// ---- mapa: slug v URL -> soubor pohledu ----
$routes = [
    ''            => 'home',
    'about'       => 'about',
    'sponsors'    => 'sponsors',
    'events'      => 'events',       // minulé ročníky
    'awards'      => 'awards',       // ocenění hráči
    'program'     => 'program',
    'reservation' => 'reservation',  // vstupenky
    'reservation/cancel' => 'reservation-cancel',  // zrušení rezervace
    'contact'     => 'contact',
];

if (array_key_exists($route, $routes)) {
    $view = $routes[$route];
} else {
    http_response_code(404);
    $view = '404';
}

// =====================  POMOCNÉ FUNKCE PRO ODKAZY  =====================

/**
 * Hezké adresy (/awards) zapnuté, nebo náhradní (?route=awards)?
 *
 * Hezké adresy potřebují přepis adres na serveru. Na Apache to zařídí .htaccess
 * v kořeni projektu. Na NGINXU .htaccess neexistuje - buď se vloží přiložený
 * nginx.conf.vzor, NEBO se tohle přepne na false a web funguje i bez jakéhokoli
 * nastavení serveru (jen adresy budou ošklivější).
 *
 * Když po nasazení funguje jen úvodní stránka a všechno ostatní hlásí 404,
 * je to přesně tenhle případ: v config/config.php nastav 'pretty_urls' => false.
 */
function safe_pretty_urls(): bool
{
    static $zapnuto = null;
    if ($zapnuto === null) {
        $cfgFile = __DIR__ . '/config/config.php';
        $cfg = is_file($cfgFile) ? require $cfgFile : [];
        $zapnuto = (bool)($cfg['pretty_urls'] ?? true);
    }
    return $zapnuto;
}

/** Sestaví adresu podle toho, jestli server umí přepis adres. */
function safe_build_url(string $cesta): string
{
    global $baseUrl;
    if (safe_pretty_urls()) {
        return $baseUrl . $cesta;
    }
    return $baseUrl . 'index.php?route=' . rawurlencode($cesta);
}

/** Odkaz na stránku v aktuálním jazyce (do navbaru, tlačítek atd.) */
function url(string $slug = ''): string
{
    global $lang;
    $prefix = ($lang === 'en') ? 'en/' : '';
    return safe_build_url($prefix . $slug);
}

/** Odkaz na stejnou stránku v druhém jazyce (přepínač CZ/EN) */
function switchUrl(): string
{
    global $lang, $route;
    if ($lang === 'en') {
        return safe_build_url($route);                                  // en -> cs
    }
    return safe_build_url('en' . ($route !== '' ? '/' . $route : ''));  // cs -> en
}

/** Odkaz na statický soubor v public/ (css, js, obrázky) */
function asset(string $path): string
{
    global $baseUrl;
    return $baseUrl . 'public/' . ltrim($path, '/');
}

/** Zkratka pro bezpečný výpis do HTML (escape). */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// =====================  KONTROLA DATABÁZE  =====================
// Bez databáze by se web vykreslil prázdný a nikdo by nepoznal proč.
// Radši rovnou srozumitelná chyba s návodem (typicky chybí práva zápisu).
require_once __DIR__ . '/app/lib/data.php';
if (scm_public_db() === null) {
    safe_db_error_page();
}

// =====================  VYKRESLENÍ  =====================

ob_start();
require __DIR__ . "/app/views/$lang/$view.php";   // pohled si nastaví $pageTitle a $bodyClass
$content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<?php require __DIR__ . '/app/views/layout/head.php'; ?>
<body class="<?= $bodyClass ?? '' ?>">
    <?php require __DIR__ . '/app/views/layout/header.php'; ?>
    <?= $content ?>
    <?php require __DIR__ . '/app/views/layout/footer.php'; ?>

    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
