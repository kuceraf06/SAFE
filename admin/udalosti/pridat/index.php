<?php
require_once '../../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | Přidat Novou Událost</title>
        <script src="https://cdn.tiny.cloud/1/6rzryildd6hlj1iln5s3k2hbidi3h8qs2s9k8cnf7e8nbfi5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            tinymce.init({
                selector: '#eventTitle', // Toto bude pro nadpis
                toolbar: false, // Definování toolbaru pro nadpis
                menubar: false,
                height: 105, // Nastavte výšku pro nadpis
                plugins: '', // Povolení formátu
                forced_root_block: 'h2', // Vynucení hlavního bloku na <h2>
                content_style: 'h2 { color: #333}', // Nastavení výchozí barvy
                setup: function (editor) {
                    editor.on('change', function () {
                        tinymce.triggerSave(); // Synchronizuje obsah s <textarea>
                    });
                    editor.on('init', function () {
                        editor.setContent('<h2></h2>'); // Nastaví výchozí obsah
                    });
                }
            });

            tinymce.init({
                selector: '#eventDescription',
                plugins: 'advlist autolink lists link charmap preview anchor pagebreak',
                toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link', // Bez tlačítka pro obrázky
                toolbar_mode: 'floating',
                menubar: true,
                height: 514,
                setup: function (editor) {
                editor.on('change', function () {
                    tinymce.triggerSave(); // Synchronizuje obsah TinyMCE s <textarea>
                });
                }
            });
        </script>
        <style>
            * {
                font-family: "Poppins", sans-serif !important;
            }
        </style>
    </head>
    <?php include '../../../skeleton/head.php'?>
    <body>
    <?php include '../../../skeleton/headerAdmin.php' ?>
        <div class="content">
        <!-- Formulář pro přidání nové události -->
            <h2>Přidat novou událost</h2>
                <form action="ulozit/" method="POST" enctype="multipart/form-data" onsubmit="tinyMCE.triggerSave();">
                    <textarea id="eventTitle" name="eventTitle" required></textarea><br><br>    
                    <textarea id="eventDescription" name="eventDescription" required></textarea><br><br>
                    <div class="custom-file">
                    <label for="eventImage" id="customLabel">Vybrat obrázek</label>
                    <input type="file" id="eventImage" name="eventImages[]" accept="image/*" multiple onchange="combinedFunction()"><br><br>
                    <div id="imagePreview" style="display: flex; gap: 10px; flex-wrap: wrap;"></div> <!-- Kontejner na náhled obrázků -->
                    </div><br><br>
                    <button type="submit" class="saveButton">Uložit událost</button>
                </form>
        </div>
        <script>
        function combinedFunction() {
                const input = document.getElementById("eventImage");
                const previewContainer = document.getElementById("imagePreview");
                previewContainer.innerHTML = "";
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = document.createElement("img");
                        img.src = e.target.result;
                        img.style.width = "100px";
                        img.style.height = "100px";
                        img.style.objectFit = "cover";
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        </script>
    </body>
</html>
