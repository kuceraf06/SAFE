<?php
include '../skeleton/db_connect.php';

$sql = "SELECT datum_cas FROM termin LIMIT 1";
$result = $conn->query($sql);

$termin = null;
$row = $result->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $termin = $row['datum_cas'];
}

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
        <title>SAFE | O Akci</title>
    </head>
    <?php include '../skeleton/head.php'?>
    <body class="aboutpage-body">
    <?php include '../skeleton/header.php'?>
        <div class="mobile-translate">
            <a href="../en/about/">CZ/EN</a>
        </div>
        <?php include '../skeleton/navbar.php'?>
            <a href="../en/about/" class="before pc-translate">CZ/EN</a>
            </nav>
        </header>
        <main class="about-page">
            <div class="heading">
                <h1>O AKCI</h1>
                <p>SAFE KAŽDOROČNĚ POŘÁDÁ BASEBALLOVÝ A SOFTBALLOVÝ KLUB MINERS KLADNO.</p>
            </div>
            <div class="about-container">
                <section class="aboutpage">
                    <div class="about-image">
                        <img src="../images/SAFE-130.jpg">
                    </div>
                    <div class="about-content">
                        <h2 id="about">Co je to SAFE</h2>
                        <p>SAFE je tradiční slavnostní galavečer baseballového a softballového oddílu <a href="https://www.minerskladno.cz" target="_blank" class="bolder">Miners Kladno</a>, který uzavírá uplynulou soutěžní sezonu a završuje celoroční snažení všech hráčů a trenérů klubu.
                        Na tomto slavnostním zakončení sezóny zhodnotíme každoroční výkony, odměníme úspěchy a pogratulujeme těm nejlepším. Již tradičně na tuto akci zveme vzácné hosty z řad našich zřizovatelů a podporovatelů klubu jako představitele Města Kladna a Středočeského kraje a dále baseballové a softballové reprezentanty a reprezentantky.
                        SAFE je také poděkováním našim <a href="../sponzori/" class="site-links">sponzorům</a>, fanouškům, rodičům a celé naší Miners Family za nekončící podporu, bez které bychom to, co děláme, dělat nemohli. 
                        Užijte si i letošní SAFE jak náleží, těšíme se na Vás.<br>
                        Více o SAFE se můžete dozvědět <a href="#history" class="site-links">zde</a>.<br>
                        Kdy a kde se akce koná se dozvíte <a href="#location" class="site-links">níže</a>.</p>
                    </div>
                </section>
            </div>
            <hr>
            <div class="about-container">
                <section class="aboutpage">
                    <div class="about-image">
                        <img src="../images/kino-sokol1.jpg">
                    </div>
                    <div class="about-content">
                        <h2 id="location">Kdy a kde?</h2>
                        <p>SAFE se tradičně pořádá vždy po konci sezóny.</p>
                        <p><strong>KDY:</strong><?php if ($termin):
                                                      $datetime = new DateTime($termin);
                                                      $day = $datetime->format('j');
                                                      $month = cz_month((int)$datetime->format('n'));
                                                      $year = $datetime->format('Y');
                                                      $time = $datetime->format('H:i'); ?>
                                                      <?php echo "$day. $month $year od $time"; ?>
                                                    <?php else: ?>
                                                        <p>Termín nebyl nastaven.</p>
                                                    <?php endif; ?></p>
                        <p><strong>ADRESA:</strong> <a target="_blank" href="https://www.google.com/maps/place//data=!4m2!3m1!1s0x470bb7da602a6c4b:0xe7204d94c85ab6b1?sa=X&ved=1t:8290&ictx=111">T. G. Masaryka 2320, 272 01 Kladno 1</a></p>
                        <p><strong>KUDY:</strong> Kino Sokol se nachází v Kladně u nám. Svobody na pěší zóně. Lehce se k nám dostanete městskou 
                                                  či pražskou dopravou autobusy (všechny spoje staví na náměstí)</p>
                    </div>
                </section>
            </div>
            <hr>
            <div class="about-container">
                <section class="aboutpage">
                    <div class="about-content">
                        <h2 id="history">Historie</h2>
                        <h3>SAFE 1993 - 2001</h3>
                        <p>Nápad uspořádat slavnostní zakončení sezóny, sezvat hráče, rodiče a zajímavé hosty na jedno místo a důstojně ocenit ty nejlepší, se zrodil v softballovém a baseballovém oddíle <strong>LASO Kladno</strong> v roce <strong>1993</strong>.<br>
                        Slavnost SAFE byla už ve své době unikátní. To, co dnes bývá u řady oddílů běžné, jsme na Kladně začali dělat mezi prvními v republice. Už tenkrát měl SAFE atributy profesionálně zprodukované podívané, s moderátorem, hosty, hudebním doprovodem, filmovými spoty a diváckými soutěžemi.<br>
                        Galavečer SAFE znamenal opravdový vrchol sezóny pro kladenské hráče i rodiče.<br>
                        Po celých <strong>9 let</strong>, až do roku 2001, se na pódiu SAFE vystřídaly desítky nejlepších hráčů a hráček od těch nejmenších až po dospělé. Ty nejlepší, oceněné titulem Talent roku, jsme pak mohli vídat v dalších letech v českých reprezentacích nebo ligových týmech.
                        </p>
                    </div>
                </section>
            </div>
        </main>
            <?php include '../skeleton/footer.php'?>
            <?php include '../skeleton/toTop.php' ?>
            <script src="../javascript/main.js"></script>
    </body>
</html>