<?php
include '../skeleton/sendmail.php';
?>
<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | Kontakt</title>
    </head>
    <?php include '../skeleton/head.php'?>
    <body class="contactpage-body">
    <?php include '../skeleton/header.php'?>
        <div class="mobile-translate">
            <a href="../en/contact/">CZ/EN</a>
        </div>
        <?php include '../skeleton/navbar.php'?>
            <a href="../en/contact/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="contact-container mobile-contact">
            <div class="contact-box">
                <div class="left">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1278.4678616738897!2d14.082344583382277!3d50.1436342810659!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x470bb7cd8c4a9c9f%3A0x7fdcecadc82d1c9f!2sMiners%20Kladno!5e0!3m2!1scs!2scz!4v1710027633321!5m2!1scs!2scz" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="right cz-right">
                    <h1>Kontaktujte nás</h1>
                    <?php
                    if(isset($_POST["send"])) {
                        $subject = $_POST["subject"];
                        $email = $_POST["email"];
                        $tel = $_POST["tel"];
                        $name = $_POST["name"];
                        $message = $_POST["message"];
                        $toEmail = "safe@minerskladno.cz";
                        
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            ?>
                            <center><div class="alert-email">
                            <?php echo "\"$email\" není platná mailová adresa." ?>
                            </div></center>
                            <?php
                        } elseif (!preg_match("/^[0-9 ]{9,}$/", $tel)) {
                            ?>
                            <center><div class="alert-phone">
                            <?php echo "\"$tel\" není platný telefonní číslo." ?>
                            </div></center>
                            <?php
                        } else {
                            $mailHeaders = "From: $email\r\n";
                            $mailHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";

                            $mailMessage = "
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
                                    .mailInfo {
                                        padding-bottom: 15px;
                                    }
                                </style>
                            </head>
                            <body>
                                <p class='first-content'>Přišla nová zpráva od uživatele $name</p>
                                <p>Informace:</p>
                                <ul class='mailInfo'>
                                    <li><strong>Jméno:</strong> $name</li>
                                    <li><strong>Email:</strong> $email</li>
                                    <li><strong>Tel.:</strong> $tel</li>
                                    <li><strong>Předmět:</strong> $subject</li>
                                    <li><strong>Zpráva:</strong> $message</li>
                                </ul>
                            </body>
                            </html>
                            ";
                          
                            if (sendEmail($toEmail, $subject, $mailMessage)) {
                                ?>
                                <center><div class="alert-success">
                                <?php echo "Váš mail úspěšně odeslán na \"$toEmail\"" ?>
                                </div></center>
                                <?php
                                $toUserSubject = "Kopie vaší zprávy";
                                $toUserSubject = mb_encode_mimeheader($toUserSubject, "UTF-8", "Q");
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
                                    <p class='first-content'>Děkujeme že jste nás kontaktovali!</p>
                                    <p>Vaše zpráva:</p>
                                    <ul>
                                        <li><strong>Jméno:</strong> $name</li>
                                        <li><strong>Email:</strong> $email</li>
                                        <li><strong>Tel.:</strong> $tel</li>
                                        <li><strong>Předmět:</strong> $subject</li>
                                        <li><strong>Zpráva:</strong> $message</li>
                                    </ul>
                                    <p>Kontaktujeme vás co nejdříve.</p>
                                    <p>pozn. tato zpráva je automatická, prosím neodpovídejte na tento email.<br><p>
                                    <img src='https://safe.minerskladno.cz/images/SAFE-logo.png' alt='Safe logo'>
                                </body>
                                </html>
                                ";

                                sendEmail($email, $toUserSubject, $toUserMessage);
                            } else {
                                ?>
                                <center><div class="alert-failed">
                                <?php echo "Chyba při odeslání emailu! " ?>
                                </div></center>
                                <?php
                            }
                        }
                    }
                    ?>

                    <form class="contact-form" method="post" autocomplete="off">
                    <input type="text" name="name" placeholder="Vaše celé jméno*" class="field" required>
                    <input type="email" name="email" class="field" placeholder="Vaše email adresa*" required>
                    <input type="tel" inputmode="numeric" name="tel" id="tel" class="field" placeholder="Vaše tel. číslo*" required>
                    <select id="subject" class="field" name="subject">
                        <option value="Question">Otázka</option>
                        <option value="Feedback">Zpětná vazba</option>
                        <option value="Complaint">Stížnost</option>
                    </select>
                    <textarea placeholder="Zpráva*" id="message" name="message" class="field" required></textarea>
                    <input type="submit" value="Odeslat" name="send" id="button" class="btn">
                    </form>
                </div>
            </div>
            <div class="contactMethod">
                <div class="method">
                    <i class="fa-solid fa-location-dot contactIcon"></i>
                    <article class="contact-text">
                        <h2 class="sub-heading">Adresa</h2>
                        <p class="para">U Trati 3489, 272 01 Kladno 1</p>
                    </article>
                </div>
                <div class="method">
                    <i class="fa-solid fa-envelope contactIcon"></i>
                    <article class="contact-text different">
                        <h2 class="sub-heading">Email</h2>
                        <p class="para"><a href="mailto:safe@minerskladno.cz" target="_blank">safe@minerskladno.cz</a></p>
                    </article>
                </div>
                <div class="method">
                    <i class="fa-solid fa-phone contactIcon"></i>
                    <article class="contact-text different-2">
                        <h2 class="sub-heading">Telefon</h2>
                        <a class="para" href="tel:+420739026342">+420 739 026 342</a>
                    </article>
                </div>
            </div>
        </main>
            <?php include '../skeleton/footer.php'?>
            <?php include '../skeleton/toTop.php' ?>
        <script src="../javascript/main.js"></script>
        <script>
            const telInput = document.getElementById('tel');

            telInput.addEventListener('input', function(event) {
                const cleaned = telInput.value.replace(/\D/g, '');

                let formatted = '';
                for (let i = 0; i < cleaned.length; i++) {
                    if (i > 0 && i % 3 === 0) {
                        formatted += ' ';
                    }
                    formatted += cleaned[i];
                }

                telInput.value = formatted;
            });
        </script>
    </body>
</html>