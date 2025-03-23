<?php
require_once '../../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
    <head>
        <title> SAFE | EN Upravit Událost</title>
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

            
        // Funkce pro zobrazení náhledu obrázků
        function previewImages(event) {
            let preview = document.getElementById('preview');
            preview.innerHTML = ''; // Vymazání předchozích náhledů

            Array.from(event.target.files).forEach(file => {
                let reader = new FileReader();
                reader.onload = function (e) {
                    let img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Náhled';
                    img.classList.add('preview-img');
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
        </script>
    </head>
    <?php include '../../../skeleton/head.php'; ?>
    <body>
        <?php include '../../../skeleton/headerAdmin.php'; ?>

        <div class="content">
            <h2>EN Upravit událost</h2>

            <?php
            // Připojení k databázi
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "mywebsite";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Připojení selhalo: " . $conn->connect_error);
            }

            // Načtení ID události
            $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($eventId <= 0) {
                die("Neplatné ID události.");
            }

            // Načtení dat z databáze
            $sql = "SELECT * FROM pasteventsen WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                die("Událost nenalezena.");
            }

            $event = $result->fetch_assoc();
            $images = json_decode($event['images'], true) ?? [];
            ?>
        <form action="aktualizace/index.php?id=<?= htmlspecialchars($eventId) ?>" method="POST" enctype="multipart/form-data" onsubmit="tinyMCE.triggerSave();">
            <textarea id="eventTitle" name="title" required><?= htmlspecialchars($event['title']) ?></textarea><br><br>
            <textarea id="eventDescription" name="description" required><?= htmlspecialchars($event['description']) ?></textarea><br><br>

            <h3>Obrázky události</h3>
            <div id="images">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $index => $image): ?>
                        <div class="image-item">
                            <img src="../../../images/<?= htmlspecialchars(basename($image)) ?>" alt="Obrázek" width="100"><br>
                            <input type="checkbox" id="delete_<?= $index ?>" name="delete_images[]" value="<?= htmlspecialchars(basename($image)) ?>">
                            <label for="delete_<?= $index ?>"></label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Žádné obrázky nejsou přidány.</p>
                <?php endif; ?>
            </div>

            <!-- Label místo select pro výběr souboru -->
            <label class="custom-label" for="new_images">Vyberte obrázek</label>
            <input type="file" name="new_images[]" id="new_images" multiple onchange="previewImages(event)"><br><br>

            <div id="preview"></div><br> <!-- Zde se zobrazí náhledy -->

            <button type="submit" class="saveButton">Uložit změny</button>
        </form>
        </div>
    </body>
</html>
