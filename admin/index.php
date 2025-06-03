<?php
require_once '../skeleton/auth.php';
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
