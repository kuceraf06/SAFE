<?php
require_once '../../skeleton/auth.php';

// Připojení k databázi – MUSÍ BÝT ZDE!
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mywebsite";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Chyba připojení: " . $conn->connect_error);
}

$error_message = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["novy_obrazek"])) {
    $target_dir = "../../images/";
    $filename = basename($_FILES["novy_obrazek"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["novy_obrazek"]["tmp_name"], $target_file)) {
        $sql = "UPDATE invation SET obrazek = '$filename' WHERE id = 1";
        if ($conn->query($sql) === TRUE) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit;
        } else {
            $error_message = "Chyba při ukládání do databáze.";
        }
    } else {
        $error_message = "Nepodařilo se nahrát obrázek.";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | Změnit Pozvánku</title>
        <style>
            * {
                font-family: "Poppins", sans-serif !important;
            }
        </style>
        <?php include '../../skeleton/head.php' ?>
    </head>
    <body>
        <?php include '../../skeleton/headerAdmin.php' ?>
        <div class="content">
        <h2>Změnit pozvánku</h2><br>
            <?php
                // Výpis aktuálního obrázku
                $sql = "SELECT obrazek FROM invation WHERE id = 1";
                $result = $conn->query($sql);

                if ($result && $row = $result->fetch_assoc()) {
                    $aktualni_obrazek = $row['obrazek'];
                    echo '<div class="preview-box">';
                    echo '<img src="../../images/' . htmlspecialchars($aktualni_obrazek) . '" style="max-width:300px;">';
                    echo '</div>';
                }
            ?>
                        <form method="POST" enctype="multipart/form-data">
                    <div class="custom-file">
                        <label for="eventImage" id="customLabel">
                            Vybrat obrázek
                        </label>
                        <input type="file" id="eventImage" name="novy_obrazek" accept="image/*" onchange="previewImage(event)" style="display: none;">
                    </div><br><br>
                    <div id="imagePreview" style="display: flex; gap: 10px; flex-wrap: wrap;"></div>
                    <button type="submit" class="saveButton">Změnit obrázek</button>
                    <?php
                    if (isset($_GET['success']) && $_GET['success'] == 1) {
                        echo "<p class='success-message'>Obrázek byl úspěšně změněn.</p>";
                    }
                    if ($error_message != '') {
                        echo "<p class='error-message'>$error_message</p>";
                    }
                    ?>
                </form>
        </div>
        <script>
        function previewImage(event) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = ""; // Vyčistit předchozí náhled

            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.style.maxWidth = "300px";
                    img.style.width = "100%";
                    img.style.borderRadius = "5px";
                    img.style.marginBottom = "15px";
                    img.style.border = "1px solid #ccc";
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        }
        </script>
    </body>
</html>