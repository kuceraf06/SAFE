<?php
include '../skeleton/sendmail.php';

// Získání aktuálního data
$currentDate = new DateTime();
$startDate = new DateTime('2024-11-01');
$endDate = new DateTime('2025-06-01');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <title>SAFE | Rezervace Vstupenek</title>
    <?php include '../skeleton/head.php'?>
</head>
<body class="ticketspage-body">
    <?php include '../skeleton/header.php'?>
    <div class="mobile-translate">
        <a href="../en/reservation/">CZ/EN</a>
    </div>
    <?php include '../skeleton/navbar.php'?>
        <a href="../en/reservation/" class="before pc-translate">CZ/EN</a>
        </nav>
    </header>
    <main class="contact-container mobile-contact">
        <?php
            // Zkontrolujeme, zda je datum mezi 1.11.2024 a 1.9.2025
            if ($currentDate >= $startDate && $currentDate < $endDate) {
                echo '<div class="ticktes-end">Rezervace vstupenek na akci <span class="gold">SAFE 2024</span> jsou již u konce. Vstupenky na akci <span class="gold">SAFE 2025</span> budou dostupné k rezervaci <span class="gold">1.9.2025</span>.</div>';
            } else {
                // Zobrazí se formulář pro rezervaci
            ?>
        <div class="contact-box">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "mywebsite"; // nebo jak se tvá databáze jmenuje

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Chyba připojení: " . $conn->connect_error);
            }

            $sql = "SELECT obrazek FROM invation WHERE id = 1";
            $result = $conn->query($sql);

            $obrazek = 'images/default.png'; // fallback
            if ($result && $row = $result->fetch_assoc()) {
                $obrazek = 'images/' . $row['obrazek'];
            }
            ?>
            <div class="left">
                <img src="../<?php echo $obrazek; ?>" alt="Úvodní obrázek">
            </div>
            <div class="right cz-right">
                <h1>Rezervace vstupenek</h1>
                <form class="contact-form" method="post" autocomplete="off">
                <?php  
                $existing_codes = array();

                // Kontrola, zda byl formulář odeslán
                if(!empty($_POST["send"])) {
                    // Získání dat z formuláře
                    $subject = "Potvrzení rezervace lístků";
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
                                        $childrenInfo .= "<li><strong>Jméno hráče:</strong> $childName, <strong>Kategorie:</strong> $childAge</li>";
                                    } else {
                                        $childrenInfo .= "<li><strong>Jméno hráče:</strong> $childName, <strong>Kategorie:</strong> (není vyplněno)</li>";
                                    }
                                } else {
                                    // Zpracování, pokud klíč neexistuje, například nastavte výchozí hodnotu nebo vynechejte tento záznam
                                    $childrenInfo .= "<li><strong>Jméno hráče:</strong> $childName, <strong>Kategorie:</strong> (není vyplněno)</li>";
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
                            echo '<div class="alert-failed">Tento e-mail již byl použit pro rezervaci!</div>';
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
                                                    <p class='first-content'>Děkujeme, že jste si rezervovali vstupenky na letošní akci SAFE.</p>
                                                    <p>Informace:</p>
                                                    <ul>
                                                        <li><strong>Váš rezervační kód:</strong> $code</li>
                                                        <li><strong>Počet vstupenek pro doprovod:</strong> $count</li>
                                                        <li><strong>Cena:</strong> $escortPrice Kč</li>
                                                        <li><strong>Jméno:</strong> $name</li>
                                                        <li><strong>Email:</strong> $email</li>
                                                    </ul>";
                                                
                                                // Přidejte podmínku pro zobrazení nadpisu "Informace o hráčích" pouze pokud existují informace o hráčích
                                                if (!empty($childrenInfo)) {
                                                    $toUserMessage .= "
                                                    <p>Informace o hráčích:</p>
                                                    <ul>
                                                        $childrenInfo
                                                    </ul>";
                                                }
                                                
                                                $toUserMessage .= "
                                                    <p>Akce se koná 9. listopadu 2024 od 18:00 v kině Sokol na adrese <a target='_blank' href='https://www.google.com/maps/place//data=!4m2!3m1!1s0x470bb7da602a6c4b:0xe7204d94c85ab6b1?sa=X&ved=1t:8290&ictx=111'>T. G. Masaryka 2320, 272 01 Kladno 1</a></p>
                                                    <p>Vstupenky si můžete vyzvednout a uhradit na akci Family Day 19. října, o dalších možných termínech budete informování emailem.</p>
                                                    <p>Těšíme se na Vás!</p>
                                                    <p>Pokud chcete rezervaci zrušit klikněte <a target='_blank' href='https://safe.minerskladno.cz/cancel.php'>zde</a></p>
                                                    <p>pozn. tato zpráva je automatická prosím neodpovídejte na tento email.<br>
                                                        V případě jakýchkoliv otázek či nejasností nás můžete kontaktovat přes formulář 
                                                        <a href='https://safe.minerskladno.cz/kontakt/'>zde</a> či na emailu <a href='mailto:safe@minerskladno.cz' target='_blank'>safe@minerskladno.cz</a></p>
                                                    <img src='https://safe.minerskladno.cz/images/SAFE-logo.png' alt='Safe logo'>
                                                </body>
                                                </html>
                                                ";

                                                if(sendEmail($email, $toUserSubject, $toUserMessage)) {
                                                    echo '<center><div class="alert-success">Váš email byl úspěšně odeslán.</div></center>';
                                                } else {
                                                    echo '<center><div class="alert-failed">Chyba při odeslání emailu!</div></center>';
                                                }
                                            } else {
                                                echo '<div class="alert-failed">Chyba při odeslání emailu!</div>';
                                            }

                                            // Uzavření curl spojení
                                            curl_close($ch);
                                        }
                                    } else {
                                        // Pokud selže kontrola duplicity, zobraz chybu a ukonči skript
                                        echo '<div class="alert-failed">Chyba při odeslání emailu!</div>';
                                    }
                                }
                ?>

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
                        <!-- Sem se přidají pole pro děti -->
                    </div>
                    <div class="counter counter-escort">
                        <div class="info-container">
                            <i class='info-btn bx bx-info-circle'></i>
                            <div class="info-popup">
                            Vzhledem ke kapacitě sálu je v první vlně objednávek uvolněna na každého hráče/trenéra 1 vstupenka pro doprovod (1+1). Ve formuláři uveďte celkový požadovaný počet vstupenek pro doprovod včetně těch požadovaných navíc.
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
                <p>Pokud chcete vaši rezervaci zrušit klikněte <a href="zruseni/">zde</a>.</p>
            </div>
            <?php
            }
            ?>
        </div>
    </main>
    <?php include '../skeleton/footer.php'?>
    <?php include '../skeleton/toTop.php' ?>
    <script src="../javascript/main.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var addChildButton = document.getElementById("addChildButton");
            var playersFields = document.getElementById("playersFields");
            var childCount = 0;
            var maxPlayers = 3;

                    addChildButton.addEventListener("click", function() {
                    if (childCount >= maxPlayers) {
                        alert("Dosáhli jste maximálního počtu hráčů!");
                        addChildButton.classList.add("disabled-button");
                    } else {
                        childCount++;
                        var inputName = "jméno-hráč" + childCount;
                        var selectName = "kategorie" + childCount;

                        var input = document.createElement("input");
                        input.type = "text";
                        input.name = inputName;
                        input.required = true;
                        input.placeholder = "Celé jméno hráče(ky)/trenéra(ky)*";
                        input.classList.add("tickets-field"); // Přidá třídu pro přizpůsobení stylu v CSS
                        
                        var select = document.createElement("select");
                        select.name = selectName;
                        select.required = true;
                        
                        // Vytvoření první možnosti s textem "Kategorie*" a nastavení jako vybrané a neaktivní
                        var defaultOption = document.createElement("option");
                        defaultOption.value = "";
                        defaultOption.text = "Kategorie*";
                        defaultOption.selected = true;
                        defaultOption.disabled = true;
                        select.appendChild(defaultOption);

                        var ages = ["U5", "U7", "U9", "U11", "U11s", "U13", "U13s", "U15", "U16s", "U18", "U18s", "ŽENY", "ŽENY B", "MUŽI"];
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
                    priceElement.innerHTML = "<strong>Cena:</strong> " + price + " Kč";
                });
            });

        </script>

</body>
</html>
