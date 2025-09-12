<?php
require_once '../../../skeleton/auth.php';
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <title>SAFE | EN Seznam Programu</title>
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
    <?php include '../../../skeleton/head.php' ?>
</head>
<body>
<?php include '../../../skeleton/headerAdmin.php' ?>

<div class="content">
    <h2>EN Seznam programu</h2><br>

    <div class="extraButtons">
        <a href="pridat/" class="addButton">+</a>
        <form method="POST" action="./" onsubmit="return confirmDeleteAll(this);">
            <button type="submit" name="delete_all_programme" class="deleteButton"><i class="bx bx-x"></i></button>
        </form>
    </div>
    <br><br>


    <?php
    include '../../../skeleton/db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_programme'])) {
        $conn->exec("DELETE FROM programmeen");
    }

    if (isset($_POST['delete_row'])) {
        $row_id = intval($_POST['row_id']);
        $stmt = $conn->prepare("DELETE FROM programmeen WHERE id = :id");
        $stmt->execute([':id' => $row_id]);
    }

    if (isset($_POST['edit_row'])) {
        $row_id = intval($_POST['row_id']);
        $description = $_POST['description'];
        $stmt = $conn->prepare("UPDATE programmeen SET description = :description WHERE id = :id");
        $stmt->execute([
            ':description' => $description,
            ':id' => $row_id
        ]);
    }

    $stmt = $conn->query("SELECT id, description FROM programmeen");
    $program = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($program)) {
        echo "<table>";
        foreach ($program as $row) {
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
