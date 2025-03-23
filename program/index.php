<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | Program</title>
    </head>
    <?php include '../skeleton/head.php'?>
    <body class="programmepage-body">
    <?php include '../skeleton/header.php'?>
        <div class="mobile-translate">
            <a href="../en/programme/">CZ/EN</a>
        </div>
        <?php include '../skeleton/navbar.php'?>
            <a href="../en/programme/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main>
            <div class="heading">
                <h1>PROGRAM</h1>
            </div>
            <div class="programme">
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

            $sql = "SELECT description FROM programme ORDER BY created_at ASC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div>";
                    echo "<label class='programmeLabel'>" . htmlspecialchars($row['description']) . "</label>";
                    echo "</div>";
                }
            }
            $conn->close();
            ?>
            </div>
        </main>
            <?php include '../skeleton/footer.php'?>
            <?php include '../skeleton/toTop.php' ?>
            <script src="../javascript/main.js"></script>
    </body>
</html>