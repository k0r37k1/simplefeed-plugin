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
    <div class="sf-alert sf-alert-<?php echo htmlspecialchars($_SESSION['feedbackType'] ?? 'success'); ?>">
        <?php echo $_SESSION['feedbackMessage']; ?>
    </div>
    <?php unset($_SESSION['feedbackMessage'], $_SESSION['feedbackType']); ?>
<?php endif; ?>

<form method="post" class="sf-edit-form" id="postForm">
    <!-- Use WonderCMS token -->
    <input type="hidden" name="token" value="<?php echo $Wcms->getToken(); ?>">
    <input type="hidden" name="original_slug" value="<?php echo htmlspecialchars($Wcms->stripTags($post['slug'])); ?>">

    <div class="sf-form-group">
        <label for="title">Title:<span class="sf-required">*</span></label>
        <input id="title" name="title" class="sf-form-control" value="<?php echo htmlspecialchars($Wcms->stripTags($post['title'])); ?>" required>
        <div class="sf-help">The title of your post.</div>
    </div>

    <div class="sf-form-group">
        <label for="date">Date:<span class="sf-required">*</span></label>
        <input type="date" id="date" name="date" class="sf-form-control" value="<?php echo htmlspecialchars($Wcms->stripTags($post['date'])); ?>" required>
        <div class="sf-help">Publication date in YYYY-MM-DD format.</div>
    </div>

    <div class="sf-form-group">
        <label for="short">Short Preview:</label>
        <textarea id="short" name="short" class="sf-form-control" rows="3"><?php echo htmlspecialchars($Wcms->stripTags($post['short'])); ?></textarea>
        <div class="sf-help">A short preview text shown in the feed list.</div>
    </div>

    <div class="sf-form-group">
        <label for="image">Image URL:</label>
        <input id="image" name="image" class="sf-form-control" value="<?php echo htmlspecialchars($Wcms->stripTags($post['image'])); ?>">
        <div class="sf-help">URL to an image (optional).</div>
    </div>

    <div class="sf-form-group">
        <label for="tags">Tags (comma separated):</label>
        <input id="tags" name="tags" class="sf-form-control" value="<?php echo htmlspecialchars($Wcms->stripTags(implode(',', $post['tags']))); ?>">
        <div class="sf-help">Enter tags separated by commas, e.g., news,update,important</div>
    </div>

    <div class="sf-form-group">
        <label for="author">Author:</label>
        <input id="author" name="author" class="sf-form-control" value="<?php echo htmlspecialchars($Wcms->stripTags($post['author'])); ?>">
        <div class="sf-help">The author's name.</div>
    </div>

    <div class="sf-form-group">
        <label for="content">Content:</label>
        <div class="sf-content-format-toggle">
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
        <textarea id="content" name="content" class="sf-form-control" rows="15"><?php
            // Preserve all formatting in the content when editing
            echo htmlspecialchars($post['content']);
            ?></textarea>
        <div class="sf-help" id="formatHelp">
            <?php if (!isset($post['use_markdown']) || $post['use_markdown']): ?>
                <span class="sf-markdown-help">
          <strong>Markdown formatting:</strong>
          **bold**, *italic*, [link](url), # Header, - list item, ```code```
          <a href="#" id="toggleMarkdownHelp">Show more</a>
          <div id="markdownHelpExpanded" style="display:none;">
            <table class="sf-markdown-cheatsheet">
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

    <div class="sf-form-actions">
        <button type="submit" class="sf-btn sf-btn-primary">Save Post</button>
        <a href="?page=simplefeed&action=list" class="sf-btn sf-btn-secondary">Cancel</a>
        <?php if($post['slug']): ?>
            <button type="button" id="previewBtn" class="sf-btn sf-btn-info">Preview</button>
        <?php endif; ?>
    </div>
</form>
