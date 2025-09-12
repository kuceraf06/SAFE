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
            <h2>EN Přidat novou událost</h2>
            <?php
            include '../../../../../skeleton/db_connect.php';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $eventTitle = $_POST['eventTitle'] ?? '';
                $eventDescription = $_POST['eventDescription'] ?? '';
                $targetDir = __DIR__ . "/../../../../../images/";
                $imagePaths = [];

                if (!empty($_FILES['eventImages']['name'][0])) {
                    foreach ($_FILES['eventImages']['name'] as $index => $fileName) {
                        $targetFile = $targetDir . basename($fileName);

                        if (!file_exists($targetDir)) {
                            mkdir($targetDir, 0777, true);
                        }

                        if (move_uploaded_file($_FILES['eventImages']['tmp_name'][$index], $targetFile)) {
                            $imagePaths[] = "images/" . basename($fileName);
                        }
                    }
                }

                $imagesJson = json_encode($imagePaths);

                $stmt = $conn->prepare("INSERT INTO pasteventsen (title, description, images) VALUES (:title, :description, :images)");
                $success = $stmt->execute([
                    ':title' => $eventTitle,
                    ':description' => $eventDescription,
                    ':images' => $imagesJson
                ]);

                if ($success) {
                    echo "<p class='sucessMessage'>Událost byla úspěšně uložena!</p>";
                } else {
                    $errorInfo = $stmt->errorInfo();
                    echo "Chyba při ukládání události: " . htmlspecialchars($errorInfo[2]);
                }
            }
            ?>
            <span><i>Zpět na</i> <strong>Události</strong> </span><a href="../../" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>
