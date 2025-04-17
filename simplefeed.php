<?php
/**
 * SimpleFeed Plugin for WonderCMS
 * 
 * A minimalist feed/blog plugin with tags and navigation for WonderCMS.
 * With integrated Markdown support and security features.
 * 
 * @version 1.1.0
 * @author k0r37k1
 * @license MIT
 */
defined('INC_ROOT') || die;
global $Wcms;

try {
    // Load core functions
    tools\inc(__DIR__ . '/core/settings.php');
    tools\inc(__DIR__ . '/core/helpers.php');

    // Load settings
    $config = sf_getConfig();

    // Create data directory if it doesn't exist
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            throw new Exception('Failed to create data directory');
        }
    }

    // Create lib directory if it doesn't exist (for Parsedown)
    $libDir = __DIR__ . '/lib';
    if (!is_dir($libDir)) {
        if (!mkdir($libDir, 0755, true)) {
            throw new Exception('Failed to create lib directory');
        }
    }

    // Check if Parsedown is available, if not show warning
    if (!file_exists(__DIR__ . '/lib/Parsedown.php')) {
        if ($Wcms->loggedIn) {
            $Wcms->alert('Parsedown library not found. Markdown features will not work correctly. Please download Parsedown.php and place it in the lib directory.', 'warning');
        }
    }

    // Add menu item
    $Wcms->addListener('menu', function(array $menu) use ($Wcms) {
        // Publicly visible - no login required
        $menu[] = ['slug' => 'simplefeed', 'name' => 'SimpleFeed'];
        return $menu;
    });

    // Add admin panel entry when logged in
    $Wcms->addListener('adminPanel', function(array $args) use ($Wcms) {
        // Only if admin is logged in
        if ($Wcms->loggedIn) {
            $args[0] .= '<a href="' . $Wcms->url('?page=simplefeed') . '" class="btn btn-info marginTop5">SimpleFeed</a>';
        }
        return $args;
    });

    // Include CSS with $Wcms->url for correct paths
    $Wcms->addListener('css', function(array $css) use ($Wcms) {
        $css[] = $Wcms->url('plugins/simplefeed/css/feed.css');
        return $css;
    });
    
    // Include JavaScript with $Wcms->url for correct paths
    $Wcms->addListener('js', function(array $js) use ($Wcms) {
        $js[] = $Wcms->url('plugins/simplefeed/js/simplefeed.js');
        return $js;
    });

    // Page listener for routing all simplefeed pages
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
