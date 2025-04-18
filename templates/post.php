<?php
defined('INC_ROOT') || die;
global $Wcms;

// Ensure post data has default values to prevent PHP notices
$title = $post['title'] ?? 'Untitled';
$date = $post['date'] ?? date('Y-m-d');
$author = $post['author'] ?? '';
$image = $post['image'] ?? '';
$content = isset($post['content_html']) ? $post['content_html'] : (isset($post['content']) ? $post['content'] : '');
$tags = $post['tags'] ?? [];

// Find previous and next posts
$prev = null;
$next = null;

foreach ($posts as $i => $p) {
    if ($p['slug'] === $post['slug']) {
        $prev = isset($posts[$i+1]) ? $posts[$i+1] : null;
        $next = isset($posts[$i-1]) ? $posts[$i-1] : null;
        break;
    }
}
?>

<article class="sf-post-full">
    <header class="sf-post-header">
        <h2><?php echo htmlspecialchars($Wcms->stripTags($title)); ?></h2>

        <div class="sf-post-meta">
            <?php if (!empty($author)): ?>
                <span class="sf-post-author">
                    By <?php echo htmlspecialchars($Wcms->stripTags($author)); ?>
                </span>
            <?php endif; ?>

            <span class="sf-post-date">
                Published on <?php echo date($config['date_format'] ?? 'd.m.Y', strtotime($date)); ?>
            </span>

            <?php if (!empty($tags)): ?>
                <div class="sf-post-tags">
                    Tags:
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?php echo $Wcms->url('?page=simplefeed&action=tag&tag=' . urlencode($tag)); ?>" class="sf-tag">
                            <?php echo htmlspecialchars($Wcms->stripTags($tag)); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!empty($image)): ?>
        <div class="sf-post-image">
            <img src="<?php echo htmlspecialchars($Wcms->stripTags($image)); ?>"
                 alt="<?php echo htmlspecialchars($Wcms->stripTags($title)); ?>"
                 loading="lazy">
        </div>
    <?php endif; ?>

    <div class="sf-post-content">
        <?php
        // Content was already processed based on Markdown setting
        echo $content;
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
                    <a href="<?php echo $Wcms->url('?page=simplefeed&action=post&slug=' . urlencode($prev['slug'])); ?>">
                        <span class="nav-arrow">←</span>
                        <span class="nav-title"><?php echo htmlspecialchars($Wcms->stripTags($prev['title'])); ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($next): ?>
                <div class="sf-post-next">
                    <a href="<?php echo $Wcms->url('?page=simplefeed&action=post&slug=' . urlencode($next['slug'])); ?>">
                        <span class="nav-title"><?php echo htmlspecialchars($Wcms->stripTags($next['title'])); ?></span>
                        <span class="nav-arrow">→</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="sf-back-to-feed">
            <a href="<?php echo $Wcms->url('?page=simplefeed'); ?>">Back to Feed</a>
        </div>
    </footer>
</article>
