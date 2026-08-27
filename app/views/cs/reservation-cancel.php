<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';
require_once __DIR__ . '/../../lib/reservation.php';

$pageTitle = 'SAFE | Zrušení Rezervace';
$bodyClass = 'reservationpage-body';
$pageCss   = 'reservation';

$isReservationActive = safe_reservation_active();

// zpracování formuláře (ověření kódu, zrušení v tabulce, potvrzovací e-mail)
$cancel        = safe_handle_cancel('cs');
$cancelDone    = $cancel['done'];
$cancelMessage = $cancel['message'];
$cancelError   = ($cancel['class'] === 'alert-failed');
?>
<main class="cancel-container page-width">
    <div class="heading">
        <h1>ZRUŠENÍ REZERVACE</h1>
    </div>

    <?php if ($isReservationActive): ?>
        <div class="remove-box">
            <div class="right cz-right">
                <?php if ($cancelDone): ?>
                    <div class="cancel-confirm">
                        <h2>Rezervace vstupenek byla zrušena</h2>
                        <p>Na váš e-mail jsme zaslali potvrzení o zrušení. Pokud do pár minut nedorazí, podívejte se prosím i do složky Spam nebo Hromadné. Děkujeme.</p>
                        <p>Chcete si rezervovat znovu? <a href="<?= url('reservation') ?>">Vytvořit novou rezervaci</a></p>
                    </div>
                <?php else: ?>
                    <?php if ($cancelMessage): ?>
                        <div class="<?= $cancelError ? 'alert-failed' : 'alert-success' ?>"><?= e($cancelMessage) ?></div>
                    <?php endif; ?>
                    <p class="cancel-intro">Pro zrušení rezervace zadejte e-mail, který jste použili při rezervaci, a svůj rezervační kód.</p>
                    <form class="contact-form" method="post" autocomplete="off">
                        <input type="email" name="email" class="tickets-cancel" placeholder="Vaše e-mailová adresa*" required>
                        <input type="text" name="code" class="tickets-cancel" placeholder="Váš rezervační kód*" required>
                        <input type="submit" value="Zrušit rezervaci" name="send" id="button" class="cancel-btn">
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="ticktes-end">Rezervace vstupenek na akci <span class="gold">SAFE</span> jsou již u konce. Termín na rezervace pro další ročník akce <span class="gold">SAFE</span> bude ještě upřesněn.</div>
    <?php endif; ?>
</main>
