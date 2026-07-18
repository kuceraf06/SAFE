<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';

$pageTitle = 'SAFE | Domovská Stránka';
$bodyClass = 'homepage-body';

$termin = safe_termin();
$terminText = safe_format_termin($termin, 'cs');
$reservationActive = safe_reservation_active();
?>
<main class="main">
        <h1>NEJOČEKÁVANĚJŠÍ AKCE ROKU!</h1>
        <br>
        <?php if ($terminText): ?>
            <i><?= e($terminText) ?></i>
        <?php else: ?>
            <i>Termín nebyl nastaven.</i>
        <?php endif; ?>

        <div class="buttons">
            <?php if ($reservationActive): ?>
                <a href="<?= url('reservation') ?>"><button type="button"><span></span>VSTUPENKY</button></a>
            <?php endif; ?>
            <a href="<?= url('about') ?>"><button type="button"><span></span>O AKCI</button></a>
        </div>
    </main>
