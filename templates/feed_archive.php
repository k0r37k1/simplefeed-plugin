<?php
require_once __DIR__ . '/../core/settings.php';
require_once __DIR__ . '/../core/helpers.php';
$config = sf_getConfig();
$posts = sf_loadPosts();

// Group posts by year
$postsByYear = [];
foreach ($posts as $post) {
    $year = date('Y', strtotime($post['date']));
    if (!isset($postsByYear[$year])) {
        $postsByYear[$year] = [];
    }
    $postsByYear[$year][] = $post;
}

// Sort years in descending order
krsort($postsByYear);
?>

<div class="sf-archive-container">
    <h2>Post Archive</h2>
    
    <?php if (empty($posts)): ?>
        <div class="sf-empty-archive">
            <p>No posts found in the archive.</p>
        </div>
    <?php else: ?>
        <div class="sf-archive-years">
            <?php foreach ($postsByYear as $year => $yearPosts): ?>
                <div class="sf-archive-year">
                    <h3><?php echo $year; ?></h3>
                    
                    <ul class="sf-archive-posts">
                        <?php foreach ($yearPosts as $post): ?>
                            <li class="sf-archive-item">
                                <span class="sf-archive-date">
                                    <?php echo date($config['date_format'], strtotime($post['date'])); ?>
                                </span>
                                
                                <a href="?page=simplefeed&action=view&slug=<?php echo urlencode($post['slug']); ?>" class="sf-archive-title">
                                    <?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>
                                </a>
                                
                                <?php if (!empty($post['tags'])): ?>
                                    <span class="sf-archive-tags">
                                        <?php foreach ($post['tags'] as $tag): ?>
                                            <a href="?page=simplefeed&action=tag&tag=<?php echo urlencode($tag); ?>" class="sf-tag sf-tag-small">
                                                <?php echo htmlspecialchars($tag, ENT_QUOTES); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="sf-archive-footer">
        <a href="?page=simplefeed" class="sf-button sf-back-to-feed">
            ← Back to Feed
        </a>
    </div>
</div>
