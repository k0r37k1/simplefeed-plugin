<?php
defined('INC_ROOT') || die;
$post = $post ?? ['slug'=>'','title'=>'','date'=>date('Y-m-d'),'short'=>'','image'=>'','author'=>'','content'=>'','tags'=>[]];
?>
<h2><?php echo $post['slug'] ? 'Edit' : 'New'; ?> Post</h2>
<form method="post">
  <input type="hidden" name="slug" value="<?php echo htmlspecialchars($post['slug'], ENT_QUOTES); ?>">
  <label>Title:<br>
    <input name="title" value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>">
  </label><br><br>
  <label>Date:<br>
    <input type="date" name="date" value="<?php echo htmlspecialchars($post['date'], ENT_QUOTES); ?>">
  </label><br><br>
  <label>Short Preview:<br>
    <textarea name="short"><?php echo htmlspecialchars($post['short'], ENT_QUOTES); ?></textarea>
  </label><br><br>
  <label>Image URL:<br>
    <input name="image" value="<?php echo htmlspecialchars($post['image'], ENT_QUOTES); ?>">
  </label><br><br>
  <label>Tags (comma separated):<br>
    <input name="tags" value="<?php echo htmlspecialchars(implode(',', $post['tags']), ENT_QUOTES); ?>">
  </label><br><br>
  <label>Author:<br>
    <input name="author" value="<?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?>">
  </label><br><br>
  <label>Content (HTML allowed):<br>
    <textarea name="content"><?php echo htmlspecialchars($post['content'], ENT_QUOTES); ?></textarea>
  </label><br><br>
  <button type="submit">Save Post</button>
</form>
