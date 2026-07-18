<?php
/**
 * VZOR přihlášení k poštovnímu serveru.
 * Zkopíruj jako config/secrets.php a vyplň. (secrets.php je v .gitignore.)
 *
 * Pozor na rozdíl:
 *   username   = schránka, která poštu odesílá (má heslo)
 *   from_email = adresa, kterou uvidí příjemce (může to být alias bez schránky)
 */
return [

    'mailer' => 'smtp',        // 'smtp' (doporučeno) | 'mail' (funkce mail() v PHP)

    'smtp' => [
        'host'          => 'smtp.gmail.com',
        'port'          => 587,
        'security'      => 'tls',

        'username'      => 'support@minerskladno.cz',   // účet s heslem
        'password'      => '',                          // heslo aplikace z Google (bez mezer)

        'from_email'    => 'safe@minerskladno.cz',      // alias, kterého uvidí příjemce
        'from_name'     => 'SAFE Miners Kladno',

        'envelope_from' => '',                          // prázdné = použije se from_email
    ],
];

/* ─────────────────────────────────────────────────────────────────────
   Google Workspace / Gmail (tady běží minerskladno.cz)
       host smtp.gmail.com, port 587, security 'tls'
       heslo = HESLO APLIKACE (Google účet → Zabezpečení → Hesla aplikací),
       běžné heslo Google do SMTP nepustí.
       Alias v from_email musí být v Gmailu povolený jako "Odeslat jako".

   Seznam.cz    host smtp.seznam.cz, port 465, security 'ssl'
   SendGrid     host smtp.sendgrid.net, port 587, username 'apikey', password = API klíč
   ───────────────────────────────────────────────────────────────────── */
