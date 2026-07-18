<?php
/**
 * Správa vstupenek - přepínač, zda jsou rezervace zapnuté nebo vypnuté.
 */

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
auth_require($config);

$db->exec("INSERT INTO reservation_status (id, is_active) SELECT 1, 0 WHERE NOT EXISTS (SELECT 1 FROM reservation_status)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $active = isset($_POST['is_active']) ? 1 : 0;
    $db->prepare('UPDATE reservation_status SET is_active = ? WHERE id = (SELECT id FROM reservation_status ORDER BY id LIMIT 1)')
       ->execute([$active]);
    flash_set('success', $active ? 'Vstupenky jsou nyní ZAPNUTÉ – návštěvníci mohou rezervovat.' : 'Vstupenky jsou nyní VYPNUTÉ.');
    redirect_admin('reservation/');
}

$row      = $db->query('SELECT is_active FROM reservation_status ORDER BY id LIMIT 1')->fetch();
$isActive = (int)($row['is_active'] ?? 0) === 1;

$pageTitle = 'Vstupenky';
$activeNav = 'reservation';
require __DIR__ . '/../lib/layout_top.php';
?>
<div class="page-head">
    <div>
        <h1>Vstupenky</h1>
        <p class="page-intro" style="margin:0">Zapnutí nebo vypnutí rezervací vstupenek. Když jsou vypnuté, na webu se místo formuláře zobrazí informace, že rezervace nejsou aktuálně dostupné.</p>
    </div>
</div>

<form method="post" class="form-card" style="max-width:560px">
    <?= csrf_field() ?>
    <div class="form-row">
        <p class="current-value">
            <strong>Aktuální stav:</strong>
            <span class="state-badge <?= $isActive ? 'state-on' : 'state-off' ?>">
                <?= $isActive ? 'ZAPNUTÉ' : 'VYPNUTÉ' ?>
            </span>
        </p>

        <label class="switch-row">
            <!-- posuvník: zapnuté / vypnuté rezervace -->
            <span class="switch">
                <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?>>
                <span class="switch-track"><span class="switch-thumb"></span></span>
            </span>
            <span class="switch-label">Rezervace vstupenek jsou zapnuté</span>
        </label>
    </div>
    <button type="submit" class="saveButton"><i class='bx bx-save'></i> Uložit stav</button>
</form>
<?php require __DIR__ . '/../lib/layout_bottom.php'; ?>
