<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SAFE | Past Events</title>
    </head>
    <?php include '../../skeleton/head.php'?>
    <body class="pastevents-body">
    <?php include '../../skeleton/header-en.php'?>
        <div class="mobile-translate">
            <a href="../../rocniky/">CZ/EN</a>
        </div>
        <?php include '../../skeleton/navbar-en.php'?>
            <a href="../../rocniky/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="pastEvents-page">
        <div class="heading">
                <h1>PAST EVENTS</h1>
            </div>
            <div class="pastEvents-container">
            <section class="evetnspage">
            <?php
            // Nastavení připojení k databázi
            $servername = "localhost"; // Obvykle "localhost" při použití XAMPP
            $username = "root";        // Výchozí uživatelské jméno v XAMPP je "root"
            $password = "";            // Výchozí heslo je prázdné (pokud jsi ho nezměnil)
            $dbname = "mywebsite";     // Název databáze, kterou jsi vytvořil (v našem případě "mywebsite")

            // Vytvoření připojení
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Kontrola připojení
            if ($conn->connect_error) {
                die("Připojení k databázi selhalo: " . $conn->connect_error);
            }
            // Připojení k databázi
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Chyba připojení: " . $conn->connect_error);
            }

            $sql = "SELECT title, description, images FROM pasteventsen ORDER BY id DESC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $totalRows = $result->num_rows; // Získání celkového počtu záznamů
                $currentRow = 0; // Počítadlo aktuálního záznamu

                while ($row = $result->fetch_assoc()) {
                    $images = json_decode($row['images'], true);
                    echo "<div class='event-main'>";
                    echo "<div class='pastEvents-content'>";
                    echo "<h2>{$row['title']}</h2>";
                    echo "<p>{$row['description']}</p>";
                    echo "</div>";

                    if (!empty($images)) {
                        echo "<div class='gallery'>";
                        echo "<center>";
                        foreach ($images as $image) {
                            $correctedPath = '../../images/' . basename($image); // Přidá správnou relativní cestu
                            echo "<img src='$correctedPath' alt='Event Image'>";
                        }
                        echo "</center>";
                        echo "</div>";
                        echo "</div>";
                    }
                    

                    // Přidání <hr> pouze pokud to není poslední (nejstarší) záznam
                    if ($currentRow < $totalRows - 1) {
                        echo "<hr>";
                    }

                    $currentRow++; // Zvýšení počítadla
                }
            }
            $conn->close();
            ?>
            </div>
            </section>
        </main>
            <?php include '../../skeleton/footer-en.php'?>
            <?php include '../../skeleton/toTop.php' ?>
    </body>
</html>