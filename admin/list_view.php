<?php
defined('INC_ROOT') || die;
global $Wcms;

// Make sure user is logged in as admin
if (!$Wcms->loggedIn) {
    echo "<div class='error'>Access denied. Please log in as administrator.</div>";
    return;
}

require_once __DIR__ . '/../core/helpers.php';
$posts = sf_loadPosts();
$config = sf_getConfig();
?>
<div class="sf-posts-list">
  <h2>All Posts</h2>
  
  <?php if (empty($posts)): ?>
    <div class="sf-info-box">
      No posts found. <a href="?page=simplefeed&action=edit">Create your first post</a>.
    </div>
  <?php else: ?>
    <div class="sf-toolbar">
      <a href="?page=simplefeed&action=edit" class="btn-primary">Create New Post</a>
    </div>
    
    <table class="sf-posts-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Date</th>
          <th>Author</th>
          <th>Tags</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $p): ?>
          <tr>
            <td>
              <a href="?page=simplefeed&action=view&slug=<?php echo urlencode($p['slug']); ?>" target="_blank">
                <?php echo htmlspecialchars($p['title'], ENT_QUOTES); ?>
              </a>
            </td>
            <td><?php echo date($config['date_format'], strtotime($p['date'])); ?></td>
            <td><?php echo htmlspecialchars($p['author'] ?? '', ENT_QUOTES); ?></td>
            <td>
              <?php if (!empty($p['tags'])): ?>
                <div class="tag-list">
                  <?php foreach ($p['tags'] as $tag): ?>
                    <span class="tag"><?php echo htmlspecialchars($tag, ENT_QUOTES); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <div class="action-buttons">
                <a href="?page=simplefeed&action=edit&slug=<?php echo urlencode($p['slug']); ?>" class="btn-edit">Edit</a>
                <a href="?page=simplefeed&action=delete&slug=<?php echo urlencode($p['slug']); ?>" class="btn-delete">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  
  <div class="sf-back-link">
    <a href="?page=simplefeed">← Back to Settings</a>
  </div>
</div>
