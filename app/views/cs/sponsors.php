<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';

$pageTitle = 'SAFE | Sponzoři';
$bodyClass = 'partners-body';
$pageCss   = 'sponsors';
?>
<main class="partner-wrap page-width">
            <h1>NAŠI SPONZOŘI</h1>
                <div class="container-partners">
                    <div class="partner-in">
                        <ul>
                            <h2>Děkujeme:</h2>
                            <li class="special-sponsors">
                                <a href="https://www.mestokladno.cz" target="_blank"><img src="<?= asset('images/sponsors/logo_kladno.jpg') ?>" class="image"></a>
                                <a href="https://www.mestokladno.cz" target="_blank"><img src="<?= asset('images/sponsors/kladno_a.jpg') ?>"></a>
                            </li>
                            <li class="special-sponsors">
                                <a href="https://kr-stredocesky.cz/web/urad" target="_blank"><img src="<?= asset('images/sponsors/logo_kraj.jpg') ?>" class="image"></a>
                                <a href="https://kr-stredocesky.cz/web/urad" target="_blank"><img src="<?= asset('images/sponsors/kraj_a.jpg') ?>"></a>
                            </li>
                            <br>
                            <li>
                                <a href="https://hemagel.cz/" target="_blank"><img src="<?= asset('images/sponsors/logo_hemagel.jpg') ?>" class="image"></a>
                                <a href="https://hemagel.cz/" target="_blank"><img src="<?= asset('images/sponsors/hemagel_a.jpg') ?>"></a>
                            </li>
                            <li>
                                <a href="https://hemacut.cz/" target="_blank"><img src="<?= asset('images/sponsors/logo_hemacut.jpg') ?>" class="image"></a>
                                <a href="https://hemacut.cz/" target="_blank"><img src="<?= asset('images/sponsors/hemcut_a.jpg') ?>"></a>
                            </li>
                            <li>    
                                <a href="https://www.underarmour.cz" target="_blank"><img src="<?= asset('images/sponsors/logo_ua.png') ?>" class="image"></a>
                                <a href="https://www.underarmour.cz" target="_blank"><img src="<?= asset('images/sponsors/ua_a.png') ?>"></a>
                            </li>
                            <br id="none">
                            <li>
                                <a href="https://kladenskymesic.cz" target="_blank"><img src="<?= asset('images/sponsors/logo_km.png') ?>" class="image"></a>
                                <a href="https://kladenskymesic.cz" target="_blank"><img src="<?= asset('images/sponsors/kmB.png') ?>"></a>
                            </li>
                            <li>
                                <a href="https://gardenservice.cz" target="_blank"><img src="<?= asset('images/sponsors/logo_aneta.jpg') ?>" class="image"></a>
                                <a href="https://gardenservice.cz" target="_blank"><img src="<?= asset('images/sponsors/anetaB.jpg') ?>"></a>
                            </li>

                            <li>    
                                <a href="https://www.cpzp.cz" target="_blank"><img src="<?= asset('images/sponsors/logo_cpzp.png') ?>" class="image"></a>
                                <a href="https://www.cpzp.cz" target="_blank"><img src="<?= asset('images/sponsors/cpzpB.png') ?>"></a>
                            </li>
                            <br id="none">
                            <li>    
                                <a target="_blank"><img src="<?= asset('images/sponsors/logo_liveout.jpg') ?>" class="image"></a>
                                <a target="_blank"><img src="<?= asset('images/sponsors/liveout_a.jpg') ?>"></a>
                            </li>
                        </ul>
                    </div>
                </div>
        </main>
