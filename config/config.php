<?php
/**
 * Konfigurace SAFE.
 * Tento soubor JE součástí gitu (varianta A) - kolega po stažení nic nenastavuje.
 * Heslo lze kdykoliv změnit v administraci (sekce Změna hesla).
 *
 * POZOR: SendGrid API klíč sem NEPATŘÍ - ten je v config/secrets.php
 *        (viz config/secrets.example.php).
 */
return [

    /*
     * Hezké adresy (/awards) místo náhradních (index.php?route=awards).
     *
     * true  = potřebuje přepis adres na serveru:
     *           • Apache – zařídí .htaccess v kořeni projektu, nic dalšího netřeba
     *           • nginx  – vlož přiložený nginx.conf.vzor
     * false = funguje na JAKÉMKOLI serveru bez nastavení, jen adresy jsou ošklivější
     *
     * KDYŽ PO NASAZENÍ FUNGUJE JEN ÚVODNÍ STRÁNKA A OSTATNÍ HLÁSÍ 404,
     * přepni tohle na false. Web začne fungovat okamžitě.
     */
    "pretty_urls" => true,
    "admin_username" => "admin",
    "admin_password_hash" => '$2y$10$aOClh7.WVvdjwl5tXTFk4uzP6F508Gf8f6BdRkDiHJg6kN5opneNi',
    "db_path" => "",
    "app_secret" => '31598b242ead7c7d3e794d4e7bf205d6e825e3437e795dbd78573d5f7624469b',
    "debug" => false,

    // Tabulka rezervací (Google Sheets přes SheetDB)
    "sheetdb_api_url" => "https://sheetdb.io/api/v1/r5qf0v0bpe8gu",

    // Kam chodí zprávy z kontaktního formuláře
    "contact_email" => "safe@minerskladno.cz",

    // Veřejná adresa webu – používá se v odkazech uvnitř e-mailů
    // (zrušení rezervace, kontaktní formulář). Bez lomítka na konci!
    // Když testuješ jinde, přepiš to sem, ať odkazy v e-mailech vedou správně.
    "public_url" => "https://safe.minerskladno.cz",
];
