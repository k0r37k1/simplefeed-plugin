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

<style>
/* Search Form Styles */
.sf-search-container {
    margin: 20px 0;
    max-width: 100%;
}

.sf-search-form {
    display: flex;
    width: 100%;
}

.sf-search-input-group {
    position: relative;
    display: flex;
    width: 100%;
}

.sf-search-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px 0 0 4px;
    font-size: 16px;
    outline: none;
    transition: border-color 0.3s;
}

.sf-search-input:focus {
    border-color: #4a89dc;
}

.sf-search-button {
    background-color: #4a89dc;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
    transition: background-color 0.3s;
}

.sf-search-button:hover {
    background-color: #3a79cc;
}

/* Search Results Styles */
.sf-search-results {
    margin-top: 20px;
}

.sf-search-result-item {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.sf-search-result-item:last-child {
    border-bottom: none;
}

.sf-search-result-title {
    margin-top: 0;
    margin-bottom: 10px;
}

.sf-search-result-title a {
    color: #333;
    text-decoration: none;
}

.sf-search-result-title a:hover {
    text-decoration: underline;
}

.sf-search-result-excerpt {
    margin-bottom: 10px;
    color: #555;
}

.sf-search-highlight {
    background-color: rgba(74, 137, 220, 0.2);
    padding: 0 2px;
    border-radius: 2px;
}

.sf-search-meta {
    color: #777;
    font-size: 14px;
}

.sf-search-no-results {
    text-align: center;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 4px;
    margin-top: 20px;
}
</style>
