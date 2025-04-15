<?php
if (!isset($_POST['title'])) die('No title.');
$dataDir = __DIR__ . '/../data/';

function sanitizeSlug($string) {
    return strtolower(preg_replace('/[^a-z0-9-]+/', '-', trim($string)));
}

$slug = sanitizeSlug($_POST['slug'] ?: $_POST['title']);
$base = $slug;
$i = 1;
while (file_exists($dataDir . $slug . '.json')) {
    $slug = $base . '-' . $i++;
}

$post = [
    'slug' => $slug,
    'title' => $_POST['title'],
    'date' => $_POST['date'] ?: date('Y-m-d'),
    'short' => $_POST['short'] ?: mb_substr(strip_tags($_POST['content']), 0, 200) . '...',
    'content' => $_POST['content'],
    'tags' => array_map('trim', explode(',', $_POST['tags'])),
    'image' => $_POST['image'],
    'author' => $_POST['author'] ?: 'admin'
];

file_put_contents($dataDir . $slug . '.json', json_encode($post, JSON_PRETTY_PRINT));
header('Location: ../?page=simplefeed');