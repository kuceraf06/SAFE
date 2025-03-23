<?php
require_once '../../../skeleton/auth.php';
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
    <?php include '../../../skeleton/head.php'; ?>
    <style>
        .programme-row {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php include '../../../skeleton/headerAdmin.php'; ?>
<div class="content">
    <h2>Přidat nový program</h2>
    <form action="ulozit/" method="POST" id="programmeForm">
        <div id="programmeContainer">
            <div class="programme-row">
            <div class="programmeDes"><textarea name="description[]" placeholder="Popis programu" required></textarea></div>
            </div>
        </div>
        <button type="button" id="addRowButton">+</button><br><br>
        <button type="submit" class="saveButton">Uložit program</button>
    </form>
</div>

<script>
    document.getElementById('addRowButton').addEventListener('click', function () {
        const container = document.getElementById('programmeContainer');

        // Vytvoření nové div pro řádek programu
        const newRow = document.createElement('div');
        newRow.classList.add('programme-row');

        // Přidání vstupních polí pro čas a popis
        newRow.innerHTML = `
            <div class="programmeDes newProgrammeDes"><textarea name="description[]" placeholder="Popis programu" required></textarea>
            <button type="button" class="removeRowButton"><i class='bx bxs-tag-x'></i></button></div>`;

        // Přidání nové div do kontejneru
        container.appendChild(newRow);

        // Přidání funkce pro odstranění řádku
        const removeButton = newRow.querySelector('.removeRowButton');
        removeButton.addEventListener('click', function () {
            container.removeChild(newRow);
        });
    });
</script>
</body>
</html>
