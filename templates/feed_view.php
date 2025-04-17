<?php
require_once __DIR__ . '/../core/settings.php';
require_once __DIR__ . '/../core/helpers.php';
$config = sf_getConfig();
$posts = sf_loadPosts();
foreach ($posts as $i => $p) {
    if ($p['slug'] === $post['slug']) {
        $prev = $posts[$i+1] ?? null;
        $next = $posts[$i-1] ?? null;
        break;
    }
}
?>
<h2><?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?></h2>
<small><?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?> – 
<?php echo date($config['date_format'], strtotime($post['date'])); ?></small>
<?php if ($post['image']): ?>
    <div class="thumbnail"><img src="<?php echo htmlspecialchars($post['image'], ENT_QUOTES); ?>" loading="lazy"></div>
<?php endif; ?>
<div><?php echo $post['content']; ?></div>
<p>
<?php if ($prev): ?>
    <a href="?page=simplefeed&view=<?php echo htmlspecialchars($prev['slug'], ENT_QUOTES); ?>">← 
    <?php echo htmlspecialchars($prev['title'], ENT_QUOTES); ?></a>
<?php endif; ?>
<?php if ($next): ?>
    <a style="float:right" href="?page=simplefeed&view=<?php echo htmlspecialchars($next['slug'], ENT_QUOTES); ?>">
    <?php echo htmlspecialchars($next['title'], ENT_QUOTES); ?> →</a>
<?php endif; ?>
</p>
