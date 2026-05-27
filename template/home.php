<?php
// template/home.php

function outputStatsGrid($stats) { ?>
    <div class="stats-grid">
        <?php foreach ($stats as $stat): ?>
            <article class="stat-box">
                <p class="stat-number">
                    <?= htmlspecialchars((string)$stat['number']) ?><?= htmlspecialchars($stat['suffix']) ?>
                </p>
                <p class="stat-label">
                    <?= htmlspecialchars($stat['label']) ?>
                </p>
            </article>
        <?php endforeach; ?>
    </div>
<?php }

function outputClassTicker($classTypes) { ?>
    <section class="classes-section">
        <div class="ticker-track">

            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="ticker-group">
                    <?php foreach ($classTypes as $type): ?>
                        <span><?= htmlspecialchars($type['label']) ?></span><span>●</span>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>

        </div>
    </section>
<?php }

function outputFeatureCard($icon, $title, $text, $link = null, $class = 'feature-card') {
    ?>
    <article class="<?= htmlspecialchars($class) ?>">
        <a href="<?= htmlspecialchars($link ?? '#') ?>" class="feature-card-link">
            <div class="feature-icon"><?= htmlspecialchars($icon) ?></div>
            <h3><?= htmlspecialchars($title) ?></h3>
            <p><?= htmlspecialchars($text) ?></p>
        </a>
    </article>
<?php }