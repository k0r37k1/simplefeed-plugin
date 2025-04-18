<?php
defined('INC_ROOT') || die;
global $Wcms;

/**
 * Search Box for SimpleFeed
 * Include this template wherever you want to show the search box
 * 
 * Example usage in theme.php:
 * <?php include __DIR__ . '/plugins/simplefeed/templates/search.php'; ?>
 */
?>

<div class="sf-search-container">
    <form action="<?php echo $Wcms->url('?page=simplefeed&action=search'); ?>" method="get" class="sf-search-form">
        <input type="hidden" name="page" value="simplefeed">
        <input type="hidden" name="action" value="search">
        <div class="sf-search-input-group">
            <input type="text" name="q" placeholder="Search posts..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="sf-search-input">
            <button type="submit" class="sf-search-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </div>
    </form>
</div>
