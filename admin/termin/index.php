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

// Získání aktuálního termínu
$sql = "SELECT datum_cas FROM termin WHERE id=1";
$result = $conn->query($sql);
$currentTerm = $result->fetch_assoc();

// Funkce pro české měsíce
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
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <title>SAFE | Termín</title>
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
        <h2>Upravit termín</h2><br>

        <!-- Zobrazení aktuálního termínu -->
        <?php if ($currentTerm && $currentTerm['datum_cas']): 
            $datetime = new DateTime($currentTerm['datum_cas']);
            $day = $datetime->format('j');
            $month = cz_month((int)$datetime->format('n'));
            $year = $datetime->format('Y');
            $time = $datetime->format('H:i');
        ?>
            <p><strong>Aktuální termín:</strong> <?php echo "$day. $month $year od $time"; ?></p>
        <?php else: ?>
            <p>Aktuální termín není nastaven.</p>
        <?php endif; ?>
        <!-- Formulář pro nastavení nového termínu -->
        <form action="" method="POST">
            <input type="datetime-local" id="datum_cas" name="datum_cas" class="dateTime" value="<?php echo $currentTerm ? $currentTerm['datum_cas'] : ''; ?>" required><br>
            <button type="submit" class="saveButton">Upravit termín</button><br>
            <?php 
            // Zpracování formuláře pro nastavení termínu
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['datum_cas'])) {
                $datum_cas = $_POST['datum_cas'];
                $sql = "UPDATE termin SET datum_cas='$datum_cas' WHERE id=1";  // Předpokládám, že je jen jeden záznam s ID=1
                if ($conn->query($sql) === TRUE) {
                    echo '<p style="color: green; margin-top: 15px;">Termín byl úspěšně aktualizován.</p>';
                } else {
                    echo '<p style="color: red; margin-top: 15px;">Chyba při aktualizaci: ' . htmlspecialchars($conn->error) . '</p>';
                }
            }
            ?>
        </form>
    </div>
</body>
</html>
