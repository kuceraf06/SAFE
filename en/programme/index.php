<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SAFE | Programme</title>
    </head>
    <?php include '../../skeleton/head.php'?>
    <body class="programmepage-body">
    <?php include '../../skeleton/header-en.php'?>
        <div class="mobile-translate">
            <a href="../../program/">CZ/EN</a>
        </div>
        <?php include '../../skeleton/navbar-en.php'?>
            <a href="../../program/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main>
            <div class="heading">
                <h1>PROGRAMME</h1>
            </div>
            <div class="programme">
            <?php
            include '../../skeleton/db_connect.php';

            try {
                $stmt = $conn->query("SELECT description FROM programmeen ORDER BY created_at ASC");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    echo "<div>";
                    echo "<label class='programmeLabel'>" . htmlspecialchars($row['description']) . "</label>";
                    echo "</div>";
                }
            } catch (PDOException $e) {
                die("Chyba při načítání programu: " . $e->getMessage());
            }
            ?>
            </div>
        </main>
            <?php include '../../skeleton/footer-en.php'?>
            <?php include '../../skeleton/toTop.php' ?>
        <script>
             document.addEventListener("DOMContentLoaded", function() {
                var programmeLabels = document.querySelectorAll('.programmeLabel');
                
                programmeLabels.forEach(function(label) {
                    label.addEventListener('click', function() {
                        var arrowIcon = this.querySelector('.fa-caret-up');
                        arrowIcon.classList.toggle('rotate');
                    });
                });
            });
        </script>
    </body>
</html>