<?php
/**
 * Ruční naplnění databáze výchozími daty z příkazové řádky.
 * -----------------------------------------------------------------------------
 * POZNÁMKA: Tohle většinou NEPOTŘEBUJEŠ. Data se totiž naplní AUTOMATICKY při
 * prvním otevření webu (viz scm_db_init_schema v db.php). Tento skript je jen
 * pro případ, že bys chtěl seed spustit ručně (např. po smazání databáze).
 *
 * Je idempotentní - plní jen prázdné tabulky, živá data nikdy nepřepíše.
 *
 * Spuštění:  php admin/lib/seed.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Seed lze spustit jen z příkazové řádky.');
}

define('SCM_ROOT', dirname(__DIR__, 2));
$config = require SCM_ROOT . '/config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/seed_data.php';

// scm_db() sama vytvoří schéma i spustí automatický seed; pro jistotu
// zavoláme seed ještě explicitně (kdyby tabulky vznikly dřív prázdné).
$db = scm_db($config);
$inserted = scm_seed_data($db);

echo 'Minulé ročníky: ' . ($inserted['events'] > 0 ? 'vloženo ' . $inserted['events'] : 'přeskočeno (už obsahují data)') . "\n";
echo 'Program: ' . ($inserted['programme'] > 0 ? 'vloženo ' . $inserted['programme'] : 'přeskočeno (už obsahují data)') . "\n";
echo 'Ocenění: ' . ($inserted['awards'] > 0 ? 'vloženo ' . $inserted['awards'] : 'přeskočeno (už obsahují data)') . "\n";
echo "Seed dokončen.\n";
