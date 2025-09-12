<?php
require_once '../../../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | Přidat Nový Program</title>
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
            <h2>Přidat novou událost</h2>
            <?php
            include '../../../../skeleton/db_connect.php';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $descriptions = $_POST['description'];

                $stmt = $conn->prepare("INSERT INTO programme (description) VALUES (:description)");

                foreach ($descriptions as $description) {
                    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
                    $stmt->execute();
                }

                echo "<p class='sucessMessage'>Program byl úspěšně uložen!</p>";
            }

            ?>
            <span><i>Zpět na</i> <strong>Program</strong> </span><a href="../../" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>
