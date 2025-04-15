<?php
$dataPath = __DIR__ . '/../data/';
$tagFilter = isset($_GET['tag']) ? $_GET['tag'] : null;
$posts = [];

foreach (glob($dataPath . '*.json') as $file) {
    $json = json_decode(file_get_contents($file), true);
    if (is_array($json)) {
        if ($tagFilter && !in_array($tagFilter, $json['tags'])) continue;
        $posts[] = $json;
    }
}

usort($posts, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

foreach ($posts as $post) {
    echo "<div class='simplefeed-post'>";
    echo "<h3>{$post['title']}</h3>";
    echo "<small>{$post['date']} – von {$post['author']}</small><br>";
    if ($post['image']) echo "<img src='{$post['image']}' alt='' style='max-width:100%;'><br>";
    echo "<p>{$post['short']}</p>";
    echo "<a href='feed/{$post['slug']}'>Mehr lesen →</a>";
    echo "<div>Tags: ";
    foreach ($post['tags'] as $tag) {
        echo "<a href='?tag={$tag}'>#{$tag}</a> ";
    }
    echo "</div></div><hr>";
}