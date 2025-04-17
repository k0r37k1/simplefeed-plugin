<?php
defined('INC_ROOT') || die;

/**
 * Generates URL-friendly slug from title.
 * Improved to better handle non-Latin characters and special cases.
 */
function sf_generateSlug(string $title): string {
    // Transliterate non-latin characters to closest ASCII equivalent
    $slug = transliterator_transliterate('Any-Latin; Latin-ASCII', $title);
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Ensure we have a valid slug, even with unusual inputs
    return !empty($slug) ? $slug : 'post-' . substr(md5($title . time()), 0, 8);
}

/**
 * Loads all posts from data/*.json, sorted by date.
 */
function sf_loadPosts(): array {
    $dataPath = __DIR__ . '/../data';
    if (!is_dir($dataPath)) {
        mkdir($dataPath, 0755, true);
        return [];
    }
    
    $files = glob($dataPath . '/*.json');
    $posts = [];
    foreach ($files as $f) {
        // Only load files that match our pattern and are valid json
        if (!preg_match('/\/([a-z0-9-]+)\.json$/', $f)) continue;
        
        $content = @file_get_contents($f);
        if (!$content) continue;
        
        $j = json_decode($content, true);
        if ($j && isset($j['slug'], $j['title'], $j['date'])) {
            $posts[] = $j;
        }
    }
    
    usort($posts, fn($a,$b) => strtotime($b['date'] ?? 'now') - strtotime($a['date'] ?? 'now'));
    return $posts;
}

/**
 * Validates post data before saving
 */
function sf_validatePost(array $post): array {
    $errors = [];
    
    // Required fields
    if (empty($post['title'])) {
        $errors[] = 'Title is required';
    }
    
    // Date validation
    if (empty($post['date']) || !strtotime($post['date'])) {
        $errors[] = 'Valid date is required';
    }
    
    // Make sure all expected fields exist
    $post = array_merge([
        'slug' => '',
        'title' => '',
        'date' => date('Y-m-d'),
        'short' => '',
        'image' => '',
        'author' => '',
        'content' => '',
        'tags' => []
    ], $post);
    
    return ['post' => $post, 'errors' => $errors];
}

/**
 * Generates a CSRF token for form protection
 */
function sf_getCSRFToken(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    if (empty($_SESSION['sf_csrf_token'])) {
        $_SESSION['sf_csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['sf_csrf_token'];
}

/**
 * Verifies a CSRF token from a form submission
 */
function sf_verifyCSRFToken(string $token): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $validToken = $_SESSION['sf_csrf_token'] ?? '';
    
    // Use constant time comparison to prevent timing attacks
    return !empty($validToken) && hash_equals($validToken, $token);
}

/**
 * Sanitizes content for safe output in HTML contexts
 */
function sf_sanitizeHTML(string $content): string {
    // Basic sanitization - for production, consider using a proper HTML purifier library
    $allowed_tags = '<p><br><b><i><u><strong><em><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><pre><code>';
    return strip_tags($content, $allowed_tags);
}

/**
 * Securely saves a post file with proper validations
 */
function sf_savePost(array $postData): array {
    // Validate post data
    $result = sf_validatePost($postData);
    if (!empty($result['errors'])) {
        return $result;
    }
    
    $post = $result['post'];
    
    // For new posts, generate slug from title
    if (empty($post['slug'])) {
        $post['slug'] = sf_generateSlug($post['title']);
    }
    
    // Sanitize tags: remove empty tags, trim each tag
    $post['tags'] = array_filter(array_map('trim', is_array($post['tags']) ? $post['tags'] : 
        explode(',', $post['tags'] ?? '')));
    
    // Prepare data directory
    $dataPath = __DIR__ . '/../data';
    if (!is_dir($dataPath)) {
        if (!mkdir($dataPath, 0755, true)) {
            return ['post' => $post, 'errors' => ['Could not create data directory']];
        }
    }
    
    // Save the post
    $filePath = $dataPath . '/' . $post['slug'] . '.json';
    $success = file_put_contents($filePath, json_encode($post, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($success === false) {
        return ['post' => $post, 'errors' => ['Failed to save post file']];
    }
    
    return ['post' => $post, 'errors' => []];
}

/**
 * Safely deletes a post file
 */
function sf_deletePost(string $slug): bool {
    // Validate slug format for security
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        return false;
    }
    
    $filePath = __DIR__ . '/../data/' . $slug . '.json';
    
    if (file_exists($filePath) && is_file($filePath)) {
        return unlink($filePath);
    }
    
    return false;
}
