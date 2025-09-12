<?php
require_once '../skeleton/auth.php';

include '../skeleton/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_reservation'])) {
    $newStatus = isset($_POST['is_active']) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE reservation_status SET is_active = :status WHERE id = 1");
    $stmt->execute([':status' => $newStatus]);
}

$stmt = $conn->query("SELECT is_active FROM reservation_status WHERE id = 1");
$resStatus = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
$isReservationActive = $resStatus ? $resStatus['is_active'] : 0;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <title>SAFE | Admin</title>
    <style>
        * {
            font-family: "Poppins", sans-serif !important;
        }
    </style>
</head>
<?php include '../skeleton/head.php'?>
<body>
<?php include '../skeleton/headerAdmin.php'?>
<div class="content">
    <div class="startForm">
    <h2>Spustit rezervaci</h2>
        <form method="post">
            <label class="switch">
                <input type="checkbox" name="is_active" <?= $isReservationActive ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label><br>
            <button type="submit" class="saveButton" name="toggle_reservation">Uložit stav</button><br><br>
        </form>

        <hr>
        <div class="addBox">
            <div class="firstBox">
                <a href="/admin/udalosti/pridat/" class="childBox">
                    <i class='bx bx-plus'></i>
                    <p>Přidat novou událost</p>
                </a>
                <a href="/admin/program/pridat/" class="childBox childBox-2">
                    <i class='bx bx-plus'></i>
                    <p>Přidat nový program</p>
                </a>
            </div>
            <div class="secondBox">
                <a href="/admin/program/pridat/" class="childBox">
                    <i class='bx bx-plus'></i>
                    <p>Aktualizovat termín</p>
                </a>
                <a href="/admin/program/pridat/" class="childBox childBox-2">
                    <i class='bx bx-plus'></i>
                    <p>Přidat novou pozvánku</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
