<?php
require_once '../../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <title>SAFE | EN Seznam Událostí</title>
    <style>
        * {
            font-family: "Poppins", sans-serif !important;
        }
    </style>
    <script>
        // Funkce pro potvrzení smazání
        function confirmDelete(event, url) {
            event.preventDefault(); // Zabrání standardní akci odkazu
            if (confirm("Opravdu chcete smazat tuto událost?")) {
                window.location.href = url; // Přesměrování na URL pro mazání
            }
        }
    </script>
    <?php include '../../../skeleton/head.php' ?>
</head>
<body>
<?php include '../../../skeleton/headerAdmin.php' ?>
<div class="content">
<h2>EN Seznam událostí</h2><br>
    <a href="pridat/" class="addButton">+</a><br>
    <table class="events-table">
        <thead>
        <tr>
            <th>Datum</th>
            <th>Nadpis</th>
            <th>Úpravy</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Připojení k databázi
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "mywebsite";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Chyba připojení: " . $conn->connect_error);
        }

        // Kontrola, zda je nastavena akce na mazání
        if (!empty($_GET['delete_id'])) {
            $delete_id = intval($_GET['delete_id']);
            $delete_sql = "DELETE FROM pasteventsen WHERE id = $delete_id";

            if ($conn->query($delete_sql) === TRUE) {
                header("Location: ./"); // Přesměrování na stejnou stránku
                exit;
            } else {
                echo "<script>alert('Chyba při mazání události: " . addslashes($conn->error) . "');</script>";
            }
        }

        // Načtení událostí
        $sql = "SELECT id, title, created_at FROM pasteventsen ORDER BY id DESC";
        $result = $conn->query($sql);

        if (!$result) {
            die("Chyba v dotazu: " . $conn->error);
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $created_at = isset($row['created_at']) ? date('d.m.Y H:i', strtotime($row['created_at'])) : 'Neznámé datum';
                $title = isset($row['title']) && !empty($row['title']) ? strip_tags($row['title']) : 'Bez názvu';

                echo "<tr>";
                echo "<td>" . $created_at . "</td>";
                echo "<td>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td class='action-buttons'>";
                echo "<a href='../upravit/?id=" . $row['id'] . "' class='edit-btn'><i class='bx bx-edit-alt'></i></a>";
                echo "<a href='#' class='delete-btn' onclick='confirmDelete(event, \"?delete_id=" . $row['id'] . "\")'><i class='bx bxs-tag-x'></i></a>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Žádné události nejsou k dispozici.</td></tr>";
        }
        $conn->close();
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
