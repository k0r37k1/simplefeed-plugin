<?php
defined('INC_ROOT') || die;

// Settings speichern
if ($_SERVER['REQUEST_METHOD']==='POST'
    && isset($_GET['page'], $_POST['date_format'])
    && $_GET['page']==='simplefeed') {
    $cfg = [
        'date_format'=> trim($_POST['date_format']),
        'show_more_limit'=> (int)$_POST['show_more_limit'],
        'use_thumbnails'=> ($_POST['use_thumbnails']=='1')
    ];
    file_put_contents(__DIR__.'/../data/settings.json', json_encode($cfg, JSON_PRETTY_PRINT));
    echo "<div class='success'>Einstellungen gespeichert.</div>";
}

// Admin-Panel
if (!isset($_GET['list'], $_GET['edit'], $_GET['delete'])) {
    include __DIR__.'/../admin/panel.php'; return;
}

// List View
if (isset($_GET['list'])) {
    include __DIR__.'/../admin/list_view.php'; return;
}

// Edit-Form Anzeige
if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD']==='GET') {
    $slug = $_GET['edit'];
    $file = __DIR__.'/../data/'.$slug.'.json';
    if (file_exists($file)) $post = json_decode(file_get_contents($file),true);
    include __DIR__.'/../admin/edit_form.php'; return;
}

// Save Post
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['title'])) {
    $slug = sf_generateSlug($_POST['title']);
    $post = [
        'slug'=>$slug,
        'title'=>$_POST['title'],
        'date'=>$_POST['date'],
        'short'=>$_POST['short'],
        'image'=>$_POST['image'],
        'author'=>$_POST['author'],
        'content'=>$_POST['content'],
        'tags'=>array_filter(array_map('trim', explode(',',$_POST['tags'])))
    ];
    file_put_contents(__DIR__.'/../data/'.$slug.'.json', json_encode($post, JSON_PRETTY_PRINT));
    header('Location:?page=simplefeed&list=1'); exit;
}

// Delete Post
if (isset($_GET['delete'])) {
    $slug = $_GET['delete'];
    $file = __DIR__.'/../data/'.$slug.'.json';
    if (!isset($_GET['confirm'])) {
        echo "<div style='padding:1em;background:#fee;'>Wirklich löschen <strong>{$slug}</strong>? "
           ."<a href='?page=simplefeed&delete={$slug}&confirm=1'>Ja</a> | "
           ."<a href='?page=simplefeed&list=1'>Nein</a></div>";
        return;
    }
    if (file_exists($file)) unlink($file);
    header('Location:?page=simplefeed&list=1'); exit;
}

// Frontend laden
$config = sf_getConfig();
$posts  = sf_loadPosts();

// Feed List
if (!isset($_GET['view'], $_GET['archive'], $_GET['tag'])) {
    include __DIR__.'/../templates/feed_list.php'; return;
}

// Archive
if (isset($_GET['archive'])) {
    include __DIR__.'/../templates/feed_archive.php'; return;
}

// Tag-Filter
if (isset($_GET['tag'])) {
    include __DIR__.'/../templates/feed_list.php'; return;
}

// Single Post
if (isset($_GET['view'])) {
    $slug = $_GET['view'];
    foreach ($posts as $p) if($p['slug']==$slug) $post=$p;
    include __DIR__.'/../templates/feed_view.php'; return;
}
