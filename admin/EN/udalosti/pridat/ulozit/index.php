<?php
require_once '../../../../../skeleton/auth.php';
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
    <?php include '../../../../../skeleton/head.php'?>
    <body>
    <?php include '../../../../../skeleton/headerAdmin.php'?>
        <div class="content">
        <!-- Formulář pro přidání nové události -->
            <h2>EN Přidat novou událost</h2>
            <?php
            // Připojení k databázi
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "mywebsite";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Chyba připojení: " . $conn->connect_error);
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $eventTitle = $_POST['eventTitle'] ?? '';
                $eventDescription = $_POST['eventDescription'] ?? '';
                $targetDir = "../../../../../images/";
                $imagePaths = [];

                if (!empty($_FILES['eventImages']['name'][0])) {
                    foreach ($_FILES['eventImages']['name'] as $index => $fileName) {
                        $targetFile = $targetDir . basename($fileName);
                        if (move_uploaded_file($_FILES['eventImages']['tmp_name'][$index], $targetFile)) {
                            $imagePaths[] = $targetFile;
                        }
                    }
                }

                $imagesJson = json_encode($imagePaths);
                $stmt = $conn->prepare("INSERT INTO pasteventsen (title, description, images) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $eventTitle, $eventDescription, $imagesJson);

                if ($stmt->execute()) {
                    echo "<p class='sucessMessage'>Událost byla úspěšně uložena!</p>";
                } else {
                    echo "Chyba při ukládání události: " . $stmt->error;
                }

                $stmt->close();
            }

            $conn->close();
            ?>
            <span><i>Zpět na</i> <strong>Události</strong> </span><a href="../../" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>
