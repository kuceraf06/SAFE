<?php require_once '../../skeleton/auth.php'; ?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <title>SAFE | Upravit Událost</title>
    <script src="https://cdn.tiny.cloud/1/6rzryildd6hlj1iln5s3k2hbidi3h8qs2s9k8cnf7e8nbfi5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#eventTitle',
            toolbar: false,
            menubar: false,
            height: 105,
            plugins: '',
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
            plugins: 'advlist autolink lists link charmap preview anchor pagebreak',
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

        function previewImages(event) {
            let preview = document.getElementById('preview');
            preview.innerHTML = '';

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
    <style>
        * {
            font-family: "Poppins", sans-serif !important;
        }

    </style>
</head>

<?php include '../../skeleton/head.php'; ?>
<body>
    <?php include '../../skeleton/headerAdmin.php'; ?>

    <div class="content">
        <h2>Upravit událost</h2>

        <?php
        include '../../skeleton/db_connect.php';

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
        ?>

        <form action="aktualizace/index.php?id=<?= htmlspecialchars($eventId) ?>" method="POST" enctype="multipart/form-data" onsubmit="tinyMCE.triggerSave();">
            <textarea id="eventTitle" name="title" required><?= htmlspecialchars($event['title']) ?></textarea><br><br>
            <textarea id="eventDescription" name="description" required><?= htmlspecialchars($event['description']) ?></textarea><br><br>

            <h3>Obrázky události</h3>
            <div id="images">
                <?php if (count($images) > 0): ?>
                    <?php foreach ($images as $image): ?>
                        <div class="image-item">
                            <img src="../../<?= htmlspecialchars($image) ?>" alt="Obrázek" width="100"><br>
                            <input type="checkbox" id="delete_<?= $index ?>" name="delete_images[]" value="<?= htmlspecialchars($image) ?>">
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
