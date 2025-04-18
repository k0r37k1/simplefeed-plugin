<?php
defined('INC_ROOT') || die;

/**
 * Loads default config and overrides with data/settings.json
 *
 * @return array Combined configuration
 */
function sf_getConfig(): array {
    global $Wcms;
    
    // Get plugin directory path (defined in functions.php)
    if (function_exists('sf_getPluginPath')) {
        $pluginPath = sf_getPluginPath();
    } else {
        $pluginPath = __DIR__ . '/..';
    }

    // Load default config
    $default = include $pluginPath . '/config.php';

    // Path to custom settings
    $settingsPath = $pluginPath . '/data/settings.json';

    // Securely check and read file
    if (file_exists($settingsPath)) {
        // Use safe file reading
        if (function_exists('sf_safeReadFile')) {
            $content = sf_safeReadFile($settingsPath);
        } else {
            $content = @file_get_contents($settingsPath);
        }

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
