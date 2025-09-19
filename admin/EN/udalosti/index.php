<?php
require_once '../../../skeleton/auth.php';
include '../../../skeleton/db_connect.php';

if (!empty($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $delete_sql = "DELETE FROM pasteventsen WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->execute([$delete_id]);

        header("Location: ./");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Chyba při mazání události: " . addslashes($e->getMessage()) . "');</script>";
    }
}
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
        function confirmDelete(event, url) {
            event.preventDefault();
            if (confirm("Opravdu chcete smazat tuto událost?")) {
                window.location.href = url;
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
        try {
            $sql = "SELECT id, title, created_at FROM pasteventsen ORDER BY id DESC";
            $stmt = $conn->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $created_at = !empty($row['created_at']) ? date('d.m.Y H:i', strtotime($row['created_at'])) : 'Unknown date';
                    $title = !empty($row['title']) ? strip_tags($row['title']) : 'Untitled';

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($created_at, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td class='action-buttons'>";
                    echo "<a href='../upravit/?id=" . (int)$row['id'] . "' class='edit-btn'><i class='bx bx-edit-alt'></i></a>";
                    echo "<a href='#' class='delete-btn' onclick='confirmDelete(event, \"?delete_id=" . (int)$row['id'] . "\")'><i class='bx bxs-tag-x'></i></a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Žádný události nejsou k dispozici.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='3'>Chyba v dotazu: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }

        $conn = null;
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
