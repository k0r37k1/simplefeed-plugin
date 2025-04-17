<?php
defined('INC_ROOT') || die;
$post = $post ?? ['slug'=>'','title'=>'','date'=>date('Y-m-d'),'short'=>'','image'=>'','author'=>'','content'=>'','tags'=>[]];
?>
<h2><?php echo $post['slug'] ? 'Edit' : 'New'; ?> Post</h2>
<form method="post" class="sf-edit-form" id="postForm">
  <input type="hidden" name="sf_csrf_token" value="<?php echo sf_generateCSRFToken(); ?>">
  <input type="hidden" name="original_slug" value="<?php echo htmlspecialchars($post['slug'], ENT_QUOTES); ?>">
  
  <div class="form-group">
    <label for="title">Title:<span class="required">*</span></label>
    <input id="title" name="title" value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>" required>
    <div class="help">The title of your post.</div>
  </div>
  
  <div class="form-group">
    <label for="date">Date:<span class="required">*</span></label>
    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($post['date'], ENT_QUOTES); ?>" required>
    <div class="help">Publication date in YYYY-MM-DD format.</div>
  </div>
  
  <div class="form-group">
    <label for="short">Short Preview:</label>
    <textarea id="short" name="short" rows="3"><?php echo htmlspecialchars($post['short'], ENT_QUOTES); ?></textarea>
    <div class="help">A short preview text shown in the feed list.</div>
  </div>
  
  <div class="form-group">
    <label for="image">Image URL:</label>
    <input id="image" name="image" value="<?php echo htmlspecialchars($post['image'], ENT_QUOTES); ?>">
    <div class="help">URL to an image (optional).</div>
  </div>
  
  <div class="form-group">
    <label for="tags">Tags (comma separated):</label>
    <input id="tags" name="tags" value="<?php echo htmlspecialchars(implode(',', $post['tags']), ENT_QUOTES); ?>">
    <div class="help">Enter tags separated by commas, e.g., news,update,important</div>
  </div>
  
  <div class="form-group">
    <label for="author">Author:</label>
    <input id="author" name="author" value="<?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?>">
    <div class="help">The author's name.</div>
  </div>
  
  <div class="form-group">
    <label for="content">Content (HTML allowed):</label>
    <textarea id="content" name="content" rows="15"><?php echo htmlspecialchars($post['content'], ENT_QUOTES); ?></textarea>
    <div class="help">HTML is allowed for formatting.</div>
  </div>
  
  <div class="form-actions">
    <button type="submit" class="btn-primary">Save Post</button>
    <a href="?page=simplefeed&action=list" class="btn-secondary">Cancel</a>
    <?php if($post['slug']): ?>
    <button type="button" id="previewBtn" class="btn-preview">Preview</button>
    <?php endif; ?>
  </div>
</form>
