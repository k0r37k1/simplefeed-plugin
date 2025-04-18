<?php
defined('INC_ROOT') || die;
global $Wcms;

// Make sure user is logged in as admin
if (!$Wcms->loggedIn) {
    $Wcms->alert('Access denied. Please log in as administrator.', 'danger');
    return;
}

$config = sf_getConfig();
?>
<div class="sf-admin-panel">
    <h2>SimpleFeed Settings</h2>

    <form method="post" action="?page=simplefeed&action=settings">
        <!-- Use WonderCMS token -->
        <input type="hidden" name="token" value="<?php echo $Wcms->getToken(); ?>">

        <div class="form-group">
            <label for="date_format">Date Format:</label>
            <input type="text" id="date_format" name="date_format" value="<?php echo $Wcms->stripTags($config['date_format']); ?>">
            <div class="help">PHP date format, e.g., d.m.Y for 31.12.2025</div>
        </div>

        <div class="form-group">
            <label for="show_more_limit">Show More Limit:</label>
            <input type="number" id="show_more_limit" name="show_more_limit" value="<?php echo (int)$config['show_more_limit']; ?>" min="1" max="50">
            <div class="help">Number of posts to show before "Show more" button</div>
        </div>

        <div class="form-group">
            <label for="use_thumbnails">Use Thumbnails:</label>
            <select id="use_thumbnails" name="use_thumbnails">
                <option value="1" <?php echo $config['use_thumbnails'] ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo !$config['use_thumbnails'] ? 'selected' : ''; ?>>No</option>
            </select>
            <div class="help">Show image thumbnails in post lists</div>
        </div>

        <div class="form-group">
            <label for="default_markdown">Default to Markdown:</label>
            <select id="default_markdown" name="default_markdown">
                <option value="1" <?php echo ($config['default_markdown'] ?? true) ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo isset($config['default_markdown']) && !$config['default_markdown'] ? 'selected' : ''; ?>>No</option>
            </select>
            <div class="help">Use Markdown as default format for new posts</div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Settings</button>
        </div>
    </form>

    <div class="admin-links">
        <h3>Management</h3>
        <a href="?page=simplefeed&action=list" class="btn-secondary">View all posts</a>
        <a href="?page=simplefeed&action=edit" class="btn-secondary">Create new post</a>
    </div>

    <div class="markdown-info">
        <h3>Markdown Support</h3>
        <p>SimpleFeed supports Markdown formatting for your posts. You can enable or disable Markdown individually for each post when editing.</p>
        <p>Benefits of using Markdown:</p>
        <ul>
            <li>Simpler and cleaner syntax compared to HTML</li>
            <li>Easier to read and write</li>
            <li>Prevents common HTML errors</li>
            <li>Better security - automatically sanitized</li>
        </ul>
        <p>The editor provides a helpful Markdown cheat sheet when you're editing a post.</p>
    </div>
</div>