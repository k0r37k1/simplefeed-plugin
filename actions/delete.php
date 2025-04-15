<?php
if (!isset($_GET['slug'])) die('Kein Beitrag angegeben.');
$slug = basename($_GET['slug']);
$path = __DIR__ . '/../data/' . $slug . '.json';
if (file_exists($path)) unlink($path);
header('Location: ../?page=simplefeed');