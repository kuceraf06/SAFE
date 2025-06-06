<?php
include '../../../skeleton/sendmail.php';

// Připojení k databázi
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mywebsite";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Chyba připojení: " . $conn->connect_error);
}

// Získání stavu rezervace (1 = aktivní, 0 = neaktivní)
$resStatus = $conn->query("SELECT is_active FROM reservation_status WHERE id = 1")->fetch_assoc();
$isReservationActive = $resStatus ? $resStatus['is_active'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SAFE | Cancel Reservation</title>
    </head>
    <?php include '../../../skeleton/head.php'?>
    <body class="cancelpage-body">
    <?php include '../../../skeleton/header-en.php'?>
        <div class="mobile-translate">
            <a href="../../../rezervace/zruseni/">CZ/EN</a>
        </div>
        <?php include '../../../skeleton/navbar-en.php'?>
            <a href="../../../rezervace/zruseni/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="cancel-container">
            <?php if ($isReservationActive): ?>
            <div class="remove-box">
                <div class="right cz-right">
                    <h1>Cancelling a reservation</h1>
                    <form class="contact-form" method="post" autocomplete="off">
                    <?php 
                        if(isset($_POST["send"])) {
                            $email = $_POST["email"];
                            $code = $_POST["code"];

                                                    // URL pro SheetDB API pro hledání záznamu podle e-mailu
                        $email_search_url = 'https://sheetdb.io/api/v1/r5qf0v0bpe8gu/search?email=' . urlencode($email);

                        // Získání odpovědi z API pro hledání e-mailu
                        $email_response = file_get_contents($email_search_url);

                        // Zpracování odpovědi pro hledání e-mailu
                        $email_existing_entry = json_decode($email_response, true);

                        // Procházení nalezených záznamů pro e-mail
                        if (!empty($email_existing_entry)) {
                            // Předpokládáme, že existuje pouze jeden odpovídající záznam
                            $email_entry = $email_existing_entry[0];

                            // Získání hodnoty sloupce "code" z nalezeného záznamu pro e-mail
                            $existing_code = $email_entry['kód rezervace'];
                            
                            // Získání hodnoty sloupce "rezervace" z nalezeného záznamu pro e-mail
                            $existing_reservation = $email_entry['rezervace'];

                            // Pokud kód neodpovídá zadanému kódu, zobraz chybu
                            if ($existing_code !== $code) {
                                echo '<div class="alert-failed">There is no record with this code.</div>';
                            } else {
                                // Aktualizace stavu rezervace na "zrušeno" a e-mailu na "neplatný"
                                $data = array('rezervace' => 'zrušeno', 'email' => $email . " - zrušen");

                                // URL pro aktualizaci záznamu
                                $update_url = 'https://sheetdb.io/api/v1/r5qf0v0bpe8gu/email/' . urlencode($email);

                                // Konfigurace HTTP požadavku pro aktualizaci
                                $options = array(
                                    'http' => array(
                                        'method'  => 'PUT',
                                        'header'  => 'Content-Type: application/json',
                                        'content' => json_encode($data)
                                    )
                                );

                                // Vytvoření kontextu pro HTTP požadavek
                                $context  = stream_context_create($options);

                                // Odeslání HTTP požadavku na aktualizaci záznamu
                                $result = file_get_contents($update_url, false, $context);

                                // Zpracování výsledku aktualizace
                                if ($result !== false) {

                            $toUserSubject = "Your reservation has been cancelled";
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
                                <h1>The ticket reservation has been cancelled</h1><br>
                                <p class='first-content'>Your reservation with the code <strong>\"$code\"</strong> and the email <strong>\"$email\"</strong> has just been cancelled.<br>
                                If it was not you, you can make a reservation retrospectively  <a href='https://safe.minerskladno.cz/tickets-en.php'>here</a>.</p>
                                <p>Note: this message is automatic, please do not reply to this email.<br>
                                    If you have any questions or concerns, you can contact us  
                                    <a href='https://safe.minerskladno.cz/contact/'>here</a> or by email <a href='mailto:safe@minerskladno.cz' target='_blank'>safe@minerskladno.cz</a></p>
                                <img src='https://safe.minerskladno.cz/images/SAFE-logo.png' alt='Safe logo'>
                            </body>
                            </html>
                            ";

                            if(sendEmail($email, $toUserSubject, $toUserMessage, $toUserHeaders)) {;
                                echo '<div class="alert-success">Your reservation has been successfully cancelled.</div>';
                            } else {
                                echo '<div class="alert-failed">Error canceling the reservation!</div>';
                            }
                        } else {
                            echo '<div class="alert-failed">Error canceling the reservation!</div>';
                        }
                    }
                } else {
                    echo '<div class="alert-failed">There is no record with this email.</div>';
                }
            }
                    ?>
                        <input type="email" name="email" class="tickets-cancel" placeholder="Your email adress*" required>
                        <input type="text" name="code" placeholder="Your reservation code*" onfocus="(this.placeholder='XXX-XXX-XXX')" onblur="(this.placeholder='Your reservation code*')" class="tickets-cancel" required>
                        <input type="submit" value="Send" name="send" id="button" class="cancel-btn">
                    </form>
                    <p>If you would like to re-book, click <a href="../">here</a>.</p>
                    <?php else: ?>
                        <div class="ticktes-end">Ticket reservations for the <span class="gold">SAFE</span> event are now over. The reservation date for the next year's <span class="gold">SAFE</span> event will be announced later.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
            <?php include '../../../skeleton/footer-en.php'?>
            <?php include '../../../skeleton/toTop.php' ?>
    </body>
</html>