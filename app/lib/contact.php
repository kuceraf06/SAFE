<?php
/**
 * SAFE – zpracování kontaktního formuláře.
 *
 * Stejně jako v původním webu se posílají DVA e-maily:
 *   1) na safe@minerskladno.cz  – zpráva od uživatele (dotaz/podnět),
 *   2) uživateli na jeho adresu – kopie toho, co odeslal.
 *
 * Hlavní je e-mail č. 1: když ten projde, formulář hlásí úspěch.
 * Kopie uživateli je doplněk (když se nepovede, zmíníme to, ale zprávu
 * už máme).
 */

require_once __DIR__ . '/view_helpers.php';
require_once __DIR__ . '/sendmail.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/antispam.php';

/**
 * Zpracuje POST z kontaktního formuláře.
 *
 * @param string $lang 'cs' | 'en'
 * @return array{message:string, class:string}
 */
function safe_handle_contact(string $lang = 'cs'): array
{
    if (empty($_POST['send'])) {
        return ['message' => '', 'class' => ''];
    }

    $cfg     = require __DIR__ . '/../../config/config.php';
    $toEmail = $cfg['contact_email'] ?? 'safe@minerskladno.cz';

    $name    = trim((string)($_POST['name'] ?? ''));
    $email   = trim((string)($_POST['email'] ?? ''));
    $tel     = trim((string)($_POST['tel'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    $t = safe_contact_texts($lang);

    // --- ochrana proti spamu (pro člověka neviditelná) ---
    $ochrana = safe_antispam_check(['name' => $name, 'message' => $message], $lang);
    if (!$ochrana['ok']) {
        if ($ochrana['silent']) {
            // Jistý robot: navenek se tváříme, že se odeslalo, ale zprávu zahodíme.
            return ['message' => $t['ok'], 'class' => 'alert-success'];
        }
        return ['message' => $ochrana['message'], 'class' => 'alert-failed'];
    }

    // --- kontrola vyplnění (stejná pravidla jako v původním webu) ---
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['message' => sprintf($t['bad_email'], $email), 'class' => 'alert-email'];
    }
    if (!preg_match('/^[0-9 +]{9,}$/', $tel)) {
        return ['message' => sprintf($t['bad_phone'], $tel), 'class' => 'alert-phone'];
    }
    if ($name === '' || $message === '') {
        return ['message' => $t['bad_fields'], 'class' => 'alert-failed'];
    }

    $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

    $style = "<style>
        body { font-family: 'Poppins', Arial, sans-serif; color: #333; }
        p, ul, img { margin-left: 15px; margin-right: 15px; }
        .first-content { padding-top: 15px; }
        img { width: 100%; max-width: 300px; }
    </style>";

    $logo = 'cid:' . SAFE_MAIL_LOGO_CID;   // logo se vkládá přímo do zprávy

    // ---------------------------------------------------------------------
    //  1) zpráva pro klub – VŽDY ČESKY, i když přišla z anglické verze webu.
    //     Schránku safe@ spravují Češi, angličtina by jim jen překážela.
    //     (Naopak všechno, co web posílá NÁVŠTĚVNÍKOVI, jde v jazyce stránky,
    //      ze které formulář odeslal.)
    // ---------------------------------------------------------------------
    $tk = safe_contact_texts('cs');

    $klubRadky = "<ul>
        <li><strong>{$tk['l_name']}:</strong> " . $e($name) . "</li>
        <li><strong>{$tk['l_email']}:</strong> " . $e($email) . "</li>
        <li><strong>{$tk['l_phone']}:</strong> " . $e($tel) . "</li>
        <li><strong>{$tk['l_subject']}:</strong> " . $e(safe_contact_subject_cs($subject)) . "</li>
        <li><strong>{$tk['l_message']}:</strong> " . nl2br($e($message)) . "</li>
        <li><strong>Odesláno z:</strong> " . ($lang === 'en' ? 'anglické verze webu (odpovězte anglicky)' : 'české verze webu') . "</li>
    </ul>";

    $adminBody = "<html><head>$style</head><body>
        <p class='first-content'>" . sprintf($tk['admin_intro'], $e($name)) . "</p>
        <p>{$tk['admin_info']}</p>
        $klubRadky
        <img src='$logo' alt='SAFE logo'>
    </body></html>";

    $adminSubject = safe_contact_subject_cs($subject) ?: $tk['fallback_subject'];

    // Reply-To = adresa tazatele, aby klub mohl rovnou odpovědět
    if (!sendEmail($toEmail, $adminSubject, $adminBody, $email)) {
        safe_http_log('Zpráva z kontaktu pro klub NEODESLÁNA: ' . safe_mail_last_error());
        return ['message' => $t['failed'], 'class' => 'alert-failed'];
    }

    // ---------------------------------------------------------------------
    //  2) kopie pro tazatele – v jazyce stránky, ze které formulář odeslal
    // ---------------------------------------------------------------------
    $rows = "<ul>
        <li><strong>{$t['l_name']}:</strong> " . $e($name) . "</li>
        <li><strong>{$t['l_email']}:</strong> " . $e($email) . "</li>
        <li><strong>{$t['l_phone']}:</strong> " . $e($tel) . "</li>
        <li><strong>{$t['l_subject']}:</strong> " . $e($subject) . "</li>
        <li><strong>{$t['l_message']}:</strong> " . nl2br($e($message)) . "</li>
    </ul>";

    $userBody = "<html><head>$style</head><body>
        <p class='first-content'>{$t['user_intro']}</p>
        <p>{$t['user_your_message']}</p>
        $rows
        <p>{$t['user_soon']}</p>
        <p>{$t['user_auto']}</p>
        <img src='$logo' alt='SAFE logo'>
    </body></html>";

    if (!sendEmail($email, $t['copy_subject'], $userBody)) {
        // Občas se nepovede navázat druhé spojení hned po prvním – zkusíme to ještě jednou.
        safe_http_log('Kopie zprávy pro ' . $email . ' napoprvé selhala (' . safe_mail_last_error() . ') – zkouším znovu.');
        usleep(500000);

        if (!sendEmail($email, $t['copy_subject'], $userBody)) {
            safe_http_log('Kopie zprávy pro ' . $email . ' se NEODESLALA: ' . safe_mail_last_error());
        }
    }

    // Klub zprávu má – to je to podstatné, proto hlásíme úspěch.
    // Kdyby nedorazila kopie, je to v logu; opakovaným odesláním formuláře
    // by se klubu jen zdvojila stejná zpráva.
    return ['message' => $t['ok'], 'class' => 'alert-success'];
}

/** Texty podle jazyka. */
function safe_contact_texts(string $lang): array
{
    if ($lang === 'en') {
        return [
            'bad_email'         => '"%s" is not a valid e-mail address.',
            'bad_phone'         => '"%s" is not a valid phone number.',
            'bad_fields'        => 'Please fill in your name and message.',
            'failed'            => 'The message could not be sent. Please contact us directly at safe@minerskladno.cz.',
            'ok'                => 'Your message has been sent. We have also sent you a copy – if it does not arrive within a few minutes, please check your spam folder.',
            'copy_subject'      => 'Copy of your message',
            'fallback_subject'  => 'SAFE - new message from website',
            'admin_intro'       => 'A new message from %s has arrived.',
            'admin_info'        => 'Details:',
            'user_intro'        => 'Thank you for contacting us!',
            'user_your_message' => 'Your message:',
            'user_soon'         => 'We will contact you as soon as possible.',
            'user_auto'         => 'Note: this message is automatic, please do not reply to this email.',
            'l_name'            => 'Name',
            'l_email'           => 'Email',
            'l_phone'           => 'Phone',
            'l_subject'         => 'Subject',
            'l_message'         => 'Message',
        ];
    }

    return [
        'bad_email'         => '„%s“ není platná e-mailová adresa.',
        'bad_phone'         => '„%s“ není platné telefonní číslo.',
        'bad_fields'        => 'Vyplňte prosím jméno a zprávu.',
        'failed'            => 'Zprávu se nepodařilo odeslat. Kontaktujte nás prosím přímo na safe@minerskladno.cz.',
        'ok'                => 'Zpráva byla úspěšně odeslána. Kopii jsme vám poslali na e-mail – pokud do pár minut nedorazí, podívejte se prosím i do složky Spam nebo Hromadné.',
        'copy_subject'      => 'Kopie vaší zprávy',
        'fallback_subject'  => 'SAFE - nová zpráva z webu',
        'admin_intro'       => 'Přišla nová zpráva od uživatele %s.',
        'admin_info'        => 'Informace:',
        'user_intro'        => 'Děkujeme, že jste nás kontaktovali!',
        'user_your_message' => 'Vaše zpráva:',
        'user_soon'         => 'Kontaktujeme vás co nejdříve.',
        'user_auto'         => 'Pozn.: tato zpráva je automatická, prosím neodpovídejte na tento e-mail.',
        'l_name'            => 'Jméno',
        'l_email'           => 'Email',
        'l_phone'           => 'Tel.',
        'l_subject'         => 'Předmět',
        'l_message'         => 'Zpráva',
    ];
}

/**
 * Předmět z formuláře přeloží do češtiny.
 *
 * Nabídka v anglické verzi má hodnoty Question / Feedback / Complaint,
 * ale zpráva pro klub má být celá česky – včetně předmětu.
 * Cokoliv jiného necháváme, jak to je.
 */
function safe_contact_subject_cs(string $subject): string
{
    $preklad = [
        'question'  => 'Otázka',
        'feedback'  => 'Zpětná vazba',
        'complaint' => 'Stížnost',
    ];

    $klic = strtolower(trim($subject));

    return $preklad[$klic] ?? $subject;
}
