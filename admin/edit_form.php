<?php
defined('INC_ROOT') || die;
$post = $post ?? ['slug'=>'','title'=>'','date'=>date('Y-m-d'),'short'=>'','image'=>'','author'=>'','content'=>'','tags'=>[]];
$errors = $errors ?? [];
?>
<h2><?php echo $post['slug'] ? 'Edit' : 'New'; ?> Post</h2>

<?php if (!empty($errors)): ?>
<div class="error">
    <ul>
        <?php foreach($errors as $error): ?>
            <li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="post">
  <!-- Add CSRF token for security -->
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(sf_getCSRFToken(), ENT_QUOTES); ?>">
  
  <!-- Preserve original slug for existing posts -->
  <input type="hidden" name="original_slug" value="<?php echo htmlspecialchars($post['slug'], ENT_QUOTES); ?>">
  
  <div class="form-group">
    <label>Title:<span class="required">*</span><br>
      <input name="title" required value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>" class="form-control">
    </label>
  </div>
  
  <div class="form-group">
    <label>Date:<span class="required">*</span><br>
      <input type="date" name="date" required value="<?php echo htmlspecialchars($post['date'], ENT_QUOTES); ?>" class="form-control">
    </label>
  </div>
  
  <div class="form-group">
    <label>Short Preview:<br>
      <textarea name="short" rows="3" class="form-control"><?php echo htmlspecialchars($post['short'], ENT_QUOTES); ?></textarea>
    </label>
  </div>
  
  <div class="form-group">
    <label>Image URL:<br>
      <input name="image" value="<?php echo htmlspecialchars($post['image'], ENT_QUOTES); ?>" class="form-control">
    </label>
  </div>
  
  <div class="form-group">
    <label>Tags (comma separated):<br>
      <input name="tags" value="<?php echo htmlspecialchars(is_array($post['tags']) ? implode(',', $post['tags']) : $post['tags'], ENT_QUOTES); ?>" class="form-control">
    </label>
  </div>
  
  <div class="form-group">
    <label>Author:<br>
      <input name="author" value="<?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?>" class="form-control">
    </label>
  </div>
  
  <div class="form-group">
    <label>Content (HTML allowed):<br>
      <textarea name="content" rows="10" class="form-control"><?php echo htmlspecialchars($post['content'], ENT_QUOTES); ?></textarea>
    </label>
    <small class="form-text text-muted">Basic HTML tags like &lt;p&gt;, &lt;br&gt;, &lt;h1&gt;-&lt;h6&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;a&gt;, &lt;img&gt; are allowed.</small>
  </div>
  
  <div class="form-group">
    <button type="submit" class="btn btn-primary">Save Post</button>
    <a href="?page=simplefeed&list=1" class="btn btn-secondary">Cancel</a>
  </div>
</form>

<style>
  .form-group { margin-bottom: 15px; }
  .form-control { width: 100%; padding: 5px; }
  textarea.form-control { min-height: 100px; }
  .required { color: red; }
  .error { background-color: #ffebee; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
  .btn { padding: 5px 10px; }
  .btn-primary { background-color: #2196F3; color: white; border: none; }
  .btn-secondary { background-color: #9e9e9e; color: white; border: none; }
</style>
