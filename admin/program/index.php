<?php
/**
 * Správa programu galavečera - řádky programu (CZ + EN).
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
        $db->prepare('DELETE FROM programme WHERE id = ?')->execute([$id]);
        flash_set('success', 'Položka programu byla odebrána.');
        redirect_admin('program/');
    }

    // nové pořadí po přetažení myší
    if ($formAction === 'reorder') {
        $poradi = $_POST['order'] ?? [];
        if (is_array($poradi)) {
            $stmt = $db->prepare('UPDATE programme SET sort_order = ? WHERE id = ?');
            foreach (array_values($poradi) as $pozice => $id) {
                $stmt->execute([$pozice, (int)$id]);
            }
        }
        // odpověď pro JavaScript (stránka se nepřenačítá)
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($formAction === 'save') {
        $id = post_int('id');
        $descCs = clean($_POST['description_cs'] ?? '');
        $descEn = clean($_POST['description_en'] ?? '');

        $errors = [];
        if ($descCs === '') {
            $errors[] = 'Vyplňte český text položky programu.';
        }
        if ($descEn === '') {
            $errors[] = 'Vyplňte anglický text položky programu.';
        }

        if (!empty($errors)) {
            foreach ($errors as $er) {
                flash_set('error', $er);
            }
            redirect_admin('program/?action=' . ($id ? 'edit&id=' . $id : 'add'));
        }

        if ($id) {
            $db->prepare('UPDATE programme SET description_cs = ?, description_en = ? WHERE id = ?')
               ->execute([$descCs, $descEn, $id]);
            flash_set('success', 'Položka programu byla upravena.');
        } else {
            $max = (int)$db->query('SELECT COALESCE(MAX(sort_order), -1) FROM programme')->fetchColumn();
            $db->prepare('INSERT INTO programme (description_cs, description_en, sort_order) VALUES (?,?,?)')
               ->execute([$descCs, $descEn, $max + 1]);
            flash_set('success', 'Položka programu byla přidána.');
        }
        redirect_admin('program/');
    }
}

$pageTitle = 'Program';
$activeNav = 'program';
require __DIR__ . '/../lib/layout_top.php';

// FORMULÁŘ (přidat/upravit)
if ($action === 'add' || ($action === 'edit' && $editId)) {
    $item = ['id' => 0, 'description_cs' => '', 'description_en' => ''];
    if ($action === 'edit') {
        $stmt = $db->prepare('SELECT * FROM programme WHERE id = ?');
        $stmt->execute([$editId]);
        $found = $stmt->fetch();
        if ($found) {
            $item = $found;
        }
    }
    ?>
    <a href="<?= e($baseUrl) ?>admin/program/" class="btn-back"><i class='bx bx-arrow-back'></i> Zpět na program</a>
    <h1><?= $item['id'] ? 'Upravit položku programu' : 'Přidat položku programu' ?></h1>
    <form method="post" class="form-card">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
        <div class="form-row">
            <label for="description_cs">Text (česky)</label>
            <input type="text" id="description_cs" name="description_cs" value="<?= e($item['description_cs']) ?>" placeholder="např. 17:30 otevření sálu" required>
        </div>
        <div class="form-row">
            <label for="description_en">Text (anglicky)</label>
            <input type="text" id="description_en" name="description_en" value="<?= e($item['description_en']) ?>" placeholder="e.g. 5:30 PM hall opening" required>
        </div>
        <button type="submit" class="saveButton"><i class='bx bx-save'></i> Uložit</button>
    </form>
    <?php
} else {
    // SEZNAM
    $rows = $db->query('SELECT * FROM programme ORDER BY sort_order, id')->fetchAll();
    ?>
    <div class="page-head">
        <div>
            <h1>Program</h1>
            <p class="page-intro" style="margin:0"><?= e(scm_plural(count($rows), 'položka', 'položky', 'položek', 'žádné položky')) ?></p>
        </div>
        <a href="<?= e($baseUrl) ?>admin/program/?action=add" class="addButton"><i class='bx bx-plus'></i> Přidat položku</a>
    </div>

    <?php if (empty($rows)): ?>
        <p class="empty-note">Zatím žádné položky programu. Přidejte první tlačítkem výše.</p>
    <?php else: ?>
        <p class="drag-hint"><i class='bx bx-move-vertical'></i> Pořadí změníte přetažením za úchyt vlevo – myší i prstem.</p>
        <div class="record-list" id="programList">
            <?php foreach ($rows as $r): ?>
                <div class="record-row is-sortable" draggable="true" data-id="<?= (int)$r['id'] ?>">
                    <span class="drag-handle" title="Přetažením změníte pořadí"><i class='bx bx-menu'></i></span>
                    <div class="record-info">
                        <strong><?= e($r['description_cs']) ?></strong>
                        <span class="record-sub"><?= e($r['description_en']) ?></span>
                    </div>
                    <div class="record-actions">
                        <a href="<?= e($baseUrl) ?>admin/program/?action=edit&id=<?= (int)$r['id'] ?>" class="btn-icon" title="Upravit"><i class='bx bx-edit'></i></a>
                        <form method="post" onsubmit="return confirm('Opravdu odebrat tuto položku programu?');" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="btn-icon btn-danger" title="Odebrat"><i class='bx bx-trash'></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <span id="orderSaved" class="order-saved"><i class='bx bx-check'></i> Pořadí uloženo</span>

        <script>
        (function () {
            var seznam = document.getElementById('programList');
            if (!seznam) { return; }

            var tazeny = null;

            /* ---------- společná logika (myš i prst) ---------- */

            function zacniTahat(radek) {
                tazeny = radek;
                radek.classList.add('is-dragging');
            }

            function prestanTahat() {
                if (!tazeny) { return; }
                tazeny.classList.remove('is-dragging');
                tazeny = null;
                ulozPoradi();
            }

            /* Vloží tažený řádek nad nebo pod ten, nad kterým je kurzor/prst. */
            function presunNad(cil, y) {
                if (!tazeny || !cil || cil === tazeny) { return; }
                var box = cil.getBoundingClientRect();
                var nadPolovinou = (y - box.top) < (box.height / 2);
                seznam.insertBefore(tazeny, nadPolovinou ? cil : cil.nextSibling);
            }

            /* ---------- myš (HTML5 drag & drop) ---------- */

            seznam.addEventListener('dragstart', function (e) {
                var radek = e.target.closest('.is-sortable');
                if (!radek) { return; }
                zacniTahat(radek);
                e.dataTransfer.effectAllowed = 'move';
            });

            seznam.addEventListener('dragover', function (e) {
                e.preventDefault();
                presunNad(e.target.closest('.is-sortable'), e.clientY);
            });

            seznam.addEventListener('dragend', prestanTahat);

            /* ---------- dotyk (mobil, tablet) ----------
               HTML5 drag & drop se na dotykových zařízeních vůbec nespustí,
               proto si dotyk obsloužíme sami. Tahat jde jen za úchyt vlevo,
               aby se dal prstem normálně posouvat obsah stránky.            */

            seznam.addEventListener('touchstart', function (e) {
                var uchyt = e.target.closest('.drag-handle');
                if (!uchyt) { return; }
                var radek = uchyt.closest('.is-sortable');
                if (radek) { zacniTahat(radek); }
            }, { passive: true });

            seznam.addEventListener('touchmove', function (e) {
                if (!tazeny) { return; }
                e.preventDefault();          // ať se pod prstem neposouvá stránka
                var prst = e.touches[0];
                var pod = document.elementFromPoint(prst.clientX, prst.clientY);
                presunNad(pod ? pod.closest('.is-sortable') : null, prst.clientY);
            }, { passive: false });

            seznam.addEventListener('touchend', prestanTahat);
            seznam.addEventListener('touchcancel', prestanTahat);

            /* ---------- uložení nového pořadí ---------- */

            function ulozPoradi() {
                var data = new FormData();
                data.append('csrf_token', '<?= e(csrf_token()) ?>');
                data.append('form_action', 'reorder');
                seznam.querySelectorAll('.is-sortable').forEach(function (r) {
                    data.append('order[]', r.dataset.id);
                });
                fetch(window.location.pathname, { method: 'POST', body: data })
                    .then(function (r) { return r.json(); })
                    .then(function () {
                        var hlaska = document.getElementById('orderSaved');
                        hlaska.classList.add('is-visible');
                        setTimeout(function () { hlaska.classList.remove('is-visible'); }, 1800);
                    })
                    .catch(function () { alert('Nové pořadí se nepodařilo uložit, zkuste to prosím znovu.'); });
            }
        })();
        </script>
    <?php endif; ?>
    <?php
}
require __DIR__ . '/../lib/layout_bottom.php';
?>
