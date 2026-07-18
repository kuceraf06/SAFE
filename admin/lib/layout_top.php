<?php
/**
 * Sdílený layout administrace SAFE (hlavička + sidebar).
 * -----------------------------------------------------------------------------
 * Použití v každé chráněné stránce:
 *     $pageTitle = 'Ocenění hráči';
 *     $activeNav = 'awards';
 *     require __DIR__ . '/../lib/layout_top.php';
 *     ... obsah ...
 *     require __DIR__ . '/../lib/layout_bottom.php';
 */

if (!isset($baseUrl)) {
    exit;
}
$pageTitle = $pageTitle ?? 'Administrace';
$activeNav = $activeNav ?? '';

/** Pomůcka: vrátí "active" pro aktivní položku menu. */
function nav_active(string $key, string $current): string
{
    return $key === $current ? ' class="active"' : '';
}
?><!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAFE | Admin<?= $pageTitle ? ' - ' . e(mb_convert_case($pageTitle, MB_CASE_TITLE, 'UTF-8')) : '' ?></title>
    <link rel="icon" type="image/png" href="<?= e($baseUrl) ?>public/images/common/favicon-32x32.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Titan One – stejný font jako v hlavičce webu, kvůli věrnému náhledu loga -->
    <link href="https://fonts.googleapis.com/css2?family=Titan+One&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?= e($baseUrl) ?>admin/assets/admin.css">
</head>
<body>
<button class="menu-toggle" onclick="toggleSidebar()" aria-label="Menu"><i class="bx bx-menu"></i></button>
<nav class="sidebar hidden" id="sidebar">
    <button class="close-btn" onclick="toggleSidebar()" aria-label="Zavřít"><i class="bx bx-x"></i></button>
    <div class="side-header">
        <a href="<?= e($baseUrl) ?>admin/"><img src="<?= e($baseUrl) ?>public/images/common/SAFE-logo.png" alt="SAFE logo"></a>
        <h2>Admin SAFE</h2>
    </div>
    <hr>
    <ul>
        <li><a href="<?= e($baseUrl) ?>admin/"<?= nav_active('dashboard', $activeNav) ?>><i class='bx bxs-dashboard'></i>Přehled</a></li>
        <span class="side-label">Akce</span>
        <li><a href="<?= e($baseUrl) ?>admin/termin/"<?= nav_active('termin', $activeNav) ?>><i class='bx bxs-calendar'></i>Termín</a></li>
        <li><a href="<?= e($baseUrl) ?>admin/invitation/"<?= nav_active('invitation', $activeNav) ?>><i class='bx bxs-image'></i>Pozvánka</a></li>
        <li><a href="<?= e($baseUrl) ?>admin/program/"<?= nav_active('program', $activeNav) ?>><i class='bx bx-list-check'></i>Program</a></li>
        <li><a href="<?= e($baseUrl) ?>admin/reservation/"<?= nav_active('reservation', $activeNav) ?>><i class='bx bxs-coupon'></i>Vstupenky</a></li>
        <span class="side-label">Obsah</span>
        <li><a href="<?= e($baseUrl) ?>admin/logo/"<?= nav_active('logo', $activeNav) ?>><i class='bx bxs-flag-alt'></i>Logo</a></li>
        <li><a href="<?= e($baseUrl) ?>admin/events/"<?= nav_active('events', $activeNav) ?>><i class='bx bxs-photo-album'></i>Minulé ročníky</a></li>
        <li><a href="<?= e($baseUrl) ?>admin/awards/"<?= nav_active('awards', $activeNav) ?>><i class='bx bxs-trophy'></i>Ocenění hráči</a></li>
        <span class="side-label">Účet</span>
        <li><a href="<?= e($baseUrl) ?>admin/account/"<?= nav_active('account', $activeNav) ?>><i class='bx bxs-key'></i>Změna hesla</a></li>
    </ul>
    <a href="<?= e($baseUrl) ?>admin/logout/" class="logout" onclick="confirmLogout(event)">
        <i class='bx bx-user-x'></i>Odhlásit se
    </a>
</nav>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('hidden');
    }
    function confirmLogout(event) {
        event.preventDefault();
        if (confirm('Opravdu se chcete odhlásit?')) {
            window.location.href = '<?= e($baseUrl) ?>admin/logout/';
        }
    }
    document.addEventListener('click', function (event) {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.querySelector('.menu-toggle');
        if (sidebar && !sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.add('hidden');
        }
    });
</script>

<div class="content">
<?php
foreach (flash_get() as $f) {
    $cls = $f['type'] === 'error' ? 'flash-error' : 'flash-success';
    echo '<div class="flash ' . $cls . '">' . e($f['message']) . '</div>';
}
