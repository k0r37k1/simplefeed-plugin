<?php
require_once __DIR__ . '/../core/settings.php';
require_once __DIR__ . '/../core/helpers.php';
$config = sf_getConfig();
$posts = sf_loadPosts();

$shown = (int)($_GET['shown'] ?? $config['show_more_limit']);
$filtered = isset($_GET['tag']) 
    ? array_filter($posts, fn($p) => in_array($_GET['tag'], $p['tags'])) 
    : $posts;

foreach (array_slice($filtered, 0, $shown) as $post) {
    echo "<div class='simplefeed-post'>";
    echo "<h3><a href='?page=simplefeed&view=" . htmlspecialchars($post['slug'], ENT_QUOTES) . "'>"
         . htmlspecialchars($post['title'], ENT_QUOTES) . "</a></h3>";
    echo "<small>" . htmlspecialchars($post['author'], ENT_QUOTES) . " – "
         . date($config['date_format'], strtotime($post['date'])) . "</small>";
    if ($config['use_thumbnails'] && $post['image']) {
        echo "<div class='thumbnail'><img src='" . htmlspecialchars($post['image'], ENT_QUOTES) . "' loading='lazy'></div>";
    }
    echo "<p>" . htmlspecialchars($post['short'], ENT_QUOTES) . "</p>";
    if (!empty($post['tags'])) {
        echo "<p>Tags: ";
        foreach ($post['tags'] as $t) {
            echo "<a href='?page=simplefeed&tag=" . urlencode($t) . "'>"
                 . htmlspecialchars($t, ENT_QUOTES) . "</a> ";
        }
        echo "</p>";
    }
    echo "<a href='?page=simplefeed&view=" . htmlspecialchars($post['slug'], ENT_QUOTES) . "'>Read more →</a>";
    echo "</div><hr>";
}

if ($shown < count($filtered)) {
    echo "<a href='?page=simplefeed&shown=" . ($shown + $config['show_more_limit']) . "'>Show more...</a>";
}
if (count($posts) > $config['show_more_limit']) {
    echo "<a href='?page=simplefeed&archive=1'>Full archive</a>";
}
