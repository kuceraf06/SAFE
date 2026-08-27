<?php
/**
 * Správa vstupenek - přepínač zapnuto/vypnuto + cena vstupenky pro doprovod.
 */

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
auth_require($config);

$db->exec("INSERT INTO reservation_status (id, is_active) SELECT 1, 0 WHERE NOT EXISTS (SELECT 1 FROM reservation_status)");

/** Horní mez ceny - pojistka proti překlepu (např. omylem přidaná nula). */
const SAFE_ESCORT_PRICE_MAX = 100000;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $active = isset($_POST['is_active']) ? 1 : 0;

    // cena: prázdné pole bereme jako 0 (zdarma), jinak celé Kč
    $priceRaw = clean($_POST['escort_price'] ?? '');
    $priceRaw = str_replace([' ', "\xc2\xa0"], '', $priceRaw);   // "1 000" i s pevnou mezerou

    if ($priceRaw !== '' && !ctype_digit($priceRaw)) {
        flash_set('error', 'Cena musí být celé číslo v korunách (0 = zdarma). Nic se neuložilo.');
        redirect_admin('reservation/');
    }

    $price = (int)$priceRaw;
    if ($price > SAFE_ESCORT_PRICE_MAX) {
        flash_set('error', 'Cena je nepřiměřeně vysoká (maximum je ' . SAFE_ESCORT_PRICE_MAX . ' Kč). Nic se neuložilo.');
        redirect_admin('reservation/');
    }

    $db->prepare('UPDATE reservation_status SET is_active = ?, escort_price = ? WHERE id = (SELECT id FROM reservation_status ORDER BY id LIMIT 1)')
       ->execute([$active, $price]);

    $cenaText = $price > 0
        ? 'cena ' . number_format($price, 0, ',', ' ') . ' Kč za lístek pro doprovod'
        : 'vstup pro doprovod je ZDARMA';
    flash_set('success', ($active ? 'Vstupenky jsou nyní ZAPNUTÉ' : 'Vstupenky jsou nyní VYPNUTÉ') . ', ' . $cenaText . '.');
    redirect_admin('reservation/');
}

$row      = $db->query('SELECT is_active, escort_price FROM reservation_status ORDER BY id LIMIT 1')->fetch();
$isActive = (int)($row['is_active'] ?? 0) === 1;
$price    = max(0, (int)($row['escort_price'] ?? 250));

$pageTitle = 'Vstupenky';
$activeNav = 'reservation';
require __DIR__ . '/../lib/layout_top.php';
?>
<div class="page-head">
    <div>
        <h1>Vstupenky</h1>
        <p class="page-intro" style="margin:0">Zapnutí nebo vypnutí rezervací vstupenek a cena vstupenky pro doprovod. Když jsou rezervace vypnuté, na webu se místo formuláře zobrazí informace, že rezervace nejsou aktuálně dostupné.</p>
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

    <hr class="form-divider">

    <div class="form-row">
        <p class="current-value">
            <strong>Aktuální cena pro doprovod:</strong>
            <span class="state-badge <?= $price > 0 ? 'state-on' : 'state-off' ?>">
                <?= $price > 0 ? e(number_format($price, 0, ',', ' ')) . ' Kč' : 'ZDARMA' ?>
            </span>
        </p>

        <label for="escort_price">
            Cena za jednu vstupenku pro doprovod
            <span class="hint">v celých korunách; <strong>0 = zdarma</strong>. Hráčů a trenérů se cena netýká, ti mají vstup vždy zdarma.</span>
        </label>
        <input type="number" id="escort_price" name="escort_price" class="input-big"
               min="0" max="<?= SAFE_ESCORT_PRICE_MAX ?>" step="1" inputmode="numeric"
               value="<?= e((string)$price) ?>">
    </div>

    <button type="submit" class="saveButton"><i class='bx bx-save'></i> Uložit nastavení</button>
</form>
<?php require __DIR__ . '/../lib/layout_bottom.php'; ?>
