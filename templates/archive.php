<?php
defined('INC_ROOT') || die;
global $Wcms;

// Group posts by year
$postsByYear = [];
foreach ($posts as $post) {
    $year = date('Y', strtotime($post['date'] ?? 'now'));
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

    <?php if (isset($_SESSION['feedbackMessage'])): ?>
        <div class="sf-alert sf-alert-<?php echo htmlspecialchars($_SESSION['feedbackType'] ?? 'success'); ?>">
            <?php echo $_SESSION['feedbackMessage']; ?>
        </div>
        <?php unset($_SESSION['feedbackMessage'], $_SESSION['feedbackType']); ?>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <div class="sf-empty-archive">
            <p>No posts found in the archive.</p>
        </div>
    <?php else: ?>
        <div class="sf-archive-years">
            <?php foreach ($postsByYear as $year => $yearPosts): ?>
                <div class="sf-archive-year">
                    <h3><?php echo htmlspecialchars($year); ?></h3>

                    <ul class="sf-archive-posts">
                        <?php foreach ($yearPosts as $post): ?>
                            <li class="sf-archive-item">
                                <span class="sf-archive-date">
                                    <?php echo date($config['date_format'] ?? 'd.m.Y', strtotime($post['date'] ?? 'now')); ?>
                                </span>

                                <a href="<?php echo $Wcms->url('?page=simplefeed&action=post&slug=' . urlencode($post['slug'])); ?>" class="sf-archive-title">
                                    <?php echo htmlspecialchars($Wcms->stripTags($post['title'] ?? 'Untitled')); ?>
                                </a>

                                <?php if (!empty($post['tags'])): ?>
                                    <span class="sf-archive-tags">
                                        <?php foreach ($post['tags'] as $tag): ?>
                                            <a href="<?php echo $Wcms->url('?page=simplefeed&action=tag&tag=' . urlencode($tag)); ?>" class="sf-tag sf-tag-small">
                                                <?php echo htmlspecialchars($Wcms->stripTags($tag)); ?>
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
        <a href="<?php echo $Wcms->url('?page=simplefeed'); ?>" class="sf-btn sf-back-to-feed">
            ← Back to Feed
        </a>
    </div>
</div>
