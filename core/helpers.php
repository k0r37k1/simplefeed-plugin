<?php
defined('INC_ROOT') || die;

/**
 * Erzeugt URL‑freundlichen Slug aus Titel.
 */
function sf_generateSlug(string $title): string {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Lädt alle Posts aus data/*.json, sortiert nach Datum.
 */
function sf_loadPosts(): array {
    $dataPath = __DIR__ . '/../data';
    $files = glob($dataPath . '/*.json');
    $posts = [];
    foreach ($files as $f) {
        $j = json_decode(file_get_contents($f), true);
        if ($j) $posts[] = $j;
    }
    usort($posts, fn($a,$b)=>strtotime($b['date'])-strtotime($a['date']));
    return $posts;
}
