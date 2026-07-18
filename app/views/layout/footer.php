<?php
// Patička webu SAFE - jeden dvojjazyčný soubor, texty z pole $foot podle $lang.
$foot = [
    'cs' => [
        'websites'  => 'DALŠÍ WEBY',
        'follow'    => 'SLEDUJTE NÁS',
        'contactUs' => 'KONTAKTUJTE NÁS',
        'rights'    => 'Všechna práva vyhrazena',
    ],
    'en' => [
        'websites'  => 'OTHER WEBSITES',
        'follow'    => 'FOLLOW US',
        'contactUs' => 'CONTACT US',
        'rights'    => 'All rights reserved',
    ],
][$lang];
?>
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-container">
                    <div class="row-footer">
                        <div class="footer-col">
                            <img src="<?= asset('images/common/SAFE-logo.png') ?>" alt="Miners Kladno logo">
                        </div>
                        <div class="footer-col">
                            <h4><?= $foot['websites'] ?></h4>
                            <ul>
                                <li><a href="https://www.minerskladno.cz" target="_blank">MINERS</a></li>
                                <li><a href="https://barochova.wixsite.com/kiwileague" target="_blank">KIWI&nbsp;LEAGUE</a></li>
                                <li><a href="https://merch.minerskladno.cz" target="_blank">E&minus;SHOP</a></li>
                            </ul>
                        </div>
                        <div class="footer-col">
                            <h4><?= $foot['follow'] ?></h4>
                            <div class="social-links">
                                <a href="https://www.facebook.com/minerskladno" target="_blank"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.flickr.com/photos/201375961@N07/" target="_blank"><i class='bx bxl-flickr'></i></a>
                                <a href="https://www.instagram.com/minerskladno/" target="_blank"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="footer-col">
                            <h4><?= $foot['contactUs'] ?></h4>
                            <div class="contact-footer">
                                <p><a href="mailto:safe@minerskladno.cz" target="_blank">safe@minerskladno.cz</a></p>
                                <p>Miners Kladno, z.s.
                                    <br>
                                    U Trati 3489
                                    <br>
                                    272 01 Kladno 1
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <a href="mailto:safe@minerskladno.cz">Miners Kladno <?= date('Y') ?> &copy; <?= $foot['rights'] ?></a>
            </div>
        </footer>

        <!-- tlačítko zpět nahoru -->
        <a href="#" class="to-top">
            <i class="fas fa-chevron-up"></i>
        </a>
