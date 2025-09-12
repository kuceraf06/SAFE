<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>SAFE | Minulé Ročníky</title>
    </head>
    <?php include '../skeleton/head.php'?>
    <body class="pastevents-body">
    <?php include '../skeleton/header.php'?>
        <div class="mobile-translate">
            <a href="../en/events/">CZ/EN</a>
        </div>
        <?php include '../skeleton/navbar.php'?>
            <a href="../en/events/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="pastEvents-page">
            <div class="heading">
                <h1>MINULÉ ROČNÍKY</h1>
            </div>
            <div class="pastEvents-container">
                <section class="evetnspage">
                <?php
                include '../skeleton/db_connect.php';

                $sql = "SELECT title, description, images FROM pastevents ORDER BY id DESC";
                $result = $conn->query($sql);

                if ($result) {
                    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
                    $totalRows = count($rows);
                    $currentRow = 0;

                    foreach ($rows as $row) {
                        $images = json_decode($row['images'], true);
                        echo "<div class='event-main'>";
                        echo "<div class='pastEvents-content'>";
                        echo "<h2>{$row['title']}</h2>";
                        echo "<p>{$row['description']}</p>";
                        echo "</div>";

                        if (!empty($images)) {
                            echo "<div class='gallery'>";
                            echo "<center>";
                            foreach ($images as $image) {
                                $correctedPath = '../images/' . basename($image);
                                echo "<img src='$correctedPath' alt='Event Image'>";
                            }
                            echo "</center>";
                            echo "</div>";
                        }
                        echo "</div>";            

                        if ($currentRow < $totalRows - 1) {
                            echo "<hr>";
                        }

                        $currentRow++;
                    }
                }
                ?>
                </section>
            </div>
        </main>
            <?php include '../skeleton/footer.php'?>
            <?php include '../skeleton/toTop.php' ?>
    </body>
    <script src="../javascript/main.js"></script>
</html>