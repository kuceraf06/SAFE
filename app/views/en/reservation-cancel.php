<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';
require_once __DIR__ . '/../../lib/reservation.php';

$pageTitle = 'SAFE | Cancel Reservation';
$bodyClass = 'reservationpage-body';
$pageCss   = 'reservation';

$isReservationActive = safe_reservation_active();

// zpracování formuláře (ověření kódu, zrušení v tabulce, potvrzovací e-mail)
$cancel        = safe_handle_cancel('en');
$cancelDone    = $cancel['done'];
$cancelMessage = $cancel['message'];
$cancelError   = ($cancel['class'] === 'alert-failed');
?>
<main class="cancel-container page-width">
    <div class="heading">
        <h1>CANCEL RESERVATION</h1>
    </div>

    <?php if ($isReservationActive): ?>
        <div class="remove-box">
            <div class="right cz-right">
                <?php if ($cancelDone): ?>
                    <div class="cancel-confirm">
                        <h2>Ticket reservation has been cancelled</h2>
                        <p>We have sent a confirmation to your e-mail. Thank you.</p>
                        <p>Want to book again? <a href="<?= url('reservation') ?>">Create a new reservation</a></p>
                    </div>
                <?php else: ?>
                    <?php if ($cancelMessage): ?>
                        <div class="<?= $cancelError ? 'alert-failed' : 'alert-success' ?>"><?= e($cancelMessage) ?></div>
                    <?php endif; ?>
                    <p class="cancel-intro">To cancel your reservation, enter the e-mail you used and your reservation code.</p>
                    <form class="contact-form" method="post" autocomplete="off">
                        <input type="email" name="email" class="tickets-cancel" placeholder="Your e-mail address*" required>
                        <input type="text" name="code" class="tickets-cancel" placeholder="Your reservation code*" required>
                        <input type="submit" value="Cancel reservation" name="send" id="button" class="cancel-btn">
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="ticktes-end">Ticket reservations for the <span class="gold">SAFE</span> event are now closed. The reservation date for the next edition of <span class="gold">SAFE</span> will be announced.</div>
    <?php endif; ?>
</main>
