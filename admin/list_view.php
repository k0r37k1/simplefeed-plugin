<?php
defined('INC_ROOT') || die;
require_once __DIR__ . '/../core/helpers.php';
$posts = sf_loadPosts();
?>
<h2>All Posts</h2>
<ul>
<?php foreach ($posts as $p): ?>
  <li>
    <strong><?php echo htmlspecialchars($p['title'], ENT_QUOTES); ?></strong>
    (<?php echo htmlspecialchars($p['date'], ENT_QUOTES); ?>)
    [<a href="?page=simplefeed&edit=<?php echo urlencode($p['slug']); ?>">Edit</a>]
    [<a href="?page=simplefeed&delete=<?php echo urlencode($p['slug']); ?>">Delete</a>]
  </li>
<?php endforeach; ?>
</ul>
