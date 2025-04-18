<?php
defined('INC_ROOT') || die;
global $Wcms;

/**
 * Get plugin directory path
 *
 * @return string Plugin directory path
 */
function sf_getPluginPath(): string {
    return __DIR__ . '/..';
}

/**
 * Get plugin data directory path
 *
 * @return string Plugin data directory path
 */
function sf_getDataPath(): string {
    return sf_getPluginPath() . '/data';
}

/**
 * Generate URL-friendly slug from title.
 * Supports non-latin characters through transliteration.
 *
 * @param string $title The title to convert to a slug
 * @return string URL-friendly slug
 */
function sf_generateSlug(string $title): string {
    // Transliterate non-latin characters
    $slug = sf_transliterateName($title);
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Transliterate non-latin characters to latin equivalents
 *
 * @param string $str String to transliterate
 * @return string Transliterated string
 */
function sf_transliterateName($str) {
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
 * Load all posts from data/*.json, sorted by date.
 *
 * @return array Array of posts
 */
function sf_loadPosts(): array {
    global $Wcms;
    $dataPath = sf_getDataPath();

    // Create data directory if it doesn't exist
    if (!is_dir($dataPath)) {
        if (!mkdir($dataPath, 0755, true)) {
            if (method_exists($Wcms, 'log')) {
                $Wcms->log('SimpleFeed: Failed to create data directory', 'danger');
            }
            return [];
        }
    }

    // Get all JSON files
    $files = glob($dataPath . '/*.json');
    if (!$files) return [];

    $posts = [];
    foreach ($files as $f) {
        // Skip settings file
        if (basename($f) === 'settings.json') continue;

        // Verify file is within the data directory (prevent path traversal)
        if (strpos(realpath($f), realpath($dataPath)) !== 0) {
            if (method_exists($Wcms, 'log')) {
                $Wcms->log('SimpleFeed: Security warning - attempted access to file outside data directory', 'danger');
            }
            continue;
        }

        // Read file securely
        $content = sf_safeReadFile($f);
        if (!$content) continue;

        // Parse JSON
        $j = json_decode($content, true);
        if ($j && isset($j['slug'], $j['title'], $j['date'])) {
            $posts[] = $j;
        }
    }

    // Sort by date (descending)
    usort($posts, function($a, $b) {
        return strtotime($b['date'] ?? 'now') - strtotime($a['date'] ?? 'now');
    });
    
    return $posts;
}

/**
 * Validates post data before saving
 *
 * @param array $post Post data to validate
 * @return array Array of validation errors
 */
function sf_validatePost(array $post): array {
    $errors = [];

    // Title is required
    if (empty($post['title'])) {
        $errors[] = 'Title is required';
    }

    // Valid date is required
    if (empty($post['date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $post['date'])) {
        $errors[] = 'Valid date (YYYY-MM-DD) is required';
    }

    // Image URL validation if provided
    if (!empty($post['image']) && filter_var($post['image'], FILTER_VALIDATE_URL) === false) {
        $errors[] = 'Image URL must be a valid URL';
    }

    return $errors;
}

/**
 * Safe file read with error handling
 *
 * @param string $path File path
 * @param bool $json Whether to parse as JSON
 * @return mixed File contents or empty string/array on error
 */
function sf_safeReadFile(string $path, bool $json = false) {
    global $Wcms;

    // Verify file exists
    if (!file_exists($path)) {
        return $json ? [] : '';
    }

    // Verify path is inside plugin directory (prevent path traversal)
    $pluginPath = realpath(sf_getPluginPath());
    $realPath = realpath($path);

    if (!$realPath || strpos($realPath, $pluginPath) !== 0) {
        if (method_exists($Wcms, 'log')) {
            $Wcms->log('SimpleFeed: Security warning - attempted to read file outside plugin directory', 'danger');
        }
        return $json ? [] : '';
    }

    // Read file
    $content = @file_get_contents($path);
    if ($content === false) {
        return $json ? [] : '';
    }

    // Parse as JSON if requested
    if ($json) {
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    return $content;
}

/**
 * Safe file write with error handling
 *
 * @param string $path File path
 * @param mixed $data Data to write
 * @return bool Success
 */
function sf_safeWriteFile(string $path, $data): bool {
    global $Wcms;

    // Create directory if it doesn't exist
    $dir = dirname($path);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            if (method_exists($Wcms, 'log')) {
                $Wcms->log('SimpleFeed: Failed to create directory: ' . $dir, 'danger');
            }
            return false;
        }
    }

    // Check directory is writable
    if (!is_writable($dir)) {
        if (method_exists($Wcms, 'log')) {
            $Wcms->log('SimpleFeed: Directory not writable: ' . $dir, 'danger');
        }
        return false;
    }

    // Verify path is inside plugin directory (prevent path traversal)
    $pluginPath = realpath(sf_getPluginPath());
    $realDir = realpath($dir);

    if (!$realDir || strpos($realDir, $pluginPath) !== 0) {
        if (method_exists($Wcms, 'log')) {
            $Wcms->log('SimpleFeed: Security warning - attempted to write file outside plugin directory', 'danger');
        }
        return false;
    }

    // Prepare content
    if (is_array($data)) {
        $content = json_encode($data, JSON_PRETTY_PRINT);
    } else {
        $content = (string)$data;
    }

    // Write file with exclusive lock
    $result = @file_put_contents($path, $content, LOCK_EX);
    if ($result === false) {
        if (method_exists($Wcms, 'log')) {
            $Wcms->log('SimpleFeed: Failed to write file: ' . basename($path), 'danger');
        }
        return false;
    }

    return true;
}
