<?php
/**
 * Správa termínu akce SAFE (jedno datum a čas).
 */

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../../app/lib/view_helpers.php';   // kvůli safe_format_termin()
auth_require($config);

// zajistíme, že řádek termínu existuje
$db->exec("INSERT INTO termin (id, datum_cas) SELECT 1, '' WHERE NOT EXISTS (SELECT 1 FROM termin)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $datum = clean($_POST['datum_cas'] ?? '');
    // ukládáme ve formátu YYYY-MM-DDTHH:MM (jak očekává web)
    $db->prepare('UPDATE termin SET datum_cas = ? WHERE id = (SELECT id FROM termin ORDER BY id LIMIT 1)')
       ->execute([$datum]);
    flash_set('success', 'Termín akce byl uložen.');
    redirect_admin('termin/');
}

$row     = $db->query('SELECT datum_cas FROM termin ORDER BY id LIMIT 1')->fetch();
$current = $row['datum_cas'] ?? '';

// hezky naformátovaný termín, jak ho uvidí návštěvník webu
$currentText = safe_format_termin($current, 'cs');

$pageTitle = 'Termín akce';
$activeNav = 'termin';
require __DIR__ . '/../lib/layout_top.php';
?>
<div class="page-head">
    <div>
        <h1>Termín akce</h1>
        <p class="page-intro" style="margin:0">Datum a čas konání galavečera SAFE. Zobrazuje se na úvodní stránce a v sekci O akci.</p>
    </div>
</div>

<form method="post" class="form-card" style="max-width:520px">
    <?= csrf_field() ?>
    <div class="form-row">
        <p class="current-value">
            <strong>Aktuální termín:</strong>
            <?php if ($currentText !== null): ?>
                <span><?= e($currentText) ?></span>
            <?php else: ?>
                <span class="current-value-empty">zatím nenastaven</span>
            <?php endif; ?>
        </p>
        <label for="datum_cas" class="sr-only">Datum a čas akce</label>
        <input type="datetime-local" id="datum_cas" name="datum_cas" class="input-big" value="<?= e($current) ?>">
    </div>
    <button type="submit" class="saveButton"><i class='bx bx-save'></i> Uložit termín</button>
</form>
<?php require __DIR__ . '/../lib/layout_bottom.php'; ?>
