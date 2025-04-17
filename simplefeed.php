<?php
/**
 * SimpleFeed Plugin Loader
 * Bindet Menü, CSS und Seiten-Listener ins WonderCMS ein.
 */
defined('INC_ROOT') || die;
global $Wcms;

// Core-Funktionen einbinden
tools\inc(__DIR__ . '/core/settings.php');
tools\inc(__DIR__ . '/core/helpers.php');

// Settings laden
$config = sf_getConfig();

// data-Verzeichnis anlegen, falls nicht vorhanden
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

// Menü-Eintrag hinzufügen
$Wcms->addListener('menu', function(array $menu) {
    $menu[] = ['slug' => 'simplefeed', 'name' => 'SimpleFeed'];
    return $menu;
});

// CSS einbinden
$Wcms->addListener('css', function(array $css) use ($Wcms) {
    $css[] = $Wcms->url('plugins/simplefeed/css/feed.css');
    return $css;
});

// Page-Listener für Routing aller simplefeed‑Seiten
$Wcms->addListener('page', function(array $page) {
    $pg = $_GET['page'] ?? '';
    if ($pg !== 'simplefeed') return $page;

    ob_start();
    include __DIR__ . '/core/routing.php';
    $page['content'] = ob_get_clean();
    $page['title']   = 'SimpleFeed';
    return $page;
});
