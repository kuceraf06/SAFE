<?php
include '../../Skeleton/sendmail.php';

include '../../skeleton/db_connect.php';

try {
    $stmt = $conn->query("SELECT is_active FROM reservation_status WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $isReservationActive = $row ? $row['is_active'] : 0;
} catch (PDOException $e) {
    die("Chyba při načítání stavu rezervace: " . $e->getMessage());
}
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
        <div class="heading">
            <h1>TICKETS RESERVATION</h1>
        </div>
        <?php if ($isReservationActive): ?>
        <main class="contact-container mobile-contact">
            <div class="contact-box">
            <?php
            try {
                $stmtImg = $conn->query("SELECT obrazek FROM invation WHERE id = 1");
                $rowImg = $stmtImg->fetch(PDO::FETCH_ASSOC);
                $obrazek = $rowImg ? 'images/' . $rowImg['obrazek'] : 'images/default.png';
            } catch (PDOException $e) {
                $obrazek = 'images/default.png';
            }
            ?>
            <div class="left">
                <img src="../../<?php echo $obrazek; ?>" alt="Úvodní obrázek">
            </div>
            <div class="right cz-right">
                <form class="contact-form" method="post" autocomplete="off">
                <?php  
                $existing_codes = array();

                if(!empty($_POST["send"])) {
                    $subject = "Ticket reservation confirmation";
                    $email = $_POST["email"];
                    $name = $_POST["jméno"];
                    $count = $_POST["doprovod"];

                    $childrenInfo = "";

                    foreach ($_POST as $key => $value) {
                        if (strpos($key, 'jméno-hráč') !== false) {
                            if (preg_match('/jméno-hráč(\d+)/', $key, $matches)) {
                                $childIndex = $matches[1];
                                $childName = $value;
                                $categoryKey = 'kategorie' . $childIndex;

                                if (isset($_POST[$categoryKey])) {
                                    $childAge = $_POST[$categoryKey];

                                    if (!empty($childAge)) {
                                        $childrenInfo .= "<li><strong>Players name:</strong> $childName, <strong>Category:</strong> $childAge</li>";
                                    } else {
                                        $childrenInfo .= "<li><strong>Players name:</strong> $childName, <strong>Category:</strong> (not filled)</li>";
                                    }
                                } else {
                                    $childrenInfo .= "<li><strong>Players name:</strong> $childName, <strong>Category:</strong> (not filled)</li>";
                                }
                            }
                        }
                    }

                    $escortPrice = $count * 250;

                    $api_url = 'https://sheetdb.io/api/v1/r5qf0v0bpe8gu';

                    $check_url = $api_url . '/search?email=' . urlencode($email);
                    $check_ch = curl_init($check_url);
                    curl_setopt($check_ch, CURLOPT_RETURNTRANSFER, true);
                    $check_response = curl_exec($check_ch);
                    curl_close($check_ch);

                    if ($check_response !== false) {
                        $existing_entries = json_decode($check_response, true);
                        $email_exists = false;
                        
                        foreach ($existing_entries as $entry) {
                            if ($entry['email'] === $email && $entry['rezervace'] === 'platí') {
                                $email_exists = true;
                                break;
                            }
                        }

                        if ($email_exists) {
                            echo '<div class="alert-failed">This email has already been used for a reservation!</div>';
                        } else {
                            do {
                                $code = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(9/strlen($x)) )),1,9);
                            } while (in_array($code, $existing_codes));

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
                                'kód rezervace' => $code,
                                'rezervace' => 'platí'
                            );

                            $ch = curl_init($api_url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

                            $response = curl_exec($ch);

                                            if ($response !== false) {
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
                                                    <p>Click <a target='_blank' href='https://safe.minerskladno.cz/cancel/'>here</a> if you want to cancel the reservation</p>
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
                                            curl_close($ch);
                                        }
                                    } else {
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
    <main class="contact-container mobile-contact">
        <div class="ticktes-end">Ticket reservations for the <span class="gold">SAFE</span> event are now over. The reservation date for the next year's <span class="gold">SAFE</span> event will be announced later.</div>
    </main>
    <?php endif; ?>
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
                        input.classList.add("tickets-field");
                        
                        var select = document.createElement("select");
                        select.name = selectName;
                        select.required = true;
                        
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
                        select.classList.add("custom-select");

                        var removeButton = document.createElement("button");
                        removeButton.type = "button";
                        removeButton.classList.add("remove-button");
                        var icon = document.createElement("i");
                        icon.classList.add("fa-solid", "fa-xmark", "delete-icon");
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
                    
                    if (isNaN(escortCount)) {
                        escortCount = 0;
                    }
                    
                    var price = escortCount * 250;
                    var priceElement = document.getElementById("escortPrice");
                    priceElement.innerHTML = "<strong>Price:</strong> " + price + " Kč";
                });
            });
                    
        </script>

    </body>
</html>