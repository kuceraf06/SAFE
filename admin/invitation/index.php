<?php
/**
 * Správa pozvánky (obrázek) - zobrazuje se v sekci Vstupenky.
 */

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/photo_field.php';
auth_require($config);

$db->exec("INSERT INTO invitation (id, image) SELECT 1, '' WHERE NOT EXISTS (SELECT 1 FROM invitation)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $errors = [];
    $newImage = null;
    try {
        $newImage = handle_image_upload('photo');
    } catch (RuntimeException $e) {
        $errors[] = $e->getMessage();
    }

    if (!empty($errors)) {
        foreach ($errors as $er) {
            flash_set('error', $er);
        }
        redirect_admin('invitation/');
    }

    if ($newImage !== null) {
        // smažeme starou pozvánku (pokud byla nahraná přes admin)
        $old = $db->query('SELECT image FROM invitation ORDER BY id LIMIT 1')->fetchColumn();
        $db->prepare('UPDATE invitation SET image = ? WHERE id = (SELECT id FROM invitation ORDER BY id LIMIT 1)')
           ->execute([$newImage]);
        delete_uploaded_image($old ?: null);
        flash_set('success', 'Pozvánka byla nahrána.');
    } else {
        flash_set('error', 'Nevybrali jste žádný obrázek.');
    }
    redirect_admin('invitation/');
}

$row = $db->query('SELECT image FROM invitation ORDER BY id LIMIT 1')->fetch();
$current = $row['image'] ?? '';

$pageTitle = 'Pozvánka';
$activeNav = 'invitation';
require __DIR__ . '/../lib/layout_top.php';
?>
<div class="page-head">
    <div>
        <h1>Pozvánka</h1>
        <p class="page-intro" style="margin:0">Obrázek pozvánky na akci. Zobrazuje se v sekci Vstupenky vedle rezervačního formuláře.</p>
    </div>
</div>

<form method="post" enctype="multipart/form-data" class="form-card" style="max-width:560px">
    <?= csrf_field() ?>
    <div class="form-row">
        <label>Obrázek pozvánky <span class="hint">(JPG/PNG/WebP, max 5 MB)</span></label>
        <?php scm_photo_upload_field($baseUrl, (string)$current, 'square', 'large'); ?>
    </div>
    <button type="submit" class="saveButton"><i class='bx bx-save'></i> Uložit pozvánku</button>
</form>
<?php scm_photo_upload_script(); ?>
<?php require __DIR__ . '/../lib/layout_bottom.php'; ?>
