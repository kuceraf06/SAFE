<?php
/** Rozcestník administrace SAFE. */
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
auth_require($config);

// počty záznamů pro přehled
$counts = [
    'events'    => (int)$db->query('SELECT COUNT(*) FROM events')->fetchColumn(),
    'programme' => (int)$db->query('SELECT COUNT(*) FROM programme')->fetchColumn(),
    'awards'    => (int)$db->query('SELECT COUNT(*) FROM awards')->fetchColumn(),
];
$reservationActive = (int)($db->query('SELECT is_active FROM reservation_status ORDER BY id LIMIT 1')->fetchColumn() ?? 0) === 1;

$pageTitle = 'Přehled';
$activeNav = 'dashboard';
require __DIR__ . '/lib/layout_top.php';
?>
<h1>Administrace SAFE</h1>
<p class="page-intro">Správa galavečera SAFE – termín, pozvánka, program, minulé ročníky a ocenění hráči.</p>

<div class="addBox">
    <a href="<?= e($baseUrl) ?>admin/termin/" class="childBox">
        <i class='bx bxs-calendar'></i>
        <p>Termín akce</p>
    </a>
    <a href="<?= e($baseUrl) ?>admin/invitation/" class="childBox">
        <i class='bx bxs-image'></i>
        <p>Pozvánka</p>
    </a>
    <a href="<?= e($baseUrl) ?>admin/program/" class="childBox">
        <i class='bx bx-list-check'></i>
        <p>Program <span style="color:var(--scm-muted)">(<?= e(scm_plural($counts['programme'], 'položka', 'položky', 'položek', 'žádné položky')) ?>)</span></p>
    </a>
    <a href="<?= e($baseUrl) ?>admin/reservation/" class="childBox">
        <i class='bx bxs-coupon'></i>
        <p>Vstupenky <span style="color:var(--scm-muted)">(<?= $reservationActive ? 'zapnuté' : 'vypnuté' ?>)</span></p>
    </a>
    <a href="<?= e($baseUrl) ?>admin/logo/" class="childBox">
        <i class='bx bxs-flag-alt'></i>
        <p>Logo</p>
    </a>
    <a href="<?= e($baseUrl) ?>admin/events/" class="childBox">
        <i class='bx bxs-photo-album'></i>
        <p>Minulé ročníky <span style="color:var(--scm-muted)">(<?= e(scm_plural($counts['events'], 'ročník', 'ročníky', 'ročníků', 'žádné ročníky')) ?>)</span></p>
    </a>
    <a href="<?= e($baseUrl) ?>admin/awards/" class="childBox">
        <i class='bx bxs-trophy'></i>
        <p>Ocenění hráči <span style="color:var(--scm-muted)">(<?= e(scm_plural($counts['awards'], 'ocenění', 'ocenění', 'ocenění', 'žádná ocenění')) ?>)</span></p>
    </a>
</div>
<?php require __DIR__ . '/lib/layout_bottom.php'; ?>
