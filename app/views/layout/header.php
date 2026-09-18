<?php
/**
 * Hlavička webu SAFE: topheader lišta, logo, navigace (desktop dropdown),
 * hamburger + celoobrazovkové mobilní menu a přepínač jazyka.
 * Jeden soubor pro oba jazyky – texty se berou z pole $nav podle $lang.
 */
$nav = [
    'cs' => [
        'about'      => 'O AKCI',
        'aboutEvent' => 'O AKCI',
        'sponsors'   => 'SPONZOŘI',
        'events'     => 'MINULÉ ROČNÍKY',
        'awards'     => 'OCENĚNÍ',
        'program'    => 'PROGRAM',
        'reservation'=> 'VSTUPENKY',
        'contact'    => 'KONTAKT',
    ],
    'en' => [
        'about'      => 'ABOUT',
        'aboutEvent' => 'ABOUT',
        'sponsors'   => 'SPONSORS',
        'events'     => 'PAST EVENTS',
        'awards'     => 'AWARDS',
        'program'    => 'PROGRAMME',
        'reservation'=> 'TICKETS',
        'contact'    => 'CONTACT',
    ],
][$lang];
?>
<div class="topheader">
    <div>Miners Kladno <span class="special">Baseball &amp; Softball</span></div>
    <div class="header-icons">
        <a href="https://softball.cz" target="_blank"><img src="<?= asset('images/common/softballczech.png') ?>" alt=""></a>
        <a href="https://www.baseball.cz" target="_blank"><img src="<?= asset('images/common/baseballczechia.png') ?>" alt=""></a>
        <a href="https://www.flickr.com/photos/201375961@N07/albums" target="_blank"><img src="<?= asset('images/common/zonerama.png') ?>" alt=""></a>
        <a href="https://www.facebook.com/minerskladno" target="_blank"><img src="<?= asset('images/common/FB.png') ?>" alt=""></a>
        <a href="https://www.instagram.com/minerskladno/" target="_blank"><img src="<?= asset('images/common/IG.png') ?>" alt=""></a>
    </div>
</div>
<header class="header">
    <a href="<?= url('') ?>"><img id="logo" src="<?= asset(safe_site_logo()) ?>" alt="SAFE logo"></a>

    <!-- hamburger – viditelný jen na mobilu, otevírá celoobrazovkové menu -->
    <i class="bx bx-menu menu-icons" id="menu-icon"></i>

    <div class="mobile-translate">
        <a href="<?= switchUrl() ?>">CZ/EN</a>
    </div>

    <nav class="navbar">
        <!-- ===== desktop: rozklikávací dropdown ===== -->
        <div class="dropdown">
            <p class="dropdown-toggle" id="aboutButton"><?= $nav['about'] ?> <i class="fa-solid fa-angle-right toggleIcon"></i></p>
            <div class="dropdown-content">
                <a href="<?= url('about') ?>"><?= $nav['aboutEvent'] ?></a>
                <a href="<?= url('sponsors') ?>"><?= $nav['sponsors'] ?></a>
                <a href="<?= url('events') ?>"><?= $nav['events'] ?></a>
                <a href="<?= url('awards') ?>"><?= $nav['awards'] ?></a>
            </div>
        </div>

        <!-- ===== společné odkazy ===== -->
        <a href="<?= url('program') ?>"><?= $nav['program'] ?></a>
        <a href="<?= url('reservation') ?>"><?= $nav['reservation'] ?></a>
        <a href="https://www.minerskladno.cz" target="_blank">MINERS</a>
        <a href="<?= url('contact') ?>"><?= $nav['contact'] ?></a>
        <a href="<?= switchUrl() ?>" class="pc-translate">CZ/EN</a>
    </nav>
</header>

<!-- ===== mobil: celoobrazovkové menu (otevírá hamburger) ===== -->
<div class="mobile-nav" id="mobileNav">
    <!-- hlavní úroveň -->
    <div class="mobile-nav-panel active" data-panel="main">
        <div class="mobile-nav-bar">
            <i class="bx bx-x mobile-nav-close"></i>
        </div>
        <ul class="mobile-nav-list">
            <li>
                <button type="button" class="mobile-nav-item" data-submenu="about">
                    <?= $nav['about'] ?> <i class="bx bx-chevron-right"></i>
                </button>
            </li>
            <li><a href="<?= url('program') ?>"><?= $nav['program'] ?></a></li>
            <li><a href="<?= url('reservation') ?>"><?= $nav['reservation'] ?></a></li>
            <li><a href="https://www.minerskladno.cz" target="_blank">MINERS</a></li>
            <li><a href="<?= url('contact') ?>"><?= $nav['contact'] ?></a></li>
        </ul>
    </div>

    <!-- podmenu: O AKCI -->
    <div class="mobile-nav-panel" data-panel="about">
        <div class="mobile-nav-bar">
            <i class="bx bx-chevron-left mobile-nav-back"></i>
            <i class="bx bx-x mobile-nav-close"></i>
        </div>
        <ul class="mobile-nav-list">
            <li><a href="<?= url('about') ?>"><?= $nav['aboutEvent'] ?></a></li>
            <li><a href="<?= url('sponsors') ?>"><?= $nav['sponsors'] ?></a></li>
            <li><a href="<?= url('events') ?>"><?= $nav['events'] ?></a></li>
            <li><a href="<?= url('awards') ?>"><?= $nav['awards'] ?></a></li>
        </ul>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ===== desktopový dropdown: klik na nadpis (nebo šipku) rozbalí obsah,
        // klik kamkoli jinam zavře. Šipka se otáčí třídou .rotate-90. =====
        var toggleIds = ['aboutButton'];
        var toggles = toggleIds.map(function (id) {
            var toggle = document.getElementById(id);
            return { toggle: toggle, icon: toggle ? toggle.querySelector('.toggleIcon') : null };
        }).filter(function (t) { return t.toggle; });

        toggles.forEach(function (item) {
            item.toggle.addEventListener('click', function () {
                toggleDropdown(item.toggle, item.icon);
            });
            if (item.icon) {
                item.icon.addEventListener('click', function (event) {
                    toggleDropdown(item.toggle, item.icon);
                    event.stopPropagation();
                });
            }
        });

        function toggleDropdown(toggle, icon) {
            var dropdownContent = toggle.nextElementSibling;
            var isOpen = dropdownContent.style.display === 'block';
            closeAllDropdowns();
            dropdownContent.style.display = isOpen ? 'none' : 'block';
            if (icon) {
                icon.classList.toggle('rotate-90', !isOpen);
            }
            setTimeout(function () {
                if (icon) icon.style.transition = 'transform 0.3s ease';
                dropdownContent.style.transition = isOpen ? 'opacity 0.3s' : 'opacity 0.5s';
            }, 50);
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-content').forEach(function (content) {
                content.style.display = 'none';
            });
            toggles.forEach(function (item) {
                if (item.icon) item.icon.classList.remove('rotate-90');
            });
        }

        document.addEventListener('click', function (event) {
            if (!event.target.matches('.dropdown-toggle')) {
                closeAllDropdowns();
            }
        });

        // ===== mobilní celoobrazovkové menu =====
        var mobileNav = document.getElementById('mobileNav');

        function showPanel(name) {
            mobileNav.querySelectorAll('.mobile-nav-panel').forEach(function (panel) {
                panel.classList.toggle('active', panel.dataset.panel === name);
            });
        }

        document.getElementById('menu-icon').addEventListener('click', function () {
            showPanel('main');
            mobileNav.classList.add('open');
        });

        mobileNav.querySelectorAll('.mobile-nav-close').forEach(function (btn) {
            btn.addEventListener('click', function () {
                mobileNav.classList.remove('open');
            });
        });

        mobileNav.querySelectorAll('.mobile-nav-back').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showPanel('main');
            });
        });

        mobileNav.querySelectorAll('.mobile-nav-item[data-submenu]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showPanel(btn.dataset.submenu);
            });
        });
    });
</script>
