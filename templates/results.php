<?php
defined('INC_ROOT') || die;
global $Wcms;

// Get search query
$query = isset($_GET['q']) ? $Wcms->stripTags($_GET['q']) : '';

// Initialize results array
$results = [];

// Perform search if query is not empty
if (!empty($query)) {
    // Search in posts
    foreach ($posts as $post) {
        $title = $post['title'] ?? '';
        $content = $post['content'] ?? '';
        $short = $post['short'] ?? '';
        $searchableContent = $title . ' ' . $short . ' ' . strip_tags($content);
        
        // If post matches search query
        if (stripos($searchableContent, $query) !== false) {
            // Create excerpt
            $excerpt = strip_tags($content);
            $position = stripos($excerpt, $query);
            
            // Create contextual excerpt
            if ($position !== false) {
                $start = max(0, $position - 50);
                $length = min(200, strlen($excerpt) - $start);
                $excerpt = substr($excerpt, $start, $length);
                
                // Add ellipsis if needed
                if ($start > 0) {
                    $excerpt = '...' . $excerpt;
                }
                if ($start + $length < strlen(strip_tags($content))) {
                    $excerpt .= '...';
                }
                
                // Highlight the query
                $excerpt = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="sf-search-highlight">$1</span>', $excerpt);
            } else {
                // Fallback to first 150 characters if query not found in content
                $excerpt = substr($excerpt, 0, 150) . '...';
            }
            
            // Add to results
            $results[] = [
                'title' => $post['title'],
                'excerpt' => $excerpt,
                'url' => $Wcms->url('?page=simplefeed&action=post&slug=' . $post['slug']),
                'date' => $post['date']
            ];
        }
    }
}
?>

<div class="sf-feed-container">
    <h2>Search Results</h2>
    
    <?php include __DIR__ . '/search.php'; ?>
    
    <?php if (empty($query)): ?>
        <div class="sf-search-initial">
            <p>Enter a search term above to find posts.</p>
        </div>
    <?php elseif (empty($results)): ?>
        <div class="sf-search-no-results">
            <p>No results found for <strong>"<?php echo htmlspecialchars($query); ?>"</strong>.</p>
            <p>Please try different keywords or check your spelling.</p>
        </div>
    <?php else: ?>
        <div class="sf-search-results">
            <p>Found <?php echo count($results); ?> result<?php echo count($results) === 1 ? '' : 's'; ?> for <strong>"<?php echo htmlspecialchars($query); ?>"</strong>:</p>
            
            <?php foreach ($results as $result): ?>
                <div class="sf-search-result-item">
                    <h3 class="sf-search-result-title">
                        <a href="<?php echo $result['url']; ?>">
                            <?php echo htmlspecialchars($result['title']); ?>
                        </a>
                    </h3>
                    <div class="sf-search-result-excerpt">
                        <?php echo $result['excerpt']; ?>
                    </div>
                    <div class="sf-search-meta">
                        <?php echo date($config['date_format'] ?? 'd.m.Y', strtotime($result['date'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="sf-back-to-feed">
        <a href="<?php echo $Wcms->url('?page=simplefeed'); ?>" class="sf-button">
            ← Back to Feed
        </a>
    </div>
</div>
