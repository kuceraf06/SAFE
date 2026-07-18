<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';

$pageTitle = 'SAFE | Programme';
$bodyClass = 'programmepage-body';
$pageCss   = 'program';

$programme = safe_programme();
?>
<main class="page-width">
            <div class="heading">
                <h1>PROGRAMME</h1>
            </div>
            <div class="programme">
            <?php if ($programme): ?>
                <?php foreach ($programme as $row): ?>
                    <div>
                        <label class="programmeLabel"><?= e($row['description_en']) ?></label>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>The programme will be published soon.</p>
            <?php endif; ?>
            </div>
        </main>
