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
            <h2>EN Přidat novou událost</h2>
            <?php
            include '../../../../../skeleton/db_connect.php';

             if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $descriptions = $_POST['description'] ?? [];

                if (!empty($descriptions)) {
                    $stmt = $conn->prepare("INSERT INTO programmeen (description) VALUES (:description)");

                    foreach ($descriptions as $description) {
                        $stmt->execute([':description' => $description]);
                    }

                    echo "<p class='sucessMessage'>Program byl úspěšně uložen!</p>";
                } else {
                    echo "<p style='color: red;'>Nebyly poskytnuty žádné popisy.</p>";
                }
            }
            ?>
            <span><i>Zpět na</i> <strong>Program</strong> </span><a href="../../" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>
