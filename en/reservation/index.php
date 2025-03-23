<?php
include '../../Skeleton/sendmail.php';

// Získání aktuálního data
$currentDate = new DateTime();
$startDate = new DateTime('2024-11-01');
$endDate = new DateTime('2025-09-01');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SAFE | Tickets Reservation</title>
    </head>
    <?php include '../../skeleton/head.php'?>
    <body class="ticketspage-body">
    <?php include '../../skeleton/header-en.php'?>
        <div class="mobile-translate">
            <a href="../../rezervace/">CZ/EN</a>
        </div>
        <?php include '../../skeleton/navbar-en.php'?>
            <a href="../../rezervace/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="contact-container mobile-contact">
        <?php
            // Zkontrolujeme, zda je datum mezi 1.11.2024 a 1.9.2025
            if ($currentDate >= $startDate && $currentDate < $endDate) {
                echo '<div class="ticktes-end">Ticket reservations for the <span class="gold">SAFE 2024</span> event are now over. Tickets for the <span class="gold">SAFE 2025</span> event will be available for reservation <span class="gold">1.9.2025</span>.</div>';
            } else {
                // Zobrazí se formulář pro rezervaci
            ?>
        <div class="contact-box">
            <div class="left">
                <img src="../../images/invation.png"></img>
            </div>
            <div class="right cz-right">
                <h1>Tickets reservation</h1>
                <form class="contact-form" method="post" autocomplete="off">
                <?php  
                $existing_codes = array();

                // Kontrola, zda byl formulář odeslán
                if(!empty($_POST["send"])) {
                    // Získání dat z formuláře
                    $subject = "Ticket reservation confirmation";
                    $email = $_POST["email"];
                    $name = $_POST["jméno"];
                    $count = $_POST["doprovod"];

                    // Inicializace proměnných pro jméno a kategorii dítěte
                    $childrenInfo = "";

                    foreach ($_POST as $key => $value) {
                        if (strpos($key, 'jméno-hráč') !== false) {
                            // Použití regulárního výrazu pro získání indexu
                            if (preg_match('/jméno-hráč(\d+)/', $key, $matches)) {
                                $childIndex = $matches[1];
                                $childName = $value;
                                $categoryKey = 'kategorie' . $childIndex;

                                // Zkontrolujte, zda existuje klíč pro kategorii dítěte
                                if (isset($_POST[$categoryKey])) {
                                    $childAge = $_POST[$categoryKey];

                                    if (!empty($childAge)) {
                                        $childrenInfo .= "<li><strong>Players name:</strong> $childName, <strong>Category:</strong> $childAge</li>";
                                    } else {
                                        $childrenInfo .= "<li><strong>Players name:</strong> $childName, <strong>Category:</strong> (not filled)</li>";
                                    }
                                } else {
                                    // Zpracování, pokud klíč neexistuje, například nastavte výchozí hodnotu nebo vynechejte tento záznam
                                    $childrenInfo .= "<li><strong>Players name:</strong> $childName, <strong>Category:</strong> (not filled)</li>";
                                }
                            }
                        }
                    }

                    $escortPrice = $count * 150;

                    // URL pro SheetDB API
                    $api_url = 'https://sheetdb.io/api/v1/r5qf0v0bpe8gu';

                    // Vytvoř nový HTTP GET požadavek pro kontrolu duplicity e-mailu
                    $check_url = $api_url . '/search?email=' . urlencode($email); // Vytvoř URL pro kontrolu e-mailu v databázi
                    $check_ch = curl_init($check_url);
                    curl_setopt($check_ch, CURLOPT_RETURNTRANSFER, true);
                    $check_response = curl_exec($check_ch);
                    curl_close($check_ch);

                    // Zkontroluj, zda e-mail již existuje v databázi
                    if ($check_response !== false) {
                        $existing_entries = json_decode($check_response, true);
                        $email_exists = false;
                        
                        // Kontrola, zda existuje záznam s e-mailem a stavem rezervace 'platí'
                        foreach ($existing_entries as $entry) {
                            if ($entry['email'] === $email && $entry['rezervace'] === 'platí') {
                                $email_exists = true;
                                break;
                            }
                        }

                        if ($email_exists) {
                            // Pokud e-mail již existuje s platnou rezervací, zobraz chybu
                            echo '<div class="alert-failed">This email has already been used for a reservation!</div>';
                        } else {
                            // Pokud e-mail neexistuje s platnou rezervací, proved zbytek kódu
                            // Generování náhodného devítimístného kódu
                            do {
                                $code = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(9/strlen($x)) )),1,9);
                            } while (in_array($code, $existing_codes)); // Opakuj generování kódu, pokud již existuje v seznamu

                            // Data, která chceš zapsat do SheetDB
                            $data = array(
                                'email' => $email,
                                'jméno' => $name,
                                'doprovod' => $count,
                                'cena' => $escortPrice,
                                'jméno hráče/trenéra 1' => isset($_POST['jméno-hráč1']) ? $_POST['jméno-hráč1'] : '',
                                'kategorie 1' => isset($_POST['kategorie1']) ? $_POST['kategorie1'] : '',
                                'jméno hráče/trenéra 2' => isset($_POST['jméno-hráč2']) ? $_POST['jméno-hráč2'] : '',
                                'kategorie 2' => isset($_POST['kategorie2']) ? $_POST['kategorie2'] : '',
                                'jméno hráče/trenéra 3' => isset($_POST['jméno-hráč3']) ? $_POST['jméno-hráč3'] : '',
                                'kategorie 3' => isset($_POST['kategorie3']) ? $_POST['kategorie3'] : '',
                                'kód rezervace' => $code, // Přidej generovaný kód do dat pro zápis do SheetDB
                                'rezervace' => 'platí' // Nastav hodnotu sloupce rezervace na "platí"
                            );

                            // Vytvoř nový HTTP POST požadavek pro zápis dat do SheetDB
                            $ch = curl_init($api_url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

                            // Odešli požadavek a získaj odpověď
                            $response = curl_exec($ch);


                                            // Zkontroluj, zda bylo zapsání úspěšné
                                            if ($response !== false) {
                                                // Odešli e-mail
                                                $toUserSubject = mb_encode_mimeheader($subject, "UTF-8", "Q");
                                                $toUserHeaders = "From: no-reply\r\n";
                                                $toUserHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";

                                                $toUserMessage = "
                                                <html>
                                                <head>
                                                    <style>
                                                        /* Styly pro text ve zprávě */
                                                        body {
                                                            font-family: 'Poppins', sans-serif; /* Příklad nastavení fontu */
                                                            color: #333; /* Příklad nastavení barvy textu */
                                                        }
                                                        p, ul, img {
                                                            margin-left: 15px;
                                                            margin-right: 15px;
                                                        }
                                                        .first-content {
                                                            padding-top: 15px;
                                                        }
                                                        img {
                                                            width: 100%;
                                                            max-width: 300px;
                                                        }
                                                    </style>
                                                </head>
                                                <body>
                                                    <p class='first-content'>Thank you for booking tickets to this year's SAFE event.</p>
                                                    <p>Information:</p>
                                                    <ul>
                                                        <li><strong>Your reservation code is:</strong> $code</li>
                                                        <li><strong>Number of entourage:</strong> $count</li>
                                                        <li><strong>Price for tickets:</strong> $escortPrice Kč</li>
                                                        <li><strong>Name:</strong> $name</li>
                                                        <li><strong>Email:</strong> $email</li>
                                                    </ul>";

                                                     // Přidejte podmínku pro zobrazení nadpisu "Informace o hráčích" pouze pokud existují informace o hráčích
                                                    if (!empty($childrenInfo)) {
                                                        $toUserMessage .= "
                                                        <p>Information about players:</p>
                                                        <ul>
                                                            $childrenInfo
                                                        </ul>";
                                                    }

                                                    $toUserMessage .= "
                                                    <p>The event takes place on November 9, 2024 from 6:00 p.m. in the Sokol cinema at <a target='_blank' href='https://www.google.com/maps/place//data=!4m2!3m1!1s0x470bb7da602a6c4b:0xe7204d94c85ab6b1?sa=X&ved=1t:8290&ictx=111'>T. G. Masaryka 2320, 272 01 Kladno 1</a></p>
                                                    <p>You can pick up and pay for tickets at the Family Day event on October 19, you will be informed about other possible dates by email.</p>
                                                    <p>We look forward to seeing you!</p>
                                                    <p>Click <a target='_blank' href='https://safe.minerskladno.cz/cancel-en.php'>here</a> if you want to cancel the reservation</p>
                                                    <p>note this message is automatic, please do not reply to this email.<br>
                                                        In case of any questions or ambiguities, you can contact us via our form
                                                        <a href='https://safe.minerskladno.cz/contact/'>here</a> or by email <a href='mailto:safe@minerskladno.cz' target='_blank'>safe@minerskladno.cz</a></p>
                                                    <img src='https://safe.minerskladno.cz/images/SAFE-logo.png' alt='Safe logo'>
                                                </body>
                                                </html>
                                                ";

                                                if(sendEmail($email, $toUserSubject, $toUserMessage)) {
                                                    echo '<center><div class="alert-success">Your email has been sent successfully.</div></center>';
                                                } else {
                                                    echo '<center><div class="alert-failed">Error sending email!</div></center>';
                                                }
                                            } else {
                                                echo '<div class="alert-failed">Error sending email!</div>';
                                            }

                                            // Uzavření curl spojení
                                            curl_close($ch);
                                        }
                                    } else {
                                        // Pokud selže kontrola duplicity, zobraz chybu a ukonči skript
                                        echo '<div class="alert-failed">Error sending email!!</div>';
                                    }
                                }
                ?>
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
                        <!-- Sem se přidají pole pro děti -->
                    </div>
                    <div class="counter counter-escort">
                        <div class="info-container">
                            <i class='info-btn bx bx-info-circle'></i>
                            <div class="info-popup">
                            Due to the capacity of the hall, 1 accompanying ticket (1+1) is released for each player/coach in the first wave of orders. In the form, indicate the total number of tickets requested for the accompaniment, including those requested in addition.
                            </div>
                        </div>
                        <h2>ACCOMPANIMENT</h2>
                        <div class="tickets">
                            <label for="escort">Number of tickets:</label>
                            <input class="counter-field" type="number" id="escort" name="doprovod" min="0" max="5" placeholder="0" value="0" required>
                        </div>
                        <div class="price">
                            <p id="escortPrice"><strong>Price:</strong> 0 Kč</p>
                        </div>
                    </div>
                    <input type="text" name="jméno" placeholder="Your full name*" class="tickets-field" required>
                    <input type="email" name="email" class="tickets-field last-field" placeholder="Your email adress*" required>
                    <input type="submit" value="Send" name="send" id="button" class="btn">
                </form>
                <p>To cancel your reservation, click <a href="cancel/">here</a>.</p>
            </div>
            <?php
            }
            ?>
        </div>
    </main>
            <?php include '../../skeleton/footer-en.php'?>
            <?php include '../../skeleton/toTop.php' ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var addChildButton = document.getElementById("addChildButton");
                var playersFields = document.getElementById("playersFields");
                var childCount = 0;
                var maxPlayers = 3;

                addChildButton.addEventListener("click", function() {
                    if (childCount >= maxPlayers) {
                        alert("You have reached the maximum number of players!");
                        addChildButton.classList.add("disabled-button");
                    } else {
                        childCount++;
                        var inputName = "jméno-hráč" + childCount;
                        var selectName = "kategorie" + childCount;

                        var input = document.createElement("input");
                        input.type = "text";
                        input.name = inputName;
                        input.required = true;
                        input.placeholder = "Full name of player/trainer*";
                        input.classList.add("tickets-field"); // Přidá třídu pro přizpůsobení stylu v CSS
                        
                        var select = document.createElement("select");
                        select.name = selectName;
                        select.required = true;
                        
                        // Vytvoření první možnosti s textem "Kategorie*" a nastavení jako vybrané a neaktivní
                        var defaultOption = document.createElement("option");
                        defaultOption.value = "";
                        defaultOption.text = "Category*";
                        defaultOption.selected = true;
                        defaultOption.disabled = true;
                        select.appendChild(defaultOption);

                        var ages = ["U5", "U7", "U9", "U11", "U11s", "U13", "U13s", "U15", "U16s", "U18", "U18s", "WOMEN", "WOMEN B", "MEN"];
                        ages.forEach(function(age) {
                            var option = document.createElement("option");
                            option.value = age;
                            option.text = age;
                            option.classList.add("option-class");
                            select.appendChild(option);
                        });
                        select.classList.add("custom-select"); // Přidá třídu pro přizpůsobení stylu v CSS

                        var removeButton = document.createElement("button");
                        removeButton.type = "button";
                        removeButton.classList.add("remove-button");
                        var icon = document.createElement("i");
                        icon.classList.add("fa-solid", "fa-xmark", "delete-icon"); // Třídy pro ikonu křížku z Font Awesome
                        removeButton.appendChild(icon);
                        removeButton.addEventListener("click", function() {
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
                    }
                });

                var escortInput = document.getElementById("escort");
                escortInput.addEventListener("input", function() {
                    var escortCount = parseInt(escortInput.value);
                    
                    // Pokud escortCount není číslo, nastav hodnotu na 0
                    if (isNaN(escortCount)) {
                        escortCount = 0;
                    }
                    
                    var price = escortCount * 150; // Cena je 150 Kč za kus
                    var priceElement = document.getElementById("escortPrice");
                    priceElement.innerHTML = "<strong>Price:</strong> " + price + " Kč";
                });
            });
                    
        </script>

    </body>
</html>