<?php
require_once '../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <title>SAFE | Seznam Programu</title>
    <style>
        * {
            font-family: "Poppins", sans-serif !important;
        }
    </style>
    <script>
        function confirmDeleteRow(form) {
            if (confirm("Opravdu chcete smazat tento řádek programu?")) {
                form.submit();
            }
        }

        function confirmDeleteAll(form) {
            if (confirm("Opravdu chcete smazat celý program?")) {
                form.submit();
            } else {
            return false;
            }
        }

        function toggleEdit(button, rowId) {
            const row = document.querySelector(`#row-${rowId}`);
            const inputs = row.querySelectorAll('input, textarea');

            if (button.dataset.editing === "false") {
                inputs.forEach(input => input.removeAttribute('readonly'));
                button.dataset.editing = "true";
                button.innerHTML = "<i class='bx bx-save'></i>";
            } else {
                row.querySelector('form.edit-form').submit();
            }
        }
    </script>
    <?php include '../../skeleton/head.php' ?>
</head>
<body>
<?php include '../../skeleton/headerAdmin.php' ?>

<div class="content">
    <h2>Seznam programu</h2><br>

    <div class="extraButtons">
        <a href="pridat/" class="addButton">+</a>
        <form method="POST" action="./" onsubmit="return confirmDeleteAll(this);">
            <button type="submit" name="delete_all_programme" class="deleteButton"><i class="bx bx-x"></i></button>
        </form>
    </div>
    <br><br>

    <?php
    include '../../skeleton/db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_programme'])) {
        try {
            $delete_all_sql = "DELETE FROM programme";
            $conn->exec($delete_all_sql);
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Chyba při mazání programu: " . $e->getMessage() . "</p>";
        }
    }

    if (isset($_POST['delete_row'])) {
        $row_id = $_POST['row_id'];
        $delete_sql = "DELETE FROM programme WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->execute([$row_id]);
    }

    if (isset($_POST['edit_row'])) {
        $row_id = $_POST['row_id'];
        $description = $_POST['description'];

        $update_sql = "UPDATE programme SET description = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->execute([$description, $row_id]);
    }

    $sql = "SELECT id, description FROM programme";
    $stmt = $conn->query($sql);
    $programRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($programRows) > 0) {
        echo "<table>";
        foreach ($programRows as $row) {
            $row_id = $row['id'];
            echo "<tr id='row-$row_id'>";
            echo "<form method='POST' class='edit-form'>";
            echo "<td class='programmeDes'><textarea name='description' readonly>" . htmlspecialchars($row['description']) . "</textarea></td>";
            echo "<td class='action-buttons programmeButtons'>";
            echo "<button class='edit-btn' type='button' data-editing='false' onclick='toggleEdit(this, $row_id)'><i class='bx bx-edit-alt'></i></button>";
            echo "<input type='hidden' name='row_id' value='$row_id'>";
            echo "<input type='hidden' name='edit_row' value='1'>";
            echo "</form>";

            echo "<form method='POST' onsubmit='confirmDeleteRow(this); return false;'>";
            echo "<input type='hidden' name='row_id' value='$row_id'>";
            echo "<input type='hidden' name='delete_row' value='1'>";
            echo "<button class='delete-btn' type='submit'><i class='bx bxs-tag-x'></i></button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Žádný program není k dispozici.";
    }
    ?>
</div>
</body>
</html>
