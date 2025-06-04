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

// Načtení aktuálního termínu z databáze
$sql = "SELECT datum_cas FROM termin LIMIT 1";
$result = $conn->query($sql);

$termin = null;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $termin = $row['datum_cas'];
}

// Funkce pro české měsíce
function cz_month($monthNumber) {
    $months = [
        1 => 'ledna',
        2 => 'února',
        3 => 'března',
        4 => 'dubna',
        5 => 'května',
        6 => 'června',
        7 => 'července',
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
    <title>SAFE | Domovská Stránka</title>
</head>
<?php include 'skeleton/head.php' ?>
<body class="homepage-body">
<?php include 'skeleton/header.php' ?>
    <div class="mobile-translate">
        <a href="en/">CZ/EN</a>
    </div>
    <?php include 'skeleton/navbar.php' ?>
        <a href="en/" class="before pc-translate">CZ/EN</a>
        </nav>
    </header>
    <main class="main">
        <h1>NEJOČEKÁVANĚJŠÍ AKCE ROKU!</h1>
        <br>

        <?php 
        if ($termin):
            $datetime = new DateTime($termin);
            $day = $datetime->format('j');
            $month = cz_month((int)$datetime->format('n'));
            $year = $datetime->format('Y');
            $time = $datetime->format('H:i');
        ?>
            <i><?php echo "$day. $month $year od $time"; ?></i>
        <?php else: ?>
            <i>Termín nebyl nastaven.</i>
        <?php endif; ?>

        <div class="buttons">
            <a href="rezervace/"><button type="button"><span></span>VSTUPENKY</button></a>
            <a href="onas/"><button type="button"><span></span>O AKCI</button></a>
        </div>
    </main>
<?php include 'skeleton/footer.php' ?>
<?php include 'skeleton/toTop.php' ?>
<script src="javascript/main.js"></script>
</body>
</html>
