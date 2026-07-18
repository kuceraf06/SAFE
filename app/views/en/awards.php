<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';

$pageTitle = 'SAFE | Awards';
$bodyClass = 'awardedpage-body';
$pageCss   = 'awards';

$awards = safe_awards_grouped();     // [rok][kategorie] => [ocenění, ...]
$years  = array_keys($awards);       // seřazeno sestupně (nejnovější první)
$lang   = 'en';
?>
<main class="awardedMain page-width">
            <div class="heading">
                <h1>AWARDS</h1>
            </div>

            <?php if (empty($years)): ?>
                <p class="no-awards">Awards will be published soon.</p>
            <?php else: ?>
                <?php foreach ($years as $yi => $year): ?>
                    <div id="<?= e($year) ?>"></div>

                    <!-- výběr roku (mimo bílé boxy) -->
                    <div class="navButtons">
                        <div class="dropdownYear">
                            <button class="dropdownButton"><?= e($year) ?> <i class='bx bx-chevron-down'></i></button>
                            <div class="dropdownContentYear">
                                <?php foreach ($years as $y): ?>
                                    <a href="#<?= e($y) ?>"<?= $y === $year ? ' class="activeYear"' : '' ?>><?= e($y) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- každá kategorie = jeden bílý box (kategorie na boku + osoby) -->
                    <?php foreach ($awards[$year] as $category => $players): ?>
                        <div class="awardedPlayers">
                            <div class="awaredHeading">
                                <h2><?= e(safe_translate_category($category, $lang)) ?></h2>
                            </div>
                            <div class="playersList">
                                <?php foreach ($players as $p): ?>
                                    <div class="onePlayer">
                                        <div class="onePlayerText">
                                            <h3><?= $p['title_en'] ?></h3>
                                            <?php if (trim(strip_tags($p['description_en'])) !== ''): ?>
                                                <p><?= $p['description_en'] ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($p['photo'])): ?>
                                            <div class="playersImages">
                                                <img src="<?= asset($p['photo']) ?>" alt="<?= e(safe_translate_category($category, $lang)) ?>">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
