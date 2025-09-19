<?php
require_once '../../skeleton/auth.php';
include '../../skeleton/db_connect.php';

if (!empty($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    try {
        $delete_sql = "DELETE FROM pastevents WHERE id = :id";
        $stmt = $conn->prepare($delete_sql);
        $stmt->execute([':id' => $delete_id]);

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
    <title>SAFE | Seznam Událostí</title>
    <script>
        function confirmDelete(event, url) {
            event.preventDefault();
            if (confirm("Opravdu chcete smazat tuto událost?")) {
                window.location.href = url;
            }
        }
    </script> 
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
<h2>Seznam událostí</h2><br>
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
        $sql = "SELECT id, title, created_at FROM pastevents ORDER BY id DESC";
        $stmt = $conn->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows && count($rows) > 0) {
            foreach ($rows as $row) {
                $created_at = !empty($row['created_at']) 
                    ? date('d.m.Y H:i', strtotime($row['created_at'])) 
                    : 'Neznámé datum';

                $title = !empty($row['title']) 
                    ? htmlspecialchars(strip_tags($row['title']), ENT_QUOTES, 'UTF-8') 
                    : 'Bez názvu';

                echo "<tr>";
                echo "<td>" . $created_at . "</td>";
                echo "<td>" . $title . "</td>";
                echo "<td class='action-buttons'>";
                echo "<a href='../upravit/?id=" . (int)$row['id'] . "' class='edit-btn'><i class='bx bx-edit-alt'></i></a>";
                echo "<a href='#' class='delete-btn' onclick='confirmDelete(event, \"?delete_id=" . (int)$row['id'] . "\")'><i class='bx bxs-tag-x'></i></a>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Žádné události nejsou k dispozici.</td></tr>";
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
