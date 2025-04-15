<?php
if (!$wCMS->loggedIn) { echo 'Access denied.'; return; }

$dataPath = __DIR__ . '/data/';
$posts = [];
foreach (glob($dataPath . '*.json') as $file) {
    $json = json_decode(file_get_contents($file), true);
    if (is_array($json)) {
        $posts[] = $json;
    }
}
usort($posts, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

echo '<h2>SimpleFeed - Beiträge</h2>';
echo '<a href="?page=simplefeed&new=1">➕ Neuer Beitrag</a><hr>';

if (isset($_GET['new']) || isset($_GET['edit'])) {
    $post = ['title'=>'', 'date'=>date('Y-m-d'), 'slug'=>'', 'short'=>'', 'content'=>'', 'tags'=>'', 'image'=>'', 'author'=>''];
    if (isset($_GET['edit'])) {
        $slug = basename($_GET['edit']);
        $path = $dataPath . $slug . '.json';
        if (file_exists($path)) {
            $post = json_decode(file_get_contents($path), true);
        }
    }
    include __DIR__ . '/templates/form.php';
} else {
    foreach ($posts as $post) {
        echo "<div><strong>{$post['title']}</strong> ({$post['date']}) - <a href='?page=simplefeed&edit={$post['slug']}'>Bearbeiten</a> | <a href='simplefeed/actions/delete.php?slug={$post['slug']}' onclick='return confirm(\"Löschen?\")'>Löschen</a></div>";
    }
}
?>