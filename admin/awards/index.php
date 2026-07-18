<?php
/**
 * Správa ocenění hráčů - NOVÁ administrace.
 * Pole: rok, kategorie, nadpis ocenění (CZ/EN), popis (CZ/EN), fotka.
 * Ocenění se na webu seskupují podle roku a kategorie.
 */

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/photo_field.php';
auth_require($config);

// nabízené kategorie (podle stávajícího webu) - správce může zadat i vlastní
$knownCategories = ['EXTRA', 'UMP', 'MUŽI', 'ŽENY', 'U16s', 'U15', 'U13s', 'U13', 'U11s', 'U11'];

$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);
$filterYear = isset($_GET['year']) ? (int)$_GET['year'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = post_int('id');
        $stmt = $db->prepare('SELECT photo FROM awards WHERE id = ?');
        $stmt->execute([$id]);
        $photo = $stmt->fetchColumn();
        if ($photo !== false) {
            $db->prepare('DELETE FROM awards WHERE id = ?')->execute([$id]);
            delete_uploaded_image($photo ?: null);
            flash_set('success', 'Ocenění bylo odebráno.');
        }
        redirect_admin('awards/');
    }

    if ($formAction === 'save') {
        $id = post_int('id');
        $year = post_int('year');
        $category = clean($_POST['category'] ?? '');
        $titleCs = clean($_POST['title_cs'] ?? '');
        $titleEn = clean($_POST['title_en'] ?? '');
        $descCs  = clean($_POST['description_cs'] ?? '');
        $descEn  = clean($_POST['description_en'] ?? '');

        $errors = [];
        if ($year < 2000 || $year > 2100) {
            $errors[] = 'Zadejte platný rok (např. 2025).';
        }
        if ($category === '') {
            $errors[] = 'Vyberte nebo zadejte kategorii.';
        }
        if ($titleCs === '') {
            $errors[] = 'Vyplňte český nadpis ocenění.';
        }

        $photoPath = null;
        if (empty($errors)) {
            try {
                $photoPath = handle_image_upload('photo');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $er) {
                flash_set('error', $er);
            }
            redirect_admin('awards/?action=' . ($id ? 'edit&id=' . $id : 'add'));
        }

        if ($id) {
            if ($photoPath !== null) {
                $oldPhoto = $db->query('SELECT photo FROM awards WHERE id = ' . (int)$id)->fetchColumn();
                $db->prepare('UPDATE awards SET year=?, category=?, title_cs=?, title_en=?, description_cs=?, description_en=?, photo=? WHERE id=?')
                   ->execute([$year, $category, $titleCs, $titleEn, $descCs, $descEn, $photoPath, $id]);
                delete_uploaded_image($oldPhoto ?: null);
            } else {
                $db->prepare('UPDATE awards SET year=?, category=?, title_cs=?, title_en=?, description_cs=?, description_en=? WHERE id=?')
                   ->execute([$year, $category, $titleCs, $titleEn, $descCs, $descEn, $id]);
            }
            flash_set('success', 'Ocenění bylo upraveno.');
        } else {
            $max = (int)$db->query('SELECT COALESCE(MAX(sort_order), -1) FROM awards')->fetchColumn();
            $db->prepare('INSERT INTO awards (year, category, title_cs, title_en, description_cs, description_en, photo, sort_order) VALUES (?,?,?,?,?,?,?,?)')
               ->execute([$year, $category, $titleCs, $titleEn, $descCs, $descEn, $photoPath ?? '', $max + 1]);
            flash_set('success', 'Ocenění bylo přidáno.');
        }
        redirect_admin('awards/' . ($year ? '?year=' . $year : ''));
    }
}

$pageTitle = 'Ocenění hráči';
$activeNav = 'awards';
require __DIR__ . '/../lib/layout_top.php';

// FORMULÁŘ
if ($action === 'add' || ($action === 'edit' && $editId)) {
    $item = ['id' => 0, 'year' => (int)date('Y'), 'category' => '', 'title_cs' => '', 'title_en' => '', 'description_cs' => '', 'description_en' => '', 'photo' => ''];
    if ($action === 'edit') {
        $stmt = $db->prepare('SELECT * FROM awards WHERE id = ?');
        $stmt->execute([$editId]);
        $found = $stmt->fetch();
        if ($found) {
            $item = $found;
        }
    }
    ?>
    <a href="<?= e($baseUrl) ?>admin/awards/" class="btn-back"><i class='bx bx-arrow-back'></i> Zpět na ocenění</a>
    <h1><?= $item['id'] ? 'Upravit ocenění' : 'Přidat ocenění' ?></h1>

    <form method="post" enctype="multipart/form-data" class="form-card">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">

        <div class="form-grid-2">
            <div class="form-row">
                <label for="year">Rok</label>
                <?php
                // nabídneme rozumný rozsah roků + všechny, které už v databázi jsou
                $roky = range((int)date('Y') + 1, 2015);
                $vDb  = $db->query('SELECT DISTINCT year FROM awards')->fetchAll(PDO::FETCH_COLUMN);
                $roky = array_unique(array_merge($roky, array_map('intval', $vDb), [(int)$item['year']]));
                rsort($roky);
                ?>
                <select id="year" name="year" class="input-big" required>
                    <?php foreach ($roky as $rok): ?>
                        <?php if ($rok <= 0) { continue; } ?>
                        <option value="<?= (int)$rok ?>" <?= (int)$rok === (int)$item['year'] ? 'selected' : '' ?>><?= (int)$rok ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="category">Kategorie</label>
                <input type="text" id="category" name="category" value="<?= e($item['category']) ?>" list="categoryList" placeholder="např. EXTRA, U15, ŽENY" required>
                <datalist id="categoryList">
                    <?php foreach ($knownCategories as $c): ?>
                        <option value="<?= e($c) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <span class="hint">Krátký název (nanejvýš jako „WOMEN"). Do angličtiny se automaticky překládá jen MUŽI a ŽENY – ostatní zůstane beze změny, volte proto univerzální slovo.</span>
            </div>
        </div>

        <div class="form-row">
            <label for="title_cs">Nadpis ocenění (česky)</label>
            <input type="text" id="title_cs" name="title_cs" value="<?= e($item['title_cs']) ?>" placeholder="např. MVP – Jan Novák" required>
        </div>
        <div class="form-row">
            <label for="title_en">Nadpis ocenění (anglicky)</label>
            <input type="text" id="title_en" name="title_en" value="<?= e($item['title_en']) ?>" placeholder="e.g. MVP – Jan Novák">
        </div>
        <div class="form-row">
            <label for="description_cs">Popis (česky)</label>
            <textarea id="description_cs" name="description_cs" rows="3" placeholder="Krátký popis úspěchu hráče"><?= e($item['description_cs']) ?></textarea>
        </div>
        <div class="form-row">
            <label for="description_en">Popis (anglicky)</label>
            <textarea id="description_en" name="description_en" rows="3" placeholder="Short description of the achievement"><?= e($item['description_en']) ?></textarea>
        </div>

        <div class="form-row">
            <label>Fotka <span class="hint">(volitelné, JPG/PNG/WebP, max 5 MB)</span></label>
            <?php scm_photo_upload_field($baseUrl, (string)$item['photo'], 'square'); ?>
        </div>

        <button type="submit" class="saveButton"><i class='bx bx-save'></i> Uložit ocenění</button>
    </form>
    <?php scm_photo_upload_script(); ?>
    <?php
} else {
    // SEZNAM - seskupený podle roku, s filtrem roku
    $allYears = $db->query('SELECT DISTINCT year FROM awards ORDER BY year DESC')->fetchAll(PDO::FETCH_COLUMN);

    if ($filterYear) {
        $stmt = $db->prepare('SELECT * FROM awards WHERE year = ? ORDER BY sort_order, id');
        $stmt->execute([$filterYear]);
        $rows = $stmt->fetchAll();
    } else {
        $rows = $db->query('SELECT * FROM awards ORDER BY year DESC, sort_order, id')->fetchAll();
    }
    $totalAll = (int)$db->query('SELECT COUNT(*) FROM awards')->fetchColumn();
    ?>
    <div class="page-head">
        <div>
            <h1>Ocenění hráči</h1>
            <p class="page-intro" style="margin:0">Celkem <?= e(scm_plural($totalAll, 'ocenění', 'ocenění', 'ocenění', 'žádná ocenění')) ?></p>
        </div>
        <a href="<?= e($baseUrl) ?>admin/awards/?action=add" class="addButton"><i class='bx bx-plus'></i> Přidat ocenění</a>
    </div>

    <?php if (!empty($allYears)): ?>
        <div class="year-filter">
            <a href="<?= e($baseUrl) ?>admin/awards/" class="year-chip<?= $filterYear === 0 ? ' active' : '' ?>">Vše</a>
            <?php foreach ($allYears as $y): ?>
                <a href="<?= e($baseUrl) ?>admin/awards/?year=<?= (int)$y ?>" class="year-chip<?= $filterYear === (int)$y ? ' active' : '' ?>"><?= e($y) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($rows)): ?>
        <p class="empty-note">Zatím žádná ocenění. Přidejte první tlačítkem výše.</p>
    <?php else: ?>
        <div class="record-list">
            <?php $lastYear = null; foreach ($rows as $r): ?>
                <?php if (!$filterYear && $r['year'] !== $lastYear): ?>
                    <div class="record-group-head"><?= e($r['year']) ?></div>
                    <?php $lastYear = $r['year']; ?>
                <?php endif; ?>
                <div class="record-row">
                    <div class="record-photo record-photo-square">
                        <?php
                        // Když fotka chybí (nebo její soubor na disku neexistuje),
                        // ukážeme logo místo rozbitého obrázku.
                        $foto = (string)$r['photo'];
                        $maFoto = $foto !== '' && is_file(__DIR__ . '/../../public/' . $foto);
                        ?>
                        <img src="<?= e($maFoto ? $baseUrl . 'public/' . $foto : $baseUrl . 'public/images/common/SAFE-logo.png') ?>" alt="">
                    </div>
                    <div class="record-info">
                        <strong><?= $r['title_cs'] ?></strong>
                        <span class="record-sub"><span class="cat-badge"><?= e($r['category']) ?></span> <?= e($r['year']) ?></span>
                    </div>
                    <div class="record-actions">
                        <a href="<?= e($baseUrl) ?>admin/awards/?action=edit&id=<?= (int)$r['id'] ?>" class="btn-icon" title="Upravit"><i class='bx bx-edit'></i></a>
                        <form method="post" onsubmit="return confirm('Opravdu odebrat toto ocenění?');" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="btn-icon btn-danger" title="Odebrat"><i class='bx bx-trash'></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
}
require __DIR__ . '/../lib/layout_bottom.php';
?>
