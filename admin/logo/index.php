<?php
/**
 * Správa loga v hlavičce webu.
 *
 * Každý ročník SAFE mívá jiné logo. Tady se dá nahrát nové (nebo vrátit výchozí).
 * Součástí je živý náhled, jak bude hlavička s novým logem vypadat na počítači
 * i na mobilu – aby bylo hned vidět, jestli logo drží design, nebo ho rozbíjí.
 */

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../../app/lib/data.php';   // kvůli safe_site_logo()
auth_require($config);

// řádek loga musí existovat
$db->exec("INSERT INTO site_logo (id, image) SELECT 1, '' WHERE NOT EXISTS (SELECT 1 FROM site_logo)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $formAction = $_POST['form_action'] ?? 'upload';

    // vrácení výchozího loga
    if ($formAction === 'reset') {
        $old = $db->query('SELECT image FROM site_logo ORDER BY id LIMIT 1')->fetchColumn();
        $db->prepare('UPDATE site_logo SET image = ? WHERE id = (SELECT id FROM site_logo ORDER BY id LIMIT 1)')
           ->execute(['']);
        delete_uploaded_image($old ?: null);
        flash_set('success', 'Vráceno výchozí logo.');
        redirect_admin('logo/');
    }

    // nahrání nového loga
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
        redirect_admin('logo/');
    }

    if ($newImage !== null) {
        $old = $db->query('SELECT image FROM site_logo ORDER BY id LIMIT 1')->fetchColumn();
        $db->prepare('UPDATE site_logo SET image = ? WHERE id = (SELECT id FROM site_logo ORDER BY id LIMIT 1)')
           ->execute([$newImage]);
        delete_uploaded_image($old ?: null);
        flash_set('success', 'Nové logo bylo nahráno.');
    } else {
        flash_set('error', 'Nevybrali jste žádný obrázek.');
    }
    redirect_admin('logo/');
}

$row       = $db->query('SELECT image FROM site_logo ORDER BY id LIMIT 1')->fetch();
$current   = $row['image'] ?? '';
$isCustom  = $current !== '';
$logoUrl   = $baseUrl . 'public/' . safe_site_logo();   // aktuálně platné logo (vlastní nebo výchozí)

$pageTitle = 'Logo v hlavičce';
$activeNav = 'logo';
require __DIR__ . '/../lib/layout_top.php';
?>
<div class="page-head">
    <div>
        <h1>Logo v hlavičce</h1>
        <p class="page-intro" style="margin:0">Logo, které se zobrazuje vlevo nahoře na celém webu. Každý ročník bývá jiné.</p>
    </div>
</div>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- HORNÍ ŘADA: vlevo nahrání + doporučení, vpravo malý náhled loga -->
    <div class="logo-top">
        <div class="logo-controls">
            <label class="photo-upload-btn logo-pick">
                <i class='bx bx-upload'></i>
                <span>Vybrat logo</span>
                <input type="file" name="photo" accept="image/*" id="logoInput">
            </label>
            <span id="logoName" class="photo-upload-name">
                <?= $isCustom ? 'Nahráním nového se stávající nahradí.' : 'Zatím se používá výchozí logo.' ?>
            </span>

            <!-- Doporučený formát -->
            <div class="logo-hint-box">
                <h3><i class='bx bx-bulb'></i> Doporučený formát</h3>
                <ul>
                    <li><strong>Poměr stran zhruba 2:1</strong> – dvakrát širší než vyšší</li>
                    <li><strong>Ideálně 300 × 148 px</strong>, na šířku</li>
                    <li><strong>Průhledné pozadí</strong> (PNG) – logo pak hezky sedí do lišty</li>
                </ul>
                <p class="logo-warn">
                    <i class='bx bx-error'></i>
                    <span>Výrazně jiný poměr (např. čtverec nebo hodně vysoké logo) rozhodí
                    vzhled hlavičky – posune navigaci nebo roztáhne lištu do výšky.
                    V náhledu níže hned uvidíte, jak logo v hlavičce dopadne.</span>
                </p>
            </div>
        </div>

        <div class="logo-current">
            <span class="logo-current-label">Aktuální logo</span>
            <div class="logo-current-frame">
                <img id="logoPreview" src="<?= e($logoUrl) ?>" alt="Náhled loga">
            </div>
        </div>
    </div>

    <!-- ŽIVÝ NÁHLED HLAVIČKY (přes celou šířku) -->
    <div class="logo-preview-panel">
        <div class="preview-switcher">
            <span class="preview-title">Jak bude vypadat hlavička</span>
            <div class="preview-tabs">
                <button type="button" class="preview-tab is-active" data-view="desktop">
                    <i class='bx bx-desktop'></i> Počítač
                </button>
                <button type="button" class="preview-tab" data-view="mobile">
                    <i class='bx bx-mobile-alt'></i> Mobil
                </button>
            </div>
        </div>

        <!-- DESKTOP náhled -->
        <div class="preview-frame preview-desktop is-active">
            <div class="mini-header">
                <div class="mini-topbar">
                    <span class="mini-topbar-text">Miners Kladno <strong>Baseball &amp; Softball</strong></span>
                    <span class="mini-social">
                        <img src="<?= e($baseUrl) ?>public/images/common/softballczech.png" alt="">
                        <img src="<?= e($baseUrl) ?>public/images/common/baseballczech.png" alt="">
                        <img src="<?= e($baseUrl) ?>public/images/common/flickr.png" alt="">
                        <img src="<?= e($baseUrl) ?>public/images/common/FB.png" alt="">
                        <img src="<?= e($baseUrl) ?>public/images/common/IG.png" alt="">
                    </span>
                </div>
                <div class="mini-main">
                    <img class="mini-logo" src="<?= e($logoUrl) ?>" alt="">
                    <div class="mini-nav">
                        <span class="mini-dropdown">O AKCI <i class='bx bx-chevron-down'></i></span>
                        <span>PROGRAM</span><span>VSTUPENKY</span><span>MINERS</span><span>KONTAKT</span>
                        <span class="mini-lang-pc">CZ/EN</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOBIL náhled -->
        <div class="preview-frame preview-mobile">
            <div class="mini-phone">
                <div class="mini-header mini-header-mobile">
                    <div class="mini-topbar mini-topbar-mobile">
                        <span class="mini-topbar-text">MINERS KLADNO</span>
                        <span class="mini-social">
                            <img src="<?= e($baseUrl) ?>public/images/common/softballczech.png" alt="">
                            <img src="<?= e($baseUrl) ?>public/images/common/baseballczech.png" alt="">
                            <img src="<?= e($baseUrl) ?>public/images/common/flickr.png" alt="">
                            <img src="<?= e($baseUrl) ?>public/images/common/FB.png" alt="">
                            <img src="<?= e($baseUrl) ?>public/images/common/IG.png" alt="">
                        </span>
                    </div>
                    <div class="mini-main-mobile">
                        <img class="mini-logo-mobile" src="<?= e($logoUrl) ?>" alt="">
                        <span class="mini-lang">CZ/EN</span>
                        <i class='bx bx-menu mini-burger'></i>
                    </div>
                </div>
            </div>
        </div>

        <p class="preview-note">Náhled ukazuje, jak logo sedí do lišty a k navigaci na počítači i na mobilu.</p>
    </div>

    <div class="logo-actions">
        <button type="submit" class="saveButton"><i class='bx bx-save'></i> Uložit logo</button>
        <?php if ($isCustom): ?>
            <button type="submit" name="form_action" value="reset" class="btn-reset"
                    onclick="return confirm('Opravdu vrátit výchozí logo?');">
                <i class='bx bx-reset'></i> Vrátit výchozí
            </button>
        <?php endif; ?>
    </div>
</form>

<script>
(function () {
    var input   = document.getElementById('logoInput');
    var jmeno   = document.getElementById('logoName');
    // všechna místa, kde se logo v náhledech zobrazuje
    var nahledy = document.querySelectorAll('#logoPreview, .mini-logo, .mini-logo-mobile');

    input.addEventListener('change', function () {
        var soubor = input.files && input.files[0];
        if (!soubor) { return; }
        jmeno.textContent = soubor.name;
        var reader = new FileReader();
        reader.onload = function (e) {
            nahledy.forEach(function (img) { img.src = e.target.result; });
        };
        reader.readAsDataURL(soubor);
    });

    // přepínání počítač / mobil
    document.querySelectorAll('.preview-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.preview-tab').forEach(function (t) { t.classList.remove('is-active'); });
            tab.classList.add('is-active');
            var pohled = tab.dataset.view;
            document.querySelector('.preview-desktop').classList.toggle('is-active', pohled === 'desktop');
            document.querySelector('.preview-mobile').classList.toggle('is-active', pohled === 'mobile');
        });
    });
})();
</script>
<?php require __DIR__ . '/../lib/layout_bottom.php'; ?>
