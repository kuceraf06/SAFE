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
        <script src="https://cdn.tiny.cloud/1/6rzryildd6hlj1iln5s3k2hbidi3h8qs2s9k8cnf7e8nbfi5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            tinymce.init({
                selector: '#eventTitle',
                toolbar: false,
                menubar: false,
                height: 105,
                plugins: 'format',
                forced_root_block: 'h2',
                content_style: 'h2 { color: #333}',
                setup: function (editor) {
                    editor.on('change', function () {
                        tinymce.triggerSave();
                    });
                    editor.on('init', function () {
                        editor.setContent('<h2></h2>');
                    });
                }
            });

            tinymce.init({
                selector: '#eventDescription',
                plugins: 'advlist autolink lists link charmap print preview hr anchor pagebreak',
                toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link',
                toolbar_mode: 'floating',
                menubar: true,
                height: 514,
                setup: function (editor) {
                editor.on('change', function () {
                    tinymce.triggerSave();
                });
                }
            });
        </script>
    </head>
    <?php include '../../../../skeleton/head.php'?>
    <body>
    <?php include '../../../../skeleton/headerAdmin.php' ?>
        <div class="content">
            <h2>EN Přidat novou událost</h2>
                <form action="ulozit/" method="POST" enctype="multipart/form-data" onsubmit="tinyMCE.triggerSave();">
                    <textarea id="eventTitle" name="eventTitle" required></textarea><br><br>    
                    <textarea id="eventDescription" name="eventDescription" required></textarea><br><br>
                    <div class="custom-file">
                    <label for="eventImage" id="customLabel">Vybrat obrázek</label>
                    <input type="file" id="eventImage" name="eventImages[]" accept="image/*" multiple onchange="combinedFunction()"><br><br>
                    <div id="imagePreview" style="display: flex; gap: 10px; flex-wrap: wrap;"></div>
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
