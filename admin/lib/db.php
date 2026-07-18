<?php
/**
 * Databázová vrstva (SQLite přes PDO).
 * -----------------------------------------------------------------------------
 * Klíčová bezpečnostní vlastnost: databáze leží MIMO web root (ve storage/,
 * ne v public/), takže ji nelze stáhnout přes URL. Na produkci lze v configu
 * nastavit absolutní cestu úplně mimo git checkout ('db_path').
 *
 * Schéma se vytvoří automaticky při prvním připojení (CREATE TABLE IF NOT
 * EXISTS). Pozn.: funkce si nechávají prefix scm_ kvůli sdílenému jádru
 * s ostatními projekty, ale patří k webu SAFE.
 */

declare(strict_types=1);

/**
 * Vrátí připojení k databázi. Cesta:
 *   1) config 'db_path', pokud je vyplněná (ideálně absolutní, mimo checkout)
 *   2) fallback: storage/db/safe.sqlite (mimo public/, ale uvnitř projektu)
 */
function scm_db(array $config): PDO
{
    static $conn = null;
    if ($conn instanceof PDO) {
        return $conn;
    }

    $dbPath = trim((string)($config['db_path'] ?? ''));
    if ($dbPath === '') {
        $dbPath = SCM_ROOT . '/storage/db/safe.sqlite';
    }

    $dir = dirname($dbPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        http_response_code(500);
        exit('SQLite PDO driver (pdo_sqlite) není v PHP dostupný.');
    }

    try {
        $conn = new PDO('sqlite:' . $dbPath);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conn->exec('PRAGMA foreign_keys = ON');
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('SAFE DB: ' . $e->getMessage());
        exit('Chyba připojení k databázi.');
    }

    scm_db_init_schema($conn);
    return $conn;
}

/**
 * Vytvoří tabulky, pokud ještě neexistují, a naplní výchozí data.
 */
function scm_db_init_schema(PDO $db): void
{
    // Termín akce (jedna hodnota - datum a čas)
    $db->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS termin (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            datum_cas  TEXT NOT NULL DEFAULT ''
        )
    SQL);

    // Pozvánka (jeden obrázek)
    $db->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS invitation (
            id       INTEGER PRIMARY KEY AUTOINCREMENT,
            image    TEXT NOT NULL DEFAULT ''
        )
    SQL);

    // Logo v hlavičce webu (každý ročník bývá jiné). Prázdné = výchozí logo.
    $db->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS site_logo (
            id    INTEGER PRIMARY KEY AUTOINCREMENT,
            image TEXT NOT NULL DEFAULT ''
        )
    SQL);

    // Stav vstupenek/rezervací (zapnuto/vypnuto)
    $db->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS reservation_status (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            is_active  INTEGER NOT NULL DEFAULT 1
        )
    SQL);

    // Minulé ročníky - CZ i EN v jedné tabulce (sloupce _cs / _en).
    // images = volitelné další obrázky (JSON pole cest), image_path = hlavní.
    $db->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS events (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            title_cs     TEXT NOT NULL DEFAULT '',
            title_en     TEXT NOT NULL DEFAULT '',
            description_cs TEXT NOT NULL DEFAULT '',
            description_en TEXT NOT NULL DEFAULT '',
            image_path   TEXT NOT NULL DEFAULT '',
            images       TEXT NOT NULL DEFAULT '',
            sort_order   INTEGER NOT NULL DEFAULT 0,
            created_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    SQL);

    // Program galavečera - řádky programu, CZ i EN.
    $db->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS programme (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            description_cs TEXT NOT NULL DEFAULT '',
            description_en TEXT NOT NULL DEFAULT '',
            sort_order     INTEGER NOT NULL DEFAULT 0,
            created_at     TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    SQL);

    // Ocenění hráči - rok + kategorie + nadpis ocenění + popis + fotka (CZ/EN).
    $db->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS awards (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            year         INTEGER NOT NULL DEFAULT 0,
            category     TEXT NOT NULL DEFAULT '',
            title_cs     TEXT NOT NULL DEFAULT '',
            title_en     TEXT NOT NULL DEFAULT '',
            description_cs TEXT NOT NULL DEFAULT '',
            description_en TEXT NOT NULL DEFAULT '',
            photo        TEXT NOT NULL DEFAULT '',
            sort_order   INTEGER NOT NULL DEFAULT 0,
            created_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    SQL);

    // automatické naplnění výchozími daty při prvním spuštění (prázdné tabulky)
    if (is_file(__DIR__ . '/seed_data.php')) {
        require_once __DIR__ . '/seed_data.php';
        try {
            scm_seed_data($db);
        } catch (\Throwable $e) {
            error_log('SAFE seed: ' . $e->getMessage());
        }
    }
}
