<?php
/**
 * Správa minulých ročníků - CZ + EN, s textovým editorem Quill (zdarma,
 * bez registrace). Nadpis i popis se ukládají jako HTML. Galerie obrázků
 * (images) se zadává jako seznam nahraných souborů.
 */

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
auth_require($config);

$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = post_int('id');
        // smažeme i nahrané obrázky galerie
        $stmt = $db->prepare('SELECT images FROM events WHERE id = ?');
        $stmt->execute([$id]);
        $imagesJson = $stmt->fetchColumn();
        if ($imagesJson !== false) {
            $imgs = json_decode($imagesJson ?: '[]', true) ?: [];
            foreach ($imgs as $img) {
                delete_uploaded_image($img);
            }
            $db->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
            flash_set('success', 'Ročník byl odebrán.');
        }
        redirect_admin('events/');
    }

    if ($formAction === 'save') {
        $id = post_int('id');
        // Quill posílá HTML - povolíme bezpečné formátovací značky
        // Nadpis je pro obě jazykové verze společný – bývá to jen rok,
        // překládat ho nemá smysl. Ukládáme tedy stejnou hodnotu do obou sloupců.
        $titleCs = clean_html($_POST['title_cs'] ?? '');
        $titleEn = $titleCs;
        $descCs  = clean_html($_POST['description_cs'] ?? '');
        $descEn  = clean_html($_POST['description_en'] ?? '');

        $errors = [];
        if (trim(strip_tags($titleCs)) === '') {
            $errors[] = 'Vyplňte nadpis ročníku.';
        }

        // stávající obrázky galerie (ponechané) + nově nahrané
        $keepImages = $_POST['keep_images'] ?? [];
        if (!is_array($keepImages)) {
            $keepImages = [];
        }
        $images = [];
        foreach ($keepImages as $ki) {
            $ki = trim((string)$ki);
            if ($ki !== '') {
                $images[] = $ki;
            }
        }

        // nově nahrané obrázky (více souborů)
        if (!empty($_FILES['gallery']['name'][0])) {
            $count = count($_FILES['gallery']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['gallery']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                try {
                    $path = handle_image_upload_file([
                        'name'     => $_FILES['gallery']['name'][$i],
                        'type'     => $_FILES['gallery']['type'][$i],
                        'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                        'error'    => $_FILES['gallery']['error'][$i],
                        'size'     => $_FILES['gallery']['size'][$i],
                    ]);
                    if ($path !== null) {
                        $images[] = $path;
                    }
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $er) {
                flash_set('error', $er);
            }
            redirect_admin('events/?action=' . ($id ? 'edit&id=' . $id : 'add'));
        }

        $imagesJson = json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($id) {
            $db->prepare('UPDATE events SET title_cs=?, title_en=?, description_cs=?, description_en=?, images=? WHERE id=?')
               ->execute([$titleCs, $titleEn, $descCs, $descEn, $imagesJson, $id]);
            flash_set('success', 'Ročník byl upraven.');
        } else {
            $max = (int)$db->query('SELECT COALESCE(MAX(sort_order), -1) FROM events')->fetchColumn();
            $db->prepare('INSERT INTO events (title_cs, title_en, description_cs, description_en, images, sort_order) VALUES (?,?,?,?,?,?)')
               ->execute([$titleCs, $titleEn, $descCs, $descEn, $imagesJson, $max + 1]);
            flash_set('success', 'Ročník byl přidán.');
        }
        redirect_admin('events/');
    }
}

$pageTitle = 'Minulé ročníky';
$activeNav = 'events';
require __DIR__ . '/../lib/layout_top.php';

// FORMULÁŘ
if ($action === 'add' || ($action === 'edit' && $editId)) {
    $item = ['id' => 0, 'title_cs' => '', 'title_en' => '', 'description_cs' => '', 'description_en' => '', 'images' => ''];
    if ($action === 'edit') {
        $stmt = $db->prepare('SELECT * FROM events WHERE id = ?');
        $stmt->execute([$editId]);
        $found = $stmt->fetch();
        if ($found) {
            $item = $found;
        }
    }
    $existingImages = json_decode($item['images'] ?: '[]', true) ?: [];
    ?>
    <a href="<?= e($baseUrl) ?>admin/events/" class="btn-back"><i class='bx bx-arrow-back'></i> Zpět na minulé ročníky</a>
    <h1><?= $item['id'] ? 'Upravit ročník' : 'Přidat ročník' ?></h1>
    <p class="page-intro">Nadpis obvykle obsahuje rok (např. „2024"). V popisu můžete používat tučné písmo, kurzívu, nadpisy a odkazy.</p>

    <form method="post" enctype="multipart/form-data" class="form-card" id="eventForm">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">

        <div class="form-row">
            <label>Nadpis ročníku</label>
            <div class="quill-editor" data-target="title_cs"><?= $item['title_cs'] ?></div>
            <input type="hidden" name="title_cs" value="<?= e($item['title_cs']) ?>">
            <span class="hint">Společný pro českou i anglickou verzi – zpravidla stačí rok, např. 2025.</span>
        </div>
        <div class="form-row">
            <label>Popis (česky)</label>
            <div class="quill-editor quill-tall" data-target="description_cs"><?= $item['description_cs'] ?></div>
            <input type="hidden" name="description_cs" value="<?= e($item['description_cs']) ?>">
        </div>
        <div class="form-row">
            <label>Popis (anglicky)</label>
            <div class="quill-editor quill-tall" data-target="description_en"><?= $item['description_en'] ?></div>
            <input type="hidden" name="description_en" value="<?= e($item['description_en']) ?>">
        </div>

        <div class="form-row">
            <label>Galerie obrázků <span class="hint">(JPG/PNG/WebP, max 5 MB za obrázek)</span></label>
            <?php if (!empty($existingImages)): ?>
                <div class="gallery-thumbs">
                    <?php foreach ($existingImages as $img): ?>
                        <div class="gallery-thumb">
                            <img src="<?= e($baseUrl . 'public/' . $img) ?>" alt="">
                            <label class="thumb-keep">
                                <input type="checkbox" name="keep_images[]" value="<?= e($img) ?>" checked>
                                ponechat
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <span class="hint">Odškrtnutím „ponechat" obrázek při uložení odeberete.</span>
            <?php endif; ?>
            <label class="photo-upload-btn" style="margin-top:10px">
                <i class='bx bx-upload'></i>
                <span>Přidat obrázky</span>
                <input type="file" name="gallery[]" accept="image/*" multiple onchange="showGalleryPreview(this)">
            </label>
            <span id="galleryCount" class="photo-upload-name">Zatím nevybráno.</span>

            <!-- náhled nově vybraných obrázků (ještě před uložením) -->
            <div id="galleryNew" class="gallery-thumbs gallery-thumbs-new"></div>
        </div>

        <button type="submit" class="saveButton"><i class='bx bx-save'></i> Uložit ročník</button>
    </form>

    <!-- Quill.js - textový editor zdarma, bez registrace, přes CDN -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.querySelectorAll('.quill-editor').forEach(function (el) {
            var targetName = el.dataset.target;
            var hidden = document.querySelector('input[name="' + targetName + '"]');
            var quill = new Quill(el, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });
            // při odeslání formuláře uložíme HTML z editoru do skrytého inputu
            document.getElementById('eventForm').addEventListener('submit', function () {
                hidden.value = quill.root.innerHTML;
            });
        });

        function showGalleryPreview(input) {
            var pocet = document.getElementById('galleryCount');
            var nahledy = document.getElementById('galleryNew');
            var soubory = input.files ? Array.prototype.slice.call(input.files) : [];

            pocet.textContent = soubory.length > 0
                ? (soubory.length + ' ' + (soubory.length === 1 ? 'nový obrázek' : 'nových obrázků'))
                : 'Zatím nevybráno.';

            nahledy.innerHTML = '';
            soubory.forEach(function (soubor) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var box = document.createElement('div');
                    box.className = 'gallery-thumb is-new';
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = soubor.name;
                    var popis = document.createElement('span');
                    popis.className = 'thumb-new-label';
                    popis.textContent = 'nový';
                    box.appendChild(img);
                    box.appendChild(popis);
                    nahledy.appendChild(box);
                };
                reader.readAsDataURL(soubor);
            });
        }
    </script>
    <?php
} else {
    // SEZNAM
    // nejnovější ročník nahoře – stejně jako na webu i v původní administraci
    $rows = $db->query('SELECT * FROM events ORDER BY sort_order DESC, id DESC')->fetchAll();
    ?>
    <div class="page-head">
        <div>
            <h1>Minulé ročníky</h1>
            <p class="page-intro" style="margin:0"><?= e(scm_plural(count($rows), 'ročník', 'ročníky', 'ročníků', 'žádné ročníky')) ?></p>
        </div>
        <a href="<?= e($baseUrl) ?>admin/events/?action=add" class="addButton"><i class='bx bx-plus'></i> Přidat ročník</a>
    </div>

    <?php if (empty($rows)): ?>
        <p class="empty-note">Zatím žádné minulé ročníky. Přidejte první tlačítkem výše.</p>
    <?php else: ?>
        <div class="record-list">
            <?php foreach ($rows as $r): ?>
                <?php $imgs = json_decode($r['images'] ?: '[]', true) ?: []; ?>
                <div class="record-row">
                    <?php if (!empty($imgs)): ?>
                        <div class="record-photo record-photo-square">
                            <img src="<?= e($baseUrl . 'public/' . $imgs[0]) ?>" alt="">
                        </div>
                    <?php endif; ?>
                    <div class="record-info">
                        <strong><?= strip_tags($r['title_cs']) ?: '(bez nadpisu)' ?></strong>
                        <span class="record-sub"><?= e(mb_substr(trim(strip_tags($r['description_cs'])), 0, 90)) ?><?= mb_strlen(trim(strip_tags($r['description_cs']))) > 90 ? '…' : '' ?></span>
                        <span class="record-sub"><?= e(scm_plural(count($imgs), 'obrázek', 'obrázky', 'obrázků', 'bez obrázků')) ?></span>
                    </div>
                    <div class="record-actions">
                        <a href="<?= e($baseUrl) ?>admin/events/?action=edit&id=<?= (int)$r['id'] ?>" class="btn-icon" title="Upravit"><i class='bx bx-edit'></i></a>
                        <form method="post" onsubmit="return confirm('Opravdu odebrat tento ročník?');" style="display:inline">
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
