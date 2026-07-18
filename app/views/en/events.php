<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';

$pageTitle = 'SAFE | Past Events';
$bodyClass = 'pastevents-body';
$pageCss   = 'events';

$events = safe_events();
$total = count($events);
?>
<main class="pastEvents-page page-width">
            <div class="heading">
                <h1>PAST EVENTS</h1>
            </div>
            <div class="pastEvents-container">
                <section class="evetnspage">
                <?php foreach ($events as $i => $row): ?>
                    <?php $images = json_decode($row['images'] ?: '[]', true) ?: []; ?>
                    <div class="event-main">
                        <div class="pastEvents-content">
                            <?= $row['title_en'] ?>
                            <?= $row['description_en'] ?>
                        </div>
                        <?php if (!empty($images)): ?>
                            <div class="gallery">
                                <center>
                                    <?php foreach ($images as $image): ?>
                                        <img src="<?= asset($image) ?>" alt="SAFE">
                                    <?php endforeach; ?>
                                </center>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($i < $total - 1): ?><hr><?php endif; ?>
                <?php endforeach; ?>
                </section>
            </div>
        </main>
