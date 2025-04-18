<?php
defined('INC_ROOT') || die;
global $Wcms;

// Make sure user is logged in as admin
if (!$Wcms->loggedIn) {
    $Wcms->alert('danger', 'Access denied. Please log in as administrator.');
    return;
}

// Initialize post with default values to prevent undefined index warnings
$post = $post ?? [
    'slug' => '',
    'title' => '',
    'date' => date('Y-m-d'),
    'short' => '',
    'image' => '',
    'author' => '',
    'content' => '',
    'tags' => [], 
    'use_markdown' => true
];
?>
<h2><?php echo $post['slug'] ? 'Edit' : 'New'; ?> Post</h2>

<?php if (isset($_SESSION['feedbackMessage'])): ?>
    <div class="sf-feedback-message <?php echo htmlspecialchars($_SESSION['feedbackType'] ?? 'success'); ?>">
        <?php echo $_SESSION['feedbackMessage']; ?>
    </div>
    <?php unset($_SESSION['feedbackMessage'], $_SESSION['feedbackType']); ?>
<?php endif; ?>

<form method="post" class="sf-edit-form" id="postForm">
    <!-- Use WonderCMS token -->
    <input type="hidden" name="token" value="<?php echo $Wcms->getToken(); ?>">
    <input type="hidden" name="original_slug" value="<?php echo htmlspecialchars($Wcms->stripTags($post['slug'])); ?>">

    <div class="form-group">
        <label for="title">Title:<span class="required">*</span></label>
        <input id="title" name="title" value="<?php echo htmlspecialchars($Wcms->stripTags($post['title'])); ?>" required>
        <div class="help">The title of your post.</div>
    </div>

    <div class="form-group">
        <label for="date">Date:<span class="required">*</span></label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($Wcms->stripTags($post['date'])); ?>" required>
        <div class="help">Publication date in YYYY-MM-DD format.</div>
    </div>

    <div class="form-group">
        <label for="short">Short Preview:</label>
        <textarea id="short" name="short" rows="3"><?php echo htmlspecialchars($Wcms->stripTags($post['short'])); ?></textarea>
        <div class="help">A short preview text shown in the feed list.</div>
    </div>

    <div class="form-group">
        <label for="image">Image URL:</label>
        <input id="image" name="image" value="<?php echo htmlspecialchars($Wcms->stripTags($post['image'])); ?>">
        <div class="help">URL to an image (optional).</div>
    </div>

    <div class="form-group">
        <label for="tags">Tags (comma separated):</label>
        <input id="tags" name="tags" value="<?php echo htmlspecialchars($Wcms->stripTags(implode(',', $post['tags']))); ?>">
        <div class="help">Enter tags separated by commas, e.g., news,update,important</div>
    </div>

    <div class="form-group">
        <label for="author">Author:</label>
        <input id="author" name="author" value="<?php echo htmlspecialchars($Wcms->stripTags($post['author'])); ?>">
        <div class="help">The author's name.</div>
    </div>

    <div class="form-group">
        <label for="content">Content:</label>
        <div class="content-format-toggle">
            <label>
                <input type="radio" name="use_markdown" value="1" <?php echo (!isset($post['use_markdown']) || $post['use_markdown']) ? 'checked' : ''; ?>>
                Markdown
            </label>
            <label>
                <input type="radio" name="use_markdown" value="0" <?php echo (isset($post['use_markdown']) && !$post['use_markdown']) ? 'checked' : ''; ?>>
                HTML
            </label>
        </div>
        <!-- Using htmlspecialchars for content when editing -->
        <textarea id="content" name="content" rows="15"><?php
            // Preserve all formatting in the content when editing
            echo htmlspecialchars($post['content']);
            ?></textarea>
        <div class="help" id="formatHelp">
            <?php if (!isset($post['use_markdown']) || $post['use_markdown']): ?>
                <span class="markdown-help">
          <strong>Markdown formatting:</strong>
          **bold**, *italic*, [link](url), # Header, - list item, ```code```
          <a href="#" id="toggleMarkdownHelp">Show more</a>
          <div id="markdownHelpExpanded" style="display:none;">
            <table class="markdown-cheatsheet">
              <tr><td><strong>Headers</strong></td><td># Title<br>## Subtitle<br>### Section</td></tr>
              <tr><td><strong>Emphasis</strong></td><td>**bold** or __bold__<br>*italic* or _italic_</td></tr>
              <tr><td><strong>Lists</strong></td><td>- Item 1<br>- Item 2<br>1. First<br>2. Second</td></tr>
              <tr><td><strong>Links</strong></td><td>[Link text](http://example.com)</td></tr>
              <tr><td><strong>Images</strong></td><td>![Alt text](http://example.com/image.jpg)</td></tr>
              <tr><td><strong>Code</strong></td><td>`inline code`<br>```<br>code block<br>```</td></tr>
              <tr><td><strong>Quotes</strong></td><td>> This is a quote</td></tr>
              <tr><td><strong>Horizontal Rule</strong></td><td>---</td></tr>
            </table>
          </div>
        </span>
            <?php else: ?>
                <span class="html-help">HTML tags are allowed for formatting.</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Save Post</button>
        <a href="?page=simplefeed&action=list" class="btn-secondary">Cancel</a>
        <?php if($post['slug']): ?>
            <button type="button" id="previewBtn" class="btn-preview">Preview</button>
        <?php endif; ?>
    </div>
</form>
