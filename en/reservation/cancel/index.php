<?php
include '../../../skeleton/sendmail.php';

include '../../../skeleton/db_connect.php';

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
        <div class="heading">
            <h1>CANCELLING RESERVATION</h1>
        </div>
        <main class="cancel-container">
            <?php if ($isReservationActive): ?>
            <div class="remove-box">
                <div class="right cz-right">
                    <form class="contact-form" method="post" autocomplete="off">
                    <?php 
                        if(isset($_POST["send"])) {
                            $email = $_POST["email"];
                            $code = $_POST["code"];

                            $email_search_url = 'https://sheetdb.io/api/v1/r5qf0v0bpe8gu/search?email=' . urlencode($email);

                            $email_response = file_get_contents($email_search_url);

                            $email_existing_entry = json_decode($email_response, true);

                            if (!empty($email_existing_entry)) {
                                $email_entry = $email_existing_entry[0];

                                $existing_code = $email_entry['kód rezervace'];
                                
                                $existing_reservation = $email_entry['rezervace'];

                                if ($existing_code !== $code) {
                                    echo '<div class="alert-failed">There is no record with this code.</div>';
                                } else {
                                    $data = array('rezervace' => 'zrušeno', 'email' => $email . " - zrušen");

                                    $update_url = 'https://sheetdb.io/api/v1/r5qf0v0bpe8gu/email/' . urlencode($email);

                                    $options = array(
                                        'http' => array(
                                            'method'  => 'PUT',
                                            'header'  => 'Content-Type: application/json',
                                            'content' => json_encode($data)
                                        )
                                    );

                                    $context  = stream_context_create($options);

                                    $result = file_get_contents($update_url, false, $context);

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
                                    If it was not you, you can make a reservation retrospectively  <a href='https://safe.minerskladno.cz/reservation/'>here</a>.</p>
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