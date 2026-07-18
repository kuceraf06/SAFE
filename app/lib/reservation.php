<?php
/**
 * SAFE – zpracování odeslané rezervace vstupenek.
 *
 * Postup (stejný jako v původním webu):
 *   1) ověří, že daný e-mail už nemá platnou rezervaci,
 *   2) vygeneruje rezervační kód,
 *   3) zapíše řádek do tabulky rezervací (Google Sheets / SheetDB),
 *   4) pošle uživateli potvrzovací e-mail s kódem.
 *
 * Používá se z app/views/cs/reservation.php i .../en/reservation.php,
 * aby logika byla na jednom místě.
 */

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/view_helpers.php';
require_once __DIR__ . '/sendmail.php';
require_once __DIR__ . '/sheets.php';
require_once __DIR__ . '/antispam.php';

/** Veřejná adresa webu – odkazy v e-mailech musí být absolutní. Nastavuje se v config/config.php. */
function safe_public_url(): string
{
    static $url = null;
    if ($url === null) {
        $cfg = require __DIR__ . '/../../config/config.php';
        $url = rtrim((string)($cfg['public_url'] ?? 'https://safe.minerskladno.cz'), '/');
    }
    return $url;
}

/** Cena jedné vstupenky pro doprovod (Kč). */
const SAFE_ESCORT_PRICE = 250;

/**
 * Zpracuje POST z formuláře rezervace.
 *
 * @param string $lang 'cs' | 'en'
 * @return array{message:string, class:string}  prázdná hláška = nic se neodesílalo
 */
function safe_handle_reservation(string $lang = 'cs'): array
{
    $none = ['message' => '', 'class' => ''];

    if (empty($_POST['send'])) {
        return $none;
    }

    $t = safe_reservation_texts($lang);

    $email = trim((string)($_POST['email'] ?? ''));
    $name  = trim((string)($_POST['jméno'] ?? ''));
    $count = (int)($_POST['doprovod'] ?? 0);
    if ($count < 0) {
        $count = 0;
    }
    $escortPrice = $count * SAFE_ESCORT_PRICE;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['message' => $t['bad_email'], 'class' => 'alert-failed'];
    }
    if ($name === '') {
        return ['message' => $t['bad_name'], 'class' => 'alert-failed'];
    }

    // --- ochrana proti spamu (pro člověka neviditelná) ---
    $ochrana = safe_antispam_check(['jméno' => $name], $lang);
    if (!$ochrana['ok']) {
        if ($ochrana['silent']) {
            // Jistý robot: navenek úspěch, rezervaci ale nezaložíme.
            return ['message' => $t['ok'], 'class' => 'alert-success'];
        }
        return ['message' => $ochrana['message'], 'class' => 'alert-failed'];
    }

    // --- hráči/trenéři z dynamických polí (max 3) ---
    $players = [];
    for ($i = 1; $i <= 3; $i++) {
        $playerName = trim((string)($_POST['jméno-hráč' . $i] ?? ''));
        if ($playerName === '') {
            continue;
        }
        $players[$i] = [
            'name'     => $playerName,
            'category' => trim((string)($_POST['kategorie' . $i] ?? '')),
        ];
    }

    // --- 1) už tenhle e-mail rezervaci má? ---
    $rows = safe_sheet_find_email($email);
    if ($rows === null) {
        // tabulka je nedostupná – nic nezapisujeme, jinak by vznikly duplicity
        safe_http_log('Rezervace neuložena – tabulka nedostupná: ' . safe_sheet_last_error());
        return ['message' => $t['error'], 'class' => 'alert-failed'];
    }
    if (safe_sheet_email_has_reservation($rows, $email)) {
        return ['message' => $t['duplicate'], 'class' => 'alert-failed'];
    }

    // --- 2) rezervační kód ---
    $existingCodes = [];
    foreach ($rows as $row) {
        if (is_array($row) && !empty($row['kód rezervace'])) {
            $existingCodes[] = $row['kód rezervace'];
        }
    }
    $code = safe_sheet_generate_code($existingCodes);

    // --- 3) zápis do tabulky ---
    $data = [
        'email'     => $email,
        'jméno'     => $name,
        'doprovod'  => $count,
        'cena'      => $escortPrice,
    ];
    for ($i = 1; $i <= 3; $i++) {
        $data['jméno hráče/trenéra ' . $i] = $players[$i]['name'] ?? '';
        $data['kategorie ' . $i]           = $players[$i]['category'] ?? '';
    }
    $data['kód rezervace'] = $code;
    $data['rezervace']     = 'platí';

    if (!safe_sheet_add_reservation($data)) {
        safe_http_log('Rezervace neuložena – zápis do tabulky selhal: ' . safe_sheet_last_error());
        return ['message' => $t['error'], 'class' => 'alert-failed'];
    }

    // --- 4) potvrzovací e-mail uživateli ---
    $body = safe_reservation_email_body($lang, $code, $count, $escortPrice, $name, $email, $players);

    if (sendEmail($email, $t['subject'], $body)) {
        return ['message' => $t['ok'], 'class' => 'alert-success'];
    }

    // E-mail se nepovedl → rezervace by zůstala v tabulce, ale uživatel by
    // neznal svůj kód a nemohl ji zrušit. Vracíme tedy zápis zpět, ať tabulka
    // a odeslaná pošta sedí dohromady.
    safe_http_log('Potvrzovací e-mail selhal: ' . safe_mail_last_error() . ' – vracím zápis v tabulce zpět (kód ' . $code . ')');

    if (!safe_sheet_delete_reservation($code)) {
        safe_http_log('POZOR: řádek s kódem ' . $code . ' se nepodařilo z tabulky odstranit – zkontroluj ji ručně.');
    }

    return ['message' => $t['error'], 'class' => 'alert-failed'];
}

/** Texty hlášek podle jazyka. */
function safe_reservation_texts(string $lang): array
{
    // Uživateli ukazujeme jen lidský text. Technický důvod chyby jde do
    // storage/logs/http.log – návštěvníkovi je k ničemu a jen ho vyděsí.
    if ($lang === 'en') {
        return [
            'subject'   => 'Ticket reservation confirmation',
            'ok'        => 'Your reservation has been created.',
            'error'     => 'The reservation could not be created. Please try again later or contact us at safe@minerskladno.cz.',
            'duplicate' => 'This e-mail address has already been used for a reservation.',
            'bad_email' => 'Please enter a valid e-mail address.',
            'bad_name'  => 'Please enter your full name.',
        ];
    }

    return [
        'subject'   => 'Potvrzení rezervace lístků',
        'ok'        => 'Rezervace byla úspěšně vytvořena.',
        'error'     => 'Rezervaci se nepodařilo vytvořit. Zkuste to prosím později nebo nás kontaktujte na safe@minerskladno.cz.',
        'duplicate' => 'Tento e-mail už byl použit pro rezervaci.',
        'bad_email' => 'Zadejte prosím platnou e-mailovou adresu.',
        'bad_name'  => 'Zadejte prosím své celé jméno.',
    ];
}

/** Sestaví HTML tělo potvrzovacího e-mailu. */
function safe_reservation_email_body(
    string $lang,
    string $code,
    int $count,
    int $escortPrice,
    string $name,
    string $email,
    array $players
): string {
    $terminText = safe_format_termin(safe_termin(), $lang) ?: '';
    $logo       = 'cid:' . SAFE_MAIL_LOGO_CID;   // logo se vkládá přímo do zprávy
    $cancelUrl  = safe_public_url() . ($lang === 'en' ? '/en/reservation/cancel' : '/reservation/cancel');
    $contactUrl = safe_public_url() . ($lang === 'en' ? '/en/contact' : '/contact');
    $mapUrl     = 'https://www.google.com/maps/place/Kino+Sokol+Kladno/@50.1449,14.1027,17z';

    $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

    // seznam hráčů/trenérů
    $playersHtml = '';
    foreach ($players as $p) {
        $cat = $p['category'] !== ''
            ? $e($p['category'])
            : ($lang === 'en' ? '(not filled in)' : '(není vyplněno)');
        $label = $lang === 'en' ? 'Player/coach name' : 'Jméno hráče/trenéra';
        $catLb = $lang === 'en' ? 'Category' : 'Kategorie';
        $playersHtml .= "<li><strong>$label:</strong> " . $e($p['name']) . ", <strong>$catLb:</strong> $cat</li>";
    }

    $style = "<style>
        body { font-family: 'Poppins', Arial, sans-serif; color: #333; }
        p, ul, img { margin-left: 15px; margin-right: 15px; }
        .first-content { padding-top: 15px; }
        img { width: 100%; max-width: 300px; }
    </style>";

    if ($lang === 'en') {
        $html = "<html><head>$style</head><body>
            <p class='first-content'>Thank you for booking tickets to this year's SAFE event.</p>
            <p>Information:</p>
            <ul>
                <li><strong>Your reservation code is:</strong> " . $e($code) . "</li>
                <li><strong>Number of accompaniment:</strong> " . $e($count) . "</li>
                <li><strong>Price for tickets:</strong> " . $e($escortPrice) . " Kč</li>
                <li><strong>Name:</strong> " . $e($name) . "</li>
                <li><strong>Email:</strong> " . $e($email) . "</li>
            </ul>";
        if ($playersHtml !== '') {
            $html .= "<p>Information about players/coaches:</p><ul>$playersHtml</ul>";
        }
        $when = $terminText !== '' ? $e($terminText) : 'the announced date';
        $html .= "
            <p>The event takes place on $when in the Sokol cinema at
               <a target='_blank' href='$mapUrl'>T. G. Masaryka 2320, 272 01 Kladno 1</a>.</p>
            <p>You will be informed about ticket pick-up and payment dates by email.</p>
            <p>We look forward to seeing you!</p>
            <p>If you want to cancel your reservation, click <a target='_blank' href='$cancelUrl'>here</a>.</p>
            <p>Note: this message is automatic, please do not reply to this email.<br>
               If you have any questions, contact us through the form
               <a href='$contactUrl'>here</a> or at
               <a href='mailto:safe@minerskladno.cz'>safe@minerskladno.cz</a>.</p>
            <img src='$logo' alt='SAFE logo'>
        </body></html>";

        return $html;
    }

    $html = "<html><head>$style</head><body>
        <p class='first-content'>Děkujeme, že jste si rezervovali vstupenky na letošní akci SAFE.</p>
        <p>Informace:</p>
        <ul>
            <li><strong>Váš rezervační kód:</strong> " . $e($code) . "</li>
            <li><strong>Počet vstupenek pro doprovod:</strong> " . $e($count) . "</li>
            <li><strong>Cena:</strong> " . $e($escortPrice) . " Kč</li>
            <li><strong>Jméno:</strong> " . $e($name) . "</li>
            <li><strong>Email:</strong> " . $e($email) . "</li>
        </ul>";
    if ($playersHtml !== '') {
        $html .= "<p>Informace o hráčích/trenérech:</p><ul>$playersHtml</ul>";
    }
    $when = $terminText !== '' ? $e($terminText) : 'v ohlášeném termínu';
    $html .= "
        <p>Akce se koná $when v kině Sokol na adrese
           <a target='_blank' href='$mapUrl'>T. G. Masaryka 2320, 272 01 Kladno 1</a>.</p>
        <p>O termínech vyzvednutí a úhrady vstupenek budete informováni e-mailem.</p>
        <p>Těšíme se na Vás!</p>
        <p>Pokud chcete rezervaci zrušit, klikněte <a target='_blank' href='$cancelUrl'>zde</a>.</p>
        <p>Pozn.: tato zpráva je automatická, prosím neodpovídejte na tento e-mail.<br>
           V případě jakýchkoliv otázek nás můžete kontaktovat přes formulář
           <a href='$contactUrl'>zde</a> či na e-mailu
           <a href='mailto:safe@minerskladno.cz'>safe@minerskladno.cz</a>.</p>
        <img src='$logo' alt='SAFE logo'>
    </body></html>";

    return $html;
}

/**
 * Zpracuje POST z formuláře pro zrušení rezervace.
 * Ověří e-mail + rezervační kód, označí rezervaci v tabulce jako zrušenou
 * a pošle uživateli potvrzení o zrušení.
 *
 * @return array{done:bool, message:string, class:string}
 */
function safe_handle_cancel(string $lang = 'cs'): array
{
    $none = ['done' => false, 'message' => '', 'class' => ''];

    if (empty($_POST['send'])) {
        return $none;
    }

    $t = safe_cancel_texts($lang);

    $email = trim((string)($_POST['email'] ?? ''));
    $code  = trim((string)($_POST['code'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $code === '') {
        return ['done' => false, 'message' => $t['bad_input'], 'class' => 'alert-failed'];
    }

    // najdeme rezervaci podle e-mailu
    $rows = safe_sheet_find_email($email);
    if ($rows === null) {
        safe_http_log('Zrušení rezervace – tabulka nedostupná: ' . safe_sheet_last_error());
        return ['done' => false, 'message' => $t['failed'], 'class' => 'alert-failed'];
    }
    if (empty($rows)) {
        return ['done' => false, 'message' => $t['not_found'], 'class' => 'alert-failed'];
    }

    // hledáme řádek s odpovídajícím kódem, který ještě platí
    $match = null;
    foreach ($rows as $row) {
        if (is_array($row) && ($row['kód rezervace'] ?? '') === $code) {
            $match = $row;
            break;
        }
    }
    if ($match === null) {
        return ['done' => false, 'message' => $t['bad_code'], 'class' => 'alert-failed'];
    }
    if (($match['rezervace'] ?? '') !== 'platí') {
        return ['done' => false, 'message' => $t['already'], 'class' => 'alert-failed'];
    }

    // zrušíme v tabulce
    if (!safe_sheet_cancel_reservation($email)) {
        safe_http_log('Zrušení rezervace selhalo: ' . safe_sheet_last_error());
        return ['done' => false, 'message' => $t['failed'], 'class' => 'alert-failed'];
    }

    // potvrzovací e-mail
    $logo    = 'cid:' . SAFE_MAIL_LOGO_CID;   // logo se vkládá přímo do zprávy
    $e       = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    $novaUrl = safe_public_url() . ($lang === 'en' ? '/en/reservation' : '/reservation');

    $body = "<html><head><style>
            body { font-family: 'Poppins', Arial, sans-serif; color: #333; }
            p, img { margin-left: 15px; margin-right: 15px; }
            img { width: 100%; max-width: 300px; }
            a { color: #C29B31; }
        </style></head><body>
        <h1>{$t['mail_title']}</h1>
        <p>" . sprintf($t['mail_body'], $e($code), $e($email)) . "</p>
        <p>" . sprintf($t['mail_again'], $novaUrl) . "</p>
        <p>{$t['mail_note']}</p>
        <img src='$logo' alt='SAFE logo'>
    </body></html>";

    sendEmail($email, $t['mail_subject'], $body);

    return ['done' => true, 'message' => $t['ok'], 'class' => 'alert-success'];
}

/** Texty pro zrušení rezervace. */
function safe_cancel_texts(string $lang): array
{
    if ($lang === 'en') {
        return [
            'bad_input'    => 'Please fill in a valid e-mail address and your reservation code.',
            'not_found'    => 'No reservation was found for this e-mail address.',
            'bad_code'     => 'A record with this code does not exist.',
            'already'      => 'This reservation has already been cancelled.',
            'failed'       => 'The reservation could not be cancelled. Please try again later or contact us at safe@minerskladno.cz.',
            'ok'           => 'Your reservation has been cancelled.',
            'mail_subject' => 'Reservation cancellation',
            'mail_title'   => 'Your ticket reservation has been cancelled',
            'mail_body'    => 'Your reservation with the code <strong>"%s"</strong> and e-mail <strong>"%s"</strong> has just been cancelled.',
            'mail_again'   => 'Changed your mind? You can <a href="%s">book tickets again here</a>.',
            'mail_note'    => 'If you did not request the cancellation, please contact us at safe@minerskladno.cz.',
        ];
    }

    return [
        'bad_input'    => 'Vyplňte prosím platnou e-mailovou adresu a rezervační kód.',
        'not_found'    => 'Rezervace s tímto e-mailem nebyla nalezena.',
        'bad_code'     => 'Záznam s tímto kódem neexistuje.',
        'already'      => 'Tato rezervace už byla zrušena.',
        'failed'       => 'Rezervaci se nepodařilo zrušit. Zkuste to prosím později nebo nás kontaktujte na safe@minerskladno.cz.',
        'ok'           => 'Vaše rezervace byla úspěšně zrušena.',
        'mail_subject' => 'Zrušení rezervace',
        'mail_title'   => 'Rezervace vstupenek byla zrušena',
        'mail_body'    => 'Vaše rezervace s kódem <strong>„%s“</strong> a e-mailem <strong>„%s“</strong> byla právě zrušena.',
        'mail_again'   => 'Rozmysleli jste si to? Novou rezervaci si můžete vytvořit <a href="%s">zde</a>.',
        'mail_note'    => 'Pokud jste o zrušení nežádali, kontaktujte nás na safe@minerskladno.cz.',
    ];
}
