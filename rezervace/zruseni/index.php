<?php
include '../../skeleton/sendmail.php';

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
<html lang="cs">
<head>
    <title>SAFE | Zrušení Rezervace</title>
    <?php include '../../skeleton/head.php'?>
</head>
<body class="cancelpage-body">
    <?php include '../../skeleton/header.php'?>
    <div class="mobile-translate">
        <a href="../../en/reservation/cancel/">CZ/EN</a>
    </div>
    <?php include '../../skeleton/navbar.php'?>
        <a href="../../en/reservation/cancel/" class="before pc-translate">CZ/EN</a>
        </nav>
    </header>
    <div class="heading">
        <h1>ZRUŠENÍ REZERVACE</h1>
    </div>
    <main class="cancel-container">
            <?php if ($isReservationActive): ?>
            <div class="remove-box">
                <div class="right cz-right">
                    <form class="contact-form" method="post" autocomplete="off">
                    <?php
                    if (!empty($_POST["send"])) {
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
                                echo '<div class="alert-failed">Záznam s tímto kódem neexistuje.</div>';
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
                                    $toUserSubject = "Zrušení rezervace";
                                    $toUserSubject = mb_encode_mimeheader($toUserSubject, "UTF-8", "Q");
                                    $toUserHeaders = "From: no-reply\r\n";
                                    $toUserHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";

                                    $toUserMessage = "
                                    <html>
                                    <head>
                                        <style>
                                            body {
                                                font-family: 'Poppins', sans-serif;
                                                color: #333;
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
                                        <h1>Rezervace vstupenek byla zrušena</h1><br>
                                        <p class='first-content'>Vaše rezervace s kódem <strong>\"$code\"</strong> a emailem <strong>\"$email\"</strong> byla právě zrušena.<br>
                                        Pokud se nejednalo o vás, můžete rezervaci zpětně vytvořit <a href='https://safe.minerskladno.cz/tickets.php'>zde</a>.</p>
                                        <p>pozn. tato zpráva je automatická, prosím neodpovídejte na tento email.<br>
                                            V případě jakých koliv otázek či nejasností nás můžete kontaktovat 
                                            <a href='https://safe.minerskladno.cz/kontakt/'>zde</a> či na emailu <a href='mailto:safe@minerskladno.cz' target='_blank'>safe@minerskladno.cz</a></p>
                                        <img src='https://safe.minerskladno.cz/images/SAFE-logo.png' alt='Safe logo'>
                                    </body>
                                    </html>
                                    ";

                                    if(sendEmail($email, $toUserSubject, $toUserMessage, $toUserHeaders)) {;
                                        echo '<div class="alert-success">Vaše rezervace úspěšně zrušena.</div>';
                                    } else {
                                        echo '<div class="alert-failed">Chyba při zrušení rezervace!</div>';
                                    }
                                } else {
                                    echo '<div class="alert-failed">Chyba při zrušení rezervace!</div>';
                                }
                            }
                        } else {
                            echo '<div class="alert-failed">Záznam s tímto e-mailem neexistuje.</div>';
                        }
                    }
                    ?>

                        <input type="email" name="email" class="tickets-cancel" placeholder="Vaše email adresa*" required>
                        <input type="text" name="code" placeholder="Váš rezervační kód*" onfocus="(this.placeholder='XXX-XXX-XXX')" onblur="(this.placeholder='Váš rezervační kód*')" class="tickets-cancel" required>
                        <input type="submit" value="Odeslat" name="send" id="button" class="cancel-btn">
                    </form>
                    <p>Pokud chcete znovu vytvořit rezervace klikněte <a href="../">zde</a>.</p>
                    <?php else: ?>
                        <div class="ticktes-end">Rezervace vstupenek na akci <span class="gold">SAFE</span> jsou již u konce. Termín na rezervace pro další ročník akce <span class="gold">SAFE</span> bude ještě upřesněn.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    <?php include '../../skeleton/footer.php'?>
    <?php include '../../skeleton/toTop.php' ?>
    <script src="../../javascript/main.js"></script>
</body>
</html>
