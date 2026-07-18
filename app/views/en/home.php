<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';

$pageTitle = 'SAFE | Home Page';
$bodyClass = 'homepage-body';

$termin = safe_termin();
$terminText = safe_format_termin($termin, 'en');
$reservationActive = safe_reservation_active();
?>
<main class="main">
        <h1>THE MOST ANTICIPATED EVENT OF THE YEAR!</h1>
        <br>
        <?php if ($terminText): ?>
            <i><?= e($terminText) ?></i>
        <?php else: ?>
            <i>The date has not been set.</i>
        <?php endif; ?>

        <div class="buttons">
            <?php if ($reservationActive): ?>
                <a href="<?= url('reservation') ?>"><button type="button"><span></span>TICKETS</button></a>
            <?php endif; ?>
            <a href="<?= url('about') ?>"><button type="button"><span></span>ABOUT</button></a>
        </div>
    </main>
