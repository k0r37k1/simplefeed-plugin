<?php
$slug = $_GET['slug'] ?? '';
$file = __DIR__ . '/../data/' . basename($slug) . '.json';
if (!file_exists($file)) { echo 'Beitrag nicht gefunden.'; return; }

$post = json_decode(file_get_contents($file), true);
echo "<article class='simplefeed-view'>";
echo "<h1>{$post['title']}</h1>";
echo "<small>{$post['date']} – von {$post['author']}</small><br>";
if ($post['image']) echo "<img src='{$post['image']}' alt='' style='max-width:100%;'><br>";
echo "<div>{$post['content']}</div>";
echo "</article>";