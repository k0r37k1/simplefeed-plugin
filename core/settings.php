<?php
defined('INC_ROOT') || die;

/**
 * Lädt Default-Config und überschreibt mit data/settings.json
 */
function sf_getConfig(): array {
    $default = include __DIR__ . '/../config.php';
    $path = __DIR__ . '/../data/settings.json';
    if (file_exists($path)) {
        $custom = json_decode(file_get_contents($path), true);
        if (is_array($custom)) return array_merge($default, $custom);
    }
    return $default;
}
