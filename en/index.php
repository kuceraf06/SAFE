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
    // Získání hodnoty termínu
    $row = $result->fetch_assoc();
    $termin = $row['datum_cas'];
}

// Zavření spojení
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SAFE | Home</title>
    </head>
    <?php include '../skeleton/head.php'?>
    <body class="homepage-body">
    <?php include '../skeleton/header-en.php'?>
        <div class="mobile-translate">
            <a href="../">CZ/EN</a>
        </div>
        <?php include '../skeleton/navbar-en.php'?>
            <a href="../" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="main">
            <h1>THE BIGGEST EVENT OF THE YEAR!</h1>
            <br>
            <?php if ($termin): ?>
                <i><?php echo date("j. F Y \o\d H:i", strtotime($termin)); ?></i>
            <?php else: ?>
                <i>Termín nebyl nastaven.</i>
            <?php endif; ?>
            <div class="buttons">
                <a href="reservation/"><button type="button"><span></span>TIKCETS</button></a>
                <a href="about/"><button type="button"><span></span>ABOUT EVENT</button></a>
            </div>
        </main>
            <?php include '../skeleton/footer-en.php'?>
            <?php include '../skeleton/toTop.php' ?>
    </body>
</html>