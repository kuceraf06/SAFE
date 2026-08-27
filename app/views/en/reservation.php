<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';
require_once __DIR__ . '/../../lib/reservation.php';

$pageTitle = 'SAFE | Tickets';
$bodyClass = 'ticketspage-body';
$pageCss   = 'reservation';

$isReservationActive = safe_reservation_active();
$escortPrice         = safe_escort_price();   // ticket price from the administration (0 = free)

// processing of the submitted form (writes to the table + confirmation e-mail)
$res = $isReservationActive ? safe_handle_reservation('en') : ['message' => '', 'class' => ''];
?>
<div class="heading">
        <h1>TICKETS RESERVATION</h1>
    </div>
    <?php if ($isReservationActive): ?>
    <div class="tutorialHeader">
        <i>How to do it? Click <a href="#tutorial" class="gold">here</a></i>
    </div>
    <main class="contact-container mobile-contact page-width">
        <div class="contact-box">
            <?php $obrazek = safe_invitation() ?: 'images/common/default.png'; ?>
            <div class="left">
                <img src="<?= asset($obrazek) ?>" alt="Invitation">
            </div>
            <div class="right cz-right">
                <form class="contact-form" method="post" autocomplete="off">
                    <?= safe_antispam_fields() ?>
                <?php if ($res['message']): ?>
                    <div class="<?= e($res['class']) ?>"><?= e($res['message']) ?></div>
                <?php endif; ?>
                    <div class="counter counter-player">
                        <h2>PLAYER/COACH</h2>
                        <div class="tickets">
                            <button class="player-field" type="button" id="addChildButton">Add</button>
                            <div class="price">
                                <p><strong>Price:</strong> Free</p>
                            </div>
                        </div>
                    </div>
                    <div id="playersFields">
                    </div>
                    <div class="counter counter-escort">
                        <div class="info-container">
                            <i class='info-btn bx bx-info-circle'></i>
                            <div class="info-popup">
                            An accompanist is anyone outside the Miners organization (players, coaches, club management).
                            </div>
                        </div>
                        <h2>ACCOMPANIMENT</h2>
                        <div class="tickets">
                            <label for="escort">Number of tickets:</label>
                            <input class="counter-field" type="number" id="escort" name="doprovod" min="0" max="5" placeholder="0" value="0" required>
                        </div>
                        <div class="price">
                            <p id="escortPrice"><strong>Price:</strong> <?= $escortPrice > 0 ? '0 Kč' : 'Free' ?></p>
                        </div>
                    </div>
                    <input type="text" name="jméno" placeholder="Your full name*" class="tickets-field" required>
                    <input type="email" name="email" class="tickets-field last-field" placeholder="Your email adress*" required>
                    <input type="submit" value="Send" name="send" id="button" class="btn">
                </form>
                <p>To cancel your reservation, click <a href="<?= url('reservation/cancel') ?>">here</a>.</p>
            </div>
        </div>
    </main>
    <div class="tutorial" id="tutorial">
        <p><span>Option 1 - </span>I am a player/coach without an escort<br />
        <p class="main-text">Click the "Add" button, select your main category, and fill in your full name. Fill in your name and email address again at the bottom. Submit the form.
        </p></p>
        <p class="main-text"><i>For example, I am a player/coach named Josef Procházka from U15. I will add one player/coach, select the U15 category, and enter the name Josef Procházka. At the bottom, I will fill in my name and email address again and submit the form.</i></p>
        <br />
        <p><span>Option 2 - </span>I am a player/coach with an accompanying person<br />
        <p class="main-text">Click the "Add" button, select your main category, and fill in your full name. In the Accompanying Person field, fill in the number of tickets you want (maximum 5). Fill in your name and email address again at the bottom. Submit the form.
        </p></p>
        <p class="main-text"><i>For example, I am a player/coach named Petr Novotný from U13 and I want to order a ticket for my parents. I will add one player/coach, select the U13 category, and enter the name Petr Novotný. I will enter 1 ticket in the Accompanying Persons field. If I want to add a second parent, the number will be 2, etc. I will then fill in my name and email address again and submit the form.</i></p>
        <br />
        <p><span>Option 3 - </span> I am an accompanying person, ordering for myself and a player
        <p class="main-text">An accompanying person is anyone who is not a player or coach. This could be a parent, grandparent, or other family member.
        </p></p>
        <p>Click the "Add" button, select the main category of the player/coach, and enter their full name. In the Accompanying Persons field, enter the number of tickets required (maximum 5). At the bottom, enter your name and email address. Submit the form.</p>
        <p class="main-text"><i>For example, I am the parent of Jan Novák from U9. I will add one player, select the U9 category, and enter the name Jan Novák. I will enter 1 ticket in the Accompanying Persons field. If I want to add a second parent, the number will be 2. If I have two children playing, I will add another player (same procedure as for the first). Next, I will fill in my name and email address and submit the form.</i></p>
    </div>
    <?php else: ?>
    <main class="contact-container mobile-contact page-width">
        <div class="ticktes-end">Ticket reservations for the <span class="gold">SAFE</span> event are now over. The reservation date for the next year's <span class="gold">SAFE</span> event will be announced later.</div>
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
                    alert("You have reached the maximum number of players/coaches (3).");
                    addChildButton.classList.add("disabled-button");
                    return;
                }
                childCount++;
                var input = document.createElement("input");
                input.type = "text";
                input.name = "jméno-hráč" + childCount;
                input.required = true;
                input.placeholder = "Full name of the player/coach*";
                input.classList.add("tickets-field");

                var select = document.createElement("select");
                select.name = "kategorie" + childCount;
                select.required = true;

                var defaultOption = document.createElement("option");
                defaultOption.value = "";
                defaultOption.text = "Category*";
                defaultOption.selected = true;
                defaultOption.disabled = true;
                select.appendChild(defaultOption);

                var ages = ["U5", "U6", "U7", "U9", "U11", "U11s", "U13", "U13s", "U15", "U16s", "WOMEN", "MEN"];
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

        // price per one accompaniment ticket, set in the administration (0 = free)
        var escortUnitPrice = <?= (int)$escortPrice ?>;

        var escortInput = document.getElementById("escort");
        if (escortInput) {
            escortInput.addEventListener("input", function () {
                var escortCount = parseInt(escortInput.value);
                if (isNaN(escortCount)) { escortCount = 0; }
                var price = escortCount * escortUnitPrice;
                var priceElement = document.getElementById("escortPrice");
                if (priceElement) {
                    priceElement.innerHTML = "<strong>Price:</strong> " + (escortUnitPrice > 0 ? price + " CZK" : "Free");
                }
            });
        }
    });
</script>
<?php endif; ?>
