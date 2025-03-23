<?php
require_once '../../../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | EN Přidat Novou Událost</title>
        <style>
            * {
                font-family: "Poppins", sans-serif !important;
            }
        </style>
    </head>
    <?php include '../../../../skeleton/head.php'?>
    <body>
    <?php include '../../../../skeleton/headerAdmin.php'?>
        <div class="content">
            <h2>EN Upravit událost</h2>
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

            // Načtení dat z databáze
            $sql = "SELECT * FROM pasteventsen WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                die("Událost nenalezena.");
            }

            $event = $result->fetch_assoc();
            $existingImages = json_decode($event['images'], true) ?? [];

            // Zpracování dat z formuláře
            $title = $_POST['title'];
            $description = $_POST['description'];
            $deleteImages = isset($_POST['delete_images']) ? $_POST['delete_images'] : [];

            // Mazání vybraných obrázků
            $updatedImages = [];
            foreach ($existingImages as $image) {
                if (!in_array(basename($image), $deleteImages)) {
                    $updatedImages[] = $image;
                } else {
                    $filePath = '../../../../images/' . basename($image);
                    if (file_exists($filePath)) {
                        unlink($filePath); // Smazání souboru ze serveru
                    }
                }
            }

            // Nahrání nových obrázků
            $uploadDir = '../../../../images/';
            $newImages = [];
            if (!empty($_FILES['new_images']['name'][0])) {
                foreach ($_FILES['new_images']['tmp_name'] as $key => $tmpName) {
                    $fileName = basename($_FILES['new_images']['name'][$key]);
                    $targetFilePath = $uploadDir . uniqid() . '_' . $fileName;

                    // Bezpečnostní kontrola
                    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    if (in_array($fileType, $allowedTypes)) {
                        if (move_uploaded_file($tmpName, $targetFilePath)) {
                            $newImages[] = $targetFilePath; // Přidání cesty k novému obrázku
                        }
                    }
                }
            }

            // Spojení stávajících a nových obrázků
            $finalImages = array_merge($updatedImages, $newImages);

            // Aktualizace databáze
            $sql = "UPDATE pasteventsen SET title = ?, description = ?, images = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $imagesJson = json_encode($finalImages);
            $stmt->bind_param("sssi", $title, $description, $imagesJson, $eventId);

            if ($stmt->execute()) {
                echo "<p class='sucessMessage'>Událost byla úspěšně aktualizována.</p>";
            } else {
                echo "Chyba při aktualizaci události: " . $conn->error;
            }

            $stmt->close();
            $conn->close();
            ?>
            <span><i>Zpět na</i> <strong>Události</strong> </span><a href="../../udalosti/" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>