<?php
defined('INC_ROOT') || die;
?>
<h2>SimpleFeed Einstellungen</h2>
<form method="post" action="?page=simplefeed">
  <?php $config = sf_getConfig(); ?>
  <label>Date Format:<br>
    <input type="text" name="date_format" value="<?php echo htmlspecialchars($config['date_format'], ENT_QUOTES); ?>">
  </label><br><br>
  <label>Show More Limit:<br>
    <input type="number" name="show_more_limit" value="<?php echo (int)$config['show_more_limit']; ?>">
  </label><br><br>
  <label>Use Thumbnails:<br>
    <select name="use_thumbnails">
      <option value="1" <?php echo $config['use_thumbnails'] ? 'selected' : ''; ?>>Yes</option>
      <option value="0" <?php echo !$config['use_thumbnails'] ? 'selected' : ''; ?>>No</option>
    </select>
  </label><br><br>
  <button type="submit">Save Settings</button>
</form>
<p><a href="?page=simplefeed&list=1">View all posts</a></p>
