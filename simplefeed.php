<?php
/**
 * SimpleFeed Plugin für WonderCMS
 * 
 * Ein minimalistisches Feed-Plugin mit Tags und Navigation für WonderCMS.
 * Mit integrierter Markdown-Unterstützung und Sicherheitsfunktionen.
 * 
 * @version 1.1.0
 * @author k0r37k1
 * @license MIT
 */
defined('INC_ROOT') || die;
global $Wcms;

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
    $Wcms->addListener('menu', function(array $menu) use ($Wcms) {
        // Öffentlich sichtbar - ohne Login erforderlich
        $menu[] = ['slug' => 'simplefeed', 'name' => 'SimpleFeed'];
        return $menu;
    });

    // Admin-Menü-Eintrag hinzufügen, wenn angemeldet
    $Wcms->addListener('adminPanel', function(array $args) use ($Wcms) {
        // Nur wenn Admin angemeldet ist
        if ($Wcms->loggedIn) {
            $args[0] .= '<a href="' . $Wcms->url('?page=simplefeed') . '" class="btn btn-info marginTop5">SimpleFeed</a>';
        }
        return $args;
    });

    // CSS einbinden mit $Wcms->url für korrekte Pfade
    $Wcms->addListener('css', function(array $css) use ($Wcms) {
        $css[] = $Wcms->url('plugins/simplefeed/css/feed.css');
        return $css;
    });
    
    // JavaScript einbinden mit $Wcms->url für korrekte Pfade
    $Wcms->addListener('js', function(array $js) use ($Wcms) {
        $js[] = $Wcms->url('plugins/simplefeed/js/simplefeed.js');
        return $js;
    });

    // Page-Listener für Routing aller simplefeed‑Seiten
    $Wcms->addListener('page', function(array $page) use ($Wcms) {
        $pg = $_GET['page'] ?? '';
        if ($pg !== 'simplefeed') return $page;

        ob_start();
        include __DIR__ . '/core/routing.php';
        $page['content'] = ob_get_clean();
        $page['title'] = 'SimpleFeed';
        return $page;
    });

} catch (Exception $e) {
    // Log error with WonderCMS functions and display friendly message
    if (method_exists($Wcms, 'log')) {
        $Wcms->log('SimpleFeed Plugin Error: ' . $e->getMessage(), 'danger');
    } else {
        error_log('SimpleFeed Plugin Error: ' . $e->getMessage());
    }
    
    if (method_exists($Wcms, 'alert')) {
        $Wcms->alert('SimpleFeed Plugin Error: ' . $Wcms->stripTags($e->getMessage()), 'danger');
    } else {
        echo '<div class="alert alert-danger">SimpleFeed Plugin Error: ' . $Wcms->stripTags($e->getMessage()) . '</div>';
    }
}
