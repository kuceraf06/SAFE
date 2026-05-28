<?php
include '../../skeleton/db_connect.php';

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
        <title>SAFE | About Event</title>
    </head>
    <?php include '../../skeleton/head.php'?>
    <body class="aboutpage-body">
    <?php include '../../skeleton/header-en.php'?>
        <div class="mobile-translate">
            <a href="../../onas/">CZ/EN</a>
        </div>
        <?php include '../../skeleton/navbar-en.php'?>
            <a href="../../onas/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="about-page">
            <div class="heading">
                <h1>ABOUT EVENT</h1>
                <p>SAFE IS ANNUALLY ORGANIZED BY THE MINERS KLADNO BASEBALL AND SOFTBALL CLUB.</p>
            </div>
            <div class="about-container">
                <section class="aboutpage">
                    <div class="about-image">
                        <img src="../../images/SAFE-130.jpg">
                    </div>
                    <div class="about-content">
                        <h2 id="about">What is SAFE</h2>
                        <p>SAFE is the traditional gala evening of the <a href="https://www.minerskladno.cz" target="_blank" class="bolder">Miners Kladno</a> baseball and softball team, which closes the past competitive season and completes the year-long efforts of all the club's players and coaches.
                        At this end-of-season celebration, we will review annual performances, reward successes and congratulate the best. Traditionally, we invite special guests from among our founders and supporters of the club, such as representatives of the City of Kladno and the Central Bohemian Region, as well as baseball and softball representatives to this event.
                        SAFE is also a thank you to our <a href="../sponsors/" class="site-links">sponsors</a>, fans, parents and our entire Miners Family for their endless support without which we could not do what we do . 
                        Enjoy this year's SAFE as well, we look forward to seeing you.<br>
                        You can see more about SAFE <a href="#history" class="site-links">here</a>.<br>
                        You can find out when and where the event is taking place <a href="#location" class="site-links">below</a>.</p>
                    </div>
                </section>
            </div>
            <hr>
            <div class="about-container">
                <section class="aboutpage">
                    <div class="about-image">
                        <img src="../../images/kino-sokol1.jpg">
                    </div>
                    <div class="about-content">
                        <h2 id="location">When and where?</h2>
                        <p>SAFE is traditionally held after the end of the season.</p>
                        <p><strong>WHEN:</strong><?php if ($termin): ?>
                                                 <?php echo date("j. F Y H:i", strtotime($termin)); ?>
                                                 <?php else: ?>
                                                    <p>The date has not been set.</p>
                                                 <?php endif; ?></p>
                        <p><strong>ADRESS:</strong> <a target="_blank" href="https://www.google.com/maps/place//data=!4m2!3m1!1s0x470bb7da602a6c4b:0xe7204d94c85ab6b1?sa=X&ved=1t:8290&ictx=111">T. G. Masaryka 2320, 272 01 Kladno 1</a></p>
                        <p><strong>HOW TO FIND US:</strong> Cinema Sokol is located in Kladno near nám. Svobody in the pedestrian zone. You can get to us by city 
                                                  or Prague buses (all buses stop on the square)</p>
                    </div>
                </section>
            </div>
            <hr>
            <div class="about-container">
                <section class="aboutpage">
                    <div class="about-content">
                        <h2 id="history">History</h2>
                        <h3>SAFE 1993 - 2001</h3>
                        <p>The idea to hold a ceremonial end of the season, invite players, parents and interesting guests to one place and honor the best was born in the <strong>LASO Kladno</strong> softball and baseball section in <strong>1993</strong>.<br>
                        The SAFE celebration was already unique in its time. In Kladno, we were among the first in the country to start doing what is common for a number of departments today. Even back then, SAFE had the attributes of a professionally produced spectacle, with a moderator, guests, musical accompaniment, film spots and audience competitions.<br>
                        The SAFE gala evening marked the real highlight of the season for Kladno players and parents.<br>
                        For <strong>9 years</strong>, until 2001, dozens of the best players, from the smallest to adults, took turns on the SAFE stage. The best ones, awarded the title of Talent of the Year, were then able to see in the following years in the Czech national teams or league teams.
                        </p>
                    </div>
                </section>
            </div>
        </main>
            <?php include '../../skeleton/footer-en.php'?>
            <?php include '../../skeleton/toTop.php' ?>
    </body>
</html>