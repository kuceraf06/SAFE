<?php
/**
 * SAFE – odesílání e-mailů přes PHPMailer.
 *
 * Knihovna PHPMailer je přibalená v app/lib/PHPMailer/ (bez Composeru).
 *
 * VŠECHNY e-maily chodí Z adresy safe@minerskladno.cz
 * a zprávy z kontaktního formuláře chodí NA safe@minerskladno.cz.
 *
 * Nastavení je v config/secrets.php:
 *   'mailer' => 'smtp'   ... přes poštovní server (doporučeno)
 *   'mailer' => 'mail'   ... přes funkci mail() v PHP (jen když ji server umí)
 *
 * Certifikáty se berou z přibaleného config/cacert.pem, takže šifrované
 * spojení funguje i tam, kde v php.ini nic nastavené není (XAMPP, NAS).
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/http.php';                  // safe_ca_bundle(), safe_http_log()
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

/** Adresa, ze které web posílá a na kterou chodí zprávy z formuláře. */
const SAFE_MAIL_ADDRESS = 'safe@minerskladno.cz';
const SAFE_MAIL_NAME    = 'SAFE Miners Kladno';

/** Značka, pod kterou se do zprávy vkládá logo (v HTML: <img src="cid:safelogo">). */
const SAFE_MAIL_LOGO_CID = 'safelogo';

/** Poslední chyba odesílání (pro zobrazení / diagnostiku). */
function safe_mail_last_error(?string $set = null): string
{
    static $error = '';
    if ($set !== null) {
        $error = $set;
    }
    return $error;
}

/** Načte config/secrets.php. */
function safe_mail_secrets(): array
{
    static $secrets = null;
    if ($secrets === null) {
        $file = __DIR__ . '/../../config/secrets.php';
        $secrets = is_readable($file) ? (array) require $file : [];
    }
    return $secrets;
}

/** Způsob odesílání: 'smtp' | 'mail'. */
function safe_mailer(): string
{
    $s = safe_mail_secrets();
    $m = strtolower(trim((string)($s['mailer'] ?? 'smtp')));
    return $m === 'mail' ? 'mail' : 'smtp';
}

/** Nastavení SMTP, nebo null když není vyplněná adresa serveru. */
function safe_smtp_config(): ?array
{
    $s   = safe_mail_secrets();
    $cfg = $s['smtp'] ?? null;
    if (!is_array($cfg) || trim((string)($cfg['host'] ?? '')) === '') {
        return null;
    }
    return $cfg;
}

/**
 * Připraví PHPMailer podle nastavení. Vrací null a nastaví chybu,
 * když nastavení chybí.
 */
function safe_mail_factory(?array $transport = null): ?PHPMailer
{
    $mail = new PHPMailer(true);   // true = vyhazovat výjimky
    $mail->CharSet  = PHPMailer::CHARSET_UTF8;
    $mail->Encoding = PHPMailer::ENCODING_BASE64;
    $mail->Timeout  = 12;
    $mail->isHTML(true);

    if (safe_mailer() === 'mail') {
        $mail->isMail();
        return $mail;
    }

    $cfg = safe_smtp_config();
    if ($cfg === null) {
        safe_mail_last_error('Není vyplněné nastavení SMTP v config/secrets.php (host, username, password).');
        safe_http_log('E-mail neodeslán – prázdné nastavení SMTP.');
        return null;
    }

    // volitelné přepsání přenosu (náhradní port/šifrování)
    if ($transport !== null) {
        $cfg = array_merge($cfg, $transport);
    }

    $mail->isSMTP();
    $mail->Host     = trim((string) $cfg['host']);
    $mail->Port     = (int) ($cfg['port'] ?? 587);
    $mail->SMTPAuth = trim((string)($cfg['username'] ?? '')) !== '';
    $mail->Username = (string) ($cfg['username'] ?? '');
    $mail->Password = (string) ($cfg['password'] ?? '');

    $security = strtolower((string) ($cfg['security'] ?? 'tls'));
    if ($security === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // přímé SSL, port 465
    } elseif ($security === 'none') {
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // STARTTLS, port 587
    }

    // Certifikáty z přibaleného balíku – kvůli XAMPPu / NASu bez nastavení php.ini
    $ca = safe_ca_bundle();
    if ($ca !== null) {
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
                'cafile'            => $ca,
            ],
        ];
    }

    return $mail;
}

/**
 * Vytvoří textovou (prostou) verzi e-mailu z HTML.
 *
 * PROČ TO NENÍ JEN strip_tags():
 * strip_tags() odstraní jen samotné značky, ale text MEZI nimi nechá – včetně
 * obsahu <style> a <script>. Do textové verze se tak dostala pravidla CSS
 * ("body { font-family: ... }") a poštovní klienti (Gmail, Outlook) je pak
 * ukazovali v NÁHLEDU zprávy místo skutečného obsahu.
 *
 * Proto nejdřív vyhodíme celé bloky <style>, <script> a <head> i s obsahem,
 * teprve pak odstraníme značky.
 */
function safe_mail_plain_text(string $html): string
{
    // 1) pryč celé bloky i s obsahem
    $text = preg_replace('#<(style|script|head)\b[^>]*>.*?</\1>#is', ' ', $html);
    $text = (string) ($text ?? $html);

    // 2) zalomení řádků tam, kde v HTML končí odstavec / položka seznamu
    $text = preg_replace('#<br\s*/?>#i', "\n", $text);
    $text = preg_replace('#</(p|div|li|tr|h[1-6])>#i', "\n", $text);
    $text = preg_replace('#<li\b[^>]*>#i', '- ', $text);

    // 3) teprve teď pryč zbylé značky
    $text = strip_tags((string) $text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    // 4) úklid bílých znaků (ať nezůstanou dlouhé mezery po odstraněných blocích)
    $text = preg_replace('/[ \t]+/u', ' ', $text);
    $text = preg_replace('/\n\s*\n\s*\n+/u', "\n\n", $text);
    $text = preg_replace('/^[ \t]+|[ \t]+$/mu', '', $text);

    return trim((string) $text);
}

/**
 * Vloží do HTML e-mailu skrytý „náhledový text“ (preheader).
 *
 * PROČ: poštovní klienti (Gmail, Outlook, Seznam…) ukazují v seznamu zpráv
 * vedle předmětu ještě útržek obsahu. Berou na něj první text, na který ve
 * zprávě narazí – a když se jim do cesty připlete něco nečitelného, ukážou to.
 * Preheader je krátký skrytý odstavec úplně na začátku těla zprávy, kterým
 * tenhle útržek určíme sami. V otevřené zprávě není vidět.
 *
 * Za text se přidávají neviditelné znaky, aby klient do náhledu nepřilepil
 * ještě kus dalšího obsahu.
 */
function safe_mail_add_preheader(string $html, string $plain): string
{
    // Když už nějaký preheader ve zprávě je, nic nepřidáváme.
    if (stripos($html, 'id="preheader"') !== false) {
        return $html;
    }

    $ukazka = trim(preg_replace('/\s+/u', ' ', $plain) ?? '');
    if ($ukazka === '') {
        return $html;
    }

    $ukazka = mb_substr($ukazka, 0, 140);
    $ukazka = htmlspecialchars($ukazka, ENT_QUOTES, 'UTF-8');

    // neviditelná výplň – zabrání přilepení dalšího textu do náhledu
    $vypln = str_repeat('&#847;&zwnj;&nbsp;&#847;&zwnj;', 30);

    $preheader = '<div id="preheader" style="display:none;max-height:0;overflow:hidden;'
        . 'mso-hide:all;font-size:1px;line-height:1px;color:transparent;opacity:0;">'
        . $ukazka . $vypln . '</div>';

    // vložíme hned za <body ...>
    $novy = preg_replace('#(<body\b[^>]*>)#i', '$1' . $preheader, $html, 1);

    // kdyby zpráva <body> neměla, dáme preheader na začátek
    return $novy !== null && $novy !== $html ? $novy : $preheader . $html;
}

/**
 * Odešle HTML e-mail. Odesílatel je vždy safe@minerskladno.cz.
 *
 * @param string      $toEmail  příjemce
 * @param string      $subject  předmět
 * @param string      $content  HTML tělo
 * @param string|null $replyTo  na koho má jít případná odpověď (nepovinné)
 */
function sendEmail($toEmail, $subject, $content, $replyTo = null): bool
{
    safe_mail_last_error('');

    $toEmail = trim((string) $toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        safe_mail_last_error('Neplatná e-mailová adresa příjemce.');
        safe_http_log('E-mail neodeslán – neplatný příjemce: ' . $toEmail);
        return false;
    }

    $cfg       = safe_smtp_config() ?? [];
    $fromEmail = trim((string)($cfg['from_email'] ?? SAFE_MAIL_ADDRESS)) ?: SAFE_MAIL_ADDRESS;
    $fromName  = (string)($cfg['from_name'] ?? SAFE_MAIL_NAME);
    $envelope  = trim((string)($cfg['envelope_from'] ?? ''));
    $login     = trim((string)($cfg['username'] ?? ''));

    // Zkusíme nastavený přenos, a kdyby vázlo spojení/šifrování, ještě náhradní
    // (587 STARTTLS ↔ 465 přímé SSL). Některé sítě a antiviry jednu z cest blokují.
    foreach (safe_mail_transports($cfg) as $transport) {

        // 1) obálka podle nastavení (prázdné = alias safe@)
        if (safe_mail_try_send($toEmail, (string) $subject, (string) $content, $fromEmail, $fromName, $envelope, $replyTo, $transport)) {
            safe_mail_log_fallback($transport, $cfg, '');
            return true;
        }
        $chyba = safe_mail_last_error();

        // 2) pojistka na odesílatele: server nemusí pustit alias v obálce zprávy
        if ($envelope === '' && $login !== '' && $login !== $fromEmail
            && filter_var($login, FILTER_VALIDATE_EMAIL)
            && safe_mail_error_is_sender($chyba)) {

            if (safe_mail_try_send($toEmail, (string) $subject, (string) $content, $fromEmail, $fromName, $login, $replyTo, $transport)) {
                safe_http_log(
                    "E-mail prošel s obálkou $login místo $fromEmail. "
                    . "Doporučení: doplň do config/secrets.php 'envelope_from' => '$login'."
                );
                safe_mail_log_fallback($transport, $cfg, '');
                safe_mail_last_error('');
                return true;
            }
            $chyba = safe_mail_last_error();
        }

        // Když problém není ve spojení ani v šifrování (např. špatné heslo),
        // jiný port nepomůže – nemá smysl čekat.
        if (!safe_mail_error_is_connection($chyba)) {
            return false;
        }
    }

    return false;
}

/** Nastavený přenos + náhradní varianta (587 STARTTLS ↔ 465 přímé SSL). */
function safe_mail_transports(array $cfg): array
{
    $transports = [null];   // null = použít nastavení tak, jak je

    if (safe_mailer() !== 'smtp') {
        return $transports;
    }

    $port     = (int)($cfg['port'] ?? 587);
    $security = strtolower((string)($cfg['security'] ?? 'tls'));

    if ($security === 'tls') {
        $transports[] = ['port' => 465, 'security' => 'ssl'];
    } elseif ($security === 'ssl') {
        $transports[] = ['port' => 587, 'security' => 'tls'];
    }

    return $transports;
}

/** Zapíše do logu, když se muselo sáhnout po náhradním portu. */
function safe_mail_log_fallback(?array $transport, array $cfg, string $note): void
{
    if ($transport === null) {
        return;
    }
    safe_http_log(
        'E-mail prošel až přes náhradní port ' . $transport['port'] . ' (' . $transport['security'] . '). '
        . 'Doporučení: uprav config/secrets.php na tento port, ať se nezkouší zbytečně ten původní. ' . $note
    );
}

/** Ukazuje chyba na to, že server nepřijal adresu odesílatele? */
function safe_mail_error_is_sender(string $error): bool
{
    foreach (['MAIL FROM', 'Sender address', 'not owned', 'From address failed', '5.7.1', '553'] as $vzor) {
        if (stripos($error, $vzor) !== false) {
            return true;
        }
    }
    return false;
}

/** Ukazuje chyba na problém se spojením nebo šifrováním? */
function safe_mail_error_is_connection(string $error): bool
{
    foreach (['Connection failed', 'connect', 'STARTTLS', 'crypto', 'certificate', 'timed out', 'refused'] as $vzor) {
        if (stripos($error, $vzor) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Jeden pokus o odeslání. Vrací true/false, chybu ukládá do safe_mail_last_error().
 */
function safe_mail_try_send(
    string $toEmail,
    string $subject,
    string $content,
    string $fromEmail,
    string $fromName,
    string $envelope,
    $replyTo,
    ?array $transport = null
): bool {
    $mail = safe_mail_factory($transport);
    if ($mail === null) {
        return false;   // chyba už je nastavená
    }

    try {
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail);

        if ($envelope !== '' && filter_var($envelope, FILTER_VALIDATE_EMAIL)) {
            $mail->Sender = $envelope;
        }

        if (is_string($replyTo) && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        }

        $mail->Subject = (string) $subject;
        $plain         = safe_mail_plain_text((string) $content);
        $mail->Body    = safe_mail_add_preheader((string) $content, $plain);
        $mail->AltBody = $plain;

        // Logo vkládáme přímo do zprávy (jako přílohu), ne odkazem na web.
        // Odkaz by se v poště nezobrazil, dokud web neběží na veřejné adrese –
        // a poštovní klienti navíc obrázky z internetu často blokují.
        //
        // POZOR na velikost: Gmail zprávy nad ~102 kB ořízne a zbytek schová
        // za odkaz "Zobrazit celou zprávu". Proto se přikládá zmenšená verze
        // loga (300×300, ~13 kB) místo té pro web (360×360, ~70 kB) – s tou
        // se česká verze e-mailu k hranici nebezpečně blížila.
        if (str_contains((string) $content, 'cid:' . SAFE_MAIL_LOGO_CID)) {
            $logo = realpath(__DIR__ . '/../../public/images/common/SAFE-logo-email.png');
            if ($logo === false || !is_readable($logo)) {
                $logo = realpath(__DIR__ . '/../../public/images/common/SAFE-logo.png');
            }
            if ($logo !== false && is_readable($logo)) {
                $mail->addEmbeddedImage($logo, SAFE_MAIL_LOGO_CID, 'SAFE-logo.png', PHPMailer::ENCODING_BASE64, 'image/png');
            }
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        $detail = $mail->ErrorInfo !== '' ? $mail->ErrorInfo : $e->getMessage();
        safe_mail_last_error('E-mail se nepodařilo odeslat (' . $detail . ').');
        safe_http_log("PHPMailer selhal pro $toEmail: $detail");
        return false;
    } catch (\Throwable $e) {
        safe_mail_last_error('E-mail se nepodařilo odeslat (' . $e->getMessage() . ').');
        safe_http_log("PHPMailer selhal pro $toEmail: " . $e->getMessage());
        return false;
    }
}
