<?php
defined('INC_ROOT') || die;
global $Wcms;

// Get and sanitize parameters using WonderCMS functions
$shown = isset($_GET['shown']) ? (int)$_GET['shown'] : $config['show_more_limit'];
$tag = isset($_GET['tag']) ? $Wcms->stripTags($_GET['tag']) : null;

// Filter posts by tag if needed
$filtered = isset($tag) 
    ? array_filter($posts, function($p) use ($tag) { 
        return !empty($p['tags']) && in_array($tag, $p['tags']); 
      })
    : $posts;
?>

<div class="sf-feed-container">
    <?php if (isset($_SESSION['feedbackMessage'])): ?>
        <div class="sf-alert sf-alert-<?php echo htmlspecialchars($_SESSION['feedbackType'] ?? 'success'); ?>">
            <?php echo $_SESSION['feedbackMessage']; ?>
        </div>
        <?php unset($_SESSION['feedbackMessage'], $_SESSION['feedbackType']); ?>
    <?php endif; ?>

    <?php if (isset($tag)): ?>
        <div class="sf-tag-filter">
            <h3>Posts tagged with: <span class="sf-tag-name"><?php echo htmlspecialchars($Wcms->stripTags($tag)); ?></span></h3>
            <a href="<?php echo $Wcms->url('?page=simplefeed'); ?>" class="sf-clear-filter">Clear filter</a>
        </div>
    <?php endif; ?>
    
    <?php if (empty($filtered)): ?>
        <div class="sf-empty-feed">
            <?php if (isset($tag)): ?>
                <p>No posts found with the tag: <?php echo htmlspecialchars($Wcms->stripTags($tag)); ?></p>
            <?php else: ?>
                <p>No posts found. Check back later for new content.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="sf-posts-list">
            <?php foreach (array_slice($filtered, 0, $shown) as $post): ?>
                <article class="sf-post">
                    <?php if ($config['use_thumbnails'] && !empty($post['image'])): ?>
                        <div class="sf-post-thumbnail">
                            <a href="<?php echo $Wcms->url('?page=simplefeed&action=post&slug=' . urlencode($post['slug'])); ?>">
                                <img src="<?php echo htmlspecialchars($Wcms->stripTags($post['image'])); ?>" 
                                     alt="<?php echo htmlspecialchars($Wcms->stripTags($post['title'])); ?>" 
                                     loading="lazy">
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="sf-post-content-wrapper">
                        <header class="sf-post-header">
                            <h3 class="sf-post-title">
                                <a href="<?php echo $Wcms->url('?page=simplefeed&action=post&slug=' . urlencode($post['slug'])); ?>">
                                    <?php echo htmlspecialchars($Wcms->stripTags($post['title'])); ?>
                                </a>
                            </h3>
                            
                            <div class="sf-post-meta">
                                <?php if (!empty($post['author'])): ?>
                                    <span class="sf-post-author"><?php echo htmlspecialchars($Wcms->stripTags($post['author'] ?? '')); ?></span>
                                <?php endif; ?>
                                
                                <span class="sf-post-date">
                                    <?php echo date($config['date_format'], strtotime($post['date'] ?? 'now')); ?>
                                </span>
                            </div>
                        </header>
                        
                        <?php if (!empty($post['short'])): ?>
                            <div class="sf-post-excerpt">
                                <?php echo htmlspecialchars($Wcms->stripTags($post['short'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <footer class="sf-post-footer">
                            <?php if (!empty($post['tags'])): ?>
                                <div class="sf-post-tags">
                                    <?php foreach ($post['tags'] as $t): ?>
                                        <a href="<?php echo $Wcms->url('?page=simplefeed&action=tag&tag=' . urlencode($t)); ?>" class="sf-tag">
                                            <?php echo htmlspecialchars($Wcms->stripTags($t)); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="<?php echo $Wcms->url('?page=simplefeed&action=post&slug=' . urlencode($post['slug'])); ?>" class="sf-read-more">
                                Read more →
                            </a>
                        </footer>
                    </div>
                </article>
                <hr class="sf-post-divider">
            <?php endforeach; ?>
        </div>
        
        <div class="sf-feed-navigation">
            <?php if ($shown < count($filtered)): ?>
                <a href="<?php echo $Wcms->url('?page=simplefeed' . (isset($tag) ? '&action=tag&tag=' . urlencode($tag) : '') . '&shown=' . ($shown + $config['show_more_limit'])); ?>" class="sf-btn sf-show-more">
                    Show more posts
                </a>
            <?php endif; ?>
            
            <?php if (count($posts) > $config['show_more_limit']): ?>
                <a href="<?php echo $Wcms->url('?page=simplefeed&action=archive'); ?>" class="sf-btn sf-archive-link">
                    View full archive
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
