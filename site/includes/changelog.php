<?php

/** @return array{current: ?array{version:string,date:string}, older: list<array{version:string,date:string}>} */
function shs_changelog_split_releases(): array
{
    $releases = sh_version_releases_public();
    $current = null;
    $older = [];
    foreach ($releases as $rel) {
        if ($rel['version'] === sh_version()) {
            $current = $rel;
        } else {
            $older[] = $rel;
        }
    }
    if ($current === null && $releases !== []) {
        $current = $releases[0];
        $older = array_slice($releases, 1);
    }
    return ['current' => $current, 'older' => $older];
}

/** @return list<string> */
function shs_changelog_items_for_release(array $rel, array $t): array
{
    $items = $t['changelog_items'][$rel['version']] ?? [];
    if ($items === [] && !empty($t['changelog_notes'][$rel['version']])) {
        $items = [$t['changelog_notes'][$rel['version']]];
    }
    return $items;
}

function shs_changelog_render_release(array $rel, array $t, bool $is_current = false): void
{
    $items = shs_changelog_items_for_release($rel, $t);
    ?>
    <li class="<?= $is_current ? 'is-current' : '' ?>">
        <div class="shs-changelog-head">
            <strong>v<?= htmlspecialchars($rel['version']) ?></strong>
            <time datetime="<?= htmlspecialchars($rel['date']) ?>"><?= htmlspecialchars($rel['date']) ?></time>
        </div>
        <?php if ($items !== []): ?>
        <ul class="shs-changelog-items">
            <?php foreach ($items as $item): ?>
            <li><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </li>
    <?php
}