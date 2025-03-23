<?php
require_once '../../../../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | EN Přidat Nový Program</title>
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
                $descriptions = $_POST['description'];

                $stmt = $conn->prepare("INSERT INTO programmeen (description) VALUES (?)");

                foreach ($descriptions as $description) {
                    $stmt->bind_param("s", $description);
                    $stmt->execute();
                }

                $stmt->close();
                echo "<p class='sucessMessage'>Program byl úspěšně uložen!</p>";
            }

            $conn->close();
            ?>
            <span><i>Zpět na</i> <strong>Program</strong> </span><a href="../../" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>
