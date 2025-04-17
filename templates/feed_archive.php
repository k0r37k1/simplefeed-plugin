<?php
require_once __DIR__ . '/../core/settings.php';
require_once __DIR__ . '/../core/helpers.php';
$posts = sf_loadPosts();
?>
<h2>Archive</h2>
<ul>
<?php foreach ($posts as $p): ?>
    <li><a href='?page=simplefeed&view=<?php echo htmlspecialchars($p['slug'], ENT_QUOTES); ?>'>
    <?php echo htmlspecialchars($p['date'], ENT_QUOTES); ?> – 
    <?php echo htmlspecialchars($p['title'], ENT_QUOTES); ?></a></li>
<?php endforeach; ?>
</ul>
