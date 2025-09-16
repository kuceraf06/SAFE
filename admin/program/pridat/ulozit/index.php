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
                if (!empty($_POST['description']) && is_array($_POST['description'])) {
                    $descriptions = $_POST['description'];

                    try {
                        $stmt = $conn->prepare("INSERT INTO programme (description) VALUES (:description)");

                        $savedCount = 0;
                        foreach ($descriptions as $description) {
                            $description = trim($description);
                            if ($description === '') continue;

                            $stmt->bindValue(':description', $description, PDO::PARAM_STR);
                            $stmt->execute();
                            $savedCount++;
                        }

                        if ($savedCount > 0) {
                            echo "<p class='sucessMessage'>Program byl úspěšně uložen!</p>";
                        } else {
                            echo "<p class='errorMessage'>Nebyl zadán žádný text k uložení.</p>";
                        }

                    } catch (PDOException $e) {
                        echo "<p style='color:red;'>Chyba při ukládání programu: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }

                } else {
                    echo "<p style='color:red;'>Nebyly odeslány žádné údaje k uložení.</p>";
                }
            }

            ?>
            <span><i>Zpět na</i> <strong>Program</strong> </span><a href="../../" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>
