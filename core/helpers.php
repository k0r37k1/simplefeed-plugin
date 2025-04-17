<?php
defined('INC_ROOT') || die;

/**
 * Erzeugt URL‑freundlichen Slug aus Titel.
 * Unterstützt auch nicht-lateinische Zeichen durch Transliteration.
 */
function sf_generateSlug(string $title): string {
    // Transliterate non-latin characters
    $slug = transliterateName($title);
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Transliterate non-latin characters to latin equivalents
 */
function transliterateName($str) {
    $transliterationTable = [
        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
        'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue',
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'å' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ç' => 'c'
    ];
    
    return str_replace(array_keys($transliterationTable), array_values($transliterationTable), $str);
}

/**
 * Lädt alle Posts aus data/*.json, sortiert nach Datum.
 */
function sf_loadPosts(): array {
    $dataPath = __DIR__ . '/../data';
    
    if (!is_dir($dataPath)) {
        mkdir($dataPath, 0755, true);
        return [];
    }
    
    $files = glob($dataPath . '/*.json');
    if (!$files) return [];
    
    $posts = [];
    foreach ($files as $f) {
        if (basename($f) === 'settings.json') continue;
        
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
    
    if (empty($post['title'])) {
        $errors[] = 'Title is required';
    }
    
    if (empty($post['date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $post['date'])) {
        $errors[] = 'Valid date (YYYY-MM-DD) is required';
    }
    
    return $errors;
}

/**
 * Sanitizes HTML content
 */
function sf_sanitizeHTML(string $html): string {
    // Basic sanitization - a more comprehensive solution would use
    // a proper HTML Purifier library
    $allowed_tags = '<p><br><h1><h2><h3><h4><h5><h6><ul><ol><li><a><strong><em><blockquote><pre><code><img><table><tr><td><th>';
    return strip_tags($html, $allowed_tags);
}

/**
 * Generate CSRF token
 */
function sf_generateCSRFToken(): string {
    if (!isset($_SESSION['sf_csrf_token'])) {
        $_SESSION['sf_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['sf_csrf_token'];
}

/**
 * Validate CSRF token
 */
function sf_validateCSRFToken(?string $token): bool {
    if (empty($_SESSION['sf_csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['sf_csrf_token'], $token);
}

/**
 * Safe file read with error handling
 */
function sf_safeReadFile(string $path, bool $json = false) {
    if (!file_exists($path)) {
        return $json ? [] : '';
    }
    
    $content = @file_get_contents($path);
    if ($content === false) {
        return $json ? [] : '';
    }
    
    if ($json) {
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
    
    return $content;
}

/**
 * Safe file write with error handling
 */
function sf_safeWriteFile(string $path, $data): bool {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            return false;
        }
    }
    
    if (is_array($data)) {
        $content = json_encode($data, JSON_PRETTY_PRINT);
    } else {
        $content = (string)$data;
    }
    
    return @file_put_contents($path, $content) !== false;
}
