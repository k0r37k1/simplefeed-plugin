<?php
defined('INC_ROOT') || die;
$config = sf_getConfig();
?>
<div class="sf-admin-panel">
  <h2>SimpleFeed Settings</h2>
  
  <form method="post" action="?page=simplefeed&action=settings">
    <input type="hidden" name="sf_csrf_token" value="<?php echo sf_generateCSRFToken(); ?>">
    
    <div class="form-group">
      <label for="date_format">Date Format:</label>
      <input type="text" id="date_format" name="date_format" value="<?php echo htmlspecialchars($config['date_format'], ENT_QUOTES); ?>">
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
    
    <div class="form-actions">
      <button type="submit" class="btn-primary">Save Settings</button>
    </div>
  </form>
  
  <div class="admin-links">
    <h3>Management</h3>
    <a href="?page=simplefeed&action=list" class="btn-secondary">View all posts</a>
    <a href="?page=simplefeed&action=edit" class="btn-secondary">Create new post</a>
  </div>
</div>
