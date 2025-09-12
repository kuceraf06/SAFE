<?php
include '../skeleton/db_connect.php';

try {
    $stmt = $conn->query("SELECT datum_cas FROM termin LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $termin = $row ? $row['datum_cas'] : null;
} catch (PDOException $e) {
    die("Chyba při načítání termínu: " . $e->getMessage());
}
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
                <i>The date has not been set.</i>
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