<?php
require_once __DIR__ . '/../core/settings.php';
require_once __DIR__ . '/../core/helpers.php';
$config = sf_getConfig();

// Make sure $post is defined
if (!isset($post) || !is_array($post)) {
    echo "<div class='error'>Post not found</div>";
    return;
}

// Find previous and next posts
$posts = $posts ?? sf_loadPosts();
$prev = null;
$next = null;

foreach ($posts as $i => $p) {
    if ($p['slug'] === $post['slug']) {
        $prev = $posts[$i+1] ?? null;
        $next = $posts[$i-1] ?? null;
        break;
    }
}
?>

<article class="sf-post-full">
    <header class="sf-post-header">
        <h2><?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?></h2>
        
        <div class="sf-post-meta">
            <?php if (!empty($post['author'])): ?>
                <span class="sf-post-author">
                    By <?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?>
                </span>
            <?php endif; ?>
            
            <span class="sf-post-date">
                Published on <?php echo date($config['date_format'], strtotime($post['date'])); ?>
            </span>
            
            <?php if (!empty($post['tags'])): ?>
                <div class="sf-post-tags">
                    Tags: 
                    <?php foreach ($post['tags'] as $tag): ?>
                        <a href="?page=simplefeed&action=tag&tag=<?php echo urlencode($tag); ?>" class="sf-tag">
                            <?php echo htmlspecialchars($tag, ENT_QUOTES); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </header>
    
    <?php if (!empty($post['image'])): ?>
        <div class="sf-post-image">
            <img src="<?php echo htmlspecialchars($post['image'], ENT_QUOTES); ?>" 
                 alt="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>" 
                 loading="lazy">
        </div>
    <?php endif; ?>
    
    <div class="sf-post-content">
        <?php 
        // Verwende content_html, der bereits in routing.php je nach Markdown-Einstellung
        // entweder über Parsedown oder über sf_sanitizeHTML geleitet wurde
        echo isset($post['content_html']) ? $post['content_html'] : sf_sanitizeHTML($post['content']); 
        ?>
    </div>
    
    <?php if (isset($post['use_markdown']) && $post['use_markdown']): ?>
        <div class="sf-post-format">
            <span class="sf-post-format-badge">Markdown</span>
        </div>
    <?php endif; ?>
    
    <footer class="sf-post-footer">
        <div class="sf-post-navigation">
            <?php if ($prev): ?>
                <div class="sf-post-prev">
                    <a href="?page=simplefeed&action=view&slug=<?php echo urlencode($prev['slug']); ?>">
                        <span class="nav-arrow">←</span> 
                        <span class="nav-title"><?php echo htmlspecialchars($prev['title'], ENT_QUOTES); ?></span>
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if ($next): ?>
                <div class="sf-post-next">
                    <a href="?page=simplefeed&action=view&slug=<?php echo urlencode($next['slug']); ?>">
                        <span class="nav-title"><?php echo htmlspecialchars($next['title'], ENT_QUOTES); ?></span>
                        <span class="nav-arrow">→</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="sf-back-to-feed">
            <a href="?page=simplefeed">Back to Feed</a>
        </div>
    </footer>
</article>
