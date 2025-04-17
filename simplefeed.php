<?php
/**
 * SimpleFeed Plugin for WonderCMS
 * 
 * A minimalist feed plugin with tags and navigation for WonderCMS.
 * 
 * @version 1.0.0
 * @author k0r37k1
 * @license MIT
 */
defined('INC_ROOT') || die;
global $Wcms;

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if WonderCMS functions are available
if (!function_exists('tools\inc')) {
    echo '<div class="error">SimpleFeed Plugin Error: WonderCMS core functions not available</div>';
    return;
}

try {
    // Core-Funktionen einbinden
    tools\inc(__DIR__ . '/core/settings.php');
    tools\inc(__DIR__ . '/core/helpers.php');

    // Settings laden
    $config = sf_getConfig();

    // data-Verzeichnis anlegen, falls nicht vorhanden
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            throw new Exception('Failed to create data directory');
        }
    }

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
    
    // JavaScript einbinden
    $Wcms->addListener('js', function(array $js) use ($Wcms) {
        $js[] = $Wcms->url('plugins/simplefeed/js/simplefeed.js');
        return $js;
    });

    // Page-Listener für Routing aller simplefeed‑Seiten
    $Wcms->addListener('page', function(array $page) {
        $pg = $_GET['page'] ?? '';
        if ($pg !== 'simplefeed') return $page;

        ob_start();
        include __DIR__ . '/core/routing.php';
        $page['content'] = ob_get_clean();
        $page['title'] = 'SimpleFeed';
        return $page;
    });

} catch (Exception $e) {
    // Log error and display friendly message
    error_log('SimpleFeed Plugin Error: ' . $e->getMessage());
    echo '<div class="error">SimpleFeed Plugin Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</div>';
}
