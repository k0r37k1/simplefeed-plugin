<?php
require_once __DIR__ . '/../core/settings.php';
require_once __DIR__ . '/../core/helpers.php';
$config = sf_getConfig();
$posts = sf_loadPosts();

// Get and sanitize parameters
$shown = isset($_GET['shown']) ? filter_var($_GET['shown'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'default' => $config['show_more_limit']]]) : $config['show_more_limit'];
$tag = isset($_GET['tag']) ? filter_var($_GET['tag'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

// Filter posts by tag if needed
$filtered = isset($tag) 
    ? array_filter($posts, fn($p) => !empty($p['tags']) && in_array($tag, $p['tags'])) 
    : $posts;
?>

<div class="sf-feed-container">
    <?php if (isset($tag)): ?>
        <div class="sf-tag-filter">
            <h3>Posts tagged with: <span class="tag-name"><?php echo htmlspecialchars($tag, ENT_QUOTES); ?></span></h3>
            <a href="?page=simplefeed" class="clear-filter">Clear filter</a>
        </div>
    <?php endif; ?>
    
    <?php if (empty($filtered)): ?>
        <div class="sf-empty-feed">
            <?php if (isset($tag)): ?>
                <p>No posts found with the tag: <?php echo htmlspecialchars($tag, ENT_QUOTES); ?></p>
            <?php else: ?>
                <p>No posts found. Check back later for new content.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="sf-posts-list">
            <?php foreach (array_slice($filtered, 0, $shown) as $post): ?>
                <article class="sf-post">
                    <header class="sf-post-header">
                        <h3 class="sf-post-title">
                            <a href="?page=simplefeed&action=view&slug=<?php echo urlencode($post['slug']); ?>">
                                <?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>
                            </a>
                        </h3>
                        
                        <div class="sf-post-meta">
                            <?php if (!empty($post['author'])): ?>
                                <span class="sf-post-author"><?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?></span>
                            <?php endif; ?>
                            
                            <span class="sf-post-date">
                                <?php echo date($config['date_format'], strtotime($post['date'])); ?>
                            </span>
                        </div>
                    </header>
                    
                    <?php if ($config['use_thumbnails'] && !empty($post['image'])): ?>
                        <div class="sf-post-thumbnail">
                            <a href="?page=simplefeed&action=view&slug=<?php echo urlencode($post['slug']); ?>">
                                <img src="<?php echo htmlspecialchars($post['image'], ENT_QUOTES); ?>" 
                                     alt="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>" 
                                     loading="lazy">
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($post['short'])): ?>
                        <div class="sf-post-excerpt">
                            <?php echo htmlspecialchars($post['short'], ENT_QUOTES); ?>
                        </div>
                    <?php endif; ?>
                    
                    <footer class="sf-post-footer">
                        <?php if (!empty($post['tags'])): ?>
                            <div class="sf-post-tags">
                                <?php foreach ($post['tags'] as $t): ?>
                                    <a href="?page=simplefeed&action=tag&tag=<?php echo urlencode($t); ?>" class="sf-tag">
                                        <?php echo htmlspecialchars($t, ENT_QUOTES); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <a href="?page=simplefeed&action=view&slug=<?php echo urlencode($post['slug']); ?>" class="sf-read-more">
                            Read more →
                        </a>
                    </footer>
                </article>
                <hr class="sf-post-divider">
            <?php endforeach; ?>
        </div>
        
        <div class="sf-feed-navigation">
            <?php if ($shown < count($filtered)): ?>
                <a href="?page=simplefeed<?php echo isset($tag) ? '&action=tag&tag=' . urlencode($tag) : ''; ?>&shown=<?php echo $shown + $config['show_more_limit']; ?>" class="sf-button sf-show-more">
                    Show more posts
                </a>
            <?php endif; ?>
            
            <?php if (count($posts) > $config['show_more_limit']): ?>
                <a href="?page=simplefeed&action=archive" class="sf-button sf-archive-link">
                    View full archive
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
