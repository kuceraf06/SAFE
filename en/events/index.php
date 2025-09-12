<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SAFE | Past Events</title>
    </head>
    <?php include '../../skeleton/head.php'?>
    <body class="pastevents-body">
    <?php include '../../skeleton/header-en.php'?>
        <div class="mobile-translate">
            <a href="../../rocniky/">CZ/EN</a>
        </div>
        <?php include '../../skeleton/navbar-en.php'?>
            <a href="../../rocniky/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="pastEvents-page">
            <div class="heading">
                <h1>PAST EVENTS</h1>
            </div>
            <div class="pastEvents-container">
                <section class="evetnspage">
                <?php
                include '../../skeleton/db_connect.php';

                $sql = "SELECT title, description, images FROM pasteventsen ORDER BY id DESC";
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
                                $correctedPath = '../../images/' . basename($image);
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
            <?php include '../../skeleton/footer-en.php'?>
            <?php include '../../skeleton/toTop.php' ?>
    </body>
</html>