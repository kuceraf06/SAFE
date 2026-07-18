<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';
require_once __DIR__ . '/../../lib/reservation.php';

$pageTitle = 'SAFE | Vstupenky';
$bodyClass = 'ticketspage-body';
$pageCss   = 'reservation';

$isReservationActive = safe_reservation_active();

// zpracování odeslaného formuláře (zápis do tabulky + potvrzovací e-mail)
$res = $isReservationActive ? safe_handle_reservation('cs') : ['message' => '', 'class' => ''];
?>
<div class="heading">
        <h1>REZERVACE VSTUPENEK</h1>
    </div>
    <?php if ($isReservationActive): ?>
    <div class="tutorialHeader">
        <i>Jak na to? Klikni <a href="#tutorial" class="gold">zde</a></i>
    </div>
    <main class="contact-container mobile-contact page-width">
        <div class="contact-box">
            <?php $obrazek = safe_invitation() ?: 'images/common/default.png'; ?>
            <div class="left">
                <img src="<?= asset($obrazek) ?>" alt="Úvodní obrázek">
            </div>
            <div class="right cz-right">
                <form class="contact-form" method="post" autocomplete="off">
                    <?= safe_antispam_fields() ?>
                <?php if ($res['message']): ?>
                    <div class="<?= e($res['class']) ?>"><?= e($res['message']) ?></div>
                <?php endif; ?>

                    <div class="counter counter-player">
                        <h2>HRÁČ/TRENÉR</h2>
                        <div class="tickets">
                            <button class="player-field" type="button" id="addChildButton">Přidat</button>
                            <div class="price">
                                <p><strong>Cena:</strong> Zdarma</p>
                            </div>
                        </div>
                    </div>
                    <div id="playersFields">
                    </div>
                    <div class="counter counter-escort">
                        <div class="info-container">
                            <i class='info-btn bx bx-info-circle'></i>
                            <div class="info-popup">
                            Doprovod je kdokoli mimo organizaci Miners (hráč, trenér, vedení).
                            </div>
                        </div>
                        <h2>DOPROVOD</h2>
                        <div class="tickets">
                            <label for="escort">Počet lístků:</label>
                            <input class="counter-field" type="number" id="escort" name="doprovod" min="0" max="5" placeholder="0" value="0" required>
                        </div>
                        <div class="price">
                            <p id="escortPrice"><strong>Cena:</strong> 0 Kč</p>
                        </div>
                    </div>
                    <input type="text" name="jméno" placeholder="Vaše celé jméno*" class="tickets-field" required>
                    <input type="email" name="email" class="tickets-field last-field" placeholder="Vaše email adresa*" required>
                    <input type="submit" value="Odeslat" name="send" id="button" class="btn">
                </form>
                <p>Pokud chcete vaši rezervaci zrušit klikněte <a href="<?= url('reservation/cancel') ?>">zde</a>.</p>
            </div>
        </div>
    </main>
    <div class="tutorial" id="tutorial">
        <p><span>Varianta 1 - </span>jsem hráč/trenér bez doprovodu<br />
        <p class="main-text">Zaklikni tlačítko "Přidat", vyber svou hlavní kategorii a vyplň celé jméno. Dole vyplň znovu své jméno a e-mailovou adresu. Odešli formulář.
        </p></p>
        <p class="main-text"><i>Například jsem hráč/trenér Josef Procházka z U15. Přidám jednoho hráče/trenéra, vyberu kategorii U15, zadám jméno Josef Procházka. Dole vyplním znovu své jméno a e-mailovou adresu a odešlu formulář.</i></p>
        <br />
        <p><span>Varianta 2 - </span>jsem hráč/trenér s doprovodem<br />
        <p class="main-text">Zaklikni tlačítko "Přidat", vyber svou hlavní kategorii a vyplň celé jméno. V kolonce Doprovod vyplň počet požadovaných lístků (maximálně 5). Dole vyplň znovu své jméno a e-mailovou adresu. Odešli formulář.
        </p></p>
        <p class="main-text"><i>Například jsem hráč/trenér Petr Novotný z U13 a chci objednat lístek i pro rodiče. Přidám jednoho hráče/trenéra, vyberu kategorii U13, zadám jméno Petr Novotný. K doprovodu zadám počet lístků 1. Pokud chci přidat druhého rodiče, počet bude 2 apod. Déle vyplním znovu své jméno a e-mailovou adresu a odešlu formulář.</i></p>
        <br />
        <p><span>Varianta 3 - </span>jsem doprovod, objednávám pro sebe a hráče
        <p class="main-text">Doprovodem nazýváme všechny, kdo nejsou hráči a trenéry. Můžeš tedy být například rodič, prarodič nebo jiný rodinný příslušník. 
        </p></p>
        <p>Zaklikni tlačítko "Přidat", vyber hlavní kategorii hráče/trenéra a vyplň jeho celé jméno. V kolonce Doprovod vyplň počet požadovaných lístků (maximálně 5). Dole vyplň své jméno a e-mailovou adresu. Odešli formulář.</p>
        <p class="main-text"><i>Například jsem rodič Honzy Nováka z U9. Přidám jednoho hráče, vyberu kategorii U9, zadám jméno Jan Novák. K doprovodu zadám počet lístků 1. Pokud chci přidat druhého rodiče, počet bude 2. Pokud mám dvě hrající děti, přidám ještě jednoho hráče (postup stejný jako u prvního). Dále vyplním své jméno a e-mailovou adresu a odešlu formulář.</i></p>
    </div>
    <?php else: ?>
    <main class="contact-container mobile-contact page-width">
        <div class="ticktes-end">Rezervace vstupenek na akci <span class="gold">SAFE</span> jsou již u konce. Termín na rezervace pro další ročník akce <span class="gold">SAFE</span> bude ještě upřesněn.</div>
    </main>
    <?php endif; ?>

<?php if ($isReservationActive): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var addChildButton = document.getElementById("addChildButton");
        var playersFields = document.getElementById("playersFields");
        var childCount = 0;
        var maxPlayers = 3;

        if (addChildButton) {
            addChildButton.addEventListener("click", function () {
                if (childCount >= maxPlayers) {
                    alert("Dosáhli jste maximálního počtu hráčů/trenérů (3).");
                    addChildButton.classList.add("disabled-button");
                    return;
                }
                childCount++;
                var inputName = "jméno-hráč" + childCount;
                var selectName = "kategorie" + childCount;

                var input = document.createElement("input");
                input.type = "text";
                input.name = inputName;
                input.required = true;
                input.placeholder = "Celé jméno hráče(ky)/trenéra(ky)*";
                input.classList.add("tickets-field");

                var select = document.createElement("select");
                select.name = selectName;
                select.required = true;

                var defaultOption = document.createElement("option");
                defaultOption.value = "";
                defaultOption.text = "Kategorie*";
                defaultOption.selected = true;
                defaultOption.disabled = true;
                select.appendChild(defaultOption);

                var ages = ["U5", "U6", "U7", "U9", "U11", "U11s", "U13", "U13s", "U15", "U16s", "ŽENY", "MUŽI"];
                ages.forEach(function (age) {
                    var option = document.createElement("option");
                    option.value = age;
                    option.text = age;
                    select.appendChild(option);
                });
                select.classList.add("custom-select");

                var removeButton = document.createElement("button");
                removeButton.type = "button";
                removeButton.classList.add("remove-button");
                var icon = document.createElement("i");
                icon.classList.add("fa-solid", "fa-xmark", "delete-icon");
                removeButton.appendChild(icon);
                removeButton.addEventListener("click", function () {
                    playersFields.removeChild(input);
                    playersFields.removeChild(select);
                    playersFields.removeChild(removeButton);
                    childCount--;
                    addChildButton.classList.remove("disabled-button");
                });

                playersFields.appendChild(removeButton);
                playersFields.appendChild(select);
                playersFields.appendChild(input);

                if (childCount >= maxPlayers) {
                    addChildButton.classList.add("disabled-button");
                }
            });
        }

        var escortInput = document.getElementById("escort");
        if (escortInput) {
            escortInput.addEventListener("input", function () {
                var escortCount = parseInt(escortInput.value);
                if (isNaN(escortCount)) { escortCount = 0; }
                var price = escortCount * 250;
                var priceElement = document.getElementById("escortPrice");
                if (priceElement) {
                    priceElement.innerHTML = "<strong>Cena:</strong> " + price + " Kč";
                }
            });
        }
    });
</script>
<?php endif; ?>
