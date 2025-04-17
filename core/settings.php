<?php
defined('INC_ROOT') || die;

/**
 * Loads default config and overrides with data/settings.json
 * 
 * @return array Combined configuration
 */
function sf_getConfig(): array {
    global $Wcms;
    
    // Load default config
    $default = include __DIR__ . '/../config.php';
    
    // Path to custom settings
    $path = __DIR__ . '/../data/settings.json';
    
    // Securely check and read file
    if (file_exists($path)) {
        // Use safe file reading instead of direct file_get_contents
        $content = @file_get_contents($path);
        
        if ($content !== false) {
            $custom = json_decode($content, true);
            
            // Validate the decoded data is an array
            if (is_array($custom)) {
                return array_merge($default, $custom);
            }
            
            // Log error if JSON could not be parsed correctly
            if (method_exists($Wcms, 'log')) {
                $Wcms->log('SimpleFeed: Invalid settings.json format', 'danger');
            }
        }
    }
    
    return $default;
}
