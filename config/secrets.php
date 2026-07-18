<?php
/**
 * SAFE – přihlášení k poštovnímu serveru. NEPATŘÍ DO GITU (je v .gitignore).
 *
 * Jak to je nastavené:
 *
 *   PŘIHLÁŠENÍ (schránka, která poštu doopravdy odesílá)
 *       support@minerskladno.cz  – jediný účet s heslem
 *
 *   ODESÍLATEL (co uvidí příjemce)
 *       safe@minerskladno.cz     – alias, vlastní schránku nemá,
 *                                  v Gmailu je povolený jako "Odeslat jako"
 *
 *   PŘÍJEMCE zpráv z kontaktního formuláře
 *       safe@minerskladno.cz     – nastaveno v config/config.php (contact_email)
 *
 * Heslo je "heslo aplikace" vygenerované v Google účtu support@minerskladno.cz
 * (Google účet → Zabezpečení → Hesla aplikací). Píše se bez mezer.
 */
return [

    // 'smtp' = přes poštovní server (Gmail)   |   'mail' = přes funkci mail() v PHP
    'mailer' => 'smtp',

    'smtp' => [
        // Pošta minerskladno.cz běží na Google Workspace.
        'host'       => 'smtp.gmail.com',
        'port'       => 587,
        'security'   => 'tls',                       // tls (587) | ssl (465)

        // Přihlášení = účet, který má schránku a heslo
        'username'   => 'support@minerskladno.cz',
        'password'   => 'warqhunvkovtrbgp',          // heslo aplikace (bez mezer)

        // Odesílatel = alias, kterého uvidí příjemce
        'from_email' => 'safe@minerskladno.cz',
        'from_name'  => 'SAFE Miners Kladno',

        // Obálkový odesílatel. Prázdné = použije se from_email (alias).
        // Kdyby Gmail alias v obálce odmítal, vyplň sem support@minerskladno.cz.
        'envelope_from' => '',
    ],
];
