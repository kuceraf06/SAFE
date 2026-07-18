<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';

$pageTitle = 'SAFE | O Akci';
$bodyClass = 'aboutpage-body';
$pageCss   = 'about';

$termin = safe_termin();
$terminText = safe_format_termin($termin, 'cs');
?>
<main class="about-page page-width">
            <div class="heading">
                <h1>O AKCI</h1>
                <p class="subheading">SAFE každoročně pořádá baseballový a softballový klub Miners Kladno.</p>
            </div>
            <div class="about-container">
                <section class="aboutpage">
                    <div class="about-image">
                        <img src="<?= asset('images/about/SAFE-130.jpg') ?>" alt="SAFE">
                    </div>
                    <div class="about-content">
                        <h2 id="about">Co je to SAFE</h2>
                        <p>SAFE je tradiční slavnostní galavečer baseballového a softballového oddílu <a href="https://www.minerskladno.cz" target="_blank" class="bolder">Miners Kladno</a>, který uzavírá uplynulou soutěžní sezonu a završuje celoroční snažení všech hráčů a trenérů klubu.
                        Na tomto slavnostním zakončení sezóny zhodnotíme každoroční výkony, odměníme úspěchy a pogratulujeme těm nejlepším. Již tradičně na tuto akci zveme vzácné hosty z řad našich zřizovatelů a podporovatelů klubu jako představitele Města Kladna a Středočeského kraje a dále baseballové a softballové reprezentanty a reprezentantky.
                        SAFE je také poděkováním našim <a href="<?= url('sponsors') ?>" class="site-links">sponzorům</a>, fanouškům, rodičům a celé naší Miners Family za nekončící podporu, bez které bychom to, co děláme, dělat nemohli.
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
                        <img src="<?= asset('images/about/kino-sokol1.jpg') ?>" alt="Kino Sokol">
                    </div>
                    <div class="about-content">
                        <h2 id="location">Kdy a kde?</h2>
                        <p>SAFE se tradičně pořádá vždy po konci sezóny.</p>
                        <p><strong>KDY:</strong> <?= $terminText ? e($terminText) : 'Termín nebyl nastaven.' ?></p>
                        <p><strong>ADRESA:</strong> <a target="_blank" href="https://www.google.com/maps/place//data=!4m2!3m1!1s0x470bb7da602a6c4b:0xe7204d94c85ab6b1?sa=X&ved=1t:8290&ictx=111">T. G. Masaryka 2320, 272 01 Kladno 1</a></p>
                        <p><strong>KUDY:</strong> Kino Sokol se nachází v Kladně u nám. Svobody na pěší zóně. Lehce se k nám dostanete městskou či pražskou dopravou autobusy (všechny spoje staví na náměstí)</p>
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
