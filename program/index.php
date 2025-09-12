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
            include '../skeleton/db_connect.php';

            try {
                $stmt = $conn->query("SELECT description FROM programme ORDER BY created_at ASC");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    echo "<div>";
                    echo "<label class='programmeLabel'>" . htmlspecialchars($row['description']) . "</label>";
                    echo "</div>";
                }
            } catch (PDOException $e) {
                echo "<p>Chyba při načítání programu: " . $e->getMessage() . "</p>";
            }
            ?>
            </div>
        </main>
            <?php include '../skeleton/footer.php'?>
            <?php include '../skeleton/toTop.php' ?>
            <script src="../javascript/main.js"></script>
    </body>
</html>