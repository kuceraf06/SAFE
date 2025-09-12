<?php
require_once '../../skeleton/auth.php';
include '../../skeleton/db_connect.php';

function cz_month($monthNumber) {
    $months = [
        1 => 'ledna',
        2 => 'února',
        3 => 'března',
        4 => 'dubna',
        5 => 'května',
        6 => 'června',
        7 => 'červenec',
        8 => 'srpna',
        9 => 'září',
        10 => 'října',
        11 => 'listopadu',
        12 => 'prosince'
    ];
    return $months[$monthNumber] ?? '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['datum_cas'])) {
    $datum_cas = $_POST['datum_cas'];
    try {
        $sql = "UPDATE termin SET datum_cas = :datum_cas WHERE id = 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':datum_cas' => $datum_cas]);
    } catch (PDOException $e) {
        $errorMessage = "Chyba při aktualizaci: " . htmlspecialchars($e->getMessage());
    }
}

$sql = "SELECT datum_cas FROM termin WHERE id=1";
$stmt = $conn->query($sql);
$currentTerm = $stmt->fetch(PDO::FETCH_ASSOC);

$inputValue = '';
if ($currentTerm && $currentTerm['datum_cas']) {
    $dt = new DateTime($currentTerm['datum_cas']);
    $inputValue = $dt->format('Y-m-d\TH:i');
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>SAFE | Termín</title>
    <style>
        * { font-family: "Poppins", sans-serif !important; }
        .dateTime { padding: 5px; font-size: 16px; margin-bottom: 10px; }
        .saveButton { padding: 8px 16px; font-size: 16px; cursor: pointer; }
    </style>
    <?php include '../../skeleton/head.php' ?>
</head>
<body>
    <?php include '../../skeleton/headerAdmin.php' ?>
    <div class="content">
        <h2>Upravit termín</h2><br>

        <?php if (!empty($successMessage)): ?>
            <p style="color: green; margin-top: 15px;"><?= $successMessage ?></p>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <p style="color: red; margin-top: 15px;"><?= $errorMessage ?></p>
        <?php endif; ?>

        <?php if ($currentTerm && $currentTerm['datum_cas']): 
            $datetime = new DateTime($currentTerm['datum_cas']);
            $day = $datetime->format('j');
            $month = cz_month((int)$datetime->format('n'));
            $year = $datetime->format('Y');
            $time = $datetime->format('H:i');
        ?>
            <p><strong>Aktuální termín:</strong> <?= "$day. $month $year od $time" ?></p>
        <?php else: ?>
            <p>Aktuální termín není nastaven.</p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="datetime-local" id="datum_cas" name="datum_cas" class="dateTime" value="<?= htmlspecialchars($inputValue) ?>" required><br>
            <button type="submit" class="saveButton">Upravit termín</button>
        </form>
    </div>
</body>
</html>
