<?php
require_once '../../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | Přidat Novou Událost</title>
        <style>
            * {
                font-family: "Poppins", sans-serif !important;
            }

        </style>
    </head>
    <?php include '../../../skeleton/head.php'?>
    <body>
    <?php include '../../../skeleton/headerAdmin.php'?>
        <div class="content">
        <!-- Formulář pro přidání nové události -->
            <h2>Přidat novou událost</h2>
            <?php
            // Připojení k databázi
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "mywebsite";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Připojení selhalo: " . $conn->connect_error);
            }

            // Načtení ID události
            $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($eventId <= 0) {
                die("Neplatné ID události.");
            }

            // Získání původních dat události
            $sql = "SELECT * FROM pastevents WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            $event = $result->fetch_assoc();
            $images = json_decode($event['images'], true) ?? [];

            // Zpracování změn
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Aktualizace textu události
                $title = isset($_POST['title']) ? $_POST['title'] : '';
                $description = isset($_POST['description']) ? $_POST['description'] : '';
                
                // Zpracování obrázků k odstranění
                if (isset($_POST['delete_images'])) {
                    $delete_images = $_POST['delete_images'];
                    foreach ($delete_images as $image_to_delete) {
                        // Opravená cesta k obrázku pro smazání
                        $image_path = "../../../images/" . $image_to_delete;
                        if (file_exists($image_path)) {
                            unlink($image_path); // Smazání souboru
                        }
                        // Odstranění obrázku z pole obrázků
                        $images = array_diff($images, [$image_to_delete]);
                    }
                }

                // Zpracování nových obrázků
                if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
                    foreach ($_FILES['new_images']['name'] as $key => $filename) {
                        // Zajištění správné cesty k uložení obrázku
                        $target_path = __DIR__ . "/../../../images/" . basename($filename);
                        
                        // Ověření existence složky a její vytvoření, pokud neexistuje
                        if (!file_exists(__DIR__ . "/../../../images/")) {
                            mkdir(__DIR__ . "/../../../images/", 0777, true); // Vytvoření složky
                        }

                        if (move_uploaded_file($_FILES['new_images']['tmp_name'][$key], $target_path)) {
                            // Uložení relativní cesty k obrázku do pole
                            $images[] = "images/" . $filename;  // Přidání relativní cesty
                        } else {
                            echo "Chyba při nahrávání souboru: " . $_FILES['new_images']['name'][$key];
                        }
                    }
                }

                // Uložení změn do databáze
                $image_json = json_encode($images); // Uložení seznamu obrázků jako JSON
                $sql = "UPDATE pastevents SET title = ?, description = ?, images = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $title, $description, $image_json, $eventId);
            }
            if ($stmt->execute()) {
                echo "<p class='sucessMessage'>Událost byla úspěšně aktualizována.</p>";
            } else {
                echo "Chyba při aktualizaci události: " . $conn->error;
            }
            ?>
            <span><i>Zpět na</i> <strong>Události</strong> </span><a href="../../udalosti/" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>
