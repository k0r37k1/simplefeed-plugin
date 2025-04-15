<?php
defined('INC_ROOT') || die;

$wCMS->addMenuItem('SimpleFeed', '?page=simplefeed');

$wCMS->addListener('page', function ($page) use ($wCMS) {
    if ($page === 'simplefeed') {
        include __DIR__ . '/admin.php';
        return true;
    }

    if (preg_match('/^feed\/(.+)/', $page, $matches)) {
        $_GET['slug'] = $matches[1];
        include __DIR__ . '/templates/view.php';
        return true;
    }
});

$wCMS->addListener('block', function ($block) {
    if ($block === 'simplefeed') {
        include __DIR__ . '/templates/list.php';
    }
});

$wCMS->addListener('css', function () {
    echo '<link rel="stylesheet" href="simplefeed/css/simplefeed.css">';
});
