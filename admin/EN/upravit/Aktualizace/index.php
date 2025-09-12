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
            include '../../../../skeleton/db_connect.php';

            $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($eventId <= 0) {
                die("Neplatné ID události.");
            }

            $sql = "SELECT * FROM pasteventsen WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                die("Událost nenalezena.");
            }

            $existingImages = json_decode($event['images'], true) ?? [];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $deleteImages = $_POST['delete_images'] ?? [];

                $updatedImages = [];
                foreach ($existingImages as $image) {
                    if (!in_array(basename($image), $deleteImages)) {
                        $updatedImages[] = $image;
                    } else {
                        $filePath = '../../../../images/' . basename($image);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
                $uploadDir = '../../../../images/';
                $newImages = [];
                if (!empty($_FILES['new_images']['name'][0])) {
                    foreach ($_FILES['new_images']['tmp_name'] as $key => $tmpName) {
                        $fileName = basename($_FILES['new_images']['name'][$key]);
                        $targetFilePath = $uploadDir . uniqid() . '_' . $fileName;

                        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
                        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                        if (in_array($fileType, $allowedTypes)) {
                            if (move_uploaded_file($tmpName, $targetFilePath)) {
                                $newImages[] = "images/" . basename($targetFilePath);
                            }
                        }
                    }   
                }

                $finalImages = array_merge($updatedImages, $newImages);
                $imagesJson = json_encode($finalImages);

                $updateSql = "UPDATE pasteventsen SET title = :title, description = :description, images = :images WHERE id = :id";
                $updateStmt = $conn->prepare($updateSql);
                if ($updateStmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':images' => $imagesJson,
                    ':id' => $eventId
                ])) {
                    echo "<p class='sucessMessage'>Událost byla úspěšně aktualizována.</p>";
                } else {
                    $errorInfo = $updateStmt->errorInfo();
                    echo "Chyba při aktualizaci události: " . htmlspecialchars($errorInfo[2]);
                }
            }
            ?>
            <span><i>Zpět na</i> <strong>Události</strong> </span>
            <a href="../../udalosti/" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>