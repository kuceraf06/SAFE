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
            <h2>Přidat novou událost</h2>
            <?php
            include '../../../skeleton/db_connect.php';

            $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($eventId <= 0) {
                die("Neplatné ID události.");
            }

            $sql = "SELECT * FROM pastevents WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                die("Událost nenalezena.");
            }

            $images = json_decode($event['images'], true) ?? [];

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                
                if (!empty($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $image_to_delete) {
                        $image_path = "../../../" . $image_to_delete;
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                        $images = array_diff($images, [$image_to_delete]);
                    }
                }

                if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
                    foreach ($_FILES['new_images']['name'] as $key => $filename) {
                        $target_dir = __DIR__ . "/../../../images/";
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }

                        $target_path = $target_dir . basename($filename);
                        if (move_uploaded_file($_FILES['new_images']['tmp_name'][$key], $target_path)) {
                            $images[] = "images/" . basename($filename);
                        } else {
                            echo "Chyba při nahrávání souboru: " . htmlspecialchars($filename);
                        }
                    }
                }

                $image_json = json_encode(array_values($images)); // array_values pro reset klíčů
                $sql = "UPDATE pastevents SET title = ?, description = ?, images = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $success = $stmt->execute([$title, $description, $image_json, $eventId]);

                if ($success) {
                    echo "<p class='sucessMessage'>Událost byla úspěšně aktualizována.</p>";
                } else {
                    echo "Chyba při aktualizaci události.";
                }
            }
            ?>
            <span><i>Zpět na</i> <strong>Události</strong> </span><a href="../../udalosti/" class="backButton"><i class='bx bx-redo'></i></a>
        </div>
    </body>
</html>
